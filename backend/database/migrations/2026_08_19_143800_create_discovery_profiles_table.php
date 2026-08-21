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
        Schema::create('discovery_profiles', function (Blueprint $table) {
            $table->id();
            
            // Core Config
            $table->string('name');
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            
            // Storing product type as a simple string or ID depending on how product types are managed.
            // Since we don't have a product_types table yet, string is safer for now.
            $table->string('product_type')->nullable(); 
            
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            
            // Deal Constraints
            $table->decimal('min_discount_percent', 5, 2)->nullable();
            $table->decimal('max_discount_percent', 5, 2)->nullable();
            $table->decimal('min_price', 10, 2)->nullable();
            $table->decimal('max_price', 10, 2)->nullable();
            $table->text('keywords')->nullable();
            
            // Operational
            $table->string('status')->default('active'); // active, paused
            $table->integer('run_interval')->default(60); // minutes between runs
            
            // Metrics
            $table->timestamp('next_run_at')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_run_status')->nullable(); // success, failed
            $table->integer('last_run_count')->default(0); // number of items found
            $table->text('last_error')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discovery_profiles');
    }
};
