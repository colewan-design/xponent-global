<?php

namespace Database\Seeders;

use App\Models\SolutionItem;
use Illuminate\Database\Seeder;

/**
 * Clear stand-in artwork from the solutions catalogue.
 *
 * Twelve catalogue items were seeded with photographs from the site-visit
 * gallery — a fern frond against "Rock Bolts, Plates and Accessories", a
 * mountain vista against "Core Orientation Solutions and Survey Cameras", staff
 * portraits against "Mining Equipment". They are real photographs, but none of
 * them depicts the product on the card, which reads worse than no image at all.
 * The card template already hides the frame when `image` is null.
 *
 * Only the catalogue reference is cleared. The files stay on the storage disk
 * because `gallery_images` still serves all sixteen of them on the gallery page,
 * where they are captioned for what they actually are.
 *
 * Genuine product artwork (`sol-image-*`, `solution-*`, `personal-*`) is left
 * alone — this matches on the `seed/gallery-img-` prefix only.
 *
 * Idempotent: a second run matches nothing and clears nothing.
 */
class RemovePlaceholderSolutionImagesSeeder extends Seeder
{
    private const PLACEHOLDER_PREFIX = 'seed/gallery-img-';

    public function run(): void
    {
        $items = SolutionItem::where('image', 'like', self::PLACEHOLDER_PREFIX.'%')->get();

        foreach ($items as $item) {
            $this->command?->info("  clearing {$item->image} from \"{$item->title}\"");
            $item->update(['image' => null]);
        }

        $this->command?->info('Cleared '.$items->count().' placeholder image(s).');
    }
}
