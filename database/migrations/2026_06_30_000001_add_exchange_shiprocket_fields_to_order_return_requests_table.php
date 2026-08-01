<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_return_requests', function (Blueprint $table) {
            $table->bigInteger('exchange_order_id')->nullable()->after('exchange_order_id');
            $table->bigInteger('exchange_shipment_id')->nullable()->after('exchange_order_id');
            $table->json('exchange_create_payload')->nullable()->after('create_return_response');
            $table->json('exchange_create_response')->nullable()->after('exchange_create_payload');
            $table->timestamp('exchange_approved_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('order_return_requests', function (Blueprint $table) {
            $table->dropColumn([
                'exchange_order_id',
                'exchange_shipment_id',
                'exchange_create_payload',
                'exchange_create_response',
                'exchange_approved_at',
            ]);
        });
    }
};
