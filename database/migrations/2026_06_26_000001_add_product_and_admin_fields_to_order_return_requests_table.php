<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_return_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('cart_id')->nullable()->after('order_id');
            $table->text('admin_comment')->nullable()->after('customer_comment');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');

            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('order_return_requests', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
            $table->dropColumn(['cart_id', 'admin_comment', 'rejected_at']);
        });
    }
};
