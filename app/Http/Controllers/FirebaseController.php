<?php

namespace App\Http\Controllers;

use App\Services\FirebaseService;

class FirebaseController extends Controller
{
    // تعريف خدمة Firebase عبر حقنها في الكونستركتور
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        // تعيين خدمة Firebase في المتغير الخاص بها
        $this->firebaseService = $firebaseService;
    }

    /**
     * تخزين البيانات في Firebase.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeData()
    {
        // تعريف البيانات التي سيتم تخزينها
        $data = [
            'name' => 'Salem',
            'age' => 25,
        ];

        // تخزين البيانات في Firebase في المسار 'users/1'
        $this->firebaseService->set('users/1', $data);

        // إرجاع استجابة تُفيد بأنه تم تخزين البيانات بنجاح
        return response()->json(['message' => 'Data stored successfully']);
    }

    /**
     * جلب البيانات من Firebase.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchData()
    {
        // جلب البيانات من المسار 'users/1'
        $data = $this->firebaseService->get('users/1');

        // إرجاع البيانات في الاستجابة
        return response()->json($data);
    }
}
