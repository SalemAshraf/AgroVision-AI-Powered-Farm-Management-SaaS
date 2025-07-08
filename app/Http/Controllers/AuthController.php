<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{

    public function getNotifications()
{
    $userId = auth('sanctum')->id();

    $notifications = Notification::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($notifications);
}

    // ✅ دالة تسجيل الدخول
    public function login(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        // ✳️ محاولة تسجيل الدخول كـ Member
        $member = Member::where('email', $request->email)->first();

        if ($member && Hash::check($request->password, $member->password)) {
            $member->tokens()->delete(); // حذف التوكنات القديمة
            $token = $member->createToken('member_token')->plainTextToken;

            return response()->json([
                'message' => 'Member logged in successfully!',
                'token' => $token,
                'role' => $member->role,
                'id' => $member->id,
                'name' => $member->name,
                'img' => $member->image,
            ]);
        }

        // ✳️ محاولة تسجيل الدخول كـ User
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            $user->tokens()->delete(); // حذف التوكنات القديمة
            $token = $user->createToken('auth_token')->plainTextToken;

            // حفظ التوكن في قاعدة البيانات كـ "remember_token"
            $rememberToken = Str::random(60);
            $user->remember_token = $rememberToken;
            $user->save();

            // إرسال التوكن ككوكيز (صالح لمدة 30 يوم)
            cookie()->queue(cookie('remember_token', $rememberToken, 60 * 24 * 30));

            return response()->json([
                'message' => 'User logged in successfully!',
                'token' => $token,
                'role' => $user->role,
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'birthday' => $user->birthday,
                'img' => $user->img,
            ]);
        }

        // ❌ في حالة فشل تسجيل الدخول
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // ✅ دالة التسجيل
    public function register(Request $request)
    {
        // التحقق من البيانات المدخلة
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',      // حرف صغير
                'regex:/[A-Z]/',      // حرف كبير
                'regex:/[0-9]/',      // رقم
                'regex:/[@$!%*?&]/',  // رمز خاص
                'confirmed',          // لازم يطابق password_confirmation
            ],
            'role' => 'nullable|string|max:255',
        ]);

            // ✅ نستخدم قيمة افتراضية 'user' إذا لم يرسل العميل حقل role
        $role = $request->input('role', 'user');

        // ❌ في حالة وجود أخطاء في البيانات
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // ✅ إنشاء مستخدم جديد
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        // إنشاء توكن للمستخدم الجديد
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ إرجاع الرد مع التوكن
        return response()->json([
            'message' => 'User created successfully',
            'token' => $token,
        ], 201); // 201 = Created
    }

    // ✅ دالة تحديث بيانات الحساب
    public function updateAccount(Request $request)
    {
    $user = Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthenticated'], 401);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'sometimes|required|string|max:255',
        'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
        'phone' => 'sometimes|nullable|string|max:20',
        'birthday' => 'sometimes|nullable|date',
        'img' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // ✅ متطابق مع اسم الملف
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // ✅ رفع الصورة إن وُجدت
    if ($request->hasFile('img')) {
        $file = $request->file('img');
        $path = $file->store('photos', 'public');
        $user->img = $path;
    }

    // ✅ تحديث باقي البيانات
    $user->fill($request->only(['name', 'email', 'phone', 'birthday']));
    $user->save();

    return response()->json([
        'message' => 'Account updated successfully!',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'birthday' => $user->birthday,
            'img_url' => $user->img ? asset('storage/' . $user->img) : null,
        ],
    ]);
}

public function logout(Request $request)
{
    $request->user()->tokens()->delete(); // Logout from all devices

    return response()->json([
        'message' => 'Logged out successfully'
    ]);
}



public function showLoginFormAdmin()
    {
        return view('admin.auth.login');
    }

    public function loginAdmin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            if (auth('sanctum')->user()->role === 'admin') {
                return redirect()->route('admin.users.index');
            } else {
                Auth::logout();
                return back()->withErrors(['email' => 'You are not authorized.']);
            }
        }

        return back()->withErrors(['email' => 'Login failed.']);
    }

    public function logoutAdmin(Request $request)
    {
        Auth::logout();
        return redirect()->route('admin.login');
    }
}
