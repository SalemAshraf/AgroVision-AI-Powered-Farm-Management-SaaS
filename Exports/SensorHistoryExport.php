<?php

namespace App\Exports;

use App\Models\SensorData; // استيراد موديل البيانات الخاصة بالحساسات
use Maatwebsite\Excel\Concerns\FromCollection; // واجهة لاستيراد البيانات من مجموعة
use Maatwebsite\Excel\Concerns\WithHeadings; // واجهة لإضافة رؤوس الأعمدة في ملف Excel

class SensorHistoryExport implements FromCollection, WithHeadings
{
    /**
     * استرجاع البيانات من قاعدة البيانات
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // جلب بيانات الحساسات من جدول sensor_data مع تحديد الأعمدة المطلوبة
        return SensorData::select(
            'sensor_id',  // معرف الحساس
            'ec',         // الموصلية الكهربائية
            'fertility',  // الخصوبة
            'hum',        // الرطوبة
            'k',          // البوتاسيوم
            'n',          // النيتروجين
            'p',          // الفوسفور
            'ph',         // مستوى الحموضة
            'temp',       // درجة الحرارة
            'recorded_at' // التاريخ والوقت الذي تم تسجيل البيانات فيه
        )->get(); // إرجاع مجموعة البيانات
    }

    /**
     * تحديد رؤوس الأعمدة في ملف Excel
     *
     * @return array
     */
    public function headings(): array
    {
        // تحديد أسماء الأعمدة في ملف Excel
        return [
            'Sensor ID',                 // معرف الحساس
            'Electrical Conductivity (EC)',  // الموصلية الكهربائية
            'Fertility',                 // الخصوبة
            'Humidity',                  // الرطوبة
            'Potassium (K)',             // البوتاسيوم
            'Nitrogen (N)',              // النيتروجين
            'Phosphorus (P)',            // الفوسفور
            'pH Level',                  // مستوى الحموضة
            'Temperature',               // درجة الحرارة
            'Timestamp',                 // التاريخ والوقت
        ];
    }
}
