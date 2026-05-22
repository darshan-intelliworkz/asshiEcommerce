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
        Schema::create('payment_refunds', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('payment_id');

            $table->string('razorpay_payment_id');
            $table->string('razorpay_refund_id')->nullable();

            $table->decimal('refund_amount', 10, 2);

            $table->enum('refund_status', [
                'pending',
                'processed',
                'failed'
            ])->default('pending');

            $table->longText('refund_response')->nullable();

            $table->text('refund_reason')->nullable();

            $table->timestamp('refunded_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_refunds');
    }
};
