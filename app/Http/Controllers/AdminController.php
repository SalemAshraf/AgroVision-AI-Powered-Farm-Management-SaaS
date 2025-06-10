<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');  // This will require a valid Sanctum token
    }

    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:members,email',
            'password' => 'required|string|min:8',
            'phone'    => 'required|string|max:15',
            'gender'   => 'required|string|in:male,female',
            'role'     => 'required|string',
            'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the currently authenticated user using Sanctum
        $user = auth('sanctum')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Create a new member and associate it with the authenticated user
        $member = new Member();
        $member->name     = $request->name;
        $member->email    = $request->email;
        $member->password = Hash::make($request->password);
        $member->phone    = $request->phone;
        $member->gender   = $request->gender;
        $member->role     = $request->role;
        $member->user_id  = $user->id;

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('/photos', 'public');
            $member->image = $path;
        }

        $member->save();

        Auth::guard('member')->login($member);

        return response()->json([
            'message' => 'Member added successfully',
            'member'  => [
                'id'     => $member->id,
                'name'   => $member->name,
                'email'  => $member->email,
                'phone'  => $member->phone,
                'gender' => $member->gender,
                'role'   => $member->role,
                'image'  => $member->image ? asset('storage/' . $member->image) : null,
            ],
        ]);
    }

    public function update(Request $request, $id)
{
    // ✅ التحقق من صلاحيات المستخدم
    if (Auth::user()->role !== 'user') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // ✅ التحقق من صحة البيانات
    $request->validate([
        'name'     => 'nullable|string|max:255',
        'email'    => 'nullable|email|unique:members,email,' . $id,
        'password' => 'nullable|string|min:8',
        'phone'    => 'nullable|string|max:15',
        'gender'   => 'nullable|string|in:male,female',
        'role'     => 'nullable|string',
        'image'    => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // ✅ إيجاد العضو
    $member = Member::findOrFail($id);

    // ✅ رفع الصورة إن وُجدت
    if ($request->hasFile('image')) {
        // حذف الصورة القديمة إن وُجدت
        if ($member->image && \Storage::disk('public')->exists($member->image)) {
            \Storage::disk('public')->delete($member->image);
        }

        $imagePath = $request->file('image')->store('members', 'public');
        $member->image = $imagePath;
    }

    // ✅ تحديث الحقول الأخرى (مع تشفير كلمة المرور إذا تم إرسالها)
    $member->fill([
        'name'     => $request->name ?? $member->name,
        'email'    => $request->email ?? $member->email,
        'phone'    => $request->phone ?? $member->phone,
        'gender'   => $request->gender ?? $member->gender,
        'role'     => $request->role ?? $member->role,
    ]);

    if ($request->password) {
        $member->password = Hash::make($request->password);
    }

    $member->save();

    return response()->json([
        'message' => 'Member updated successfully',
        'member'  => [
            'id'     => $member->id,
            'name'   => $member->name,
            'email'  => $member->email,
            'phone'  => $member->phone,
            'gender' => $member->gender,
            'role'   => $member->role,
            'image_url' => $member->image ? asset('storage/' . $member->image) : null,
        ]
    ]);
}


    public function destroy($id)
    {
        // التحقق من صلاحيات المستخدم
        if (Auth::user()->role !== 'user') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // إيجاد العضو وحذفه
        $member = Member::findOrFail($id);
        $member->delete();

        return response()->json([
            'message' => 'Member deleted successfully',
        ]);
    }
}
