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
        Schema::create('order_return_requests', function (Blueprint $table) {

        $table->id();

        $table->unsignedBigInteger('order_id');
        $table->enum('return_type', ['return', 'exchange'])
            ->default('return');
        $table->text('reason')->nullable();
        $table->text('customer_comment')->nullable();
        $table->json('images')->nullable();
        $table->string('status')->default('pending');
        
        $table->bigInteger('shiprocket_return_order_id')->nullable();
        $table->bigInteger('shiprocket_shipment_id')->nullable();

        $table->string('awb_code')->nullable();
        $table->string('courier_name')->nullable();

        $table->string('pickup_token_number')->nullable();
        $table->dateTime('pickup_scheduled_date')->nullable();
        $table->string('pickup_status')->nullable();

        $table->string('current_tracking_status')->nullable();
        $table->json('tracking_data')->nullable();

        $table->string('refund_status')->nullable();

        $table->decimal('refund_amount', 10, 2)->nullable();

        $table->string('refund_id')->nullable();


        $table->unsignedBigInteger('exchange_order_id')->nullable();

        $table->json('create_return_response')->nullable();

        $table->json('awb_response')->nullable();

        $table->json('pickup_response')->nullable();

        $table->timestamp('approved_at')->nullable();

        $table->timestamp('pickup_completed_at')->nullable();

        $table->timestamp('refunded_at')->nullable();
        $table->json('create_return_payload')->nullable();

        $table->json('awb_payload')->nullable();

        $table->json('pickup_payload')->nullable();

        $table->json('tracking_payload')->nullable();

        $table->json('refund_payload')->nullable();

        $table->json('error_response')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_return_requests');
    }
};
