<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function autocomplete(Request $request): JsonResponse
    {
        $product = Product::where('Name', 'LIKE', '%'.$request->get('term').'%')->get();

        return response()->json($product->toArray());
    }

    public function index(): View
    {
        $product = Product::all();

        return view('product.index')->withResults($product);
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), ['name' => 'required', 'productPrice' => 'required|numeric']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $product = new Product;
            $product->name = $request->get('name');
            $product->price = $request->get('productPrice');
            $product->save();

            if ($product->exists) {
                $this->updateProductCache();

                return response()->json(['success' => true, 'id' => $product->id, 'name' => $product->name, 'price' => $product->price]);
            } else {
                return response()->json(['errors' => 'Could not be added to the database']);
            }
        }
    }

    public function edit($id): View
    {
        return view('product.edit')->with('product', Product::find($id));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $v = Validator::make($request->all(), ['productName' => 'required', 'productPrice' => 'required|numeric']);

        if (! $v->passes()) {
            return response()->json(['errors' => $v->errors()]);
        } else {
            $product = Product::find($id);
            $product->Name = $request->get('productName');
            $product->Price = $request->get('productPrice');

            if ($product->save()) {
                $this->updateProductCache();

                return response()->json(['success' => true, 'message' => $product->name.' Successfully edited']);
            } else {
                return response()->json(['errors' => 'Could not be updated']);
            }
        }
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::find($id);

        $product->delete();

        if (! $product->exists) {
            $this->updateProductCache();

            return response()->json(['success' => true, 'message' => $product->name.' Successfully deleted']);
        } else {
            return response()->json(['errors' => 'Could not be deleted']);
        }
    }

    private function updateProductCache(): void
    {
        if (Cache::has('products')) {
            Cache::forget('products');
        }
    }
}
