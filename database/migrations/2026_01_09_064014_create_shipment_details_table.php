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
        Schema::create('shipment_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('order_number')->nullable();
            $table->string('shipment_id')->nullable();
            $table->string('shipment_order_id')->nullable();
            $table->string('shipment_awb')->nullable();
            $table->string('pickup_id')->nullable();
            $table->string('shipment_status')->nullable();
            $table->text('label_pdf')->nullable();
            $table->text('shipment_response')->nullable();
            $table->text('pickup_request_response')->nullable();
            $table->text('manifest_url')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipment_details');
    }
};
