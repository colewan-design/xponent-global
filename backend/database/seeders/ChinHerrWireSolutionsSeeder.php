<?php

namespace Database\Seeders;

use App\Models\SolutionCategory;
use App\Models\SolutionItem;
use Illuminate\Database\Seeder;

/**
 * Chin Herr Industries (M) Sdn Bhd range.
 *
 * Coil-form wire and the finished mesh products drawn from it are split into two
 * categories, matching how the mill itself separates semi-finished from finished
 * goods. Item order follows the product list supplied by the client.
 *
 * Idempotent, and safe to run against a populated database: categories key on
 * slug, items key on title within their category, so re-running updates copy in
 * place rather than duplicating rows. That makes this runnable on production
 * without a destructive re-seed.
 */
class ChinHerrWireSolutionsSeeder extends Seeder
{
    public function run(): void
    {
        $steelWire = SolutionCategory::updateOrCreate(
            ['slug' => 'steel-wire-products'],
            [
                'title' => 'Steel Wire Products',
                'description' => 'Coil-form steel wire manufactured to JIS, BS, AS/NZS, BS EN, ASTM and MS specifications, or to customised requirements. Supplied through our partnership with Chin Herr Industries (M) Sdn Bhd.',
                'image' => 'seed/wire-galvanised.png',
                'sort_order' => 5,
            ],
        );

        $this->syncItems($steelWire, [
            ['title' => 'Black Annealed Wire', 'description' => "Annealed low carbon steel wire for tying, re-drawing and general purposes.\n\nProduct code: JIS G3532 SWM-A\nDiameter range: 0.70mm to 5.50mm\nPacking: Customised, from 50kg up to 300kg\nReference standard: JIS G3532 SWM-A", 'image' => 'seed/wire-black-annealed.png'],
            ['title' => 'Bright Drawn Wire', 'description' => "Ordinary low carbon steel wire with a clean, bright drawn finish.\n\nProduct code: JIS G3532 SWM-B\nDiameter range: 0.70mm to 10.00mm\nReference standard: JIS G3532 SWM-B\nApplications: Fan guards, pet cages, BBQ sets, wire fabric, furniture and general purposes", 'image' => null],
            ['title' => 'Hard Drawn Wire', 'description' => 'Cold drawn high tensile steel wire for applications requiring greater strength and reduced elongation than standard low carbon wire. Diameters and tensile grades supplied to specification.', 'image' => null],
            ['title' => 'Galvanised Wire', 'description' => "Zinc coated low carbon steel wire, standard coating weight.\n\nProduct code: JIS G3547\nDiameter range: 0.89mm to 8.00mm\nPacking: Customised, from 50kg up to 1000kg\nReference standards: JIS G3547, BS 443, BS 1442, ASTM A641/A641M, BS EN 10244, MS 274, or customised specification\nApplications: Armouring cable, chain link fence, barbed wire, galvanised mesh, gabion, tying and general purposes", 'image' => 'seed/wire-galvanised.png'],
            ['title' => 'Heavy Galvanised Wire', 'description' => "Zinc coated low carbon steel wire to heavy coating weight, for extended service life in exposed and corrosive environments. Also available as zinc-aluminium coated wire, 90% Zn + 10% Al (ECOZal-10™).\n\nProduct code: JIS G3547\nDiameter range: 0.89mm to 8.00mm\nPacking: Customised, from 50kg up to 1000kg\nReference standards: JIS G3547, BS 443, BS 1442, ASTM A641/A641M, BS EN 10244, MS 274, or customised specification", 'image' => 'seed/wire-galvanised.png'],
            ['title' => 'PVC Coated Wire', 'description' => "PVC coated colour steel wire for tying, wire fabric, gabion and general purposes.\n\nProduct code: JIS G3543\nDiameter range: According to JIS G3543 or customised specification\nPacking: Customised, from 50kg up to 500kg\nReference standard: JIS G3543\nStandard colours: Green, Dark Green, Grey, Blue, White and Black", 'image' => null],
            ['title' => 'High Tensile Fence Wire', 'description' => 'High tensile galvanised wire for permanent fencing, offering greater strain retention and longer post spacing than standard fence wire. Supplied in coil form to customer specification.', 'image' => null],
            ['title' => 'Mesh Manufacturing Wire', 'description' => 'Wire drawn and supplied specifically for welded wire mesh and fencing mesh production, held to the diameter and tensile consistency that automated mesh welding lines require.', 'image' => null],
            ['title' => 'Nail Wire', 'description' => "Low carbon steel wire for nail manufacturing.\n\nProduct code: JIS G3532 SWM-N\nDiameter range: 1.50mm to 6.65mm\nPacking: Customised, from 100kg up to 500kg\nReference standard: JIS G3532 SWM-N\n\nCarbon steel wire for cold heading and cold forging (CHQ) is also available for collated nail, fastener and screw production — product code JIS G3539, diameter range 1.60mm to 5.50mm, packing from 100kg up to 300kg.", 'image' => null],
            ['title' => 'Binding / Tie Wire', 'description' => 'Soft annealed and galvanised binding wire for rebar tying, bundling and general site fixing. Available black annealed, galvanised or PVC coated, in coil and customised pack sizes.', 'image' => null],
            ['title' => 'Vineyard / Orchard Wire', 'description' => 'Galvanised and high tensile trellis wire for vineyard, orchard and horticultural training systems, selected for corrosion resistance and consistent strain over long runs.', 'image' => null],
            ['title' => 'Barbed Wire', 'description' => 'Galvanised barbed wire for perimeter security, boundary and agricultural fencing. Available across the full range of wire diameters and barb configurations — contact us for the current combination list and sample availability.', 'image' => null],
            ['title' => 'Customised Coil Wire Products', 'description' => 'Beyond the standard range, Chin Herr manufactures wire products in coil form to customer drawings and specifications. Talk to us about diameter, coating, tensile grade, colour and packing requirements.', 'image' => null],
        ]);

        $wireMesh = SolutionCategory::updateOrCreate(
            ['slug' => 'wire-mesh-and-gabions'],
            [
                'title' => 'Wire Mesh, Gabions and Fencing',
                'description' => 'Finished mesh and fencing systems manufactured from our steel wire range. Chin Herr Industries was the first Malaysian manufacturer to obtain SIRIM Product Certification for these products, in 2018, under ISO 9001:2015 certified by SIRIM QAS International.',
                'image' => 'seed/mesh-gabion.png',
                'sort_order' => 6,
            ],
        );

        $this->syncItems($wireMesh, [
            ['title' => 'Gabions and Mattresses', 'description' => "Gabion baskets and mattresses for retaining structures, erosion control and river training works.\n\nCoating type: Heavy galvanised\nMesh wire size: 2.7mm or 3.0mm\nSelvedge wire size: 3.4mm", 'image' => 'seed/mesh-gabion.png'],
            ['title' => 'Grill Mesh', 'description' => "Welded grill mesh fencing for boundary, perimeter and access control.\n\nApplications: Residential areas, pedestrian walkways, schools and universities, prisons and hospitals, community areas, commercial sites, sports facilities, industrial sites, airports and railway stations, army camps, amusement parks and playgrounds, ranches and farms.", 'image' => 'seed/mesh-grill.png'],
            ['title' => 'Anti-Climb and Security Fencing', 'description' => 'High-security welded mesh fencing with apertures sized to resist climbing and cutting, for critical infrastructure, industrial perimeters and restricted sites.', 'image' => null],
            ['title' => 'MSE and Green MSE Wall Systems', 'description' => 'Mechanically stabilised earth wall and netting systems, including vegetated Green MSE walls designed to blend the finished structure back into the surrounding environment.', 'image' => null],
            ['title' => 'Poultry and Agricultural Mesh', 'description' => 'Welded and woven mesh for poultry farming, livestock enclosure and general agricultural use, supplied galvanised or PVC coated.', 'image' => null],
            ['title' => 'Customised Mesh Products', 'description' => "Mesh manufactured to customer requirements for specialised industrial applications.\n\nPacking standards: Coil form, PE wrap, bundle form.", 'image' => null],
        ]);

        // PPE sat at 5 before the two wire ranges were inserted ahead of it.
        SolutionCategory::where('slug', 'personal-protection-equipment')->update(['sort_order' => 7]);
    }

    private function syncItems(SolutionCategory $category, array $items): void
    {
        foreach ($items as $index => $item) {
            SolutionItem::updateOrCreate(
                ['solution_category_id' => $category->id, 'title' => $item['title']],
                [
                    'description' => $item['description'],
                    'image' => $item['image'],
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
