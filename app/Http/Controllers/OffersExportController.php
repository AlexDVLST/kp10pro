<?php

// v 0.95
// TODO цвет кнопок
// TODO убрать лишний парсинг по html
// TODO нет валют

// Название вариантов
// 2
//

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Offer;
use PDF;
use TCPDF_STATIC;
use TCPDF_FONTS;
use Htmldom;
use Storage;
use Excel;
use PHPExcel_Worksheet_Drawing;
use PHPExcel_RichText;
use PHPExcel_Style_Fill;
use Products;
use App\Models\Product;
use PHPExcel_Style_Alignment;
use App\Helpers\StorageHelper;
use Intervention\Image\Facades\Image;
use App\Models\User;
use App\Models\Order;

class OffersExportController extends Controller
{
    public function variants($offer)
    {
        // Список товаров
        $variants = $offer->variants;

        $variants->each(function ($variantValue, $varianKey) {

            if ( ! $variantValue->active) {
                return true;
            }

            if ($variantValue->products) {
                // Variant Data
                $totalCost                = 0;
                $totalCostWithoutDiscount = 0;
                $discountEnabled          = 0;
                $groupCosts               = [];
                $groupId                  = 0;
                $currentGroupId           = 0;

                $variantValue->costWithDiscount    = 0;
                $variantValue->costWithoutDiscount = 0;

                // Перебиваем товары:
                $variantValue->products->each(function ($product, $productKey) use (
                    &$discountEnabled,
                    &$totalCost,
                    &$totalCostWithoutDiscount,
                    &$groupCosts,
                    &$groupId,
                    &$products,
                    &$variantValue,
                    &$groupTotalCost,
                    $varianKey,
                    &$currentGroupId
                ) {

                    // Product Data
                    $count      = 0;
                    $discount   = 0;
                    $price      = 0;
                    $goodsValue = 1;

                    if ($product->group) {
                        //$groupTotalCost = 0;
                        $groupId        = $product->id;
                        $currentGroupId = $productKey;
                    }

                    if ( ! $product->group && $product->values) {
                        $product->values->each(function ($value, $valueKey) use (
                            &$price,
                            &$count,
                            &$discount,
                            &$goodsValue,
                            &$discountEnabled,
                            &$totalCost,
                            &$totalCostWithoutDiscount
                        ) {
                            switch ($value->type) {
                                case 'price':
                                    $price += (float) str_replace(" ", "", $value->value);
                                    break;
                                case 'count':
                                    $count = str_replace(" ", "", $value->value);

                                    break;
                                case 'discount':
                                    $discount        = str_replace(" ", "", $value->value);
                                    $discountEnabled = true;
                                    break;
                                case 'good-coll':
                                    if ($value->value_in_price) {
                                        $goodsValue *= (float) str_replace(" ", "", $value->value);
                                    }
                                    break;
                            }
                        });
                    }

                    $cost = ($count * $price * $goodsValue);

                    if ($discountEnabled && $discount) {
                        $cost -= $cost * $discount * 0.01;
                    }

                    $totalCost += $cost;
                    
                    if ($discountEnabled) {
                        $totalCostWithoutDiscount += $price * $count * $goodsValue;
                    }

                    if ($groupId) {
                        if (isset($variantValue->products[0])) {
                            //$groupTotalCost += $cost;
                            $variantValue->products[$currentGroupId]->totalCost += $cost;
                        } else {
                            //$groupTotalCost = $cost;
                            $variantValue->products[$currentGroupId]->totalCost = $cost;
                        }
                    }

                    $variantValue->costWithDiscount    += $cost;
                    $variantValue->costWithoutDiscount = $totalCostWithoutDiscount;

                });

                //Special discount
                if($variantValue->specialDiscounts){
                    $variantValue->specialDiscounts->each(function($discount) use (&$variantValue){
                        $variantValue->costWithDiscount -= $discount->value;
                    });
                }
            }

        });

        return $variants;
    }

    public function exportExcel($url)
    {
        $maxRowsCount = 0;

        $cellsArray = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ',
            'BA',
            'BB',
            'BC',
            'BD',
            'BE',
            'BF',
            'BG',
            'BH',
            'BI',
            'BJ',
            'BK',
            'BL',
            'BM',
            'BN',
            'BO',
            'BP',
            'BR',
            'BS',
            'BT',
            'BU',
            'BV',
            'BW',
            'BX',
            'BY',
            'BZ',
            'CA',
            'CB',
            'CC',
            'CD',
            'CE',
            'CF',
            'CG',
            'CH',
            'CI',
            'CJ',
            'CK',
            'CL',
            'CM',
            'CN',
            'CO',
            'CP',
            'CR',
            'CS',
            'CT',
            'CU',
            'CV',
            'CW',
            'CX',
            'CY',
            'CZ'
        );

        // Get offer Data
        $offer = offer::with('variants.products.product', 'variants.products.values', 'variants.fields',
            'variants.specialDiscounts', 'currency.data.system')->whereUrl($url)->first();

        if ( ! $offer) {
            abort(403);
        }

        // Получаем иконку валюты
        $charCode = null;
        if(isset($offer->currency->data->system->code)){
            $offerCharCode = $offer->currency->data->system->char_code;
            $charCode = self::getCurrencyFonts(strtolower($offerCharCode));
        }

        $variantsProducts = self::variants($offer);

        // Offer variants
        $offerVariants = false;
        if (isset($offer->variants)) {
            $offerVariants = $offer->variants;
        }

        // Get offer URL. If offer file is not exist return false
        $offerUrl = $offer['url'];
        if ( ! file_exists(storage_path() . "/app/public/offers/" . $offerUrl . "/index.html")) {
            abort(403, 'Перед экспортированием коммерческого предложения в PDF или EXCEL сохраните его через редактор');
        }

        // Initialization of HtmlDom:
        $offerContent = file_get_contents(storage_path() . '/app/public/offers/' . $offerUrl . '/index.html');
        $baBer        = new \Htmldom();
        $htmlDom      = $baBer->str_get_html($offerContent);

        // Name of the excel file:
        $offer    = Offer::whereUrl($url)->get()->first();
        $fileName = "КП№ " . $offer->offer_name . " от " . date('d-m-y');
        $fileName = self::rus2translit($fileName);

        // CREATE EXCEL

        return Excel::create('products',
            function ($excel) use ($fileName, $htmlDom, $offerUrl, $cellsArray, $variantsProducts, $charCode) {
                $excel->setTitle($fileName);
                $excel->sheet('mySheet',
                    function ($sheet) use ($htmlDom, $offerUrl, $cellsArray, $variantsProducts, &$maxRowsCount, $charCode) {

                        foreach (range('D', 'Z') as $columnID) {
                            $sheet->getColumnDimension($columnID)->setAutoSize(true);
                        }

                        $cells = $sheet->getStyle('A1:O900');
                        $cells->getFont()->applyFromArray(array(
                            'name'  => 'Arial',
                            'color' => array('rgb' => '333333')
                        ));
                        $sheet->setShowGridlines(false);
                        $cells->getFont()->setSize(14);

                        $sheet->setWidth(array(
                            'A' => 3,
                            'B' => 10,
                            'C' => 45
                        ));

                        // Offer Title
                        $str = 3;
                        $sheet->setCellValueByColumnAndRow(1, 2, "Коммерческое предложение");
                        $sheet->getRowDimension(2)->setRowHeight(25);
                        $cells = $sheet->getStyle('B2');
                        $cells->getFont()->setSize(24);
                        $cells->getAlignment()->setWrapText(false);
                        $sheet->setCellValueByColumnAndRow(1, $str, "Открыть в браузере с фото");

                        // Link to offer
                        $sheet->getCellByColumnAndRow(1,
                            $str)->getHyperlink()->setUrl(env('APP_PROTOCOL') . env('APP_DOMAIN') . "/" . $offerUrl);
                        $sheet->getRowDimension($str)->setRowHeight(20);
                        $cells = $sheet->getStyle('B' . $str);
                        $cells->getFont()->setUnderline(true);
                        $cells->getFont()->getColor()->applyFromArray(array('rgb' => '5b9bd5'));
                        $cells->getAlignment()->setWrapText(false);
                        $cells->getFont()->setSize(14);

                        /**
                         * Get and draw Logo
                         */

                        $logo = $htmlDom->find('.logo', 0);
                        if ($logo !== null) {
                            $logo = $logo->src;

                            //                    $logo = "http://megaplan.kp10.ru/storage/megaplan/Сотрудники/b3d155da0087c2c019917e6cb85f1aa8.png";
                            //                    $pos = strpos($logo, "http");
                            //                    if ($pos !== false) {
                            //                        $logo = explode("/storage", $logo);
                            //                        if(isset($logo[1])){
                            //                            $logo = '/storage'.$logo[1];
                            //                        }
                            //                    }

                            $imgUrl = str_replace('storage', 'public', $logo);
                            if (Storage::exists($imgUrl)) {
                                $objDrawing = new PHPExcel_Worksheet_Drawing;
                                $logo       = str_replace('storage', 'app/public', $logo);
                                $objDrawing->setPath(storage_path() . $logo);
                                $objDrawing->setWorksheet($sheet);
                                $objDrawing->setCoordinates('D2');
                                $objDrawing->setResizeProportional(true);
                                $objDrawing->setHeight(70);
                            }
                        }

                        /**
                         * Offer Details
                         */

                        $cpDetailsCells = $htmlDom->find('.cp-details-about .row');
                        $detailsArray   = array();
                        if (is_array($cpDetailsCells) and $cpDetailsCells != "") {
                            foreach ($cpDetailsCells as $value) {
                                $detailsArray[] = array(
                                    trim($value->find('.cp-details-about-cell', 0)->plaintext),
                                    trim($value->find('.cp-details-about-cell', 1)->plaintext)
                                );
                            }
                        }
                        if (is_array($detailsArray) and $detailsArray != "") {
                            foreach ($detailsArray as $detail) {
                                $str         = $str + 1;
                                $objRichText = new PHPExcel_RichText();
                                $objRichText->createText('');
                                $objBold = $objRichText->createTextRun(stripslashes($detail[0]) . ": ");
                                $objBold->getFont()->setBold(true);
                                $objBold->getFont()->applyFromArray(array(
                                    'name'  => 'Arial',
                                    'color' => array('rgb' => '333333')
                                ));
                                $objBold->getFont()->setSize(14);
                                $objNotBold = "";
                                $objNotBold = $objRichText->createTextRun(stripslashes($detail[1]));
                                $objNotBold->getFont()->applyFromArray(array(
                                    'name'  => 'Arial',
                                    'color' => array('rgb' => '333333')
                                ));
                                $objNotBold->getFont()->setSize(14);
                                $sheet->setCellValueByColumnAndRow(1, $str, $objRichText);
                                $sheet->getStyle('B' . $str)->getAlignment()->setWrapText(false);
                                $sheet->getRowDimension($str)->setRowHeight(20);
                            }
                        }
                        $str = $str + 1;
                        $str += 2;

                        /**
                         * Product Data Table
                         */

                        if ($variantsProducts) {


                            // Перебиваем товары:
                            // $str - номер следующей строки
                            $variantsProducts->each(function ($varianValue, $varianKey) use (
                                $sheet,
                                &$str,
                                $cellsArray,
                                &$maxRowsCount,
                                $charCode
                            ) {

                                if ($varianValue->active == 0) {
                                    return true;
                                }

                                /**
                                 * Название варианта
                                 */

                                if ($varianValue->name) {  // Получаем название варианта
                                    $variantName = trim($varianValue->name);
                                } else {
                                    $variantName = "Вариант";
                                }
                                $sheet->setCellValueByColumnAndRow(1, $str,
                                    $variantName);  // Экспортируем название варианта в ексель
                                $sheet->getRowDimension($str)->setRowHeight(25);
                                $sheet->getStyle('B' . $str)->getFont()->setBold(false);
                                $sheet->getStyle('B' . $str)->getFont()->setSize(24);
                                $str += 1;

                                /**
                                 * Заголовки полей таблицы товаров
                                 */

                                $str += 1;
                                $sheet->setCellValueByColumnAndRow(1, $str, "Артикул"); // Артикул

                                if ($varianValue->fields) {

//                                    $fields = $varianValue->fields->sortBy('index');

                                    $varianValue->fields->each(function ($fieldValue, $fieldKey) use (
                                        $sheet,
                                        $cellsArray,
                                        &$str,
                                        $charCode
                                    ) {
                                        $pos = $fieldValue->index;
                                        if($fieldValue->type == "cost" or $fieldValue->type=="price" or $fieldValue->type=="price-with-discount"){
                                            $fieldValue->name = $fieldValue->name . " ". $charCode;
                                        }
                                        $sheet->setCellValueByColumnAndRow($pos + 2, $str, $fieldValue->name);
                                        if (isset($cellsArray[$pos + 3])) {
                                            $sheet->getStyle($cellsArray[$pos + 3] . $str)->getAlignment()
                                                  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                        }
                                        if ($pos > 0) {
                                            if ($fieldValue->name) {
                                                $count = mb_strlen(trim($fieldValue->name));
                                                if (isset($cellsArray[$pos + 2])) {
                                                    $sheet->setWidth(array(
                                                        $cellsArray[$pos + 2] => $count + 6
                                                    ));
                                                }
                                            }
                                        }

                                    });
                                }

                                $sheet->getStyle('B' . $str . ':L' . $str)->getFont()->setBold(false);
                                $sheet->getStyle('B' . $str . ':L' . $str)->getFont()->setSize(13);
                                $sheet->getStyle('B' . $str . ':L' . $str)->getFont()->applyFromArray(array(
                                    'name'  => 'Arial',
                                    'color' => array('rgb' => '8c8c8c')
                                ));

                                /**
                                 * Перебираем товары
                                 */

                                $groupArray = [];
                                $rowsCount  = 0;

                                if ($varianValue->products) {

                                    $varianValue->products->each(function ($productValue, $productKey) use (
                                        $sheet,
                                        &$str,
                                        $cellsArray,
                                        &$groupArray,
                                        &$rowsCount,
                                        &$maxRowsCount,
                                        $charCode
                                    ) {

                                        $str += 1;

                                        // Это гпурра или нет (плашка)
                                        $group = 0;
                                        if ($productValue->group) {
                                            $group = $productValue->group;
                                        }

                                        // Подсчитуем количество столбцов для бекгроунда плашки
                                        $rowsCount = 1;

                                        $totalCost = 0;

                                        if ($group != 1) {
                                            if ($productValue->values->count() > $rowsCount) {
                                                $rowsCount = $productValue->values->count();
                                                if ($maxRowsCount < $rowsCount) {
                                                    $maxRowsCount = $rowsCount;
                                                }
                                            }
                                        } else {
                                            $groupArray[] = $str;
                                            $totalCost    = $productValue->totalCost;
                                        }

                                        // article
                                        if (isset($productValue->product->article)) {
                                            $sheet->setCellValueByColumnAndRow(1, $str,
                                                $productValue->product->article);
                                        }

                                        if ($productValue->values) {

                                            // Сортировка
                                            //$productValue->values = $productValue->values->sortBy('index');

                                            $productValue->values->each(function ($value, $tabKey) use (
                                                $group,
                                                $sheet,
                                                &$str,
                                                $totalCost,
                                                $cellsArray,
                                                $productValue,
                                                $charCode
                                            ) {
                                                $pos = $value->index;
                                                if ($group == 1) {
                                                    // Это группа
                                                    $sheet->setCellValueByColumnAndRow($pos + 1, $str,
                                                        $value->value . " ( " . number_format($totalCost, 2, '.','') . " ".$charCode." ) ");
                                                    $sheet->getRowDimension($str)->setRowHeight(20);
                                                    $sheet->getStyle('B' . $str . ':D' . $str)->getFont()->setBold(true);

                                                } else {
                                                    // Это не группа

                                                    $string = $value->value;

                                                    switch ($value->type) {
                                                        case 'price':
                                                            $string = str_replace(" ", "", $string);
                                                            $string = floatval($string);

                                                            $sheet->getStyle($cellsArray[$pos + 2] . $str)->getAlignment()
                                                                  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                                            break;
                                                        case 'count':
                                                            $string = floatval($string);

                                                            if ( ! isset($cellsArray[$pos + 2])) {
                                                                break;
                                                            }

                                                            $sheet->getStyle($cellsArray[$pos + 2] . $str)->getAlignment()
                                                                  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                                            break;
                                                        case 'discount':
                                                            $string = str_replace(" ", "", $string);
                                                            $string = floatval($string);
                                                            $string .= " %";

                                                            if ( ! isset($cellsArray[$pos + 2])) {
                                                                break;
                                                            }

                                                            $sheet->getStyle($cellsArray[$pos + 2] . $str)->getAlignment()
                                                                  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                                            break;
                                                        case 'cost':
                                                            $string = str_replace(" ", "", $string);
                                                            $string = floatval($string);

                                                            if ( ! isset($cellsArray[$pos + 2])) {
                                                                break;
                                                            }

                                                            $sheet->getStyle($cellsArray[$pos + 2] . $str)->getAlignment()
                                                                  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                                            break;
                                                        case 'good-coll':
                                                            break;
                                                        case 'price-with-discount':

                                                            $string = str_replace(" ", "", $string);
                                                            $string = floatval($string);
//
                                                            if ( ! isset($cellsArray[$pos + 2])) {
                                                                break;
                                                            }

                                                            $sheet->getStyle($cellsArray[$pos + 2] . $str)->getAlignment()
                                                                  ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                                            break;
                                                    }

                                                    $sheet->setCellValueByColumnAndRow($pos + 2, $str, $string);
                                                    $sheet->getRowDimension($str)->setRowHeight(25);
                                                }
                                            });
                                        }
                                    });

                                    // Background плашек
                                    if (is_array($groupArray)) {
                                        foreach ($groupArray as $group) {
                                            if (isset($cellsArray[$rowsCount + 1])) {
                                                $sheet->getStyle('B' . $group . ':' . $cellsArray[$rowsCount + 1] . $group)->getFill()
                                                      ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                                                      ->getStartColor()->setARGB('EEEEEEEE');
                                            }
                                        }
                                    }
                                }

                                if ($varianValue->price) {
                                    $variantPrice = $varianValue->price;
                                } else {
                                    $variantPrice = 0;
                                }

                                /**
                                 * Итого
                                 */

                                $str += 2;

                                $sheet->setCellValueByColumnAndRow(1, $str, 'Итого:');
                                $sheet->getStyle('B' . $str)->getFont()->setBold(true);


                                /**
                                 * Записываем цены //2
                                 */

                                // Цена со скидкой

                                if ($varianValue->costWithDiscount) {

                                    /**
                                     * Спец скидки для итоговой цены
                                     */
                                    if ($varianValue->specialDiscounts) {
                                        $varianValue->specialDiscounts->each(function ($discountValue, $discountKey) use
                                        (
                                            &$varianValue
                                        ) {
                                            $tempCost = floatval($varianValue->costWithDiscount);
                                            if ($discountValue->value) {
                                                $varianValue->costWithDiscount = $tempCost - $discountValue->value;
                                            }
                                            if ($varianValue->costWithDiscount < 0) {
                                                $varianValue->costWithDiscount = 0;
                                            }
                                        });
                                    }
                                    $sheet->setCellValueByColumnAndRow($rowsCount + 1, $str,
                                        number_format($varianValue->costWithDiscount,2,'.','')." ".$charCode);
                                    if (isset($cellsArray[$rowsCount + 1])) {
                                        $sheet->getStyle($cellsArray[$rowsCount + 1] . $str)->getFont()->setBold(true);
                                        $sheet->getStyle($cellsArray[$rowsCount + 1] . $str)->getAlignment()
                                              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                                    }
                                }

                                // Цена без скидки
                                if ($varianValue->costWithoutDiscount) { //
                                    $sheet->setCellValueByColumnAndRow($rowsCount, $str,
                                        number_format($varianValue->costWithoutDiscount,2,'.','')." ".$charCode);
                                    if (isset($cellsArray[$rowsCount])) {
                                        $sheet->getStyle($cellsArray[$rowsCount] . $str)->getFont()->setBold(true);
                                        $sheet->getStyle($cellsArray[$rowsCount] . $str)->getAlignment()
                                              ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

                                    }
                                }

                                if ($varianValue->specialDiscounts) {

                                    $varianValue->specialDiscounts->each(function ($discountValue, $discountKey) use (
                                        &$varianValue,
                                        &$str,
                                        $sheet,
                                        $charCode
                                    ) {
                                        $name  = "Скидка";
                                        $value = 0;
                                        $name  = $discountValue->name;
                                        $value = $discountValue->value;
                                        $str   += 1;
                                        $sheet->setCellValueByColumnAndRow(1, $str, $name . " ( " . number_format($value,2,'.','') . " ".$charCode." ) ");
                                        $sheet->getRowDimension($str)->setRowHeight(15);
                                        //$sheet->getStyle('B' . $str)->getFont()->setSize(10)->getStartColor()->setARGB('EEEEEEEE');;

                                        $sheet->getStyle('B' . $str)->getFont()->applyFromArray(array(
                                            'name'  => 'Arial',
                                            'color' => array('rgb' => '878787')
                                        ));
                                    });
                                }

                                // НДС
                                if (isset($varianValue->tax)) {
                                    $tax = $varianValue->tax;
                                    switch ($tax) {
                                        case "1":
                                            $str += 1;
                                            if (isset($varianValue->costWithDiscount)) {
                                                $nds = number_format((floatval($varianValue->costWithDiscount) / 1.18) * 0.18,
                                                    2, '.', '');
                                                $sheet->setCellValueByColumnAndRow(1, $str, 'Включая НДС : ' . number_format($nds,2,'.','').' '.$charCode );
                                                $sheet->getStyle('B' . $str)->getFont()->applyFromArray(array(
                                                    'name'  => 'Arial',
                                                    'color' => array('rgb' => '878787')
                                                ));
                                            }
                                            break;
                                        case "2":
                                            $str += 1;
                                            $sheet->setCellValueByColumnAndRow(1, $str, 'НДС НЕ ОБЛАГАЕТСЯ');
                                            $sheet->getStyle('B' . $str)->getFont()->applyFromArray(array(
                                                'name'  => 'Arial',
                                                'color' => array('rgb' => '878787')
                                            ));
                                            $str += 1;
                                            $sheet->setCellValueByColumnAndRow(1, $str,
                                                '(согласно п.2, ст.346.11 нк рф)');
                                            $sheet->getStyle('B' . $str)->getFont()->applyFromArray(array(
                                                'name'  => 'Arial',
                                                'color' => array('rgb' => '878787')
                                            ));
                                            break;
                                    }
                                }
                                $str += 2;
                            });

                        }

                        // Manager Sign and Manager Photo

                        $str += 1;

                        // Sign
                        $current_manager_sign = $htmlDom->find('.person__info-text', 0);
                        $current_manager_sign = trim($current_manager_sign->plaintext);

                        // Photo:

                        $manager_photo = false;

                        if ($htmlDom->find('.photo_medium', 0)) {
                            $manager_photo = $htmlDom->find('.photo_medium', 0)->src;

                            $pos = strpos($manager_photo, "http");
                            if ($pos !== false) {
                                $manager_photo = explode("/storage", $manager_photo);
                                if (isset($logo[1])) {
                                    $manager_photo = '/storage' . $manager_photo[1];
                                }
                            }

                            $issetImage = preg_split("/storage/", $manager_photo);
                            if (isset($issetImage[1])) {
                                if (Storage::exists("public/" . $issetImage[1])) {
                                    if (isset($cellsArray[$maxRowsCount])) {
                                        $objDrawing = new PHPExcel_Worksheet_Drawing();
                                        $objDrawing->setPath(public_path() . "/" . $manager_photo);
                                        $objDrawing->setCoordinates($cellsArray[$maxRowsCount] . $str);

//                                        $colWidth = $sheet->getColumnDimension($cellsArray[$maxRowsCount])->getWidth();
//                                        //dd($colWidth);
//                                        if ($colWidth == -1) {
//                                            $colWidthPixels = 64;
//                                        } else {
//                                            $colWidthPixels = $colWidth * 7.0017094;
//                                        }
//
//                                        $offsetX = $colWidthPixels - $objDrawing->getWidth();
//                                        $objDrawing->setOffsetX(444);

                                        $objDrawing->setWorksheet($sheet);
                                        $objDrawing->setResizeProportional(true);
                                        $objDrawing->setHeight(40);
                                    }
                                }
                            }
                        }

                        //    $logo = $htmlDom->find('.logo', 0);
                        //    if ($logo !== null) {
                        //        $logo = $logo->src;
                        //        $imgUrl = str_replace('storage', 'public', $logo);
                        //        if (Storage::exists($imgUrl)) {
                        //            try {
                        //                $objDrawing = new PHPExcel_Worksheet_Drawing;
                        //                $logo = str_replace('storage', 'app/public', $logo);
                        //                $objDrawing->setPath(storage_path() . $logo);
                        //                $objDrawing->setWorksheet($sheet);
                        //                $objDrawing->setCoordinates('D2');
                        //                $objDrawing->setResizeProportional(true);
                        //                $objDrawing->setHeight(70);
                        //            }
                        //            catch (Exception $e) {
                        //                echo $e->getTraceAsTring();
                        //            }
                        //        }
                        //    }


                        //Добавление текста подписи:
                        $sign_text = nl2br($current_manager_sign);
                        $sign_text = str_replace("<br />", "newline", $sign_text);
                        $pieces    = explode("newline", $sign_text);
                        if (is_array($pieces) and $pieces != "") {
                            foreach ($pieces as $value) {
                                $sheet->setCellValueByColumnAndRow(1 + $maxRowsCount, $str, $value);
                                $sheet->getRowDimension($str)->setRowHeight(20);
                                $str += 1;
                            }
                        }
                        $sheet->getStyle(5, $str)->getAlignment()->setWrapText(true);

                        $sheet->cells('A1:Z' . $str, function ($cells) {
                            $cells->setValignment('center');
                        });

                    });
            })->setFilename($fileName)->download();
    }

    /**
     * Export offer to PDF
     *
     * @param string $url
     * @return void
     */
    public function pdf($url, $fullExport = false)
    {
        $offer = offer::with(
            'variants.products.product', 
            'variants.products.values', 
            'variants.fields',
            'variants.specialDiscounts', 
            'currency.data.system'
            )->whereUrl($url)->first();

        //Offer not found
        if (!$offer) {
            abort(403);
        }
        //Html file not generated
        if (!Storage::exists('public/offers/' . $url . '/index.html')) {
            abort(404);
        }

        $user = User::whereId($offer->account_id)->first();
        //Check if order is paid
        $isPaidOrder = Order::first()->expired_at->isFuture();
        
        //For tmp converted gif
        $tmpGif = [];
        $userDomain = env('APP_PROTOCOL') . $user->domain . '.' . env('APP_DOMAIN');
        $webUrlOffer = $userDomain . '/' . $offer->url;
        
        $variants = $this->variants($offer);

        //Format price
        $variants->each(function($variant){
            $variant->costWithDiscount = number_format($variant->costWithDiscount, 0 , ' ',' ');
            $variant->costWithoutDiscount = number_format($variant->costWithoutDiscount, 0 , ' ',' ');

            $variant->products->each(function ($product){
                if($product->group){
                    $product->totalCost = number_format($product->totalCost, 0 , ' ',' ');
                }
            });

            //Special discount
            if($variant->specialDiscounts){
                $variant->specialDiscounts->each(function($discount) use (&$variantValue){
                    $discount->value = number_format($discount->value, 0 , ' ',' ');
                });
            }
        });

        $gjs_css        = $offer->gjs_css;
        $corporateColor = "#00a65a"; // Корпоративный цвет по умолчанию
        $gjs_css        = explode('.', $gjs_css);
        if (is_array($gjs_css)) {
            foreach ($gjs_css as $style) {
                $pos = strpos($style, "corporate-color");
                if ($pos !== false) {
                    $style = explode('corporate-color{color:#', $style);
                    if (isset($style[1])) {
                        $style = explode(' ', $style[1]);
                        if (isset($style)) {
                            $corporateColor = $style[0];
                        }
                    }
                }
            }
            $corporateColor = '#'.str_replace('#', '', $corporateColor);
        }
        //Offer curency 
        $currencyFont = null;
        if(isset($offer->currency->data->system->code)){
            $offerCharCode = $offer->currency->data->system->char_code;
            $currencyFont = self::getCurrencyFonts(strtolower($offerCharCode));
        }
        
        //Get html from file
        $HtmlDom = (new Htmldom())->load(Storage::get('public/offers/' . $url . '/index.html'));
        
        //Get cover
        $cover = $HtmlDom->find('.cover', 0);
        $logo = $HtmlDom->find('.logo', 0);
        //Get manager greeting
        $greeting = $HtmlDom->find('.message__tooltip', 0);
        $greeting =
            '<div style="margin:50px; min-height:300px;">
                <h3 style="line-height:12px;">' . trim($greeting->plaintext) . '</h3>
                <br><br>
            </div>';
        //Get manager photo and sign
        $manager = $HtmlDom->find('.photo_medium', 0);
        $managerSign = $HtmlDom->find('.person__info-text', 0)->innertext; 
        $managerImg = $this->pdfImageToGif($manager->src, $tmpGif);
        $mainChildren = $HtmlDom->find('main', 0)->children;        

        //Custom fonts
        $fontPtsans = TCPDF_FONTS::addTTFfont(public_path() . '/fonts/pdf/ptsans.ttf', 'TrueTypeUnicode', '', '96');
        $fontPtsansRegular = TCPDF_FONTS::addTTFfont(public_path() . '/fonts/pdf/ptsans_regular.ttf', 'TrueTypeUnicode', '', '96');
        
        PDF::SetTitle($offer->offer_name);
        PDF::SetHeaderMargin(0);
        PDF::SetFooterMargin(0);
        PDF::SetFont('dejavusans', '', 14);
        
        //For free version
        PDF::setFooterCallback(function($pdf) use($isPaidOrder){
            $pdf->SetY(-10);
            // Set font
            $pdf->SetFont('dejavusans', 'I', 10);
            $width = PDF::getPageWidth() / 2;
            $notPaidMessage = '';
            $pdf->SetTextColor(149, 149, 156);
            if(!$isPaidOrder){
                $pdf->Rect(0, PDF::getPageHeight() - 10, PDF::getPageWidth(), 10, 'F', [], [54, 127, 169]);
                $notPaidMessage = 'Создано при помощи сервиса https://kp10.pro';
                $pdf->SetTextColor(255, 255, 255);
            }

            $pdf->MultiCell($width - 10, 10, $notPaidMessage, 0, 'L', 0, 0, '', '', true, 0, false, true, 10, 'M');
            $pdf->MultiCell($width + 20, 10, 'Страница ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, 'R', 0, 0, '', '', true, 0, false, true, 10, 'M');
        });

        //New page
        PDF::AddPage();
        // //Default styles
        // PDF::writeHTML('<style>div,span,h1,h2{font-weight:normal;line-height: 12px;margin:0;padding:0;}</style>', true, false, true, false, '');
        //Remove top margin
        PDF::SetMargins(0, 0, 0);
        PDF::SetY(PDF::getY() - 10);
        //Cover
        PDF::writeHTML($cover->outertext, true, false, true, false, '');
        //Logo
        PDF::Image(public_path().$logo->src, 15, 18, 70, 0, '', false, '', false, 300);
        //Manager greeting message
        PDF::SetY(PDF::getY() - 5);
        PDF::SetMargins(10, 0, 10);
        PDF::SetFont($fontPtsans, 'I', 10);
        PDF::writeHTML($greeting, true, false, true, false, '');
        //Arrow down
        PDF::SetMargins(0, 0, 0);
        PDF::writeHTML('<img src="' . public_path() . '/pdf/pdf_01_2.png"><br>', true, false, true, false, '');
        //Manager sign
        PDF::SetFont($fontPtsansRegular, '', 12);
        PDF::SetFont('fontawesome', '', 20);
        PDF::writeHTMLCell(0, 0, 0, PDF::getY(), view('pdf.manager-sign', ['sign' => trim($managerSign), 'managerImg' => $managerImg, 'webUrlOffer' => $webUrlOffer, 'corporateColor' => $corporateColor]), 0, 1, 0, true, 'J', true);
        //Walk on child
        PDF::SetMargins(10, 10, 10);
        //Change position
        PDF::SetY(PDF::GetY() - 10);

        collect($mainChildren)->each(function($child) use ($HtmlDom, $offer, $variants, $managerSign, $managerImg, $currencyFont, 
            $fontPtsansRegular, $userDomain, $corporateColor, $fullExport, &$tmpGif){
            //Just text elements h1, h2, span, div
            $this->pdfText($child, $fontPtsansRegular);
            //Change position
            PDF::SetY(PDF::GetY() + 3);
            //Order detail
            if($child->attr['class'] == 'cp-details'){
                //Get all children
                collect($child->children[0]->children)->each(function($child) use ($fontPtsansRegular){
                    //Just text elements h1, h2, span, div
                    if($child->attr['class'] != 'container-fluid') {
                        if($child->tag == 'span'){
                            //Set new style
                            $child->setAttribute('style', 'font-weight:normal;font-size:8px;color:#959595;text-transform:uppercase;');
                            //Set font
                            PDF::SetFont($fontPtsansRegular, '', 8);
                            PDF::writeHTML($child->outertext, true, false, true, false, '');
                            //Change position
                            PDF::SetY(PDF::GetY() + 3);
                            return;
                        }
                        //Just text elements h1, h2, span, div
                        $this->pdfText($child, $fontPtsansRegular);
                        //Change position
                        PDF::SetY(PDF::GetY() + 3);
                    } else { //Table details
                        $details = $child->children[0]->children[0];
                        $title = $details->children[0];
                        //Title
                        PDF::SetFont($fontPtsansRegular, '', 12);
                        PDF::writeHTML('<h2 style="font-weight:normal">'.trim($title->innertext).'</h2>', true, false, true, false, '');
                        //Add space before table
                        PDF::SetY(PDF::GetY() + 5);
                        PDF::writeHTML(view('pdf.order-details', ['details' => $details->children[1]->children]), true, false, true, false, '');   
                    }
                });
            }
            //Variants container
            if ($child->attr['class'] == 'cp-options') {
                //If new page then show variants for current page
                if(PDF::getPage() == 1){
                    PDF::AddPage();
                }
                //Get title children
                collect($child->children[0]->children)->each(function($child) use ($fontPtsansRegular){
                    //Just text elements h1, h2, span, div
                    $this->pdfText($child, $fontPtsansRegular);
                    //Change position
                    PDF::SetY(PDF::GetY() + 3);
                });
                //Get variant description
                $variantDescription = $HtmlDom->find('.tab-pane-inner__description');
                $variantTabs = $HtmlDom->find('ul.nav-tabs li');
                
                //Variants
                if($variants && $variants->isNotEmpty()){
                    $variants->each(function($variant, $index) use ($variantDescription, $variantTabs, $managerImg, $currencyFont, $fullExport, 
                        $userDomain, $fontPtsansRegular, &$tmpGif){
                        //If variant disabled
                        if($variantTabs[$index]->class == 'disabled-variant'){
                            return;
                        }
                        //Remove maring
                        PDF::SetX(0);
                        PDF::SetRightMargin(0);
                        
                        //Convert product images
                        $variant->products->each(function(&$product) use(&$tmpGif){
                            if ($product->image && !$product->group) {
                                $product->image = $this->pdfImageToGif($product->image, $tmpGif);
                            }
                        });

                        $html = view('pdf.variant-products', [
                            'currencyFont' => $currencyFont, 
                            'variant' => $variant,
                            'variantDescription' => $this->pdfFixSymbols($variantDescription[$index]->children[0]->innertext), 
                            'managerImg' => $managerImg
                            ]);
                        
                        PDF::writeHTML($html, true, false, true, false, '');

                        //Full export
                        if($fullExport){
                            $html = view('pdf.variant-products-full', ['currencyFont' => $currencyFont, 'variant' => $variant, 'userDomain' => $userDomain]);
                            PDF::writeHTML($html, true, false, true, false, '');   
                        }
                    });
                    //Restore margins
                    PDF::SetX(10);
                    PDF::SetRightMargin(10);
                }
            }

            //Carousel
            if($child->attr['data-gjs-type'] == 'gallery'){
                $carouselImages = array_chunk($child->find('.carousel-item'), 2);
                PDF::writeHTML(view('pdf.carousel', ['carouselImages' => $carouselImages, 'userDomain' => $userDomain]), true, false, true, false, '');   
            }
            //Slider
            if($child->attr['data-gjs-type'] == 'slider'){
                $sliderImages = $child->find('ul img');
                PDF::writeHTML(view('pdf.slider', ['sliderImages' => $sliderImages, 'userDomain' => $userDomain]), true, false, true, false, '');   
            }
            //cp-advantages
            if($child->class == 'cp-advantages'){
                $advantages = array_chunk($child->find('.advantage-block'), 2);
                $faCodes = self::getFontAwesomeCode();
                if(is_array($advantages)){
                    PDF::writeHTML(view('pdf.advantages', ['advantages' => $advantages, 'faCodes' => $faCodes, 'corporateColor' => $corporateColor]), true, false, true, false, '');
                }
            }
        });

        PDF::SetX(PDF::GetX() - 10);
        PDF::SetMargins(0, 10, 0);
        //Footer
        $footerMessage = $HtmlDom->find('.footer__message-inner', 0)->innertext;
        
        PDF::writeHTML(view('pdf.footer', ['managerSign' => $managerSign, 'managerImg' => $managerImg, 'footerMessage' => $footerMessage]), true, false, true, false, '');
    
        //Clean up temp gif files
        if(!empty($tmpGif)){
            collect($tmpGif)->each(function($image){
                try{
                    if (is_file($image)) {
                        unlink($image);
                    }
                }catch(ErrorException $e){
                    Log::debug($e->getMessage());
                }
            });
        }

        PDF::Output($offer->offer_name.'.pdf');
    }

    /**
     * Add text child nodes
     *
     * @param object $child
     * @param font $fontPtsansRegular
     * @return void
     */
    public function pdfText($child, $fontPtsansRegular)
    {
        //Just text elements h1, h2, span, div
        if( !strpos($child->class, 'slider') && ($child->tag == 'span' || $child->tag == 'h1' || $child->tag == 'h2' || $child->tag == 'div')){
            //Set font
            PDF::SetFont($fontPtsansRegular, '', 12);
            //Set new style
            if ($child->tag == 'h1' || $child->tag == 'h2' || $child->tag == 'span') {
                PDF::writeHTML('<' . $child->tag . ' style="font-weight:normal;">' . $this->pdfFixSymbols($child->plaintext) . '</' . $child->tag . '>', true, false, true, false, '');
            } else {
                $childrenCount = count($child->children);
                if ( $childrenCount > 0) {
                    $nodesCount = count($child->nodes);
                    //If text exist and this text without tag
                    if ($nodesCount > $childrenCount && $child->nodes[0]->plaintext) {
                        //Print first
                        PDF::writeHTML('<' . $child->tag . ' style="font-weight:normal;">' . $this->pdfFixSymbols($child->nodes[0]->plaintext) . '</' . $child->tag . '>', true, false, true, false, '');
                    }
                    //Print children
                    collect($child->children)->each(function ($chd) use ($fontPtsansRegular) {
                        $this->pdfText($chd, $fontPtsansRegular);
                    });
                } else {
                    PDF::writeHTML('<' . $child->tag . ' style="font-weight:normal;">' . $this->pdfFixSymbols($child->plaintext) . '</' . $child->tag . '>', true, false, true, false, '');
                }
            }
            //Change position
            // PDF::SetY(PDF::GetY() + 3);
        }
    }

    /**
     * Fix cyrillic symbols
     *
     * @param string $text
     * @return string
     */
    public function pdfFixSymbols($text)
    {
        $text = str_replace('й', 'й', $text);
        $text = trim($text);
        return $text;
    }

    /**
     *  Convert image to gif. TCPDF require gif (transparent) or jpeg
     *
     * @param string $image
     * @param array $tmpGif
     * @return void
     */
    public function pdfImageToGif($image, &$tmpGif)
    {
        if(!is_file(public_path().$image)){
            $image = '/storage/resource/templates/base/product/empty.png';
        }
        $gif = Image::make(public_path().$image)->encode('gif');
        $gif->save(public_path().$image.'.gif');

        if(!in_array(public_path().$image.'.gif', $tmpGif)) {
            $tmpGif[] = public_path().$image.'.gif';
        }

        return $image.'.gif';
    }

    public function getCurrencyFont()
    {
//        $currencyFontCodess = array(
//            'icon-chf'     => '₣',
//            'icon-byn'     => 'Br',
//            'icon-uah'     => '₴',
//            'icon-hryvnia' => '₴',
//            'icon-kzt'     => '₸',
//            'icon-tenge'   => '₸',
//            'icon-cny'     => '¥',
//            'icon-jpy'     => '¥',
//            'icon-rmb'     => '¥',
//            'icon-yen'     => '¥',
//            'icon-gbp'     => '£',
//            'icon-dollar'  => '$',
//            'icon-usd'     => '$',
//            'icon-rouble'  => '₽',
//            'icon-rub'     => '₽',
//            'icon-ruble'   => '₽',
//            'icon-eur'     => '€'
//        );

        $currencyFontCodess = array(
        'chf' => '\e908',
        'byn' => '\e907',
        'uah' => '\e901',
        'hryvnia' => '\e901',
        'kzt' => '\e900',
        'tenge' => '\e900',
        'cny' => '\e902',
        'jpy' => '\e902',
        'rmb' => '\e902',
        'yen' => '\e902',
        'gbp' => '\e903',
        'dollar' => '\e904',
        'usd' => '\e904',
        'rouble' => '\e905',
        'rub' => '\e905',
        'ruble' => '\e905',
        'eur' => '\e906'
        );

        return $currencyFontCodess;
    }

    public function getFontAwesomeCode()
    {
        $fontCodess = array(
            'fa-image'                               => '',
            'fa-glass'                               => '',
            'fa-music'                               => '',
            'fa-search'                              => '',
            'fa-envelope-o'                          => '',
            'fa-heart'                               => '',
            'fa-star'                                => '',
            'fa-star-o'                              => '',
            'fa-user'                                => '',
            'fa-film'                                => '',
            'fa-th-large'                            => '',
            'fa-th'                                  => '',
            'fa-th-list'                             => '',
            'fa-check'                               => '',
            'fa-times'                               => '',
            'fa-search-plus'                         => '',
            'fa-search-minus'                        => '',
            'fa-power-off'                           => '',
            'fa-signal'                              => '',
            'fa-cog'                                 => '',
            'fa-trash-o'                             => '',
            'fa-home'                                => '',
            'fa-file-o'                              => '',
            'fa-clock-o'                             => '',
            'fa-road'                                => '',
            'fa-download'                            => '',
            'fa-arrow-circle-o-down'                 => '',
            'fa-arrow-circle-o-up'                   => '',
            'fa-inbox'                               => '',
            'fa-play-circle-o'                       => '',
            'fa-repeat'                              => '',
            'fa-refresh'                             => '',
            'fa-list-alt'                            => '',
            'fa-lock'                                => '',
            'fa-flag'                                => '',
            'fa-headphones'                          => '',
            'fa-volume-off'                          => '',
            'fa-volume-down'                         => '',
            'fa-volume-up'                           => '',
            'fa-qrcode'                              => '',
            'fa-barcode'                             => '',
            'fa-tag'                                 => '',
            'fa-tags'                                => '',
            'fa-book'                                => '',
            'fa-bookmark'                            => '',
            'fa-print'                               => '',
            'fa-camera'                              => '',
            'fa-font'                                => '',
            'fa-bold'                                => '',
            'fa-italic'                              => '',
            'fa-text-height'                         => '',
            'fa-text-width'                          => '',
            'fa-align-left'                          => '',
            'fa-align-center'                        => '',
            'fa-align-right'                         => '',
            'fa-align-justify'                       => '',
            'fa-list'                                => '',
            'fa-outdent'                             => '',
            'fa-indent'                              => '',
            'fa-video-camera'                        => '',
            'fa-picture-o'                           => '',
            'fa-pencil'                              => '',
            'fa-map-marker'                          => '',
            'fa-adjust'                              => '',
            'fa-tint'                                => '',
            'fa-pencil-square-o'                     => '',
            'fa-share-square-o'                      => '',
            'fa-check-square-o'                      => '',
            'fa-arrows'                              => '',
            'fa-step-backward'                       => '',
            'fa-fast-backward'                       => '',
            'fa-backward'                            => '',
            'fa-play'                                => '',
            'fa-pause'                               => '',
            'fa-stop'                                => '',
            'fa-forward'                             => '',
            'fa-fast-forward'                        => '',
            'fa-step-forward'                        => '',
            'fa-eject'                               => '',
            'fa-chevron-left'                        => '',
            'fa-chevron-right'                       => '',
            'fa-plus-circle'                         => '',
            'fa-minus-circle'                        => '',
            'fa-times-circle'                        => '',
            'fa-check-circle'                        => '',
            'fa-question-circle'                     => '',
            'fa-info-circle'                         => '',
            'fa-crosshairs'                          => '',
            'fa-times-circle-o'                      => '',
            'fa-check-circle-o'                      => '',
            'fa-ban'                                 => '',
            'fa-arrow-left'                          => '',
            'fa-arrow-right'                         => '',
            'fa-arrow-up'                            => '',
            'fa-arrow-down'                          => '',
            'fa-share'                               => '',
            'fa-expand'                              => '',
            'fa-compress'                            => '',
            'fa-plus'                                => '',
            'fa-minus'                               => '',
            'fa-asterisk'                            => '',
            'fa-exclamation-circle'                  => '',
            'fa-gift'                                => '',
            'fa-leaf'                                => '',
            'fa-fire'                                => '',
            'fa-eye'                                 => '',
            'fa-eye-slash'                           => '',
            'fa-exclamation-triangle'                => '',
            'fa-plane'                               => '',
            'fa-calendar'                            => '',
            'fa-random'                              => '',
            'fa-comment'                             => '',
            'fa-magnet'                              => '',
            'fa-chevron-up'                          => '',
            'fa-chevron-down'                        => '',
            'fa-retweet'                             => '',
            'fa-shopping-cart'                       => '',
            'fa-folder'                              => '',
            'fa-folder-open'                         => '',
            'fa-arrows-v'                            => '',
            'fa-arrows-h'                            => '',
            'fa-bar-chart'                           => '',
            'fa-twitter-square'                      => '',
            'fa-facebook-square'                     => '',
            'fa-camera-retro'                        => '',
            'fa-key'                                 => '',
            'fa-cogs'                                => '',
            'fa-comments'                            => '',
            'fa-thumbs-o-up'                         => '',
            'fa-thumbs-o-down'                       => '',
            'fa-star-half'                           => '',
            'fa-heart-o'                             => '',
            'fa-sign-out'                            => '',
            'fa-linkedin-square'                     => '',
            'fa-thumb-tack'                          => '',
            'fa-external-link'                       => '',
            'fa-sign-in'                             => '',
            'fa-trophy'                              => '',
            'fa-github-square'                       => '',
            'fa-upload'                              => '',
            'fa-lemon-o'                             => '',
            'fa-phone'                               => '',
            'fa-square-o'                            => '',
            'fa-bookmark-o'                          => '',
            'fa-phone-square'                        => '',
            'fa-twitter'                             => '',
            'fa-facebook'                            => '',
            'fa-github'                              => '',
            'fa-unlock'                              => '',
            'fa-credit-card'                         => '',
            'fa-rss'                                 => '',
            'fa-hdd-o'                               => '',
            'fa-bullhorn'                            => '',
            'fa-bell'                                => '',
            'fa-certificate'                         => '',
            'fa-hand-o-right'                        => '',
            'fa-hand-o-left'                         => '',
            'fa-hand-o-up'                           => '',
            'fa-hand-o-down'                         => '',
            'fa-arrow-circle-left'                   => '',
            'fa-arrow-circle-right'                  => '',
            'fa-arrow-circle-up'                     => '',
            'fa-arrow-circle-down'                   => '',
            'fa-globe'                               => '',
            'fa-wrench'                              => '',
            'fa-tasks'                               => '',
            'fa-filter'                              => '',
            'fa-briefcase'                           => '',
            'fa-arrows-alt'                          => '',
            'fa-users'                               => '',
            'fa-link'                                => '',
            'fa-cloud'                               => '',
            'fa-flask'                               => '',
            'fa-scissors'                            => '',
            'fa-files-o'                             => '',
            'fa-paperclip'                           => '',
            'fa-floppy-o'                            => '',
            'fa-square'                              => '',
            'fa-bars'                                => '',
            'fa-list-ul'                             => '',
            'fa-list-ol'                             => '',
            'fa-strikethrough'                       => '',
            'fa-underline'                           => '',
            'fa-table'                               => '',
            'fa-magic'                               => '',
            'fa-truck'                               => '',
            'fa-pinterest'                           => '',
            'fa-pinterest-square'                    => '',
            'fa-google-plus-square'                  => '',
            'fa-google-plus'                         => '',
            'fa-money'                               => '',
            'fa-caret-down'                          => '',
            'fa-caret-up'                            => '',
            'fa-caret-left'                          => '',
            'fa-caret-right'                         => '',
            'fa-columns'                             => '',
            'fa-sort'                                => '',
            'fa-sort-desc'                           => '',
            'fa-sort-asc'                            => '',
            'fa-envelope'                            => '',
            'fa-linkedin'                            => '',
            'fa-undo'                                => '',
            'fa-gavel'                               => '',
            'fa-tachometer'                          => '',
            'fa-comment-o'                           => '',
            'fa-comments-o'                          => '',
            'fa-bolt'                                => '',
            'fa-sitemap'                             => '',
            'fa-umbrella'                            => '',
            'fa-clipboard'                           => '',
            'fa-lightbulb-o'                         => '',
            'fa-exchange'                            => '',
            'fa-cloud-download'                      => '',
            'fa-cloud-upload'                        => '',
            'fa-user-md'                             => '',
            'fa-stethoscope'                         => '',
            'fa-suitcase'                            => '',
            'fa-bell-o'                              => '',
            'fa-coffee'                              => '',
            'fa-cutlery'                             => '',
            'fa-file-text-o'                         => '',
            'fa-building-o'                          => '',
            'fa-hospital-o'                          => '',
            'fa-ambulance'                           => '',
            'fa-medkit'                              => '',
            'fa-fighter-jet'                         => '',
            'fa-beer'                                => '',
            'fa-h-square'                            => '',
            'fa-plus-square'                         => '',
            'fa-angle-double-left'                   => '',
            'fa-angle-double-right'                  => '',
            'fa-angle-double-up'                     => '',
            'fa-angle-double-down'                   => '',
            'fa-angle-left'                          => '',
            'fa-angle-right'                         => '',
            'fa-angle-up'                            => '',
            'fa-angle-down'                          => '',
            'fa-desktop'                             => '',
            'fa-laptop'                              => '',
            'fa-tablet'                              => '',
            'fa-mobile'                              => '',
            'fa-circle-o'                            => '',
            'fa-quote-left'                          => '',
            'fa-quote-right'                         => '',
            'fa-spinner'                             => '',
            'fa-circle'                              => '',
            'fa-reply'                               => '',
            'fa-github-alt'                          => '',
            'fa-folder-o'                            => '',
            'fa-folder-open-o'                       => '',
            'fa-smile-o'                             => '',
            'fa-frown-o'                             => '',
            'fa-meh-o'                               => '',
            'fa-gamepad'                             => '',
            'fa-keyboard-o'                          => '',
            'fa-flag-o'                              => '',
            'fa-flag-checkered'                      => '',
            'fa-terminal'                            => '',
            'fa-code'                                => '',
            'fa-reply-all'                           => '',
            'fa-star-half-o'                         => '',
            'fa-location-arrow'                      => '',
            'fa-crop'                                => '',
            'fa-code-fork'                           => '',
            'fa-chain-broken'                        => '',
            'fa-question'                            => '',
            'fa-info'                                => '',
            'fa-exclamation'                         => '',
            'fa-superscript'                         => '',
            'fa-subscript'                           => '',
            'fa-eraser'                              => '',
            'fa-puzzle-piece'                        => '',
            'fa-microphone'                          => '',
            'fa-microphone-slash'                    => '',
            'fa-shield'                              => '',
            'fa-calendar-o'                          => '',
            'fa-fire-extinguisher'                   => '',
            'fa-rocket'                              => '',
            'fa-maxcdn'                              => '',
            'fa-chevron-circle-left'                 => '',
            'fa-chevron-circle-right'                => '',
            'fa-chevron-circle-up'                   => '',
            'fa-chevron-circle-down'                 => '',
            'fa-html5'                               => '',
            'fa-css3'                                => '',
            'fa-anchor'                              => '',
            'fa-unlock-alt'                          => '',
            'fa-bullseye'                            => '',
            'fa-ellipsis-h'                          => '',
            'fa-ellipsis-v'                          => '',
            'fa-rss-square'                          => '',
            'fa-play-circle'                         => '',
            'fa-ticket'                              => '',
            'fa-minus-square'                        => '',
            'fa-minus-square-o'                      => '',
            'fa-level-up'                            => '',
            'fa-level-down'                          => '',
            'fa-check-square'                        => '',
            'fa-pencil-square'                       => '',
            'fa-external-link-square'                => '',
            'fa-share-square'                        => '',
            'fa-compass'                             => '',
            'fa-caret-square-o-down'                 => '',
            'fa-caret-square-o-up'                   => '',
            'fa-caret-square-o-right'                => '',
            'fa-eur'                                 => '',
            'fa-gbp'                                 => '',
            'fa-usd'                                 => '',
            'fa-inr'                                 => '',
            'fa-jpy'                                 => '',
            'fa-rub'                                 => '',
            'fa-krw'                                 => '',
            'fa-btc'                                 => '',
            'fa-file'                                => '',
            'fa-file-text'                           => '',
            'fa-sort-alpha-asc'                      => '',
            'fa-sort-alpha-desc'                     => '',
            'fa-sort-amount-asc'                     => '',
            'fa-sort-amount-desc'                    => '',
            'fa-sort-numeric-asc'                    => '',
            'fa-sort-numeric-desc'                   => '',
            'fa-thumbs-up'                           => '',
            'fa-thumbs-down'                         => '',
            'fa-youtube-square'                      => '',
            'fa-youtube'                             => '',
            'fa-xing'                                => '',
            'fa-xing-square'                         => '',
            'fa-youtube-play'                        => '',
            'fa-dropbox'                             => '',
            'fa-stack-overflow'                      => '',
            'fa-instagram'                           => '',
            'fa-flickr'                              => '',
            'fa-adn'                                 => '',
            'fa-bitbucket'                           => '',
            'fa-bitbucket-square'                    => '',
            'fa-tumblr'                              => '',
            'fa-tumblr-square'                       => '',
            'fa-long-arrow-down'                     => '',
            'fa-long-arrow-up'                       => '',
            'fa-long-arrow-left'                     => '',
            'fa-long-arrow-right'                    => '',
            'fa-apple'                               => '',
            'fa-windows'                             => '',
            'fa-android'                             => '',
            'fa-linux'                               => '',
            'fa-dribbble'                            => '',
            'fa-skype'                               => '',
            'fa-foursquare'                          => '',
            'fa-trello'                              => '',
            'fa-female'                              => '',
            'fa-male'                                => '',
            'fa-gratipay'                            => '',
            'fa-sun-o'                               => '',
            'fa-moon-o'                              => '',
            'fa-archive'                             => '',
            'fa-bug'                                 => '',
            'fa-vk'                                  => '',
            'fa-weibo'                               => '',
            'fa-renren'                              => '',
            'fa-pagelines'                           => '',
            'fa-stack-exchange'                      => '',
            'fa-arrow-circle-o-right'                => '',
            'fa-arrow-circle-o-left'                 => '',
            'fa-caret-square-o-left'                 => '',
            'fa-dot-circle-o'                        => '',
            'fa-wheelchair'                          => '',
            'fa-vimeo-square'                        => '',
            'fa-try'                                 => '',
            'fa-plus-square-o'                       => '',
            'fa-space-shuttle'                       => '',
            'fa-slack'                               => '',
            'fa-envelope-square'                     => '',
            'fa-wordpress'                           => '',
            'fa-openid'                              => '',
            'fa-university'                          => '',
            'fa-graduation-cap'                      => '',
            'fa-yahoo'                               => '',
            'fa-google'                              => '',
            'fa-reddit'                              => '',
            'fa-reddit-square'                       => '',
            'fa-stumbleupon-circle'                  => '',
            'fa-stumbleupon'                         => '',
            'fa-delicious'                           => '',
            'fa-digg'                                => '',
            'fa-pied-piper-pp'                       => '',
            'fa-pied-piper-alt'                      => '',
            'fa-drupal'                              => '',
            'fa-joomla'                              => '',
            'fa-language'                            => '',
            'fa-fax'                                 => '',
            'fa-building'                            => '',
            'fa-child'                               => '',
            'fa-paw'                                 => '',
            'fa-spoon'                               => '',
            'fa-cube'                                => '',
            'fa-cubes'                               => '',
            'fa-behance'                             => '',
            'fa-behance-square'                      => '',
            'fa-steam'                               => '',
            'fa-steam-square'                        => '',
            'fa-recycle'                             => '',
            'fa-car'                                 => '',
            'fa-taxi'                                => '',
            'fa-tree'                                => '',
            'fa-spotify'                             => '',
            'fa-deviantart'                          => '',
            'fa-soundcloud'                          => '',
            'fa-database'                            => '',
            'fa-file-pdf-o'                          => '',
            'fa-file-word-o'                         => '',
            'fa-file-excel-o'                        => '',
            'fa-file-powerpoint-o'                   => '',
            'fa-file-image-o'                        => '',
            'fa-file-archive-o'                      => '',
            'fa-file-audio-o'                        => '',
            'fa-file-video-o'                        => '',
            'fa-file-code-o'                         => '',
            'fa-vine'                                => '',
            'fa-codepen'                             => '',
            'fa-jsfiddle'                            => '',
            'fa-life-ring'                           => '',
            'fa-circle-o-notch'                      => '',
            'fa-rebel'                               => '',
            'fa-empire'                              => '',
            'fa-git-square'                          => '',
            'fa-git'                                 => '',
            'fa-hacker-news'                         => '',
            'fa-tencent-weibo'                       => '',
            'fa-qq'                                  => '',
            'fa-weixin'                              => '',
            'fa-paper-plane'                         => '',
            'fa-paper-plane-o'                       => '',
            'fa-history'                             => '',
            'fa-circle-thin'                         => '',
            'fa-header'                              => '',
            'fa-paragraph'                           => '',
            'fa-sliders'                             => '',
            'fa-share-alt'                           => '',
            'fa-share-alt-square'                    => '',
            'fa-bomb'                                => '',
            'fa-futbol-o'                            => '',
            'fa-tty'                                 => '',
            'fa-binoculars'                          => '',
            'fa-plug'                                => '',
            'fa-slideshare'                          => '',
            'fa-twitch'                              => '',
            'fa-yelp'                                => '',
            'fa-newspaper-o'                         => '',
            'fa-wifi'                                => '',
            'fa-calculator'                          => '',
            'fa-paypal'                              => '',
            'fa-google-wallet'                       => '',
            'fa-cc-visa'                             => '',
            'fa-cc-mastercard'                       => '',
            'fa-cc-discover'                         => '',
            'fa-cc-amex'                             => '',
            'fa-cc-paypal'                           => '',
            'fa-cc-stripe'                           => '',
            'fa-bell-slash'                          => '',
            'fa-bell-slash-o'                        => '',
            'fa-trash'                               => '',
            'fa-copyright'                           => '',
            'fa-at'                                  => '',
            'fa-eyedropper'                          => '',
            'fa-paint-brush'                         => '',
            'fa-birthday-cake'                       => '',
            'fa-area-chart'                          => '',
            'fa-pie-chart'                           => '',
            'fa-line-chart'                          => '',
            'fa-lastfm'                              => '',
            'fa-lastfm-square'                       => '',
            'fa-toggle-off'                          => '',
            'fa-toggle-on'                           => '',
            'fa-bicycle'                             => '',
            'fa-bus'                                 => '',
            'fa-ioxhost'                             => '',
            'fa-angellist'                           => '',
            'fa-cc'                                  => '',
            'fa-ils'                                 => '',
            'fa-meanpath'                            => '',
            'fa-buysellads'                          => '',
            'fa-connectdevelop'                      => '',
            'fa-dashcube'                            => '',
            'fa-forumbee'                            => '',
            'fa-leanpub'                             => '',
            'fa-sellsy'                              => '',
            'fa-shirtsinbulk'                        => '',
            'fa-simplybuilt'                         => '',
            'fa-skyatlas'                            => '',
            'fa-cart-plus'                           => '',
            'fa-cart-arrow-down'                     => '',
            'fa-diamond'                             => '',
            'fa-ship'                                => '',
            'fa-user-secret'                         => '',
            'fa-motorcycle'                          => '',
            'fa-street-view'                         => '',
            'fa-heartbeat'                           => '',
            'fa-venus'                               => '',
            'fa-mars'                                => '',
            'fa-mercury'                             => '',
            'fa-transgender'                         => '',
            'fa-transgender-alt'                     => '',
            'fa-venus-double'                        => '',
            'fa-mars-double'                         => '',
            'fa-venus-mars'                          => '',
            'fa-mars-stroke'                         => '',
            'fa-mars-stroke-v'                       => '',
            'fa-mars-stroke-h'                       => '',
            'fa-neuter'                              => '',
            'fa-genderless'                          => '',
            'fa-facebook-official'                   => '',
            'fa-pinterest-p'                         => '',
            'fa-whatsapp'                            => '',
            'fa-server'                              => '',
            'fa-user-plus'                           => '',
            'fa-user-times'                          => '',
            'fa-bed'                                 => '',
            'fa-viacoin'                             => '',
            'fa-train'                               => '',
            'fa-subway'                              => '',
            'fa-medium'                              => '',
            'fa-y-combinator'                        => '',
            'fa-optin-monster'                       => '',
            'fa-opencart'                            => '',
            'fa-expeditedssl'                        => '',
            'fa-battery-full'                        => '',
            'fa-battery-three-quarters'              => '',
            'fa-battery-half'                        => '',
            'fa-battery-quarter'                     => '',
            'fa-battery-empty'                       => '',
            'fa-mouse-pointer'                       => '',
            'fa-i-cursor'                            => '',
            'fa-object-group'                        => '',
            'fa-object-ungroup'                      => '',
            'fa-sticky-note'                         => '',
            'fa-sticky-note-o'                       => '',
            'fa-cc-jcb'                              => '',
            'fa-cc-diners-club'                      => '',
            'fa-clone'                               => '',
            'fa-balance-scale'                       => '',
            'fa-hourglass-o'                         => '',
            'fa-hourglass-start'                     => '',
            'fa-hourglass-half'                      => '',
            'fa-hourglass-end'                       => '',
            'fa-hourglass'                           => '',
            'fa-hand-rock-o'                         => '',
            'fa-hand-paper-o'                        => '',
            'fa-hand-scissors-o'                     => '',
            'fa-hand-lizard-o'                       => '',
            'fa-hand-spock-o'                        => '',
            'fa-hand-pointer-o'                      => '',
            'fa-hand-peace-o'                        => '',
            'fa-trademark'                           => '',
            'fa-registered'                          => '',
            'fa-creative-commons'                    => '',
            'fa-gg'                                  => '',
            'fa-gg-circle'                           => '',
            'fa-tripadvisor'                         => '',
            'fa-odnoklassniki'                       => '',
            'fa-odnoklassniki-square'                => '',
            'fa-get-pocket'                          => '',
            'fa-wikipedia-w'                         => '',
            'fa-safari'                              => '',
            'fa-chrome'                              => '',
            'fa-firefox'                             => '',
            'fa-opera'                               => '',
            'fa-internet-explorer'                   => '',
            'fa-television'                          => '',
            'fa-contao'                              => '',
            'fa-500px'                               => '',
            'fa-amazon'                              => '',
            'fa-calendar-plus-o'                     => '',
            'fa-calendar-minus-o'                    => '',
            'fa-calendar-times-o'                    => '',
            'fa-calendar-check-o'                    => '',
            'fa-industry'                            => '',
            'fa-map-pin'                             => '',
            'fa-map-signs'                           => '',
            'fa-map-o'                               => '',
            'fa-map'                                 => '',
            'fa-commenting'                          => '',
            'fa-commenting-o'                        => '',
            'fa-houzz'                               => '',
            'fa-vimeo'                               => '',
            'fa-black-tie'                           => '',
            'fa-fonticons'                           => '',
            'fa-reddit-alien'                        => '',
            'fa-edge'                                => '',
            'fa-credit-card-alt'                     => '',
            'fa-codiepie'                            => '',
            'fa-modx'                                => '',
            'fa-fort-awesome'                        => '',
            'fa-usb'                                 => '',
            'fa-product-hunt'                        => '',
            'fa-mixcloud'                            => '',
            'fa-scribd'                              => '',
            'fa-pause-circle'                        => '',
            'fa-pause-circle-o'                      => '',
            'fa-stop-circle'                         => '',
            'fa-stop-circle-o'                       => '',
            'fa-shopping-bag'                        => '',
            'fa-shopping-basket'                     => '',
            'fa-hashtag'                             => '',
            'fa-bluetooth'                           => '',
            'fa-bluetooth-b'                         => '',
            'fa-percent'                             => '',
            'fa-gitlab'                              => '',
            'fa-wpbeginner'                          => '',
            'fa-wpforms'                             => '',
            'fa-envira'                              => '',
            'fa-universal-access'                    => '',
            'fa-wheelchair-alt'                      => '',
            'fa-question-circle-o'                   => '',
            'fa-blind'                               => '',
            'fa-audio-description'                   => '',
            'fa-volume-control-phone'                => '',
            'fa-braille'                             => '',
            'fa-assistive-listening-systems'         => '',
            'fa-american-sign-language-interpreting' => '',
            'fa-deaf'                                => '',
            'fa-glide'                               => '',
            'fa-glide-g'                             => '',
            'fa-sign-language'                       => '',
            'fa-low-vision'                          => '',
            'fa-viadeo'                              => '',
            'fa-viadeo-square'                       => '',
            'fa-snapchat'                            => '',
            'fa-snapchat-ghost'                      => '',
            'fa-snapchat-square'                     => '',
            'fa-pied-piper'                          => '',
            'fa-first-order'                         => '',
            'fa-yoast'                               => '',
            'fa-themeisle'                           => '',
            'fa-google-plus-official'                => '',
            'fa-font-awesome'                        => '',
            'fa-handshake-o'                         => '',
            'fa-envelope-open'                       => '',
            'fa-envelope-open-o'                     => '',
            'fa-linode'                              => '',
            'fa-address-book'                        => '',
            'fa-address-book-o'                      => '',
            'fa-address-card'                        => '',
            'fa-address-card-o'                      => '',
            'fa-user-circle'                         => '',
            'fa-user-circle-o'                       => '',
            'fa-user-o'                              => '',
            'fa-id-badge'                            => '',
            'fa-id-card'                             => '',
            'fa-id-card-o'                           => '',
            'fa-quora'                               => '',
            'fa-free-code-camp'                      => '',
            'fa-telegram'                            => '',
            'fa-thermometer-full'                    => '',
            'fa-thermometer-three-quarters'          => '',
            'fa-thermometer-half'                    => '',
            'fa-thermometer-quarter'                 => '',
            'fa-thermometer-empty'                   => '',
            'fa-shower'                              => '',
            'fa-bath'                                => '',
            'fa-podcast'                             => '',
            'fa-window-maximize'                     => '',
            'fa-window-minimize'                     => '',
            'fa-window-restore'                      => '',
            'fa-window-close'                        => '',
            'fa-window-close-o'                      => '',
            'fa-bandcamp'                            => '',
            'fa-grav'                                => '',
            'fa-etsy'                                => '',
            'fa-imdb'                                => '',
            'fa-ravelry'                             => '',
            'fa-eercast'                             => '',
            'fa-microchip'                           => '',
            'fa-snowflake-o'                         => '',
            'fa-superpowers'                         => '',
            'fa-wpexplorer'                          => '',
            'fa-remove'                              => '',
            'fa-close'                               => '',
            'fa-gear'                                => '',
            'fa-gears'                               => '',
            'fa-gears'                               => '',
            'fa-rotate-right'                        => '',
            'fa-dedent'                              => '',
            'fa-photo'                               => '',
            'fa-edit'                                => '',
            'fa-warning'                             => '',
            'fa-bar-chart-o'                         => '',
            'fa-facebook-f'                          => '',
            'fa-feed'                                => '',
            'fa-group'                               => '',
            'fa-chain'                               => '',
            'fa-cut'                                 => '',
            'fa-copy'                                => '',
            'fa-save'                                => '',
            'fa-navicon'                             => '',
            'fa-reorder'                             => '',
            'fa-unsorted'                            => '',
            'fa-sort-down'                           => '',
            'fa-sort-up'                             => '',
            'fa-rotate-left'                         => '',
            'fa-legal'                               => '',
            'fa-dashboard'                           => '',
            'fa-flash'                               => '',
            'fa-paste'                               => '',
            'fa-mobile-phone'                        => '',
            'fa-mail-forward'                        => '',
            'fa-mail-reply'                          => '',
            'fa-mail-reply-all'                      => '',
            'fa-star-half-empty'                     => '',
            'fa-star-half-full'                      => '',
            'fa-unlink'                              => '',
            'fa-toggle-down'                         => '',
            'fa-toggle-up'                           => '',
            'fa-toggle-right'                        => '',
            'fa-euro'                                => '',
            'fa-dollar'                              => '',
            'fa-rupee'                               => '',
            'fa-cny'                                 => '',
            'fa-rmb'                                 => '',
            'fa-yen'                                 => '',
            'fa-ruble'                               => '',
            'fa-rouble'                              => '',
            'fa-won'                                 => '',
            'fa-bitcoin'                             => '',
            'fa-gittip'                              => '',
            'fa-toggle-left'                         => '',
            'fa-turkish-lira'                        => '',
            'fa-institution'                         => '',
            'fa-bank'                                => '',
            'fa-mortar-board'                        => '',
            'fa-automobile'                          => '',
            'fa-cab'                                 => '',
            'fa-file-photo-o'                        => '',
            'fa-file-picture-o'                      => '',
            'fa-file-zip-o'                          => '',
            'fa-file-sound-o'                        => '',
            'fa-file-movie-o'                        => '',
            'fa-life-bouy'                           => '',
            'fa-life-buoy'                           => '',
            'fa-life-saver'                          => '',
            'fa-support'                             => '',
            'fa-y-combinator-square'                 => '',
            'fa-wechat'                              => '',
            'fa-send'                                => '',
            'fa-send-o'                              => '',
            'fa-soccer-ball-o'                       => '',
            'fa-shekel'                              => '',
            'fa-sheqel'                              => '',
            'fa-intersex'                            => '',
            'fa-hotel'                               => '',
            'fa-yc'                                  => '',
            'fa-battery-4'                           => '',
            'fa-battery'                             => '',
            'fa-battery-3'                           => '',
            'fa-battery-2'                           => '',
            'fa-battery-1'                           => '',
            'fa-battery-0'                           => '',
            'fa-hourglass-1'                         => '',
            'fa-hourglass-2'                         => '',
            'fa-hourglass-3'                         => '',
            'fa-hand-grab-o'                         => '',
            'fa-hand-stop-o'                         => '',
            'fa-tv'                                  => '',
        );

        return $fontCodess;
    }

    public function rus2translit($string)
    {
        $converter = array(
            'а' => 'a',
            'б' => 'b',
            'в' => 'v',
            'г' => 'g',
            'д' => 'd',
            'е' => 'e',
            'ё' => 'e',
            'ж' => 'zh',
            'з' => 'z',
            'и' => 'i',
            'й' => 'y',
            'к' => 'k',
            'л' => 'l',
            'м' => 'm',
            'н' => 'n',
            'о' => 'o',
            'п' => 'p',
            'р' => 'r',
            'с' => 's',
            'т' => 't',
            'у' => 'u',
            'ф' => 'f',
            'х' => 'h',
            'ц' => 'c',
            'ч' => 'ch',
            'ш' => 'sh',
            'щ' => 'sch',
            'ь' => '\'',
            'ы' => 'y',
            'ъ' => '\'',
            'э' => 'e',
            'ю' => 'yu',
            'я' => 'ya',

            'А' => 'A',
            'Б' => 'B',
            'В' => 'V',
            'Г' => 'G',
            'Д' => 'D',
            'Е' => 'E',
            'Ё' => 'E',
            'Ж' => 'Zh',
            'З' => 'Z',
            'И' => 'I',
            'Й' => 'Y',
            'К' => 'K',
            'Л' => 'L',
            'М' => 'M',
            'Н' => 'N',
            'О' => 'O',
            'П' => 'P',
            'Р' => 'R',
            'С' => 'S',
            'Т' => 'T',
            'У' => 'U',
            'Ф' => 'F',
            'Х' => 'H',
            'Ц' => 'C',
            'Ч' => 'Ch',
            'Ш' => 'Sh',
            'Щ' => 'Sch',
            'Ь' => '\'',
            'Ы' => 'Y',
            'Ъ' => '\'',
            'Э' => 'E',
            'Ю' => 'Yu',
            'Я' => 'Ya',
            'й' => 'i'
        );
        $newString = strtr($string, $converter);
        $newString = str_replace(' ', ' ', $newString);
        return $newString;
    }

    public function getCurrencyFonts($code){
        $currencies = array(
            'kzt' => '₸',
            'uah' => '₴',
            'cny' => '¥',
            'gbp' => '£',
            'usd' => '$',
            'rub' => '₽',
            'eur' => '€',
            'byn' => 'Br',
            'chf' => '₣',
            'jpy' => '¥'
        );
        if (isset($currencies[$code])){
            return $currencies[$code];
        }else{
            return false;
        }
    }
}


