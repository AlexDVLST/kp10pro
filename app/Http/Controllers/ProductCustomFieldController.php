<?php

namespace App\Http\Controllers;

use App\Models\ProductCustomField;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCustomFieldController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $page = Page::whereSlug('product-custom-fields')->first();

        return view('pages/product-custom-fields', ['user' => $user, 'page' => $page]);
    }

    public function show($account, $id)
    {
        $productdf = ProductCustomField::whereId($id)->first();

        return response()
            ->json($productdf);
    }

    public function getProductDopFieldsList($account)
    {
        $user = Auth::user();
        $offers = ProductCustomField::paginate(10);
        $response = [
            'pagination' => [
                'total'        => $offers->total(),
                'per_page'     => $offers->perPage(),
                'current_page' => $offers->currentPage(),
                'last_page'    => $offers->lastPage(),
                'from'         => $offers->firstItem(),
                'to'           => $offers->lastItem()
            ],
            'data'       => $offers
        ];

        return $response;
    }

    public function getProductDopFieldsListJson($account)
    {
        return response()->json($this->getProductDopFieldsList($account));
    }

    public function store($account, Request $request)
    {
        $user = Auth::user();
        // Todo Реализовать обработку ошибок
        $productdf = new ProductCustomField();
        $productdf->account_id = $user->accountId;
        $productdf->name = $request->product_dop_field['name'];
        $productdf->type = $request->product_dop_field['type'];
        $productdf->save();
    }

    public function destroy($account, $id)
    {
        //todo !
        ProductCustomField::whereId($id)->first()->delete();
    }

    public function update($account, $id, Request $request)
    {
        $productdf = ProductCustomField::whereId($id)->first();
        ;
        $productdf->name = $request->product_dop_field['name'];
        $productdf->type = $request->product_dop_field['type'];
        $productdf->save();
    }
}
