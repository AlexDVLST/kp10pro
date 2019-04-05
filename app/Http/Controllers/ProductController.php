<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\FileProduct;
use App\Models\Product;
use App\Models\Page;
use App\Models\File;
use App\Models\UserMeta;
use App\Models\ProductCustomField;
use App\Models\ProductCustomFieldValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Validator;
use Excel;
use App\Models\User;
use Input;
use App\Http\Traits\CurrencyTrait;

class ProductController extends Controller
{
    use CurrencyTrait;
    
    public function __construct()
    {
        //Permissions
        $this->middleware(['permission:create product|view product']);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($account)
    {
        //dd($user);
        $user           = Auth::user();
        $page           = Page::whereSlug('products')->first();
        $dopfields_list = ProductCustomField::get();

        return view('pages/products', ['dopfields_list' => $dopfields_list, 'user' => $user, 'page' => $page]);
    }

    /**
     * @param $accountgit
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($account, Request $request)
    {
        $user = Auth::user();
        //Permissions
        if (!$user->userCan('create product')) {
            abort(403);
        }

        //TODO: Вообще нет никаких проверок на ошибки!

        $validate = self::validateProduct($request);
        if ($validate !== true) {
            return response()
                ->json(['status' => 'error', 'fields' => $validate->errors()]);
        }

        $product              = new Product;
        $product->account_id  = $user->accountId;
        $product->name        = $request->product['product_name'];
        $product->article     = $request->product['product_article'];
        $product->description = $request->product['description'];
        $product->cost        = $request->product['product_cost'];
        $product->prime_cost  = 0; //$request->product['product_prime_cost'];
        $product->removed     = null;
        $product->save();

        $product_dopfields = $request['product_dopfields'];

        if (is_array($product_dopfields)) {
            foreach ($product_dopfields as $dopfield) {
                $prodCustomFieldValue                          = new ProductCustomFieldValue;
                $prodCustomFieldValue->product_id              = $product->id;
                $prodCustomFieldValue->product_custom_field_id = $dopfield['customFieldId'];
                $prodCustomFieldValue->value                   = $dopfield['dopfieldValue'];
                $prodCustomFieldValue->save();
            }
        }

        //Сохраняем изображение
        $fileId = $request->product['fileId'];
        self::addFile($product->id, $fileId);
    }

    /**
     * @param $account
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getDeletedProducts($account)
    {
//        $page     = Page::whereSlug('offers')->first();
//        $user     = Auth::user();
//        $products = Product::whereUserId($user->id)->whereRemoved(1)->get();
//
//        return view('offers-deleted',
//            ['page' => $page, 'user' => $user, 'deleted_offers' => $products]);
    }

    public function getProductListJson($account, Request $request)
    {
        $user = Auth::user();
        // Получаем валюты пользователя
        $currencies = $this->getUserCurrencies($user); //syncRate

        $defaultPaginate = '50';
        $search          = $request['search'];
        $orderby         = isset($request['orderby']) ? $request['orderby'] : 'id';
        $orderasc        = isset($request['order']) ? $request['order'] : 'asc';

        // Параметр QuickList. Получить весь список товаров без связей. Без учёта страниц и сортировки.
        if (isset($request['quickList'])) {
            $products = Product::whereNull('removed')->get();
            $response = [
                'products' => $products,
            ];

            return $response;
        }

        $query = Product::with('productCustomFieldValue', 'file.fileRelation')
                        ->whereNull('removed')
                        ->orderBy($orderby, $orderasc);

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                      ->orWhere('article', 'like', '%' . $search . '%')
                      ->orWhere('description', 'like', '%' . $search . '%')
                      ->orWhereHas(
                          'productCustomFieldValue',
                          function ($query) use ($search) {
                              $query->where('value', 'like', '%' . $search . '%');
                          }
                      );
            });
        }

        $products = $query->paginate($defaultPaginate);

        if ($products) {
            foreach ($products as $prodKey => $prodValue) {
                $prodValue->fullCurrency;

                $newCustomFields         = [];
                $productCustomFieldValue = $prodValue->productCustomFieldValue;
                if ($productCustomFieldValue) {
                    foreach ($productCustomFieldValue as $key => $value) {
                        $newCustomFields[$value->product_custom_field_id] = $value->value;
                    }
                }
                $products[$prodKey]['dopfields'] = $newCustomFields;

                // File
                if (isset($prodValue->file[0])) {
                    $file_rel = $prodValue->file[0]->fileRelation;
                    if (isset($file_rel)) {
                        if (isset($file_rel->path)) {
                            $file = '/' . str_replace('public', 'storage', $file_rel->path) . '/' . $file_rel->file;
                        } else {
                            $file = '/storage/resource/templates/base/product/empty.png';
                        }
                    } else {
                        $file = '/storage/resource/templates/base/product/empty.png';
                    }
                } else {
                    $file = '/storage/resource/templates/base/product/empty.png';
                }
                $products[$prodKey]['file'][0] = $file;

                //syncRate
                $fullCurrencyAttribute = [];
                if ($currencies) {
                    foreach ($currencies as $currency) {
                        $fullCurrencyAttribute[$currency->id]['id'] = $currency->id;
                        if ($currency->sync) {
                            $fullCurrencyAttribute[$currency->id]['cost'] = round($prodValue->cost / $currency->syncRate, 2);
                        } else {
                            $fullCurrencyAttribute[$currency->id]['cost'] = round($prodValue->cost / $currency->rate, 2);
                        }
                        $fullCurrencyAttribute[$currency->id]['charCode'] = $currency->charCode;
                        $fullCurrencyAttribute[$currency->id]['sign']     = $currency->sign;
//                        $fullCurrencyAttribute[$currency->id]['synchronization'] = ($currency->sync)? 1:0;
                    }
                }
                $products[$prodKey]['currencies'] = $fullCurrencyAttribute;
            }
        }

        $response = [
            'pagination' => [
                'total'        => $products->total(),
                'per_page'     => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'from'         => $products->firstItem(),
                'to'           => $products->lastItem()
            ],
            'products'   => $products->items(),
            //'currencies'    => $currencies,
            //'basicCurrency' => $basicCurrency
        ];

        return $response;
    }

    /**
     * @param $account
     * @param $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($account, $id)
    {
        //user
        $user = Auth::user();

        //Permissions
        if (!$user->userCan('edit product')) {
            abort(403);
        }

        //Получаем текущий продукт.
        $product = Product::whereId($id)->first();

        if(!$product){
            return response()->json(['errors' => __('messages.product.not_found')], 422);
        }

        //Получаем список всех дополнительных полей данного Аккаунта
        //Ключиком делаем поле id
        $customFieldsList = ProductCustomField::get()->keyBy('id');

        //Получаем список всех дополнительных полей только данного товара
        //Ключиком делаем (product_custom_field_id)
        $productCustomFields = $product->productCustomFieldValue()->get()->keyBy('product_custom_field_id');

        //Создаём новый массив дополнительных полей который будет содержать все дополнительные поля аккаунта
        //По сути это тот же массив дополнительных полей + значение текущего товара(если есть)

        $customFieldsArray = [];

        //todo: check!
        if ($customFieldsList->isNotEmpty()) {
            foreach ($customFieldsList as $key => $value) {
                $customFieldsArray[$value['id']] = $value;
                if (isset($productCustomFields[$key])) {
                    $customFieldsArray[$value['id']]['product_value'] = $productCustomFields[$key]['value'];
                }
            }
        }

        return view('pages/product-edit', [
            'product'           => $product,
            'page_type'         => 'edit',
            'customFieldsArray' => $customFieldsArray,
            'user'              => $user
        ]);
    }

    public function validateProduct(Request $request)
    {
        $rules = [
            'product_name'    => 'required|min:3',
            'product_article' => 'required',
            'product_cost'    => 'required|numeric'
        ];

//        $messsages = array(
//            'product_name.required'       => 'Введите имя продукта',
//            'product_name.min'            => 'Минимальная длинна названия: 3 символа',
//            'product_article.required'    => 'Введите артикул',
//            'product_cost.required'       => 'Введите цену',
//            'product_prime_cost.required' => 'Введите себестоимость',
//            'description.required' => 'Введите описание'
//        );

        $validator = Validator::make($request->product, $rules);

        if ($validator->fails()) {
            return $validator;
        } else {
            return true;
        }
    }

    /**
     * @param $account
     * @param $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($account, $id, Request $request)
    {
        $user = Auth::user();
        //Permissions
        if (!$user->userCan('edit product')) {
            abort(403);
        }

        $updateType = $request['updateType'];
        $product    = Product::whereId($id)->first();

        switch ($updateType) {
            // Only file update
            case 'file':
                //Сохраняем изображение
                $fileId = $request->product['fileId'];
                self::addFile($product->id, $fileId);
                break;
            // Global update
            default:
                $validate = self::validateProduct($request);
                if ($validate !== true) {
                    return response()
                        ->json(['status' => 'error', 'fields' => $validate->errors()]);
                }
                $product->name        = $request->product['product_name'];
                $product->article     = $request->product['product_article'];
                $product->description = $request->product['description'];
                $product->cost        = $request->product['product_cost'];
                $product->prime_cost  = 0;//$request->product['product_prime_cost'];
                $product->save();

                //Теперь работаем с дополнительными полями

                $product_dopfields = $request['product_dopfields'];
                if (is_array($product_dopfields)) {
                    foreach ($product_dopfields as $dopfield) {
                        $prodCustomFieldValue = ProductCustomFieldValue::whereProduct_id($id)
                                                                       ->whereProduct_custom_field_id($dopfield['customFieldId'])->first();

                        if ($prodCustomFieldValue) {
                            $prodCustomFieldValue->value = $dopfield['dopfieldValue'];
                            $prodCustomFieldValue->save();
                        } else {
                            $prodCustomFieldValue                          = new ProductCustomFieldValue;
                            $prodCustomFieldValue->product_id              = $id;
                            $prodCustomFieldValue->product_custom_field_id = $dopfield['customFieldId'];
                            $prodCustomFieldValue->value                   = $dopfield['dopfieldValue'];
                            $prodCustomFieldValue->save();
                        }
                    }
                }

                //Сохраняем изображение
                $fileId = $request->product['fileId'];
                self::addFile($product->id, $fileId);
                break;
        }
    }

    /**
     * @param $account
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($account)
    {
        $user = Auth::user();

        //Permissions
        if (!$user->userCan('create product')) {
            abort(403);
        }

        $product          = new Product();
        $customFieldsList = ProductCustomField::get()->keyBy('id')->toArray();

        return view('pages/product-edit', [
            'product'           => $product,
            'page_type'         => 'add',
            'customFieldsArray' => $customFieldsList,
            'user'              => $user
        ]);
    }

    /**
     * @param $account
     * @param $id
     * @param Request $request
     */
    public function addcustomfield($account, $id, Request $request)
    {
        //        $product_custom_field_id = intval($request['product_custom_field_id']);
        //        $product = Product::whereId($id)->first();
        //        $product->productCustomField()->attach([
        //            1 => [
        //                'product_id' => $id,
        //                'product_custom_field_id' => $product_custom_field_id,
        //                'value' => ''
        //            ]
        //        ]);
    }

    public function getProductFile($account, $id)
    {
        $product = Product::whereId($id)->first();
        $pivot   = $product->file->first();
        if ($pivot) {
            $file = File::whereId($pivot->file_id)->first();
        } else {
            return response()->json(['status' => 'error', 'message' => 'У продукта нет изображения']);
        }
        if ($file) {
            return response()
                ->json($file);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Не могу найти изображения в таблице.']);
        }
    }

    public function addFile($productId, $fileId)
    {
        if ($fileId !== null) {
            $file_product = FileProduct::whereProduct_id($productId)->first();
            if ($file_product) {
                $file_product->file_id = $fileId;
            } else {
                $file_product             = new FileProduct;
                $file_product->product_id = $productId;
                $file_product->file_id    = $fileId;
            }
            $file_product->save();
        }
    }

    /**
     * @param $account
     *
     * @return mixed
     */
    public function exportProducts($account)
    {
        $user = Auth::user();
        //Permissions
        if (!$user->userCan('import product')) {
            abort(403);
        }

        // Список товаров пользователя
        $products = Product::whereNull('removed')->get(
            ['id', 'name', 'article', 'description', 'cost'] //, 'prime_cost'
        );
        // Если товаров нет и экпортировать нечего
        if (!$products) {
            return false;
        }
        // Список дополнительныз полей пользователя
        $customFieldsList = ProductCustomField::get()->keyBy('id');
        if (is_object($products)) {
            foreach ($products as $key => $value) {
                $dopfields = $value->productCustomFieldValue()->whereProduct_id($value['id'])->get()->toArray();
                if (is_array($dopfields)) {
                    foreach ($dopfields as $dvalue) {
                        $products[$key]['Custom field:' . $dvalue['product_custom_field_id']] = $dvalue['value'];
                    }
                }
            }
        }
        // Генерируем название документа
        $name = 'Товары КП10_' . Carbon::now()->format('d.m.y');

        return Excel::create('products', function ($excel) use ($products, $customFieldsList) {
            // Заголовок документа
            $excel->setTitle('Товары');
            $excel->sheet('mySheet', function ($sheet) use ($products, $customFieldsList) {
                // Количество дополнительных полей каждый раз разное. Пример: sheet->getStyle('A1:'.$count.'1')
                $cellsAlphabet = [
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
                    'Z'
                ];

                // Количество полей (столбцов) в целом
                $fieldsCount = 5;
                if (is_object($customFieldsList)) {
                    $fieldsCount += count($customFieldsList);
                }
                // Устанавливаем жирный текст
                $sheet->getStyle('A1:' . $cellsAlphabet[$fieldsCount] . '1')->getFont()->setBold(true);
                // Ставим рамку
                $sheet->setBorder('A1:' . $cellsAlphabet[$fieldsCount] . (sizeOf($products) + 1), 'thin');
                // Ставим фон для первой строки
                $sheet->cells('A1:' . $cellsAlphabet[$fieldsCount] . '1', function ($cells) {
                    $cells->setBackground('#e3e8ef');
                });
                // Устанавливаем формат ячеек:
                $sheet->setColumnFormat([
                    'D' => '0.00',
                    'E' => '0.00'
                ]);
                // Устанавливаем выравнивание текста
                $sheet->cells('A1:' . $cellsAlphabet[$fieldsCount] . '1', function ($cells) {
                    $cells->setAlignment('center');
                });
                $sheet->cells('D1:E' . (sizeOf($products) + 1), function ($cells) {
                    $cells->setAlignment('left');
                });

                // Устанавливаем ширину колонок.
                $sheet->setAutoSize(true);

                // Устанавливаем ширину колонке "Описание"
                $sheet->setWidth('C', 50);

                // Устанавливаем textWrap полю description
                $sheet->getStyle('C1:C' . (sizeOf($products) + 1))->getAlignment()->setWrapText(true);

                // Устанавливаем выравнивание по вертикали
                $sheet->cells('A1:' . $cellsAlphabet[$fieldsCount] . (sizeOf($products) + 1), function ($cells) {
                    $cells->setValignment('center');
                });

                // Подготавливаем данные для експорта.
                $exportData         = [];
                $exportCustomFields = [];
                // Перебераем все товары:
                if (is_object($products)) {
                    foreach ($products as $key => $product) {
                        // Каждый товар подста
                        $exportData[$key] = [
                            'Артикул'         => $product->article,
                            'Название товара' => $product->name,
                            'Описание'        => $product->description,
                            'Цена'            => floatval($product->cost),
                            // 'Себестоимость'   => floatval($product->prime_cost),
                        ];
                        // Собираем дополнительные поля
                        $productCustomFields = $product->productCustomFieldValue()->get()->keyBy('product_custom_field_id')->toArray();
                        if ($customFieldsList) {
                            foreach ($customFieldsList as $field) {
                                // $exportCustomFields[$key] = array($field['name'] => $field['id']['value']);
                                $exportData[$key][$field['name']] = '';
                                if (isset($productCustomFields[$field['id']])) {
                                    $exportData[$key][$field['name']] = $productCustomFields[$field['id']]['value'];
                                }
                            }
                        }
                        // Изображегние
                        $exportData[$key]['Изображение'] = '';
                        $file_id                         = $product->file()->get()->first();
                        if ($file_id) {
                            $file = File::whereId($file_id->file_id)->get()->first();
                            if ($file) {
                                $file = $file->file;
                            }
                            $exportData[$key]['Изображение'] = $file;
                        };
                    }
                }
                $sheet->fromArray($exportData, null);
            });
        })->setFilename($name)->download();
    }

    public function getProgressExcel($account)
    {
        session_start();

        //TODO: excelImoirtProgresses => excelImportProgresses
        return response()->json(['status' => 'ok', 'progress' => isset($_SESSION['excelImoirtProgresses'])?$_SESSION['excelImoirtProgresses']:0]);
        session_write_close();
    }

    /**
     * Import products
     *
     * @param stirng $account
     * @param Request $request
     *
     * @return void
     */
    public function importProducts($account, Request $request)
    {
        $user = Auth::user();
        //Permissions

        if (!$user->userCan('import product')) {
            abort(403);
        }

        $existingProductsCount = input::get('existingProductsCount');
        $newProductsCount      = input::get('newProductsCount');

        $type = Input::get('type');
        try {
            Excel::load(
                Input::file('file'),
                function ($reader) use ($account, $type, $existingProductsCount, $newProductsCount, $user) {
                    $objExcel      = $reader->getExcel();
                    $sheet         = $objExcel->getSheet(0);
                    $highestRow    = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();

                    $productFromExel    = [];
                    $productTitlesArray = [];

                    // Получаем заголовки
                    $titles = $sheet->rangeToArray('A1:' . $highestColumn . '1', null, true, false);

                    //var_dump(print_r($titles, true));

                    for ($col = 1; $col <= count($titles[0]); $col++) {
                        $productTitlesArray[] = $titles[0][$col - 1];
                    }

                    for ($row = 2; $row <= $highestRow; $row++) {
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, null, true, false);
                        for ($coldata = 0; $coldata < count($rowData[0]); $coldata++) {
                            if (isset($productTitlesArray[$coldata])) {
                                if (isset($rowData[0][$coldata])) {
                                    $productFromExel[$row][$productTitlesArray[$coldata]] = $rowData[0][$coldata];
                                }
                            }
                        }
                    }

                    $countRows = count($productFromExel);
                    $counter   = 0;

                    foreach ($productFromExel as $row) {
                        switch ($type) {
                            case 'noconflict':
                                // Экпортируем все позиции.
                                $product = Product::whereArticle($row['Артикул'])->first();
                                if (!$product) {
                                    $product = new Product();
                                }
                                $countRowsForProgressBar = $newProductsCount;
                                break;
                            case 'onlynew':
                                // Загрузить только новые
                                $product = Product::whereArticle($row['Артикул'])->first();
                                if (!$product) {
                                    $product = new Product();
                                }
                                $countRowsForProgressBar = $newProductsCount;
                                break;
                            case 'addandreplace':
                                // Загрузить новые и обновить конфликты
                                $product = Product::whereArticle($row['Артикул'])->first();
                                if (!$product) {
                                    $product = new Product();
                                }
                                $countRowsForProgressBar = $newProductsCount + $existingProductsCount;
                                break;
                            case 'allnew':
                                //  Загрузить все как новые товар
                                $product                = new Product();
                                $searchDuplicateArticle = Product::whereArticle($row['Артикул'])->first();
                                if ($searchDuplicateArticle) {
                                    $counterDublicate = 2;
                                    while (Product::whereArticle($row['Артикул'] . ' /' . $counterDublicate)->first()) {
                                        $counterDublicate += 1;
                                    }
                                    $param          = preg_split("/\//", $row['Артикул']);
                                    $row['Артикул'] = $param[0] . ' /' . $counterDublicate;
                                }
                                $countRowsForProgressBar = $newProductsCount + $existingProductsCount;
                                break;
                        }

                        // ProgressBar

                        $counter++;

                        if ($counter % 20 == 0) {
                            session_start();
                            $_SESSION['excelImoirtProgresses'] = (100 / $countRowsForProgressBar) * $counter;
                            session_write_close();
                        }
                        if ($counter > $countRowsForProgressBar - 10) {
                            session_start();
                            $_SESSION['excelImoirtProgresses'] = 100;
                            session_write_close();
                        }

                        // end ProgressBar

                        $customFieldsList = ProductCustomField::get()->keyBy('id')->toArray();

                        // Регулярные поля
                        if (isset($row['Название товара'])) {
                            $product->name = $row['Название товара'];
                        }
                        
                        if (isset($row['Артикул'])) {
                            $product->article = $row['Артикул'];
                        }
                        if (isset($row['Описание'])) {
                            $product->description = $row['Описание'];
                        }
                        if (isset($row['Цена'])) {
                            $product->cost = str_replace(" ",'',$row['Цена']);
                        }
                        if (isset($row['Себестоимость'])) {
                            $product->prime_cost = str_replace(" ", '',$row['Себестоимость']);
                        }

                        $product->account_id = $user->accountId;

                        $product->save();

                        $lastInsertedId = $product->id;
                        // Дополнительные поля

                        if (is_array($customFieldsList)) {
                            foreach ($customFieldsList as $field) {
                                $transliteName = $field['name'];
                                //$transliteName = Str::slug($field['name']);
                                //$transliteName = str_replace('-', '_', $transliteName);

                                // Id дополнительного поля
                                $customFieldId = $field['id'];
                                // Значение дополнительного поля
                                if (isset($row[$transliteName])) {
                                    $customFieldValue = $row[$transliteName];

                                    $cf = ProductCustomFieldValue::whereProduct_id($lastInsertedId)->whereProduct_custom_field_id($customFieldId)->first();
                                    if ($cf) {
                                        $cf->product_id              = $lastInsertedId;
                                        $cf->product_custom_field_id = $field['id'];
                                        $cf->value                   = $customFieldValue;
                                        $cf->save();
                                    } else {
                                        $cf                          = new ProductCustomFieldValue();
                                        $cf->product_id              = $lastInsertedId;
                                        $cf->product_custom_field_id = $field['id'];
                                        $cf->value                   = $customFieldValue;
                                        $cf->save();
                                    }
                                }
                            }
                        }

                        //var_dump(print_r($row, true));

                        // Импорт изображения
                        if (isset($row['Изображение'])) {
                            $image = $row['Изображение'];
                        }

                        if (isset($image) AND !empty($image)) {

                            //var_dump('Есть картинка');

                            $file = File::wherefile($image)->first();

                            //var_dump(print_r($file, true));

                            if ($file) {
                                $fileId      = $file['id'];

                                //var_dump(print_r($fileId, true));

                                $fileProduct = FileProduct::whereFile_id($fileId)->whereProduct_id($lastInsertedId)->first();

                                //var_dump(print_r($fileProduct, true));

                                if ($fileProduct) {
                                    $fileProduct->product_id = $lastInsertedId;
                                    $fileProduct->file_id    = $fileId;
                                    $fileProduct->save();
                                } else {
                                    $fileProduct             = new FileProduct();
                                    $fileProduct->product_id = $lastInsertedId;
                                    $fileProduct->file_id    = $fileId;
                                    $fileProduct->save();
                                }
                            }
                        }
                    }
                }
            );
        } catch (\Exception $e) {
            // dd($e->getMessage());
            //TODO:
        }
        //TODO: не вистачає результату
    }

    public function importProductsCheck($account)
    {
        $user = Auth::user();
        //Permissions
        if (!$user->userCan('import product')) {
            abort(403);
        }

        $excelCheckResult = [];
        try {
            Excel::load(Input::file('file'), function ($reader) use ($account, &$excelCheckResult) {
                $excelCheckResult['newProducts']      = [];
                $excelCheckResult['existingProducts'] = [];

                $objExcel   = $reader->getExcel();
                $sheet      = $objExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $rowData    = $sheet->rangeToArray('A2:A' . $highestRow, null, true, false);
                foreach ($rowData as $row) {
                    $product = Product::whereArticle($row[0])->first();
                    if (!$product) {
                        if($row[0] !== NULL) {
                            $excelCheckResult['newProducts'][] = $row[0];
                        }
                    } else {
                        $excelCheckResult['existingProducts'][] = $row[0];
                    }
                }
            });
        } catch (\Exception $e) {
        }

        return response()->json(['status' => 'ok', 'files' => $excelCheckResult]);
    }

    /**
     * Delete selected products
     *
     * @param string $account
     * @param Request $request
     *
     * @return void
     */
    public function deletechecked($account, Request $request)
    {
        $user = Auth::user();

        //Permissions
        if (!$user->userCan('delete product')) {
            abort(403);
        }

        if (is_array($request['data'])) {
            foreach ($request['data'] as $id) {
                $Product = Product::whereId($id)->get()->first();
                if ($Product) {
                    $Product->delete();
                }
            }
        }

        //TODO: небхідно повертати результат
    }

    public function getVisibleProductCollumns($account)
    {
        $visibleProductFields = UserMeta::getMeta('visible-product-fields');
        if ($visibleProductFields) {
            $visibleProductFields = unserialize($visibleProductFields->meta_value);

            return response()->json($visibleProductFields);
        } else {
            $user                 = Auth::user();
            $userMeta             = new UserMeta;
            $enableProductColl    = ['collName', 'collDescription', 'collCost', 'collPrimeCost'];
            $userMeta->user_id    = $user->id;
            $userMeta->meta_key   = 'visible-product-fields';
            $userMeta->meta_value = serialize($enableProductColl);
            $userMeta->save();

            return response()->json($enableProductColl);
        }
    }

    public function setVisibleProductCollumns($account, Request $request)
    {
        $enableProductColl = serialize($request['enableProductColl']);
        $user              = Auth::user();
        $userMeta          = UserMeta::whereUserId($user->id)->whereMetaKey('visible-product-fields')->get()->first();
        if ($userMeta) {
            $userMeta->user_id    = $user->id;
            $userMeta->meta_key   = 'visible-product-fields';
            $userMeta->meta_value = $enableProductColl;
            $userMeta->save();
        }
    }
}
