<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CropController;
use App\Http\Controllers\MemberController;
use Kreait\Firebase\Factory;
use App\Http\Controllers\FirebaseController;
use App\Http\Controllers\AnalysisController;
use App\Http\Controllers\SensorDataController;
use App\Models\SensorData;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;


Route::middleware('auth:sanctum')->delete('/conversations/{id}', [ConversationController::class, 'destroy']);


Route::middleware('auth:sanctum')->get('/notifications', [NotificationController::class, 'getNotifications']);
Route::middleware('auth:sanctum')->post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

Route::get('/clear-cache', function() {
    Artisan::call('route:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache cleared!";
});
//////////////EZZ
//use App\Http\Controllers\CropController;  // ✅ Make sure this matches the file location
use App\Http\Controllers\OrderController;
use App\Models\Crop;
use App\Http\Controllers\ProductController;
//////////////////////////////////////
//orders apis
Route::middleware('auth:sanctum')->get('/farmers/orders', [OrderController::class, 'getFarmerOrders']);
Route::middleware('auth:sanctum')->get('/latest-conversations', [MessageController::class, 'latestConversations']);

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::put('/orders/{id}', [OrderController::class, 'update']);
Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

 Route::get('/categories/{category_id}/products', [ProductController::class, 'getProductsByCategory']);
 
Route::middleware('auth:sanctum') ->post('/products/add-from-crop', [ProductController::class, 'addProductFromCrop']);
    
    // عرض جميع المنتجات
    Route::get('/products', [ProductController::class, 'index']);
    
    // عرض تفاصيل منتج معين
    Route::get('/products/{id}', [ProductController::class, 'show']);
    
    //carts apis
    Route::middleware(['api', \Illuminate\Session\Middleware\StartSession::class])->group(function () {
        // Add a product to the cart
        Route::post('/cart/add', [ProductController::class, 'add_to_cart']);
        
        // Display the content of the cart
        Route::get('/cart', [ProductController::class, 'cart']);
        
        Route::post('/place-order', [ProductController::class, 'place_an_order'])->middleware('auth:api');    
        // Update the quantity of a product in the cart
        Route::put('/cart/update/{id}', [ProductController::class, 'updateCart']);
    
        // Remove a product from the cart
        Route::delete('/cart/remove/{id}', [ProductController::class, 'removeFromCart']);
    
        // Clear the entire cart
        Route::delete('/cart/clear', [ProductController::class, 'clearCart']);
    });



    Route::get('/categories', [Productcontroller::class, 'getAllCategories']);
    Route::get('/users/{userId}/orders', [OrderController::class, 'getUserOrders']);



//////////////Ezz

Route::get('/order-analytics', [AnalysisController::class, 'getFarmerOrderAnalytics']);




Route::middleware('auth:sanctum')->group(function () {
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);

    Route::post('/messages', [MessageController::class, 'store']);
    Route::get('/messages/{conversationId}', [MessageController::class, 'getMessages']);
    Route::post('/messages/read/{messageId}', [MessageController::class, 'markAsRead']);
});


// Sensor Data API
Route::get('/export-sensors', [SensorDataController::class, 'export']);
Route::post('/firebase/store', function (Request $request) {
    try {
        // إنشاء اتصال بفايربيز
        $firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');

        $database = $firebase->createDatabase();

        // جلب آخر البيانات من Firebase
        $data = $database->getReference('sensor_data')
                         ->orderByKey()
                         ->limitToLast(1)
                         ->getValue();

        // التحقق مما إذا كانت البيانات فارغة
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'No data found in Firebase',
            ], 404);
        }

        // استخراج آخر عنصر من البيانات
        $latestRecord = array_values($data)[0];

        // التحقق من صحة البيانات قبل الإدخال في MySQL
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

        // تخزين البيانات في MySQL
        SensorData::create([
            'sensor_id' => 'agro_0001', // المعرف ثابت
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

Route::get('/firebase/retrieve', function () {
    try {
        // تهيئة Firebase
        $firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');

        $database = $firebase->createDatabase();

        // استدعاء البيانات من المسار المحدد
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
Route::get('/firebase/last-record', function () {
    try {
        $firebase = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials_file'))
            ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/');

        $database = $firebase->createDatabase();

        // جلب آخر إدخال
        $data = $database->getReference('sensor_data')
                         ->orderByKey()
                         ->limitToLast(1)
                         ->getValue();

        // تحويل النتيجة إلى أول عنصر
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


// Member & Crops Data API
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/add-member', [AdminController::class, 'store']);
    Route::put('/add-member/{id}', [AdminController::class, 'update']);
    Route::delete('/add-member/{id}', [AdminController::class, 'destroy']);
});
Route::get('/users/{user_id}/members', [MemberController::class, 'getMembersByUserId']);



// Order Data API
Route::middleware('auth:sanctum')->group(function () {
    // استرجاع الطلبات الخاصة بالمستخدم الحالي
    Route::get('/orders', [OrderController::class, 'index']);
    // إنشاء طلب جديد
    Route::post('/orders', [OrderController::class, 'store']);
    // تعديل حالة الطلب
    Route::put('/orders/{id}', [OrderController::class, 'update']);
    Route::get('/users/{userId}/orders', [OrderController::class, 'getUserOrders']);

});


// Auth Data API
Route::post('register', [AuthController::class, 'register']);  // Route for registering a user
Route::post('login', [AuthController::class, 'login']);  // Route for logging in
Route::middleware('auth:sanctum')->post('/update-account', [AuthController::class, 'updateAccount']);
// Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');



// Crops Data API
Route::post('/crops', [CropController::class, 'store']);
Route::put('/crops/{id}', [CropController::class, 'update']);
Route::delete('/crops/{id}', [CropController::class, 'destroy']);
Route::get('/users/{user_id}/crops', [CropController::class, 'getCropsByUserId']);


Route::middleware('auth:sanctum')->get('user', [AuthController::class, 'user']);
    Route::get('/admin/dashboard', function () {
        return response()->json(['message' => 'Welcome Admin!']);
    });
    
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);
    
    //favorites apis 
      Route::middleware('auth:sanctum')->group(function () {
        Route::post('/favorite/{product}', [productController::class, 'addFavorite']);
        Route::delete('/favorite/{product}', [productController::class, 'removeFavorite']);
        Route::get('/favorites', [productController::class, 'getFavorites']);
    });

