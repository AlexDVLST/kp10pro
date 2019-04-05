<?php

namespace App\Helpers;

use App\Models\File;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File as HttpFile;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Intervention\Image\Facades\Image;

class StorageHelper
{
    /**
     * @return string
     */
    public static function getUploadPath()
    {
        return 'public/' . Auth::user()->domain;
    }

    /**
     * @return string
     */
    public static function getStoragePath()
    {
        return 'storage/' . Auth::user()->domain;
    }

    /**
     * @param $template
     *
     * @return string
     */
    public static function getUploadPathByTemplate($template)
    {
        $path = self::getUploadPath();

        switch ($template) {
            case 1: //Логотип
                $path .= '/Логотипы'; //Purple
                break;
            case 2: //Обложка
                $path .= '/Обложки'; //красный
                break;
            case 3: //Фото товара
                $path .= '/Товары'; //зеленый
                break;
            case 4: //Фото сотрудника
                $path .= '/Сотрудники'; //желтый
                break;
            case 5: //Фото галереи
                $path .= '/Галерея'; //синий
                break;
            // case 6: //Без изменений
            //     $path = '';
            //     break;
            default:
                $path = self::getUploadPath(); //Primary
                break;
        }

        return $path;
    }

    /**
     * Get assets with folder for current user
     * @return array
     */
    public static function getStorageData()
    {
        //Set locale globally for the project
        //Fix basename()
        // setlocale(LC_ALL, 'ru_RU.UTF-8');

        // Get the currently authenticated user...
        $user = Auth::user();

        $assets = [];
        $path   = self::getUploadPath();

        //Get file stored in DB
        $files = File::all();

        $directories = collect(Storage::allDirectories($path))->map(function ($directory) {
            $pathArr = explode('/', $directory); //get diractory name
            return [
                'name'       => $pathArr[count($pathArr) - 1],
                'path'       => $directory,
                'countFiles' => count(Storage::allFiles($directory))
            ];
        });

        if ($directories) {
            //Sorting
            $directories = $directories->sortBy(function ($item, $key) {
                $index = 5;
                if (strpos($item['name'], 'Галерея') !== false) {
                    $index = 0;
                }
                if (strpos($item['name'], 'Товары') !== false) {
                    $index = 1;
                }
                if (strpos($item['name'], 'Логотипы') !== false) {
                    $index = 2;
                }
                if (strpos($item['name'], 'Обложки') !== false) {
                    $index = 3;
                }
                if (strpos($item['name'], 'Сотрудники') !== false) {
                    $index = 4;
                }

                return $index;
            });

            foreach ($directories as $directory) {
                $assets[] = [
                    'type'       => 'folder',
                    'folder'     => str_replace('/' . $directory['name'], '', $directory['path']), //using for filtering
                    'name'       => $directory['name'],
                    'path'       => $directory['path'],
                    'countFiles' => $directory['countFiles']
                ];
            }
        }

        if ($files) {
            foreach ($files as $file) {
                //Don't show uncropped files
                if ($file->cropped) {
                    $assets[] = self::prepareImageAssets($file);
                }
            }
        }

        return $assets;
    }

    /**
     * Prepare image for assets
     *
     * @param File $file
     * @return array
     */
    public static function prepareImageAssets($file)
    {
        return [
            'id'      => $file['id'],
            'type'    => 'image',
            'name'    => $file['name'],
            'folder'  => $file['path'], //using for filtering
            'src'     => Storage::url($file['path'] . '/' . $file['file']),
            'cropped' => $file['cropped'],
            'file'    => $file['file']
        ];
    }

    /**
     * @param $ext
     *
     * @return bool
     */
    public static function isAllowedExtension($ext)
    {
        $allowed = [
            'gif',
            'png',
            'jpg',
            'jpeg',
            'JPG',
            'tiff'
        ];

        if (in_array($ext, $allowed)) {
            return true;
        }

        return false;
    }

    /**
     * Store local file and add it into DB
     *
     * @param HttpFile $file
     * @param $uploadPath
     *
     * @return array|bool
     */

    /**
     * @param UploadedFile $file
     * @param $uploadPath
     * @param $name
     * @param $cropped
     *
     * @return array|bool
     */
    public static function storeFile(UploadedFile $file, $uploadPath, $name, $cropped, $template = 0)
    {
        $user    = Auth::user();
        $account = $user->domain;

        //unique name
        $md5Name   = md5(microtime());
        $extension = $file->extension();

        if (self::isAllowedExtension($extension)) {
            //Store files on server
            $storedFile = Storage::putFileAs($uploadPath, $file, $md5Name . '.' . $extension);
            //Get url
            $path = Storage::url($storedFile);
            //Optimize uploaded image
            ImageOptimizer::optimize(storage_path() . '/app/' . $storedFile);

            if (!$name) {
                //remove extension from the file;
                $nameArray = explode('.', $file->getClientOriginalName());
                $name      = $nameArray[0];
            }

            //Resize image with template
            if ($cropped && $template) {
                //For different templates may store new file
                $resizedData = StorageHelper::resizeImageByTemplate([
                    'template'   => $template,
                    'name'       => $md5Name,
                    'extension'  => $extension,
                    'uploadPath' => $uploadPath
                    ]);

                //If extension was changed
                if ($extension != $resizedData['extension']) {
                    //Replace extension with new one
                    $extension = $resizedData['extension'];
                }
            }

            //Store file in DB
            $createdFile = File::create([
                'file'       => $md5Name . '.' . $extension,
                'account_id' => $user->accountId,
                'name'       => $name,
                'path'       => $uploadPath,
                'cropped'    => $cropped
            ]);

            if ($createdFile) {
                return [
                    'id'      => $createdFile['id'],
                    'name'    => $name,
                    'path'    => url($path),
                    'file'    => $md5Name . '.' . $extension,
                    'cropped' => $cropped
                ];
            }
        }

        return false;
    }

    /**
     * Resize uploaded imaged relative to template
     *
     * @param array $params
     * @return array
     */
    public static function resizeImageByTemplate($params)
    {
        $template             = $params['template'];
        $name                 = $params['name'];
        $extension            = $params['extension'];
        $uploadPath           = $params['uploadPath'];
        $fileName             = $name . '.' . $extension;
        $path                 = storage_path() . '/app/' . $uploadPath . '/' . $fileName;
        $pathWithoutExtension = storage_path() . '/app/' . $uploadPath . '/' . $name;

        $img = '';
        switch ($template) {
            case 1: //Логотип
                $img = Image::make($path)->resize(335, 78)->save();
                break;
            case 2: //Обложка
                $img = Image::make($path)->resize(1300, 300)->save();
                break;
            case 3: //Фото товара
                $img = Image::make($path)->resize(400, 400)->save();
                break;
            case 4: //Фото сотрудника
                // $img = Image::make($path)->resize(300, 300)->save();
                break;
            case 5: //Фото галереи
                $img = Image::make($path)->resize(450, 300)->save();
                break;
            default:
                $img = Image::make($path)->resize(900, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save();
                break;
        }

        return ['extension' => $extension];
    }
}
