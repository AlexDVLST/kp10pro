<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Help;
use App\Models\HelpSection;
use Illuminate\Support\Facades\DB;

class HelpController extends Controller
{
    public function __construct()
    {
        //Permissions
        $this->middleware(['permission:admin help']);
    }
    /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
    public function index()
    {
        return view('admin.helps');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store($account, Request $request)
    {
        $request->validate([
            'name'         => 'required|string',
            'sectionId'    => 'required|numeric',
            'video'        => 'required|string',
            'externalLink' => 'nullable|string',
        ]);

        $help = [];

        try {
            DB::transaction(function () use ($request, &$help) {
                $user = Auth::user();

                $help = Help::create([
                    'name'            => $request->input('name'),
                    'video'           => $request->input('video'),
                    'section_id'      => $request->input('sectionId'),
                    'external_link'   => $request->input('externalLink'),
                    'creator_user_id' => $user->id,
                    'editor_user_id'  => $user->id,
                ]);
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        $help = Help::with('section', 'creator', 'editor')->whereId($help->id)->first();

        return response()->json($help);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update($account, Request $request, $id)
    {
        $request->validate([
            'name'         => 'required|string',
            'sectionId'    => 'required|numeric',
            'video'        => 'required|string',
            'externalLink' => 'nullable|string',
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $user = Auth::user();

                Help::whereId($id)->update([
                    'name'            => $request->input('name'),
                    'video'           => $request->input('video'),
                    'section_id'      => $request->input('sectionId'),
                    'external_link'   => $request->input('externalLink'),
                    'editor_user_id'  => $user->id,
                ]);
            });
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        $help = Help::with('section', 'creator', 'editor')->whereId($id)->first();

        return response()->json($help);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($account, $id)
    {
        try {
            Help::whereId($id)->delete();
        } catch (Exception $e) {
            return response()->json(['errors' => $e->getMessage()], 422);
        }

        return response()->json(['message' => __('messages.admin.help.delete.success')]);
    }

    /**
     * List helps
     *
     * @param string $account
     * @return json
     */
    public function listJson($account)
    {
        return response()->json(Help::with('section', 'creator', 'editor')->get());
    }

    /**
     * Sections list
     *
     * @param string $account
     * @return json
     */
    public function sectionListJson($account)
    {
        return response()->json(HelpSection::all());
    }
}
