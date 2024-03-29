<?php namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function autocomplete()
    {
        $product = Product::where('Name', 'LIKE', '%' . Input::get('term') . '%')->get();
        return Response::json($product->toArray());
    }

    public function index()
    {
        $product = Product::all();
        return view('product.index')->withResults($product);
    }

    public function store()
    {
        $v = Validator::make(Input::all(), ['name' => 'required', 'productPrice' => 'required|numeric']);

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $product = new Product;
            $product->name = Input::get('name');
            $product->price = Input::get('productPrice');
            $product->save();

            if ($product->exists) {
                $this->updateProductCache();
                return Response::json(['success' => true, 'id' => $product->id, 'name' => $product->name, 'price' => $product->price]);
            } else {
                return Response::json(['errors' => 'Could not be added to the database']);
            }
        }
    }

    public function edit($id)
    {
        return view('product.edit')->with('product', Product::find($id));
    }

    public function update($id)
    {
        $v = Validator::make(Input::all(), ['productName' => 'required', 'productPrice' => 'required|numeric']);

        if (!$v->passes()) {
            return Response::json(['errors' => $v->errors()]);
        } else {
            $product = Product::find($id);
            $product->Name = Input::get('productName');
            $product->Price = Input::get('productPrice');

            if ($product->save()) {
                $this->updateProductCache();
                return Response::json(['success' => true, 'message' => $product->name . ' Successfully edited']);
            } else {
                return Response::json(['errors' => 'Could not be updated']);
            }
        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);

        $product->delete();

        if (!$product->exists) {
            $this->updateProductCache();
            return Response::json(['success' => true, 'message' => $product->name . ' Successfully deleted']);
        } else {
            return Response::json(['errors' => 'Could not be deleted']);
        }
    }

    private function updateProductCache()
    {
        if (Cache::has('products')) {
            Cache::forget('products');
        }
    }
}
