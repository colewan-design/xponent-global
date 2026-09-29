<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Seeder;

/**
 * Opening copy for the Industries page.
 *
 * Industries are not a table. They are a way of reading the catalogue that
 * already exists — a sector names the ranges it buys from, and every range here
 * is a real solution_categories row. Giving them their own model would mean a
 * second place to keep the same list of ranges in step, so the sectors live in
 * page_content like the other editorial pages and the site maps each heading to
 * the ranges it draws on (see `industryRanges` in pages/industries.vue).
 *
 * That mapping is keyed on the heading, so renaming a heading in the admin drops
 * its range links rather than breaking the page. Rewording a body is always
 * safe; renaming a heading is the one edit that needs a look at the frontend map.
 *
 * Idempotent, and run on its own rather than from DatabaseSeeder: production is
 * already seeded, so this has to be safe to run against a live database.
 */
class IndustriesPageContentSeeder extends Seeder
{
    public function run(): void
    {
        PageContent::updateOrCreate(
            ['page' => 'industries'],
            ['sections' => [
                [
                    'heading' => null,
                    'body' => "Xponent Global supplies the sectors that move earth, sink holes and build what comes after. The ranges below are drawn from one catalogue and one supply chain — the difference between them is the operating conditions they are specified for, not the warehouse they ship from.\n\nEvery sector here is served from stock held in Brisbane, Manila and Hong Kong, with consolidated freight into site.",
                    'image' => null,
                ],
                [
                    'heading' => 'Mining',
                    'body' => "Underground and surface operations run on consumables that cannot be late. We supply production tooling, ground support, mine cars, rails and fasteners against call-off schedules, and hold the fast-moving lines locally so a shift is never waiting on a port.\n\nWhere an operation runs its own camp, the same order can carry the facilities and PPE that keep it staffed.",
                    'image' => null,
                ],
                [
                    'heading' => 'Exploration and Geotechnical',
                    'body' => "Drilling programmes are judged on metres per day and on core that survives the trip to the lab. Our exploration range covers impregnated diamond bits, reamers and casing shoes, core saws and blades, and the trays and guides that get the core out of the hole intact.\n\nBits are matched to formation rather than sold from a single grade — the range adjusts from soft ground to the abrasive, broken conditions that eat standard tooling.",
                    'image' => null,
                ],
                [
                    'heading' => 'Oil, Gas and Energy',
                    'body' => "Energy work is remote, heavily audited and unforgiving of substitutions. We supply drilling consumables and certified personal protection equipment into these programmes, with documentation that survives an audit and specifications held constant across repeat orders.\n\nWhere a project spans several countries, we consolidate supply through one account rather than leaving each site to source locally.",
                    'image' => null,
                ],
                [
                    'heading' => 'Construction and Infrastructure',
                    'body' => "Civil works buy steel wire and mesh by the tonne, to standard. Our wire range is manufactured to JIS, BS, AS/NZS, BS EN, ASTM and MS specifications or to a customer's own, and the finished mesh, gabion and fencing systems are made from that same wire.\n\nGabions and reno mattresses carry retaining walls, channel protection and slope stabilisation; welded and grill mesh handles perimeters, walkways and access control.",
                    'image' => null,
                ],
                [
                    'heading' => 'Agriculture and Land Management',
                    'body' => "Fencing is a boundary, a stock control system and an erosion measure at once. High tensile fence wire, PVC coated wire, binding wire and the straining and tying accessories that go with them are stocked as running lines rather than made to order.\n\nFor land under active repair, the gabion and mattress range doubles as erosion and watercourse protection.",
                    'image' => null,
                ],
            ]],
        );
    }
}
