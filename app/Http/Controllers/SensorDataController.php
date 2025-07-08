<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\SensorHistoryExport; // استيراد الكلاس الخاص بتصدير البيانات
use Maatwebsite\Excel\Facades\Excel; // استيراد الواجهة الخاصة بالتعامل مع Excel
use Symfony\Component\HttpFoundation\BinaryFileResponse; // استيراد رد الفعل الخاص بالملفات الثنائية (للتحميل)

class SensorDataController extends Controller
{
    /**
     * This method handles the export of sensor data to an Excel file.
     * 
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export()
    {
        // تحميل البيانات عبر الكلاس المخصص للتصدير (SensorHistoryExport)
        // و إرجاع الملف بصيغة Excel
        return Excel::download(new SensorHistoryExport, 'sensor_history.xlsx');
    }
}
