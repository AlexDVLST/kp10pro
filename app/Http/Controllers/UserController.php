<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserMeta;

class UserController extends Controller
{
    /**
     * Get user meta value
     *
     * @param string $account
     * @param string $metaKey
     * @return json
     */
    public function meta($account, $metaKey)
    {
        return response()->json(UserMeta::getMeta($metaKey));
    }

    /**
     * Update user meta
     *
     * @param string $metaKey
     * @param string $metaValue
     * @return json
     */
    public function updateMeta($account, $metaKey, Request $request)
    {
        return response()->json(UserMeta::updateMeta($metaKey, $request->input('value')));
    }
}
