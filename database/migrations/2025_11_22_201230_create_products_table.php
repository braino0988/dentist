<?php

use App\Models\Category;
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
            //stock keeping unit
            $table->string('sku')->unique();
            $table->foreignIdFor(Category::class)->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('s_name');
            $table->text('description')->nullable();
            $table->text('s_description')->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('cost', 8, 2)->nullable();
            $table->decimal('tax_rate', 5, 2)->min(0)->max(100.0)->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->string('unit')->nullable();
            $table->string('status')->default('instock'); //instock, outofstock, alertstock
            $table->decimal('product_rate',2,1)->min(0)->max(5.0)->default(0);
            $table->string('delivery_option')->nullable();
            //restrict this to be max 100
            $table->decimal('discount_rate', 5, 2)->min(0)->max(100.0)->nullable();
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
