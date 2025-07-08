<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\OTransaction;
    use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalysisController extends Controller
{


public function getFarmerOrderAnalytics()
{
    $farmerId = auth('sanctum')->id();

    // جلب الطلبات المرتبطة بالمزارع
    $orders = Order::whereHas('orderItems.product', function ($query) use ($farmerId) {
        $query->where('farmer_id', $farmerId);
    })->with('orderItems.product')->get();

    // إجمالي الطلبات
    $totalOrders = $orders->count();

    // إجمالي الأرباح
    $totalSales = 0;
    foreach ($orders as $order) {
        foreach ($order->orderItems as $item) {
            if ($item->product->farmer_id == $farmerId) {
                $totalSales += $item->price * $item->quantity;
            }
        }
    }

    // عدد الطلبات حسب الحالة
    $statusCounts = [
        'pending' => 0,
        'delivered' => 0,
        'canceled' => 0,
    ];

    foreach ($orders as $order) {
        $status = strtolower($order->status);
        if (isset($statusCounts[$status])) {
            $statusCounts[$status]++;
        }
    }

    // مبيعات شهرية
    $monthlySales = [];

    foreach ($orders as $order) {
        $month = Carbon::parse($order->created_at)->format('M');
        foreach ($order->orderItems as $item) {
            if ($item->product->farmer_id == $farmerId) {
                $monthlySales[$month] = ($monthlySales[$month] ?? 0) + ($item->price * $item->quantity);
            }
        }
    }

    // ترتيب حسب الشهور
    $orderedMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $monthlySalesFormatted = [];
    foreach ($orderedMonths as $month) {
        $monthlySalesFormatted[] = [
            'month' => $month,
            'total' => $monthlySales[$month] ?? 0,
        ];
    }

    // آخر طلبين
    $latestOrders = $orders->sortByDesc('created_at')->take(2)->values()->map(function ($order) use ($farmerId) {
        $total = 0;
        foreach ($order->orderItems as $item) {
            if ($item->product->farmer_id == $farmerId) {
                $total += $item->price * $item->quantity;
            }
        }
        return [
            'order_id' => $order->id,
            'customer' => $order->name,
            'amount' => $total,
            'created_at' => $order->created_at->format('Y-m-d'),
        ];
    });

$clients = [];

foreach ($orders as $order) {
    // تجاهل الطلبات اللي ملهاش مستخدم مسجل
    if (!$order->user_id) continue;

    $key = $order->user_id;

    if (!isset($clients[$key])) {
        $clients[$key] = [
            'user_id' => $order->user_id,
            'name' => $order->name,
            'phone' => $order->phone,
            'orders_count' => 1
        ];
    } else {
        $clients[$key]['orders_count']++;
    }
}

// تحويل للـ array
$clientsList = array_values($clients);

$currentMonthOrders = Order::whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->whereHas('orderItems.product', function ($q) use ($farmerId) {
        $q->where('farmer_id', $farmerId);
    })
    ->count();
$lastMonth = now()->subMonth();

$lastMonthOrders = Order::whereMonth('created_at', $lastMonth->month)
    ->whereYear('created_at', $lastMonth->year)
    ->whereHas('orderItems.product', function ($q) use ($farmerId) {
        $q->where('farmer_id', $farmerId);
    })
    ->count();
$change = 0;
$direction = 'neutral';

if ($lastMonthOrders > 0) {
    $change = (($currentMonthOrders - $lastMonthOrders) / $lastMonthOrders) * 100;
    $direction = $change > 0 ? 'up' : 'down';
}
$now = Carbon::now();
$lastMonth = $now->copy()->subMonth();

// 📦 الطلبات المكتملة في هذا الشهر (delivered)
$currentCompleted = Order::whereMonth('created_at', $now->month)
    ->whereYear('created_at', $now->year)
    ->where('status', 'delivered')
    ->whereHas('orderItems.product', function ($q) use ($farmerId) {
        $q->where('farmer_id', $farmerId);
    })
    ->count();

// 📦 الطلبات المكتملة في الشهر السابق
$lastCompleted = Order::whereMonth('created_at', $lastMonth->month)
    ->whereYear('created_at', $lastMonth->year)
    ->where('status', 'delivered')
    ->whereHas('orderItems.product', function ($q) use ($farmerId) {
        $q->where('farmer_id', $farmerId);
    })
    ->count();

// 🧠 حساب نسبة التغير
$completedChange = 0;
$completedDirection = 'neutral';

if ($lastCompleted > 0) {
    $completedChange = (($currentCompleted - $lastCompleted) / $lastCompleted) * 100;
    $completedDirection = $completedChange > 0 ? 'up' : 'down';
}


    return response()->json([
        'total_orders' => $totalOrders,
        'total_sales' => round($totalSales, 2),
        'status_counts' => $statusCounts,
        'monthly_sales' => $monthlySalesFormatted,
        'latest_orders' => $latestOrders,
        'clients' => $clientsList,
        'invoice_sent' => [
    'count' => $currentMonthOrders,
    'change' => round($change, 1),
    'direction' => $direction
        ],
        'invoice_completed' => [
    'count' => $currentCompleted,
    'change' => round($completedChange, 1),
    'direction' => $completedDirection
]


    ]);
}

}
