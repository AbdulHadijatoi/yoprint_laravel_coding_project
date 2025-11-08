<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_upload_id')->nullable()->constrained('product_uploads')->nullOnDelete();
            $table->string('unique_key')->unique();
            $table->string('product_title')->nullable();
            $table->text('product_description')->nullable();
            $table->string('style_number')->nullable();
            $table->string('available_sizes')->nullable();
            $table->string('brand_logo_image')->nullable();
            $table->string('thumbnail_image')->nullable();
            $table->string('color_swatch_image')->nullable();
            $table->string('product_image')->nullable();
            $table->string('spec_sheet')->nullable();
            $table->string('price_text')->nullable();
            $table->string('size')->nullable();
            $table->string('color_name')->nullable();
            $table->string('sanmar_mainframe_color')->nullable();
            $table->decimal('piece_price', 10, 2)->nullable();
            $table->decimal('dozens_price', 10, 2)->nullable();
            $table->decimal('case_price', 10, 2)->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
