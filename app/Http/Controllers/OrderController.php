<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * جلب طلبات المستخدم باستخدام معرف المستخدم
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserOrders($userId)
    {
        // التحقق من وجود المستخدم أولاً (اختياري)
        if (!User::where('id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // جلب جميع طلبات المستخدم
        $orders = Order::where('user_id', $userId)->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }
    
    /**
     * إنشاء طلب جديد للمستخدم
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function store(Request $request)
    // {
    //     // تحقق من صحة البيانات المدخلة
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //     ]);

    //     // جلب محتويات العربة للمستخدم
    //     $cartItems = Cart::where('user_id', $request->user_id)->with('product')->get();

    //     // التحقق إذا كانت العربة فارغة
    //     if ($cartItems->isEmpty()) {
    //         return response()->json(['error' => 'Cart is empty'], 400);
    //     }

    //     // حساب إجمالي سعر الطلب
    //     $totalPrice = $cartItems->sum(function ($item) {
    //         return $item->product->price * $item->quantity;
    //     });

    //     // إنشاء الطلب
    //     $order = Order::create([
    //         'user_id' => $request->user_id,
    //         'total_price' => $totalPrice,
    //         'status' => 'pending',  // حالة الطلب الافتراضية هي "قيد الانتظار"
    //     ]);

    //     // إضافة المنتجات إلى الطلب
    //     foreach ($cartItems as $item) {
    //         $order->products()->attach($item->product_id, [
    //             'quantity' => $item->quantity,
    //             'price' => $item->product->price,
    //         ]);
    //     }

    //     // تفريغ العربة بعد إنشاء الطلب
    //     Cart::where('user_id', $request->user_id)->delete();

    //     return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    // }

    /**
     * جلب تفاصيل طلب معين باستخدام معرف الطلب
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // جلب الطلب مع المنتجات المرتبطة به
        $order = Order::with('products')->find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json(['order' => $order]);
    }

    /**
     * تحديث حالة الطلب (مثال: إتمام أو إلغاء أو قيد الانتظار)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // تحقق من صحة الحالة المدخلة
        $request->validate([
            'status' => 'required|in:pending,delivered,cancelled',
        ]);

        // العثور على الطلب
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // تحديث حالة الطلب
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Order status updated', 'order' => $order]);
    }

    /**
     * حذف طلب معين باستخدام معرف الطلب
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // العثور على الطلب
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // حذف الطلب
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
    public function getFarmerOrders()
    {
        $farmerId = auth('sanctum')->id();

        $orders = Order::whereHas('orderItems.product', function ($query) use ($farmerId) {
            $query->where('farmer_id', $farmerId);
        })->with(['orderItems.product'])->get();

        return response()->json([
            'orders' => $orders
        ]);
    }
}
