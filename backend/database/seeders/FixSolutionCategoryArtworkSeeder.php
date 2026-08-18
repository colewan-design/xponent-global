<?php

namespace Database\Seeders;

use App\Models\SolutionCategory;
use Illuminate\Database\Seeder;

/**
 * Point category artwork at images that actually depict the range.
 *
 * The homepage tiles are photographic entry points with white text over a dark
 * gradient, so unlike the catalogue cards they cannot simply drop the image —
 * a null would leave unreadable text over an empty tile. These categories need
 * a picture; they just need the right one.
 *
 * Four were showing stand-ins from the site-visit gallery: a fern frond for
 * Mining and Production Consumables, a group portrait for Construction, and
 * landscapes for the two wire ranges. Each is repointed at artwork the range
 * genuinely owns:
 *
 *   Mining      -> DTH drilling tools, the consumable behind its Production
 *                  Drilling line. The nearest honest match in the library;
 *                  there is no photography of rock bolts, grinding media or
 *                  mine rails to draw on.
 *   Construction-> the CIMC two-storey complex, which is its Modular &
 *                  Relocatable Buildings line.
 *   Wire / Mesh -> the Chin Herr product shots supplied for those ranges.
 *
 * Idempotent, and deliberately narrow: it only rewrites a category whose image
 * is still the known stand-in, so artwork replaced later through the admin is
 * never clobbered by a re-run.
 */
class FixSolutionCategoryArtworkSeeder extends Seeder
{
    /** slug => [stand-in currently in place, replacement] */
    private const ARTWORK = [
        'mining-and-production-consumables' => ['seed/gallery-img-13.jpg', 'seed/sol-image-06.png'],
        'construction' => ['seed/gallery-img-06.jpg', 'seed/solution-2.png'],
        'steel-wire-products' => ['seed/gallery-img-11.jpg', 'seed/wire-galvanised.png'],
        'wire-mesh-and-gabions' => ['seed/gallery-img-02.jpg', 'seed/mesh-gabion.png'],
    ];

    public function run(): void
    {
        $changed = 0;

        foreach (self::ARTWORK as $slug => [$placeholder, $replacement]) {
            $category = SolutionCategory::where('slug', $slug)->first();

            if (! $category) {
                $this->command?->warn("  skipped {$slug} — category not found");

                continue;
            }

            if ($category->image !== $placeholder) {
                $this->command?->line("  skipped {$slug} — already set to {$category->image}");

                continue;
            }

            $category->update(['image' => $replacement]);
            $this->command?->info("  {$slug}: {$placeholder} -> {$replacement}");
            $changed++;
        }

        $this->command?->info("Updated {$changed} category image(s).");
    }
}
