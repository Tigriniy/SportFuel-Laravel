<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->orders()
            ->withCount('items')
            ->latest()
            ->orderByDesc('id')
            ->paginate(12);

        return view('orders.index', ['orders' => $orders]);
    }

    public function show(Request $request, string $order): View
    {
        $order = $request->user()->orders()->with('items')->findOrFail($order);

        return view('orders.show', ['order' => $order]);
    }
}
