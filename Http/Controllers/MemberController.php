<?php

namespace App\Http\Controllers;

use App\Models\Member;

class MemberController extends Controller
{
    /**
     * استرجاع الأعضاء المرتبطين بـ admin_id.
     *
     * @param  int  $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMembersByUserId($user_id)
    {
        try {
            // الحصول على الأعضاء المرتبطين بـ user_id
            $members = Member::where('user_id', $user_id)->get();

            // التحقق إذا كانت النتيجة فارغة
            if ($members->isEmpty()) {
                // في حال عدم وجود أعضاء
                return response()->json(['message' => 'No members found for this admin.'], 404);
            }

            // إعادة الأعضاء في شكل JSON مع استجابة ناجحة (رمز حالة 200)
            return response()->json(['members' => $members], 200);
        } catch (\Exception $e) {
            // التعامل مع الأخطاء في حال حدوث استثناء
            return response()->json(['error' => 'Something went wrong!', 'details' => $e->getMessage()], 500);
        }
    }
}
