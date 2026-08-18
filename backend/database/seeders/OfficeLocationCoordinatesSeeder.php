<?php

namespace Database\Seeders;

use App\Models\OfficeLocation;
use Illuminate\Database\Seeder;

/**
 * Plot the existing offices.
 *
 * Coordinates were geocoded from each office's own postal address via OSM
 * Nominatim, then rounded to the precision the source returned. They sit at
 * street level for Brisbane and Hong Kong, where the address resolves to a
 * building, and at suburb level for the two Philippine sites, whose compound
 * and subdivision addresses do not resolve individually — close enough for a
 * pin on a regional map, and worth refining if the business has exact figures.
 *
 * Matched on `label` rather than id so it survives a re-seed that renumbers the
 * table. Idempotent: re-running writes the same values.
 */
class OfficeLocationCoordinatesSeeder extends Seeder
{
    /** label => [latitude, longitude] */
    private const COORDINATES = [
        'Brisbane, Australia' => [-27.4984053, 153.2575597],
        'Philippine Warehouse' => [14.5095273, 121.0380244],
        'Philippine Head Office' => [7.0899788, 125.5781439],
        'Hong Kong, China' => [22.3008651, 114.1783375],
    ];

    public function run(): void
    {
        foreach (self::COORDINATES as $label => [$latitude, $longitude]) {
            $office = OfficeLocation::where('label', $label)->first();

            if (! $office) {
                $this->command?->warn("  skipped \"{$label}\" — no matching office");

                continue;
            }

            $office->update(['latitude' => $latitude, 'longitude' => $longitude]);
            $this->command?->info("  {$label}: {$latitude}, {$longitude}");
        }

        $plotted = OfficeLocation::whereNotNull('latitude')->count();
        $this->command?->info("{$plotted} of ".OfficeLocation::count().' offices now plottable.');
    }
}
