<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->string('sensor_id'); // معرف الحساس
            $table->float('ec')->nullable(); // التوصيل الكهربائي
            $table->float('fertility')->nullable(); // خصوبة التربة
            $table->float('humidity')->nullable(); // الرطوبة
            $table->float('k')->nullable(); // البوتاسيوم
            $table->float('n')->nullable(); // النيتروجين
            $table->float('p')->nullable(); // الفوسفور
            $table->float('ph_level')->nullable(); // مستوى الحموضة
            $table->float('temperature')->nullable(); // درجة الحرارة
            $table->timestamp('recorded_at')->useCurrent(); // وقت قراءة البيانات
            $table->timestamps(); // وقت الإنشاء والتحديث
        });
    }

    public function down()
    {
        Schema::dropIfExists('sensor_data');
    }
};
