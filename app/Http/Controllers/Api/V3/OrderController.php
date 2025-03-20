<?php

namespace App\Http\Controllers\Api\V3;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;


class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        if (Auth::user()->hasRole('client')) {
            $query = Order::where('user_id', $request->user()->id)->with('orderItems');
        } else {
            $query = Order::with(['user', 'orderItems']);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has(['start_date', 'end_date'])) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        if ($request->has('customer')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer . '%')
                    ->orWhere('email', 'like', '%' . $request->customer . '%');
            });
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(10);

        $orders->getCollection()->each(function ($order) {
            $totals = Helper::calculateTotalHelper($order->orderItems);
            $order->total_before_tax = $totals['total_before_tax'];
            $order->total_tax = $totals['total_tax'];
            $order->total_after_tax = $totals['total_after_tax'];
            $order->total_discount = $totals['total_discount'];
            $order->total_final = $totals['total_final'];
        });

        return response()->json($orders);
    }

    // cancel order function
    public function cancel()
    {

        return response()->json([
            'status' => 'success',
            'message' => 'payment has been canceled',
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $order = Order::with('orderItems')
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'order' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,in progress,shipped,canceled',
        ]);

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'message' => 'Statut de la commande mis à jour avec succès.',
            'order' => $order
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Order deleted successfully'
        ]);
    }
}
