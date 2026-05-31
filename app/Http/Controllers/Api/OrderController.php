<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Order::class);

        $orders = $request->user()->isAdmin()
            ? Order::with(['product:id,name', 'user:id,name'])->get()
            : Order::where('user_id', $request->user()->id)->with('product:id,name')->get();

        return response()->json($orders);
    }

    public function show(Request $request, Order $order)
    {
        $this->authorize('view', $order);

        $order->load(['product:id,name,price', 'user:id,name']);

        return response()->json($order);
    }

    public function store(StoreOrderRequest $request)
    {
        $product = Product::active()->findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return response()->json(['message' => '在庫が不足しています。'], 409);
        }

        $order = Order::create([
            'user_id'     => $request->user()->id,
            'product_id'  => $product->id,
            'quantity'    => $request->quantity,
            'total_price' => $product->price * $request->quantity,
        ]);

        $product->decrement('stock', $request->quantity);

        return response()->json($order->load('product:id,name,price'), 201);
    }

    public function cancel(Request $request, Order $order)
    {
        $this->authorize('cancel', $order);

        if (! $order->isPending()) {
            return response()->json(['message' => '注文中の注文のみキャンセルできます。'], 409);
        }

        $order->update(['status' => 'cancelled']);
        $order->product->increment('stock', $order->quantity);

        return response()->json(['message' => '注文をキャンセルしました。']);
    }

    public function return(Request $request, Order $order)
    {
        $this->authorize('return', $order);

        if (! $order->isPending()) {
            return response()->json(['message' => '注文中の注文のみ返品申請できます。'], 409);
        }

        $order->update(['status' => 'return_requested']);

        return response()->json(['message' => '返品申請を受け付けました。']);
    }

    public function returnApprove(Order $order)
    {
        $this->authorize('returnApprove', Order::class);

        if (! $order->isReturnRequested()) {
            return response()->json(['message' => '返品申請中の注文のみ承認できます。'], 409);
        }

        $order->update(['status' => 'return_approved']);
        $order->product->increment('stock', $order->quantity);

        return response()->json(['message' => '返品を承認しました。']);
    }

    public function returnReject(Order $order)
    {
        $this->authorize('returnReject', Order::class);

        if (! $order->isReturnRequested()) {
            return response()->json(['message' => '返品申請中の注文のみ却下できます。'], 409);
        }

        $order->update(['status' => 'return_rejected']);

        return response()->json(['message' => '返品申請を却下しました。']);
    }
}
