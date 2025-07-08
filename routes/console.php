<?php

use Illuminate\Support\Facades\Schedule; // تأكد من استيراد الواجهة Schedule
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// --- بداية الكود المحول ---

// جدولة تشغيل الأمر fetch:sensor-data كل دقيقة
Schedule::command('fetch:sensor-data')->everyFiveMinutes();

// --- نهاية الكود المحول ---


// يمكنك إضافة أي تعريفات أوامر Artisan أخرى هنا أيضاً
