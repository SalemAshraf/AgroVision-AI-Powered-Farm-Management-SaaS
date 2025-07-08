<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SensorData;
use Kreait\Firebase\Factory;
use Carbon\Carbon;

class FetchSensorData extends Command
{
    /**
     * الاسم والتوقيع الخاص بالأمر.
     *
     * @var string
     */
    protected $signature = 'fetch:sensor-data';

    /**
     * وصف الأمر في وحدة التحكم.
     *
     * @var string
     */
    protected $description = 'Fetch data from Firebase and store in MySQL';

    /**
     * تنفيذ الأمر.
     *
     * @return void
     */
    public function handle()
    {
        // إعداد الاتصال بـ Firebase باستخدام بيانات الخدمة
        try {
            $firebase = (new Factory)
                ->withServiceAccount(config('services.firebase.credentials_file')) // استخدام بيانات الاعتماد الخاصة بـ Firebase
                ->withDatabaseUri('https://agrovision-sensor-data-default-rtdb.firebaseio.com/'); // تحديد URI لقاعدة بيانات Firebase
    
            $database = $firebase->createDatabase(); // إنشاء قاعدة البيانات عبر Firebase

            // جلب البيانات من Firebase
            $data = $database->getReference('sensor_data')->getValue(); // الحصول على البيانات من المرجع 'sensor_data'

            // إذا كانت البيانات موجودة، نقوم بتخزينها في MySQL
            if ($data) {
                // التكرار عبر البيانات المسترجعة
                foreach ($data as $key => $sensor) {
                    // التأكد من وجود timestamp في البيانات
                    if (!isset($sensor['timestamp'])) {
                        continue; // إذا لم تحتوي البيانات على timestamp، نقوم بتخطي هذا العنصر
                    }
                
                    // تحويل الـ timestamp إلى صيغة DateTime باستخدام Carbon
                    $timestamp = Carbon::parse($sensor['timestamp'])->toDateTimeString();
                
                    // التحقق من وجود بيانات بنفس الـ timestamp لتجنب التكرار
                    $existingData = SensorData::where('sensor_id', $sensor['sensor_id'])
                                              ->where('recorded_at', $timestamp)
                                              ->first(); // البحث عن سجل بنفس الـ sensor_id و timestamp
                
                    if (!$existingData) {
                        // إذا لم يوجد سجل، نقوم بإنشاء سجل جديد في قاعدة البيانات
                        SensorData::create([
                            'sensor_id' => $sensor['sensor_id'], // معرّف الحساس
                            'ec' => $sensor['EC'] ?? null, // القيمة EC (إذا كانت موجودة)
                            'fertility' => $sensor['Fertility'] ?? null, // القيمة Fertility (إذا كانت موجودة)
                            'hum' => $sensor['Hum'] ?? null, // القيمة Hum (إذا كانت موجودة)
                            'k' => $sensor['K'] ?? null, // القيمة K (إذا كانت موجودة)
                            'n' => $sensor['N'] ?? null, // القيمة N (إذا كانت موجودة)
                            'p' => $sensor['P'] ?? null, // القيمة P (إذا كانت موجودة)
                            'ph' => $sensor['PH'] ?? null, // القيمة PH (إذا كانت موجودة)
                            'temp' => $sensor['Temp'] ?? null, // القيمة Temp (إذا كانت موجودة)
                            'recorded_at' => $timestamp, // التاريخ والوقت المسجل
                        ]);
                    }
                }

                // إظهار رسالة تفيد بأن العملية تمت بنجاح
                $this->info('Sensor data fetching and storing process completed.');
            } else {
                // في حالة عدم وجود بيانات في Firebase
                $this->error('No data found from Firebase.');
            }
        } catch (\Exception $e) {
            // في حالة حدوث خطأ أثناء الاتصال أو حفظ البيانات
            $this->error('Error fetching or storing data: ' . $e->getMessage());
        }
    }
}
