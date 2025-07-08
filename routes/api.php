<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;


// Controllers
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SensorDataController;
use App\Http\Controllers\Auth\ForgotPasswordController;

// Models
use App\Models\SensorData;

/*
|--------------------------------------------------------------------------
| UTILITY ROUTES
|--------------------------------------------------------------------------
*/

// Clear application cache
Route::get('/clear-cache', function () {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache cleared!";
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

// User registration and login
Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

// Password reset functionality
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

// Protected authentication routes
Route::middleware('auth:sanctum')->group(function () {
    // Get current user info
    Route::get('user', [AuthController::class, 'user']);

    // Update user account
    Route::post('/update-account', [AuthController::class, 'updateAccount']);

    // User logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| ADMIN ROUTES
|--------------------------------------------------------------------------
*/

// Admin dashboard
Route::get('/admin/dashboard', function () {
    return response()->json(['message' => 'Welcome Admin!']);
});

// Member management (Admin only)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/add-member', [AdminController::class, 'store']);
    Route::put('/add-member/{id}', [AdminController::class, 'update']);
    Route::delete('/add-member/{id}', [AdminController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| PRODUCT & CATEGORY ROUTES
|--------------------------------------------------------------------------
*/

// Public product routes
Route::get('/products', [ProductController::class, 'index']); // Get all products
Route::get('/products/{id}', [ProductController::class, 'show']); // Get specific product
Route::get('/categories', [ProductController::class, 'getAllCategories']); // Get all categories
Route::get('/categories/{category_id}/products', [ProductController::class, 'getProductsByCategory']); // Get products by category

// Protected product routes
Route::middleware('auth:sanctum')->group(function () {
    // Add product from crop
    Route::post('/products/add-from-crop', [ProductController::class, 'addProductFromCrop']);
});

/*
|--------------------------------------------------------------------------
| SHOPPING CART ROUTES
|--------------------------------------------------------------------------
*/

// Cart functionality with session middleware
Route::middleware(['api', \Illuminate\Session\Middleware\StartSession::class])->group(function () {
    // Add product to cart
    Route::post('/cart/add', [ProductController::class, 'add_to_cart']);

    // View cart contents
    Route::get('/cart', [ProductController::class, 'cart']);

    // Update cart item quantity
    Route::put('/cart/update/{id}', [ProductController::class, 'updateCart']);

    // Remove item from cart
    Route::delete('/cart/remove/{id}', [ProductController::class, 'removeFromCart']);

    // Clear entire cart
    Route::delete('/cart/clear', [ProductController::class, 'clearCart']);

    // Place order (requires authentication)
    Route::post('/place-order', [ProductController::class, 'place_an_order'])->middleware('auth:api');
});

/*
|--------------------------------------------------------------------------
| FAVORITES ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Add product to favorites
    Route::post('/favorite/{product}', [ProductController::class, 'addFavorite']);

    // Remove product from favorites
    Route::delete('/favorite/{product}', [ProductController::class, 'removeFavorite']);

    // Get user's favorite products
    Route::get('/favorites', [ProductController::class, 'getFavorites']);
});

/*
|--------------------------------------------------------------------------
| ORDER MANAGEMENT ROUTES
|--------------------------------------------------------------------------
*/

// Order CRUD operations
Route::post('/orders', [OrderController::class, 'store']); // Create order
Route::get('/orders/{id}', [OrderController::class, 'show']); // Get specific order
Route::put('/orders/{id}', [OrderController::class, 'update']); // Update order
Route::delete('/orders/{id}', [OrderController::class, 'destroy']); // Delete order

// User-specific order routes
Route::get('/users/{userId}/orders', [OrderController::class, 'getUserOrders']); // Get orders for specific user

// Protected order routes
Route::middleware('auth:sanctum')->group(function () {
    // Get current user's orders
    Route::get('/orders', [OrderController::class, 'index']);

    // Get farmer's orders
    Route::get('/farmers/orders', [OrderController::class, 'getFarmerOrders']);
});

/*
|--------------------------------------------------------------------------
| CROP MANAGEMENT ROUTES
|--------------------------------------------------------------------------
*/

// Crop CRUD operations
Route::post('/crops', [CropController::class, 'store']); // Create crop
Route::put('/crops/{id}', [CropController::class, 'update']); // Update crop
Route::delete('/crops/{id}', [CropController::class, 'destroy']); // Delete crop
Route::get('/users/{user_id}/crops', [CropController::class, 'getCropsByUserId']); // Get crops by user

/*
|--------------------------------------------------------------------------
| MEMBER MANAGEMENT ROUTES
|--------------------------------------------------------------------------
*/

// Get members by user ID
Route::get('/users/{user_id}/members', [MemberController::class, 'getMembersByUserId']);

/*
|--------------------------------------------------------------------------
| MESSAGING & COMMUNICATION ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Conversation management
    Route::get('/conversations', [ConversationController::class, 'index']); // Get all conversations
    Route::post('/conversations', [ConversationController::class, 'store']); // Create conversation
    Route::delete('/conversations/{id}', [ConversationController::class, 'destroy']); // Delete conversation

    // Message management
    Route::post('/messages', [MessageController::class, 'store']); // Send message
    Route::get('/messages/{conversationId}', [MessageController::class, 'getMessages']); // Get conversation messages
    Route::post('/messages/read/{messageId}', [MessageController::class, 'markAsRead']); // Mark message as read
    Route::get('/latest-conversations', [MessageController::class, 'latestConversations']); // Get latest conversations
});

/*
|--------------------------------------------------------------------------
| NOTIFICATION ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {
    // Get user notifications
    Route::get('/notifications', [NotificationController::class, 'getNotifications']);

    // Mark notification as read
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});

/*
|--------------------------------------------------------------------------
| ANALYTICS & REPORTING ROUTES
|--------------------------------------------------------------------------
*/

// Order analytics for farmers
Route::get('/order-analytics', [AnalysisController::class, 'getFarmerOrderAnalytics']);

/*
|--------------------------------------------------------------------------
| SENSOR DATA & FIREBASE ROUTES
|--------------------------------------------------------------------------
*/

// Sensor data export
Route::get('/export-sensors', [SensorDataController::class, 'export']);

// Firebase data synchronization - Store latest Firebase data to MySQL
Route::post('/firebase/store', function (Request $request) {
    try {
        // Initialize Firebase connection
        $firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');

        $database = $firebase->createDatabase();

        // Fetch latest data from Firebase
        $data = $database->getReference('sensor_data')
            ->orderByKey()
            ->limitToLast(1)
            ->getValue();

        // Check if data exists
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'No data found in Firebase',
            ], 404);
        }

        // Extract the latest record
        $latestRecord = array_values($data)[0];

        // Validate data before storing in MySQL
        $validator = Validator::make($latestRecord, [
            'EC' => 'required|numeric',
            'Fertility' => 'required|numeric',
            'Hum' => 'required|numeric',
            'K' => 'required|numeric',
            'N' => 'required|numeric',
            'P' => 'required|numeric',
            'PH' => 'required|numeric',
            'Temp' => 'required|numeric',
            'timestamp' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Store data in MySQL
        SensorData::create([
            'sensor_id' => 'agro_0001', // Fixed sensor ID
            'ec' => $latestRecord['EC'],
            'fertility' => $latestRecord['Fertility'],
            'hum' => $latestRecord['Hum'],
            'k' => $latestRecord['K'],
            'n' => $latestRecord['N'],
            'p' => $latestRecord['P'],
            'ph' => $latestRecord['PH'],
            'temp' => $latestRecord['Temp'],
            'recorded_at' => $latestRecord['timestamp'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data fetched from Firebase and stored successfully in MySQL',
            'data' => $latestRecord,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to fetch and store data',
            'error' => $e->getMessage(),
        ]);
    }
});

// Retrieve all sensor data from Firebase
Route::get('/firebase/retrieve', function () {
    try {
        // Initialize Firebase
        $firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');

        $database = $firebase->createDatabase();

        // Retrieve data from specified path
        $data = $database->getReference('sensor_data')->getValue();

        return response()->json([
            'success' => true,
            'message' => 'Data retrieved successfully from Firebase',
            'data' => $data,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve data',
            'error' => $e->getMessage(),
        ]);
    }
});

// Get the last sensor record from Firebase
Route::get('/firebase/last-record', function () {
    try {
        $firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');

        $database = $firebase->createDatabase();

        // Fetch the latest entry
        $data = $database->getReference('sensor_data')
            ->orderByKey()
            ->limitToLast(1)
            ->getValue();

        // Convert result to first element
        $lastRecord = $data ? array_values($data)[0] : null;

        return response()->json([
            'success' => true,
            'message' => 'Last record retrieved successfully',
            'data' => $lastRecord,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to retrieve last record',
            'error' => $e->getMessage(),
        ]);
    }
});
