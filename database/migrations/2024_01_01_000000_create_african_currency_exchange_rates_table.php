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
        Schema::create('african_currency_exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3);
            $table->date('date');
            $table->time('time');
            $table->decimal('buying_rate', 10, 4);
            $table->decimal('mid_rate', 10, 4);
            $table->decimal('selling_rate', 10, 4);
            $table->timestamps();

            $table->unique(['currency_code', 'date', 'time'], 'currency_date_time_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('african_currency_exchange_rates');
    }
};
