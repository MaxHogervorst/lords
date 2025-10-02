<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Repositories\InvoiceRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly InvoiceRepository $invoiceRepository,
        private readonly ProductRepository $productRepository
    ) {}

    /**
     * Store a newly created resource in storage.
     */
    public function postStore(StoreOrderRequest $request, string $type): JsonResponse
    {
        $validated = $request->validated();

        if ($type == 'Member') {
            $type = 'App\Models\Member';
        } else {
            $type = 'App\Models\Group';
        }

        $currentMonth = $this->invoiceRepository->getCurrentMonth();

        $order = $this->orderRepository->create([
            'invoice_group_id' => $currentMonth->id,
            'ownerable_id' => $validated['memberId'],
            'ownerable_type' => $type,
            'product_id' => $validated['product'],
            'amount' => $validated['amount'],
        ]);

        if ($order->exists) {
            // Load the product relationship
            $product = $this->productRepository->find($order->product_id);

            return response()->json([
                'success' => true,
                'date' => date('Y-m-d G:i:s'),
                'product' => $product->name,
                'amount' => $order->amount,
                'product_id' => $product->id,
                'member_id' => $order->ownerable_id,
                'message' => 'order successfully',
            ]);
        } else {
            return response()->json(['errors' => 'Could not be added to the database']);
        }
    }
}
