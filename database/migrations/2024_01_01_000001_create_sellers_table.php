<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->string('business_email');
            $table->string('phone_number');
            $table->string('whatsapp_number')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->decimal('subscription_fee', 10, 2)->default(0);
            $table->enum('subscription_status', ['active', 'inactive', 'expired'])->default('inactive');
            $table->timestamp('subscription_expires_at')->nullable();
            $table->integer('max_products')->default(50);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('user_id');
            $table->index('subscription_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
