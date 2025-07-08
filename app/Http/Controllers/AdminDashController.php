<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminDashController extends Controller
{
    /**
     * عرض كل المستخدمين
     */
    public function showUsers()
    {
        $users = User::with('license')->get();

        return view('admin.users.index', compact('users'));
    }

    /**
     * إضافة أو تحديث رخصة وسنسور ID للمستخدم
     */
    public function updateUserLicense(Request $request, $userId)
    {
        $request->validate([
            'sensor_id' => 'required|string|max:255',
            'expires_at' => 'required|date|after:today'
        ]);

        $user = User::findOrFail($userId);

        $license = $user->license;

        if (!$license) {
            $license = $user->license()->create([
                'license_key' => Str::uuid(),
                'sensor_id' => $request->sensor_id,
                'expires_at' => $request->expires_at,
                'is_active' => true
            ]);
        } else {
            $license->update([
                'sensor_id' => $request->sensor_id,
                'expires_at' => $request->expires_at,
                'is_active' => true
            ]);
        }

        return redirect()->back()->with('success', 'License updated successfully.');
    }
}
