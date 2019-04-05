<?php

namespace App\Http\Controllers;

use App\Helpers\StorageHelper;
use App\Models\File;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class FileManagerController extends Controller
{
    /**
     * @param $account
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($account)
    {
        $user = Auth::user();

        if (!$user->userCan('view file-manager')) {
            abort(403);
        }

        $page = Page::whereSlug('file-manager')->first();

        return view('pages.file-manager', [
            'user'        => $user,
            'page'        => $page,
            'storageData' => StorageHelper::getStorageData(),
            'uploadPath'  => StorageHelper::getUploadPath()
        ]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        $files      = $request->file('files') ? $request->file('files') : [];
        $uploadPath = $request->input('path') ? $request->input('path') : StorageHelper::getUploadPath();
        $result     = [];

        $validator = Validator::make($files, [
            '*' => 'image|max:2000'
        ]);

        if ($validator->fails()) {
            return response()
                ->json(['errors' => $validator->errors()], 422);
        }

        if (is_array($files) && !empty($files)) {
            foreach ($files as $file) {
                $storedFile = StorageHelper::storeFile($file, $uploadPath, '', 0);

                if ($storedFile) {
                    $result['files'][] = [
                        'name'    => $storedFile['name'],
                        'src'     => $storedFile['path'],
                        'file'    => $storedFile['file'],
                        'folder'  => $uploadPath,
                        'cropped' => 0
                    ];
                }
            }
        }

        //Errors
        if (empty($result)) {
            return response()
                ->json(['errors' => __('messages.file_type_error')], 422);
        }

        $result['folder'] = $uploadPath;

        return response()
            ->json($result);
    }

    /**
     * @param $account
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCropped($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        $image    = $request->file('croppedImage');
        $name     = $request->input('name');
        $file     = $request->input('file');
        $template = $request->input('template');
        $filePath = $request->input('path'); //new upload path for file

        $uploadPath = StorageHelper::getUploadPathByTemplate($template);

        $path = !$filePath ? $uploadPath : $filePath;

        //Find file in DB
        $fileDb = File::whereFile($file)->first();

        if ($fileDb) {
            //Path from db
            $pathDb   = $fileDb->path;

            //Check if file exists
            if (Storage::exists($pathDb . '/' . $file)) {
                //Save new file name
                if ($fileDb->delete()) {
                    //Delete old file
                    Storage::delete($pathDb . '/' . $file);

                    $storedFile = StorageHelper::storeFile($image, $path, $name, 1, $template);

                    if ($storedFile) {
                        $result = [
                            'success' => __('messages.file_store_success'),
                            'fileId'  => $storedFile['id'],
                            'fileSrc' => $storedFile['path']
                        ];
                    } else {
                        $result = ['errors' => __('messages.file_store_error')];
                    }
                } else {
                    $result = ['errors' => __('messages.mysql_delete_error')];
                }
            } else {
                $result = ['errors' => __('messages.file_not_found')];
            }
        } else {
            $result = ['errors' => __('messages.mysql_empty_result')];
        }

        //Errors
        if (isset($result['errors'])) {
            return response()
                ->json($result, 422);
        }

        return response()
            ->json($result);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createFolder(Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        $path   = $request->input('path');
        $folder = $request->input('folder');

        if ($path && $folder) {
            $result = Storage::makeDirectory($path . '/' . $folder);

            return response()
                ->json($result);
        }

        return response()
            ->json(['errors' => __('messages.file_store_error')], 422);
    }

    /**
     * @param $account
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFolder($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        $path = $request->input('path');

        $response = [];

        if ($path) {
            $result = Storage::deleteDirectory($path);

            if ($result) {
                //Delete files from DB by folder
                try {
                    File::where('path', 'like', $path . '%')
                        ->delete();
                } catch (\Exception $e) {
                    $response = ['errors' => __('messages.mysql_delete_error')];
                }
            }
        }

        if (!empty($response['errors'])) {
            return response()
                ->json($response, 422);
        }

        return response()
            ->json($response);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteFile($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        $file     = $request->input('file');
        $folder   = $request->input('folder');
        $response = [];

        //create array from file
        if (!is_array($file)) {
            $file = [$file];
        }

        try {
            foreach ($file as $fileName) {
                //If file exist in DB
                $fileDb = File::where(['file' => $fileName])->first();

                if ($fileDb) {
                    $fileDb->delete();
                }

                Storage::delete($folder . '/' . $fileName);
            }
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        if (!empty($response['errors'])) {
            return response()
                ->json($response, 422);
        }

        return response()
            ->json($response);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStorageDataJson()
    {
        $user = Auth::user();

        $needCropp = File::whereCropped(0)->get()
            ->map(function ($file) {
                return StorageHelper::prepareImageAssets($file);
            });

        return response()
            ->json([
                'storageData' => StorageHelper::getStorageData(),
                'path'        => StorageHelper::getUploadPath(),
                'needCropp'   => $needCropp
            ]);
    }

    /**
     * @param $account
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveFile($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        $file     = $request->input('file');
        $folder   = $request->input('folder');
        $response = [];

        //create array from file
        if (!is_array($file)) {
            $file = [$file];
        }

        if ($file && $folder && is_array($file) && !empty($file)) {
            try {
                foreach ($file as $fileName) {
                    $storedFile = File::where(['file' => $fileName])->first();

                    if ($storedFile) {
                        if (Storage::move($storedFile->path . '/' . $fileName, $folder . '/' . $fileName)) {
                            //set new path
                            $storedFile->path = $folder;
                            $storedFile->save();
                        } else {
                            $response['errors'][] = __('messages.file_move_error');
                        }
                    } else {
                        $response['errors'][] = __('messages.mysql_empty_result');
                    }
                }
            } catch (\Exception $e) {
                $response['errors'][] = __('messages.mysql_empty_result');
            }
        } else {
            $response['errors'][] = __(''); //todo !!!
        }

        if (!empty($response['errors'])) {
            return response()
                ->json($response, 422);
        }

        return response()
            ->json($response);
    }

    /**
     * @param $account
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyFile($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        $file     = $request->input('file');
        $folder   = $request->input('folder');
        $response = [];

        //create array from file
        if (!is_array($file)) {
            $file = [$file];
        }

        if ($file && $folder && is_array($file) && !empty($file)) {
            try {
                foreach ($file as $fileName) {
                    $dbFile = File::where(['file' => $fileName])->first();

                    if ($dbFile) {
                        $name    = $dbFile->name;
                        $cropped = $dbFile->cropped;
                        //current file
                        $filePath = str_replace('public', 'storage', $dbFile->path) . '/' . $dbFile->file;

                        $storedFile =
                            StorageHelper::storeFile(new UploadedFile($filePath, $name), $folder, '', $cropped);

                        if ($storedFile) {
                            $response[] = $storedFile;
                        } else {
                            $response['errors'][] = __('messages.file_store_error');
                        }
                    } else {
                        $response['errors'][] = __('messages.mysql_empty_result');
                    }
                }
            } catch (\Exception $e) {
                $response['errors'][] = __('messages.mysql_empty_result');
            }
        } else {
            $response['errors'][] = __(''); //todo !!!
        }

        if (!empty($response['errors'])) {
            return response()
                ->json($response, 422);
        }

        return response()
            ->json($response);
    }

    /**
     * @param $account
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function renameFile($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        //Validate input data
        $request->validate([
            'name' => 'required|string',
            'file' => 'required|string',
        ]);

        $file     = $request->input('file');
        $name     = $request->input('name');
        $response = [];

        $dbFile = File::where(['file' => $file])->first();

        if (!$dbFile) {
            return response()
                ->json(['errors' => __('messages.file.not_found')], 422);
        }
        try {
            $dbFile->name = $name;
            $dbFile->save();
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.file.rename.success')]);
    }

    /**
     * @param $account
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function renameFolder($account, Request $request)
    {
        $user = Auth::user();

        if (!$user->userCan('edit file-manager')) {
            return response()
                ->json(['errors' => __('messages.permission.denied')], 422);
        }

        //Validate input data
        $request->validate([
            'name' => 'required|string',
            'path' => 'required|string',
        ]);

        $path     = $request->input('path');
        $name     = $request->input('name');
        $folder   = $request->input('folder');
        $response = [];

        try {
            File::where(['path' => $path])
               ->update(['path' => $folder . '/' . $name]);

            //Rename folder
            rename(Storage::path($path), Storage::path($folder . '/' . $name));
        } catch (\Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.folder.rename.success')]);
    }
}
