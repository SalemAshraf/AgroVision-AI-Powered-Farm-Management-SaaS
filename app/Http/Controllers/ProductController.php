<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Product;
use App\Models\Crop;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\Address;
use App\Models\Order;
use App\Models\Category;
use App\Models\Orderitem;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ProductController extends Controller
{


    /**
     * Retrieve all product categories.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllCategories()
    {
        $categories = Category::all();

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Add a product from a crop.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */

public function addProductFromCrop(Request $request)
{
    // ✅ تحقق من البيانات المرسلة
    $validatedData = $request->validate([
        'crop_id' => 'required|exists:crops,id',
        'product_name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0.01',
        'description' => 'nullable|string',
        'category_id' => 'required|exists:categories,id',
        'quantity' => 'nullable|integer|min:1',
        'images' => 'nullable|string'
    ]);

    // ✅ التحقق من وجود المنتج مسبقًا
    $existingProduct = Product::where('crop_id', $request->crop_id)
                              ->where('name', $request->product_name)
                              ->first();

    if ($existingProduct) {
        return response()->json([
            'error' => 'هذا المنتج مسجل بالفعل',
            'existing_product' => $existingProduct
        ], 409);
    }

    // ✅ جلب بيانات المحصول
    $crop = Crop::findOrFail($request->crop_id);

    // ✅ الصورة: لو مفيش صورة مرسلة، استخدم صورة المحصول كما هي
    $imageInput = $request->input('images');
    $imagePath = null;

    if ($imageInput) {
        // فقط نتحقق من وجودها في التخزين، بدون فك أو نسخ
        if (Storage::disk('public')->exists('photos/' . $imageInput)) {
            $imagePath = 'photos/' . $imageInput;
        }
    } elseif ($crop->photo) {
        // استخدام صورة المحصول كما هي
        $imagePath = $crop->photo;
    }

    // ✅ إنشاء المنتج
    $product = Product::create([
        'crop_id'     => $request->crop_id,
        'category_id' => $request->category_id,
        'name'        => $request->product_name,
        'price'       => $request->price,
        'description' => $request->description,
        'quantity'    => $request->quantity ?? 1,
        'status'      => 'instock',
        'farmer_id'   => auth('scantum')->id(),
        'images'      => $imagePath,
    ]);

    // ✅ الاستجابة النهائية
    return response()->json([
        'success'    => true,
        'message'    => 'Product added successfully',
        'product'    => $product,
        'image_url'  => $imagePath ? url('storage/' . $imagePath) : null,
        'debug_info' => [
            'used_crop_photo' => !$imageInput,
            'storage_path'    => $imagePath,
            'exists_in_disk'  => $imagePath ? Storage::disk('public')->exists($imagePath) : false
        ]
    ], 201);
}



    /**
     * Display all products.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $products = Product::all();
        return response()->json([
            'status' => 'success',
            'data' => $products,
        ]);
    }

    /**
     * Display a specific product's details.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'المنتج غير موجود',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $product,
        ]);
    }

    /**
     * Add a product to the shopping cart.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_to_cart(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id', // Ensure the product exists
            'quantity' => 'required|integer|min:1', // Quantity must be at least 1
        ]);

        // Find the product in the database
        $product = Product::find($request->id);

        // Check if the product is out of stock
        if ($product->stock_status === 'outofstock') {
            return response()->json([
                'status' => 'error',
                'message' => 'Product is out of stock',
            ], 400);
        }

        // Check if the requested quantity is available
        if ($product->quantity < $request->quantity) {
            return response()->json([
                'status' => 'error',
                'message' => 'Requested quantity not available',
            ], 400);
        }

        // Retrieve the cart from the session
        $cart = session()->get('cart', []);

        // Check if the product already exists in the cart
        if (isset($cart[$product->id])) {
            // Update the quantity if the product exists
            $cart[$product->id]['quantity'] += $request->quantity;
        } else {
            // Add the product to the cart if it doesn't exist
            $cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $request->quantity,
                'photo' => $product->photo, // Optional: Add photo if available
                'description' => $product->description, // Optional: Add description
            ];
        }

        // Save the updated cart back to the session
        session()->put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to cart successfully',
            'cart' => $cart,
        ]);
    }

    /**
     * Display the current cart contents.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cart()
    {
        Session::start(); // Ensure session starts

        $cartItems = session()->get('cart', []);

        return response()->json([
            'status' => 'success',
            'data' => $cartItems,
        ]);
    }

    /**
     * Place an order based on the cart items.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function place_an_order(Request $request)
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user_id = Auth::id();

        // Find the user's default address
        $address = Address::where('user_id', $user_id)->where('isdefault', true)->first();

        // If no default address exists, create a new one
        if (!$address) {
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:10',
                'city' => 'required',
                'email' => 'required|email',
                'floor' => 'nullable|int',
                'street_number' => 'nullable|int',
                'country' => 'required|string',
            ]);

            $address = Address::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'city' => $request->city,
                'email' => $request->email,
                'floor' => $request->floor,
                'country' => $request->country,
                'street_number' => $request->street_number,
                'user_id' => auth('scantum')->id(),
                'isdefault' => true,
            ]);
        }

        // Get cart items from the session
        $cart = session()->get('cart', []);

        // If the cart is empty, return an error
        if (empty($cart)) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        // Calculate subtotal
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Apply discount if a coupon is provided
        $discount = 0;
        if ($request->has('coupon')) {
            $coupon = $request->coupon;
            // For example, assume a 10% discount if the coupon is valid
            $discount = $subtotal * 0.10;
        }

        $total = $subtotal - $discount;

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the order
            $order = Order::create([
                'user_id' => $user_id,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'name' => $address->name,
                'phone' => $address->phone,
                'email' => $address->email,
                'city' => $address->city,
                'floor' => $address->floor,
                'street_number' => $address->street_number,
                'country' => $address->country,
            ]);

            // Add order items
            foreach ($cart as $item) {
                Orderitem::create([
                    'product_id' => $item['id'],
                    'order_id' => $order->id,
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                ]);
            }

            // Create a transaction if payment mode is COD
            if ($request->mode == "cod") {
                Transaction::create([
                    'user_id' => $user_id,
                    'order_id' => $order->id,
                    'mode' => "cod",
                    'status' => "pending",
                ]);
            }

            // Commit the transaction
            DB::commit();

            // Clear the cart
            session()->forget('cart');

            foreach ($order->orderItems as $item) {
    $farmerId = $item->product->farmer_id;

    Notification::create([
        'user_id' => $farmerId,
        'title' => 'طلب جديد',
        'body' => 'وصلك طلب جديد على منتج: ' . $item->product->name,
    ]);
}

            return response()->json([
                'message' => 'Order placed successfully',
                'order_id' => $order->id,
                'total' => $total,
                'discount' => $discount,
            ]);
        } catch (\Exception $e) {
            // Rollback the transaction in case of an error
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to place order',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the quantity of a product in the cart.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCart(Request $request, $id)
    {
        // Retrieve the cart from the session
        $cart = session()->get('cart', []);

        // Validate the input
        $request->validate([
            'quantity' => 'required|integer|min:1', // Ensure quantity is a positive integer
        ]);

        // Check if the product exists in the cart
        if (!isset($cart[$id])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found in the cart',
            ], 404);
        }

        // Update the quantity of the product
        $cart[$id]['quantity'] = $request->quantity;

        // Save the updated cart back to the session
        session()->put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart updated successfully!',
            'data' => $cart,
        ]);
    }

    /**
     * Remove a product from the cart.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeFromCart($id)
    {
        // Retrieve the cart from the session
        $cart = session()->get('cart', []);

        // Check if the product exists in the cart
        if (!isset($cart[$id])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found in the cart',
            ], 404);
        }

        // Remove the product from the cart
        unset($cart[$id]);

        // Save the updated cart back to the session
        session()->put('cart', $cart);

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from cart',
            'data' => $cart,
        ]);
    }
}
