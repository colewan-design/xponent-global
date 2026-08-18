<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Offices carry a postal address but nothing plottable, so the "Where We
     * Operate" panel could only ever show a flat picture of a map. These two
     * columns let it render a real one.
     *
     * Nullable because the address remains the source of truth: an office added
     * through the admin without coordinates still lists correctly below the map,
     * it just does not get a pin.
     *
     * decimal(10, 7) gives ~1cm resolution and comfortably holds the full
     * -180..180 longitude range, which is far finer than a city-scale pin needs.
     */
    public function up(): void
    {
        Schema::table('office_locations', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('country');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('office_locations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
