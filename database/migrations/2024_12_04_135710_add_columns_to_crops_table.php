<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
    $table->string('productName');
    $table->string('productCategory');
    $table->decimal('pricePerKilo', 8, 2);
    $table->integer('quantity');
    $table->string('status');
    $table->string('photo')->nullable();
    $table->unsignedBigInteger('user_id'); // يجب أن يكون موجودًا
    $table->timestamps();

    // إضافة العلاقة مع جدول المستخدمين
    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            //
        });
    }
};
