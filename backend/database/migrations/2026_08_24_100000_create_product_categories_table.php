<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Product categories are deliberately separate from `solution_categories`.
 *
 * The solutions catalogue is marketing copy — what the site says Xponent can
 * supply. These group the operational SKUs that are actually stocked, priced
 * and shipped. The two overlap in name today but diverge the moment a product
 * is discontinued or a solutions page is rewritten, so they do not share a
 * table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
