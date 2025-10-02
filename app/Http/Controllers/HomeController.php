<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\InvoiceRepository;
use App\Repositories\OrderRepository;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly InvoiceRepository $invoiceRepository,
        private readonly OrderRepository $orderRepository
    ) {}

    public function getIndex(): View
    {
        $currentMonth = $this->invoiceRepository->getCurrentMonth();
        $id = $currentMonth ? $currentMonth->id : 0;

        $orders = $this->orderRepository->findBy('invoice_group_id', $id);
        // Sort by ID descending
        $orders = $orders->sortByDesc('id');

        return view('home.index')->with('orders', $orders);
    }
}
