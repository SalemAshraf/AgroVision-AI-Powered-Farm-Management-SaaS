<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CropController extends Controller
{
    /**
     * Store a new crop record.
     *
     * This method validates the input data, checks the authentication of the user, 
     * and then creates a new crop record. If a photo is uploaded, it saves the image 
     * and associates it with the crop.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // التحقق من صحة المدخلات
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id', // التحقق من وجود المستخدم
            'productName' => 'required|string|max:255',
            'productCategory' => 'required|string|max:255',
            'pricePerKilo' => 'required|numeric',
            'quantity' => 'required|integer',
            'status' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // التحقق من الصورة
        ]);

        // التحقق من أن المستخدم مفوض
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401); // رسالة غير مفوضة إذا لم يكن المستخدم موجود
        }

        // إنشاء المحصول الجديد بدون الصورة
        $crop = Crop::create($validatedData);

        // إذا تم إرسال صورة، يتم تحميلها وتخزينها
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $path = $photo->store('photos', 'public');
            $crop->photo = $path;

            $crop->save();
        }

        // إرجاع استجابة JSON تتضمن المحصول
        return response()->json([
            'message' => 'Crop added successfully',
            'data' => [
                'id' => $crop->id,
                'user_id' => $crop->user_id,
                'productName' => $crop->productName,
                'productCategory' => $crop->productCategory,
                'pricePerKilo' => $crop->pricePerKilo,
                'quantity' => $crop->quantity,
                'status' => $crop->status,
                'photo' => $crop->photo, // إرجاع اسم الصورة فقط
                'created_at' => $crop->created_at,
                'updated_at' => $crop->updated_at,
            ]
        ]);
    }

    /**
     * Update an existing crop record.
     *
     * This method finds the crop by its ID and updates its fields based on the 
     * validated request data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
{
    // البحث عن المحصول بواسطة المعرف
    $crop = Crop::find($id);

    if (!$crop) {
        return response()->json(['error' => 'Crop not found'], 404);
    }

    // التحقق من البيانات المرسلة
    $request->validate([
        'user_id' => 'required|integer',
        'productName' => 'required|string|max:255',
        'productCategory' => 'required|string|max:255',
        'pricePerKilo' => 'required|numeric',
        'quantity' => 'required|integer',
        'status' => 'required|string',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);

    // ✅ حذف الصورة القديمة إذا وُجدت ورفع الجديدة
    if ($request->hasFile('photo')) {
        // حذف الصورة القديمة
        if ($crop->photo && \Storage::disk('public')->exists($crop->photo)) {
            \Storage::disk('public')->delete($crop->photo);
        }

        $file = $request->file('photo');
        $path = $file->store('photos', 'public');
        $crop->photo = $path;
    }

    // ✅ تحديث باقي البيانات (بدون photo)
    $crop->fill($request->only([
        'user_id', 'productName', 'productCategory', 'pricePerKilo', 'quantity', 'status'
    ]));

    $crop->save();

    // ✅ الرد
    return response()->json([
        'message' => 'Product updated successfully',
        'crop' => [
            'id' => $crop->id,
            'user_id' => $crop->user_id,
            'productName' => $crop->productName,
            'productCategory' => $crop->productCategory,
            'pricePerKilo' => $crop->pricePerKilo,
            'quantity' => $crop->quantity,
            'status' => $crop->status,
            'photo' => $crop->photo,

        ]
    ]);
}

    /**
     * Delete a crop record by its ID.
     *
     * This method attempts to find and delete the crop record. If it fails, an error 
     * message is returned.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            // البحث عن المحصول
            $crop = Crop::findOrFail($id);

            // حذف المحصول
            $crop->delete();

            return response()->json(['message' => 'Crop deleted successfully'], 200); // إرجاع رسالة النجاح
        } catch (\Exception $e) {
            // إذا حدث خطأ أثناء الحذف، إرجاع رسالة خطأ
            \Log::error($e->getMessage());

            return response()->json(['error' => 'An error occurred while deleting the crop'], 500);
        }
    }

    /**
     * Get all crops for a specific user.
     *
     * This method retrieves all the crops associated with the given user ID. If no crops 
     * are found, a message is returned.
     *
     * @param  int  $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCropsByUserId($user_id)
    {
        try {
            // البحث عن المحاصيل المرتبطة بالمستخدم
            $crops = Crop::where('user_id', $user_id)->get();

            if ($crops->isEmpty()) {
                return response()->json(['message' => 'No Crop found for this user.'], 404); // في حال عدم وجود محاصيل
            }

            // تنسيق البيانات لتحتوي على اسم الصورة فقط
            $crops->transform(function ($crop) {
                return [
                    'id' => $crop->id,
                    'user_id' => $crop->user_id,
                    'productName' => $crop->productName,
                    'productCategory' => $crop->productCategory,
                    'pricePerKilo' => $crop->pricePerKilo,
                    'quantity' => $crop->quantity,
                    'status' => $crop->status,
                    'photo' => $crop->photo ? basename($crop->photo) : null, // 👉 يرجّع بس اسم الصورة
                    'created_at' => $crop->created_at,
                    'updated_at' => $crop->updated_at,
                ];
            });

            return response()->json(['Crops' => $crops], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Something went wrong!', 'details' => $e->getMessage()], 500); // في حال حدوث خطأ
        }
    }
}
