<?php

namespace App\Http\Controllers\Api\V3;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;


/**

 * @OA\Tag(
 *     name="Orders",
 *     description="Order Management"
 * )
 */


class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v3/orders",
     *     summary="Get a list of orders",
     *     description="Retrieve a paginated list of orders. Clients see their own orders, while admins see all orders.",
     *     tags={"Orders"},
     *     security={{"sanctum": {}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by order status (pending, shipped, etc.)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter orders created after this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter orders created before this date (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="customer",
     *         in="query",
     *         description="Search by customer name or email",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of orders",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Order"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
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
    public function updateStatus($OrderId, $status)
    {
        $order = Order::where('id', $OrderId)->first();
        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        if (!in_array($status, ['pending', 'in progress', 'shipped', 'canceled'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid status'
            ], 400);
        }
        $oldStatus = $order->status;

        $order->status = $status;
        $order->save();

        $order->user->notify(new OrderStatusUpdated($order, $oldStatus));
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
