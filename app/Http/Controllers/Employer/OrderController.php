<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->where('employer_id', $request->user('employer')->id)
            ->with('plan')
            ->latest('id')
            ->paginate(10);

        return view('employer.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        abort_unless($order->employer_id === auth('employer')->id(), 404);

        $order->load(['plan', 'subscription']);

        return view('employer.orders.show', compact('order'));
    }
}
