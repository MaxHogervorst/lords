<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    public function autocomplete(Request $request): JsonResponse
    {
        $product = $this->productRepository->search($request->get('term'));

        return response()->json($product->toArray());
    }

    public function index(): View
    {
        $product = $this->productRepository->all();

        return view('product.index')->withResults($product);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = $this->productRepository->create([
            'name' => $validated['name'],
            'price' => $validated['productPrice'],
        ]);

        if ($product->exists) {
            $this->updateProductCache();

            return response()->json(['success' => true, 'id' => $product->id, 'name' => $product->name, 'price' => $product->price]);
        } else {
            return response()->json(['errors' => 'Could not be added to the database']);
        }
    }

    public function edit(string $id): View
    {
        $product = $this->productRepository->find((int) $id);

        return view('product.edit')->with('product', $product);
    }

    public function update(StoreProductRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        $product = $this->productRepository->update($product, [
            'name' => $validated['productName'],
            'price' => $validated['productPrice'],
        ]);

        $this->updateProductCache();

        return response()->json(['success' => true, 'message' => $product->name.' Successfully edited']);
    }

    public function destroy(string $id): JsonResponse
    {
        $product = $this->productRepository->find((int) $id);

        $deleted = $this->productRepository->delete($product);

        if ($deleted) {
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
