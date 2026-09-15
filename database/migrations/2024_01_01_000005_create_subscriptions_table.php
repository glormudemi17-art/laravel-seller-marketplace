<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
            $table->string('plan_name');
            $table->decimal('amount', 10, 2);
            $table->enum('period', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->timestamp('started_at');
            $table->timestamp('expires_at');
            $table->timestamp('renewed_at')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->text('payment_reference')->nullable();
            $table->timestamps();
            
            $table->index('seller_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
