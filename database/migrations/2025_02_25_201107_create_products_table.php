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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->integer('quantity');
            $table->text('images')->nullable();
            $table->enum('stock_status',['instock','outofstock']);
            $table->string('photo')->nullable();
            $table->string('description')->nullable();
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->unsignedBigInteger('crop_id');
            $table->foreign('crop_id')->nullable()->references('crop_id')->on('crops')->onDelete('cascade');
            $table->unsignedBigInteger('farmer_id')->nullable(false);
            $table->foreign('farmer_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
