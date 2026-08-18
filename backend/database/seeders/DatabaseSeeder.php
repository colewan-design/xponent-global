<?php

namespace Database\Seeders;

use App\Models\ContactEnquiry;
use App\Models\GalleryImage;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\NewsletterSubscriber;
use App\Models\OfficeLocation;
use App\Models\PageContent;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Resource as ResourceModel;
use App\Models\Setting;
use App\Models\SolutionCategory;
use App\Models\SolutionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedSettings();
        $this->seedOfficeLocations();
        $this->seedPageContent();
        $this->seedPartners();
        $this->seedGallery();
        $this->seedSolutions();
        $this->seedJobs();
        $this->seedResources();
        $this->seedPosts();
        $this->seedNewsletterSubscribers();
        $this->seedContactEnquiries();
        $this->seedJobApplications();
    }

    private function seedUsers(): void
    {
        User::create([
            'name' => 'Xponent Admin',
            'email' => 'admin@xponent-global.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Content Editor',
            'email' => 'editor@xponent-global.com',
            'password' => 'password',
            'role' => 'editor',
        ]);
    }

    private function seedSettings(): void
    {
        $settings = [
            'company_name' => 'Xponent Global',
            'company_legal_name' => 'Xponent Global Limited',
            'company_tagline' => 'Supplying Confidence. Delivering Certainty.',
            // The legacy site comments out every named-staff contact block (Rosemarie
            // Hamilton / Connie da Cunha, both phone numbers, both personal addresses)
            // on all 15 of its pages. The single address it still publishes is the
            // shared inbox on contact.html, so that is the only one seeded here.
            // Admin > Settings can restore the others if the business wants them back.
            'contact_email' => 'info@xponent-global.com',
            'contact_email_alt' => '',
            'contact_phone' => '',
            'contact_phone_alt' => '',
            // "Schedule a Visit", repeated on 7 of the 12 legacy pages (home, about,
            // clients, sustainability, resources, newsletter, career). Its body copy
            // there is Lorem Ipsum, so only the hours are carried over.
            'hours_weekdays' => '9:00am - 4:30pm',
            'hours_saturday' => '9:00am - 1:30pm',
            'hours_sunday' => 'Closed',
            'footer_about' => "Xponent Global is an international total solutions provider in the mining, drilling, oil and gas, construction and energy sector.\n\nWith combined professional experience of over 30 years, Xponent Global's broad scope of expertise includes effectively turning ideas into opportunities and opportunities into action, helping nations thrive and work towards a better world.",
            'facebook_url' => '',
            'instagram_url' => '',
            'twitter_url' => '',
            'youtube_url' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value]);
        }
    }

    private function seedOfficeLocations(): void
    {
        $locations = [
            ['label' => 'Brisbane, Australia', 'address' => '251-255 Wellington Street, Ormiston', 'city' => 'Queensland 4160', 'latitude' => -27.4984053, 'longitude' => 153.2575597, 'country' => 'Australia'],
            ['label' => 'Philippine Warehouse', 'address' => 'Bldg. 3A Panorama Compound, Veterans Road, Veterans Center, Western Bicutan', 'city' => 'Taguig City, Metro Manila 1630', 'latitude' => 14.5095273, 'longitude' => 121.0380244, 'country' => 'Philippines'],
            ['label' => 'Philippine Head Office', 'address' => '423 Green Meadows, Purok 11, Maa', 'city' => 'Davao City, Davao Del Sur 8000', 'latitude' => 7.0899788, 'longitude' => 125.5781439, 'country' => 'Philippines'],
            ['label' => 'Hong Kong, China', 'address' => 'Rm 805 Harbour Crystal Centre, 100 Granville Road, Tsim Sha Tsui', 'city' => 'Kowloon', 'latitude' => 22.3008651, 'longitude' => 114.1783375, 'country' => 'Hong Kong'],
        ];

        foreach ($locations as $index => $location) {
            OfficeLocation::create($location + ['sort_order' => $index + 1]);
        }
    }

    private function seedPageContent(): void
    {
        PageContent::create([
            'page' => 'home',
            'sections' => [
                [
                    'heading' => 'Driving Innovation, Powering Progress',
                    'body' => "With over 30 years of expertise, we transform ideas into opportunities and opportunities into action—leading the way in mining, drilling, oil & gas, construction, and energy. Helping nations thrive for a better tomorrow.\n\nWhy partner with us? Xponent Global stands by only one promise: to deliver quality around the globe.\n\nXponent Global's products, practices, and procedures were developed through its extensive global experience in mining, drilling, exploration, and construction sectors.",
                    'image' => 'seed/IMG_2919.jpg',
                ],
            ],
        ]);

        PageContent::create([
            'page' => 'about',
            'sections' => [
                [
                    'heading' => 'About XGL — Who we are',
                    'body' => "Xponent Global Limited\n\nXponent Global is an international total solutions provider in the mining, drilling, oil and gas, construction and energy sector. With combined professional experience of over 30 years, Xponent Global's broad scope of expertise includes effectively turning ideas into opportunities and opportunities into action, helping nations thrive and work towards a better world.",
                    'image' => 'seed/IMG_2958.jpg',
                ],
                [
                    'heading' => 'Our Vision',
                    'body' => 'To drive innovation, excellence, and reliability, ensuring we remain the preferred choice for companies worldwide.',
                    'image' => 'seed/gallery-img-08.jpg',
                ],
                [
                    'heading' => 'Our Mission',
                    'body' => 'To provide the best possible product, service, technology and pricing on merchandise with the expertise to bring the deal together smoothly and quickly. We believe that to be able to run a great drilling & mining operation, a dependable and reliable supplier should be part of the team.',
                    'image' => 'seed/gallery-img-06.jpg',
                ],
                [
                    'heading' => 'Our Core Values',
                    'body' => "Quality: We prioritize client satisfaction by optimizing resources and ensuring rigorous quality control, maintaining accuracy from loading to shipment.\n\nCommitment: Upholding combined 30+ years of excellence, we seamlessly extend client operations with timely, organized delivery exceeding expectations.\n\nValue: We take pride in delivering the best deals, earning trust as the preferred global supplier in construction, mining, and exploration.",
                    'image' => 'seed/gallery-img-11.jpg',
                ],
                [
                    // The legacy page shows only a heading and the map here; the offices
                    // themselves come from the office_locations resource.
                    'heading' => 'Where We Operate',
                    'body' => null,
                    'image' => 'seed/map-img.jpg',
                ],
                [
                    'heading' => 'Our Affiliations',
                    'body' => "We proudly support these organizations based on our mutual interests within the mining, construction and geotechnical industry.\n\nWe aim to develop and maintain a strong relationship with them to support their causes, extend our own knowledge, expertise and network of specialists.",
                    'image' => 'seed/our-aff.jpg',
                ],
            ],
        ]);

        PageContent::create([
            'page' => 'sustainability',
            'sections' => [
                [
                    'heading' => null,
                    'body' => "At Xponent Global, sustainability is not just an initiative—it's a commitment woven into the very core of how we operate. With over 30 years of combined experience in mining, drilling, oil & gas, construction, and energy sectors, we recognize our responsibility to deliver long-term value for clients, communities, and the environment.",
                    'image' => 'seed/gallery-img-01.jpg',
                ],
                [
                    'heading' => 'Our Commitment to Sustainability',
                    'body' => 'Our commitment to sustainability is rooted in our promise to deliver quality around the globe while minimizing environmental impact and maximizing shared value. We believe in responsible business practices that contribute to a better world—transforming resources efficiently and ethically to help nations thrive. We embed sustainability into our operations through strategic planning, risk mitigation, supply chain optimization, and continuous improvement across our value chain.',
                    'image' => 'seed/gallery-img-05.jpg',
                ],
                [
                    'heading' => 'Safe and Responsible Business',
                    'body' => 'We place safety, integrity, and responsibility at the forefront of our operations. Our products and procedures are developed with global safety standards in mind, ensuring that every operation—whether in remote mining sites or complex energy projects—follows strict compliance and risk management protocols. We foster a culture of safety and accountability for our people, partners, and clients, supporting robust incident prevention and emergency preparedness systems.',
                    'image' => 'seed/gallery-img-02.jpg',
                ],
                [
                    'heading' => 'Climate and the Natural Environment',
                    'body' => 'Recognizing the importance of environmental stewardship, Xponent Global actively works to reduce emissions, minimize waste, and promote resource efficiency. We support the use of environmentally responsible drilling and mining consumables and encourage sustainable site development practices. Through innovation and smart procurement, we aim to reduce our carbon footprint and support our clients in achieving their own environmental goals.',
                    'image' => 'seed/gallery-img-11.jpg',
                ],
                [
                    'heading' => 'Community and Relationships',
                    'body' => 'Xponent Global values strong relationships with local communities, suppliers, and clients. We strive to create positive social and economic outcomes by sourcing responsibly, supporting local employment, and engaging respectfully with stakeholders. Our operations aim to leave a lasting legacy by contributing to the social infrastructure, training, and development of the communities in which we work.',
                    'image' => 'seed/gallery-img-04.jpg',
                ],
                [
                    'heading' => 'People and Capabilities',
                    'body' => 'Our team is our strength. We invest in our people to cultivate technical expertise, leadership, and operational excellence. By fostering an inclusive, collaborative, and empowering workplace, we ensure our employees are equipped with the skills and knowledge to deliver outstanding results sustainably. Our clients benefit from a dependable team that acts as an extension of their operations—efficient, capable, and committed to success.',
                    'image' => 'seed/gallery-img-06.jpg',
                ],
                [
                    'heading' => 'Technology and Innovation',
                    'body' => "Innovation drives our sustainable impact. We continually seek and deploy advanced technologies that improve operational efficiency, reduce environmental harm, and enhance product performance. Whether it's through more sustainable PPE materials, smart construction solutions, or cleaner exploration tools, we align technology adoption with sustainable development goals. Our commitment to innovation ensures we remain at the forefront of responsible growth in our industries.",
                    'image' => 'seed/gallery-img-02.jpg',
                ],
            ],
        ]);

        PageContent::create([
            'page' => 'careers',
            'sections' => [
                [
                    'heading' => 'Join a Global Force Powering Innovation and Progress',
                    'body' => 'At Xponent Global, we don\'t just offer jobs—we offer careers with purpose. With over 30 years of pioneering expertise, we operate at the frontlines of mining, drilling, energy, construction, and oil & gas. Whether you\'re in the field or at HQ, you\'ll be part of a team that\'s shaping industries and building futures.',
                    'image' => 'seed/gallery-img-02.jpg',
                ],
                [
                    'heading' => 'Why Work With Us?',
                    'body' => "Global Projects: Work on impactful projects across Africa, Asia, the Middle East, and beyond.\n\nTechnology-Driven: Be part of an organization that embraces innovation and cutting-edge practices.\n\nCulture of Excellence: We foster a collaborative environment that values performance, integrity, and growth.\n\nPeople First: Your safety, well-being, and professional development are our top priorities.",
                    'image' => 'seed/gallery-img-06.jpg',
                ],
            ],
        ]);

        // The three section titles and lead lines from the legacy resources.html.
        // Order matters: the page destructures these positionally.
        PageContent::create([
            'page' => 'resources',
            'sections' => [
                [
                    'heading' => 'Technical Documents',
                    'body' => 'Get the specs, data sheets, and certifications you need to ensure quality and compliance.',
                    'image' => null,
                ],
                [
                    'heading' => 'Case Studies',
                    'body' => 'Discover how Xponent Global delivers real-world impact across diverse sectors.',
                    'image' => null,
                ],
                [
                    'heading' => 'News & Insights',
                    'body' => 'Stay informed with our latest updates, market trends, and industry commentary.',
                    'image' => null,
                ],
            ],
        ]);
    }

    private function seedPartners(): void
    {
        $affiliations = [
            ['name' => 'Chamber of Mines of the Philippines', 'logo' => 'main-cl-01.jpg', 'description' => 'A professional association of the country\'s largest mining, quarrying, and mineral processing companies, formed with the aim of promoting responsible exploration, development, and utilisation of minerals.'],
            ['name' => 'Australian Chamber of Commerce and Industry', 'logo' => 'main-cl-02.jpg', 'description' => 'The nation\'s largest and most representative business network, with members spanning state and territory chambers of commerce, national industry associations, and a council of business leaders. Together, they represent businesses of all sizes, across every sector of the economy, and from every region of Australia.'],
            ['name' => 'Chamber of Commerce and Industry Australia Philippines (CCIAP)', 'logo' => 'main-cl-03.jpg', 'description' => 'The first local chamber in Australia whose charter is focused on bilateral trade relations between Australia and the Philippines. CCIAP believes in the Australian multicultural principle of productive diversity, which asserts the significant cultural and economic dividends arising from the diversity of the population.'],
            ['name' => 'Australia New Zealand Chamber (ANZCHAM) Philippines', 'logo' => 'main-cl-04.jpg', 'description' => 'An organisation that visibly supports and promotes business relationships between Australia, New Zealand and the Philippines.'],
            ['name' => 'Philippines Mine Safety and Environment Association (PMSEA)', 'logo' => 'main-cl-05.jpg', 'description' => 'The forerunner in the promotion of occupational safety and health, sound environmental management and social responsibility in the mineral industry. PMSEA believes that responsible mining is the key to a great nation.'],
            ['name' => 'DIWATA', 'logo' => 'main-cl-06.jpg', 'description' => 'A non-government organisation advocating the responsible development of the Philippines\' wealth in resources, including mining, oil and gas, quarrying, and other mineral resources.'],
            ['name' => 'Philippine Mining & Exploration Association (PMEA)', 'logo' => 'main-cl-07.jpg', 'description' => 'A non-stock, non-profit entity providing effective and responsible representation of the mineral exploration and mineral resources development sectors in the Philippines.'],
        ];

        foreach ($affiliations as $index => $affiliation) {
            Partner::create([
                'type' => 'affiliation',
                'name' => $affiliation['name'],
                'logo' => 'seed/'.$affiliation['logo'],
                'description' => $affiliation['description'],
                'sort_order' => $index + 1,
            ]);
        }

        // The legacy `our-clients.html` carousel shows 12 logos with no captions or alt text.
        // Names below were read off the logo artwork itself (cl-logo-01..12.jpg), in carousel order.
        $clients = [
            ['name' => 'Philsaga Mining Corporation', 'website_url' => 'https://www.philsaga.com'],
            ['name' => 'SBF Philippines Drilling Resources Corporation', 'website_url' => 'https://www.sbfdrilling.com'],
            ['name' => 'Quest Exploration Drilling (Philippines) Inc.', 'website_url' => 'https://www.qedrill.com'],
            ['name' => 'Apex Mining Co., Inc.', 'website_url' => 'https://www.apexmines.com'],
            ['name' => 'Geodrill', 'website_url' => 'https://www.geodrill.ltd'],
            ['name' => 'SR Metals, Inc.', 'website_url' => null],
            ['name' => 'Philex Mining Corporation', 'website_url' => 'https://philexmining.com.ph'],
            ['name' => 'Major Drilling', 'website_url' => 'https://www.majordrilling.com'],
            ['name' => 'OceanaGold', 'website_url' => 'https://oceanagold.com'],
            ['name' => 'Filminera Resources Corporation', 'website_url' => 'https://filminera.ph'],
            ['name' => 'Capital Drilling', 'website_url' => 'https://www.capdrill.com'],
            ['name' => 'Lepanto Consolidated Mining Company', 'website_url' => 'https://www.lepantomining.com'],
        ];

        foreach ($clients as $index => $client) {
            Partner::create([
                'type' => 'client',
                'name' => $client['name'],
                'logo' => sprintf('seed/cl-logo-%02d.jpg', $index + 1),
                'website_url' => $client['website_url'],
                'sort_order' => $index + 1,
            ]);
        }

        $brandLogos = ['brand-1.png', 'brand-3.png', 'brand-4.png', 'unnamed.png'];
        foreach ($brandLogos as $index => $logo) {
            Partner::create([
                'type' => 'brand_partner',
                'name' => 'Brand Partner '.($index + 1),
                'logo' => 'seed/'.$logo,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function seedGallery(): void
    {
        // The legacy gallery is a bare lightbox grid — 16 photos, every one with
        // alt="", no captions and no categories. Captions here are written from the
        // photographs themselves so the grid, the lightbox aria-label and the
        // screen-reader heading have something real to announce; the public page
        // never renders them visually, so the layout still matches the legacy site.
        $captions = [
            1 => 'Site visit team on the mountain access road to a remote drill site, with support vehicles parked along the ridge',
            2 => 'Xponent Global and site representatives beside drill rods and rig equipment in a covered workshop',
            3 => 'Geologist in high-visibility gear inspecting recovered drill core in racked core trays at the core yard',
            4 => 'Site team reviewing a core sample together at the core storage racks',
            5 => 'Drill crew in hard hats reviewing survey data on a laptop and tablet at the rig',
            6 => 'Group photo with the drill crew on the rig platform, drill rods stacked alongside',
            7 => 'Mineralised hand specimen with green copper staining, held above the core trays',
            8 => 'Ridgeline view across the project area, with exploration access tracks cut into the forested slopes',
            9 => 'Forested valley and winding access roads across the exploration tenement',
            10 => 'Exploration team on a ridge overlooking the project area',
            11 => 'Rainforest ridges and exploration tracks seen from a cleared vantage point',
            12 => 'Young fern fronds unfurling in regrowth, with the forested valley beyond',
            13 => 'Fern fronds unfurling above the forest canopy',
            14 => 'Fiddlehead fern against the mountain backdrop at the project site',
            15 => 'Fern frond unfurling against a clear sky over the ranges',
            16 => 'Geologist logging a mineralised rock specimen at the core yard, racked core trays behind',
        ];

        for ($i = 1; $i <= 16; $i++) {
            GalleryImage::create([
                'image' => sprintf('seed/gallery-img-%02d.jpg', $i),
                'caption' => $captions[$i],
                'sort_order' => $i,
            ]);
        }
    }

    private function seedSolutions(): void
    {
        $exploration = SolutionCategory::create([
            'title' => 'Exploration and Geotechnical Products and Solutions',
            'slug' => 'exploration-and-geotechnical',
            'description' => 'Underground and surface tooling, drilling consumables, and coring systems engineered for demanding exploration and geotechnical programs.',
            'image' => 'seed/sol-image-03.png',
            'sort_order' => 1,
        ]);

        $explorationItems = [
            ['title' => 'Coring System', 'description' => 'Xponent Global offers a complete coring system line. We have available rods with various thread types to meet your drilling needs. We have threads available in different tube sizes, ranging from BQ to PQ3 size.', 'image' => 'seed/sol-image-01.png'],
            ['title' => 'Drill Rods, Casing and Subs', 'description' => "Developed by Xponent Global's affiliate, Drill Rods that continually prove to be at par in rod joint load testing, bench and field bending, pull tests and make-break studies, with international leading brands.\n\nHeavy-duty drill rods and casings built to maintain borehole integrity across a wide range of ground conditions.", 'image' => 'seed/sol-image-00.jpg'],
            ['title' => 'Bits and Reamers', 'description' => "Xponent Global's diamond bits are highly versatile, capable of adjusting from one ground formation to another. They are also very durable, able to resist the wear and punishment of extreme ground conditions.\n\nWe use the latest technological advances to keep up with industry demand, producing reliable, high-production bits at a competitive price for even the harshest drilling conditions.\n\nOur bits are commonly used for geotechnical coring, material sample coring, and mineral exploration.\n\nThe range covers button drills, rotary drill bits, impregnated diamond bits, casing shoes and reamers.", 'image' => 'seed/sol-image-05.png'],
            ['title' => 'Button Drills', 'description' => 'Tungsten-carbide button bits for percussive and rotary-percussive drilling, matched to ground formation and hole size.', 'image' => 'seed/sol-image-03.png'],
            ['title' => 'Rotary Drill Bits', 'description' => 'Rotary bits for exploration and material sample drilling, selected to suit formation hardness and required penetration rate.', 'image' => 'seed/sol-image-04.png'],
            ['title' => 'Impregnated Diamond Bits', 'description' => 'Impregnated diamond bits for hard-formation coring, engineered for consistent penetration rates and long bit life in abrasive ground.', 'image' => 'seed/sol-image-05.png'],
            ['title' => 'DTH Drilling Tools', 'description' => 'Our DTH Drilling Consumables are widely used in surface mining, water well drilling, oil, gas, construction and field exploration projects because of its excellent performance and reliable quality.', 'image' => 'seed/sol-image-06.png'],
            ['title' => 'Reverse-Circulation System', 'description' => 'RC drilling equipment delivering fast, contamination-free sample recovery for exploration programs.', 'image' => 'seed/sol-image-02.png'],
            ['title' => 'Core Saw, Blades and Accessories', 'description' => 'Precision core saws and diamond blades for clean, accurate core sample cutting.', 'image' => 'seed/sol-image-07.png'],
            ['title' => 'Discoverer Core Trays', 'description' => "Proven in the field since 1993 and used by many of the world's leading mining companies. Designed and engineered for ease of use, safety in the field and to withstand the harshest climates, from sub-zero conditions to extreme heat. Manufactured to exacting standards to give you the ultimate in core storage.\n\nFeatures:\nErgonomic built-in handles\nBold START indicator\nNesting capabilities\nStacking capabilities\nFull depth channel heights\nRibbing for stability in transportation\nIntegral strength and high durability\nDrainage holes that prevent water-logging\n\nDiscoverer Accessories:\nCore markers\nLids and lid clips\nLocking links\nAluminium ID tags", 'image' => 'seed/sol-image-08.png'],
            ['title' => 'Core Orientation Solutions and Survey Cameras', 'description' => 'Orientation tools and downhole survey cameras for accurate structural analysis of recovered core.', 'image' => null],
            ['title' => 'Drilling Fluids and Additives', 'description' => 'A range of drilling fluids and additives formulated to improve hole stability and drilling performance.', 'image' => null],
        ];
        $this->createSolutionItems($exploration, $explorationItems);

        $mining = SolutionCategory::create([
            'title' => 'Mining and Production Consumables',
            'slug' => 'mining-and-production-consumables',
            'description' => 'Rock support, rail infrastructure, and production consumables that keep underground and surface mining operations running.',
            'image' => 'seed/sol-image-06.png',
            'sort_order' => 2,
        ]);

        $miningItems = [
            ['title' => 'Rock Bolts, Plates and Accessories', 'description' => 'A full range of rock bolts, plates, and accessories for reliable ground support.', 'image' => null],
            ['title' => 'Grinding Balls and Rods', 'description' => 'High-chrome grinding media engineered for consistent performance and low wear rates in mineral processing.', 'image' => null],
            ['title' => 'Mine Rails, Mine Cars and Consumables', 'description' => 'Steel rails, mine cars, and associated consumables for underground haulage systems.', 'image' => null],
            ['title' => 'Mining Equipment', 'description' => 'Supporting equipment for day-to-day mining operations, sourced from trusted global manufacturers.', 'image' => null],
            ['title' => 'Steel Mesh', 'description' => 'Welded steel mesh for ground support and reinforcement applications.', 'image' => null],
            ['title' => 'Mobile Crimping Workshop', 'description' => 'On-site mobile crimping services to minimize downtime on cable and rope maintenance.', 'image' => null],
            ['title' => 'Production Drilling', 'description' => 'Tooling and consumables purpose-built for high-volume production drilling.', 'image' => null],
        ];
        $this->createSolutionItems($mining, $miningItems);

        $campFacilities = SolutionCategory::create([
            'title' => 'Mining Camp Facilities',
            'slug' => 'mining-camp-facilities',
            'description' => 'Xponent Global, in partnership with China International Marine Containers (Group) Ltd. (CIMC), is a world-leading supplier of logistics and energy equipment. Together we supply high-quality, reliable equipment and services — including containers, vehicles, energy, chemical and food equipment, offshore, logistics services, airport facilities and more.',
            'image' => 'seed/solution-1.png',
            'sort_order' => 3,
        ]);

        // Legacy page order: the quality-standard block sits directly under the CIMC
        // intro, ahead of the three accommodation units.
        $campItems = [
            ['title' => 'Global Quality Standard', 'description' => "The assessment of buildings has been conducted in accordance with the following Australian Standards:\n\nAS/NZS 1170.0:2002 — Structural Design Actions\nAS/NZS 1170.1:2002 — Permanent, Imposed, and Other Actions\nAS/NZS 1170.2:2011 — Wind Load Actions\nAS 1170.4:2007 — Earthquake Load Actions\nAS 4100-1998 — Steel Structures\nAS/NZS 4600:2005 — Cold-Formed Steel Structures", 'image' => null],
            ['title' => 'CIMC Modular 4-Bedroom Relocatable Accommodation — Upper Level', 'description' => "Brand new. Steel chassis, sandwich panel construction with galvanised external cladding. Building dimensions 15.15m x 4.45m including services frame (contains HWS, air-con, switch gear). Meets AS1170.2-2002 for wind region A1, terrain category 2.\n\nSpecification:\nDaikin Inverter air-conditioning (indoor unit FTXS25KVMA, heat pump outdoor unit RXS25KVMA)\nHot water service — Rheem MPI-325 electric, 325L, integrated heat pump (model 55132505)\nFridge — Haier 130L (model HBF130W)\nEnsuite comprising tiled floor, imperial ware ceramic 4-star water rating dual flush toilet, 2-door lockable ceramic vanity, and glass shower door\nRooms wired for voice & data\n4 x 10-amp double power outlets\n1 x 10-amp single power outlet\nHard-wired smoke detector\n\nEach bedroom also features:\n26\" LCD TV with wall bracket\nSealy king single ensemble bed & mattress\nBlock-out blinds\nCordless kettle\nBedside 3-drawer mobile unit\nFixed shelf unit\n2-door lockable storage cupboard\nDesk\nBlack mesh chair", 'image' => 'seed/solution-1.png'],
            ['title' => 'CIMC Modular 4-Bedroom Relocatable Accommodation — Lower Level', 'description' => "Brand new. Steel chassis, sandwich panel construction with galvanised external cladding. Building dimensions 15.15m x 4.45m including services frame (contains HWS, air-con, switch gear). Meets AS1170.2-2002 for wind region A1, terrain category 2.\n\nSpecification:\nDaikin Inverter air-conditioning (indoor unit FTXS25KVMA, outdoor unit RXS25KVMA)\nHot water service — Rheem MPI-325 electric, 325L, integrated heat pump (model 55132505)\nFridge — Haier 130L (model HBF130W)\nEnsuite comprising tiled floor, imperial ware ceramic 4-star water rating dual flush toilet, 2-door lockable ceramic vanity, and glass shower door\nRooms wired for voice & data\n4 x 10-amp double power outlets\n1 x 10-amp single power outlet\nHard-wired smoke detector\n\nEach bedroom includes:\n26\" LCD TV with wall bracket\nSealy king single ensemble bed & mattress\nBlock-out blinds\nCordless kettle\nBedside 3-drawer mobile unit\nFixed shelf unit\n2-door lockable storage cupboard\nDesk\nBlack mesh chair", 'image' => 'seed/solution-lower.png'],
            ['title' => 'CIMC Modular 4-Bedroom Relocatable Accommodation — Building', 'description' => "Two-storey relocatable accommodation complexes, available in three sizes: 44, 47 or 60 rooms.\n\n44 Room Two-Storey Complex:\n6 x CIMC Modular 4-Bedroom Relocatable Accommodation Buildings — Upper Level\n5 x CIMC Modular 4-Bedroom Relocatable Accommodation Buildings — Lower Level\n1 x CIMC Modular Relocatable Laundry Services Building — Lower Level\nFabricated Steel Balcony & Stair Structure\n\n47 Room Two-Storey Complex:\n6 x CIMC Modular 4-Bedroom Relocatable Accommodation Buildings — Upper Level\n5 x CIMC Modular 4-Bedroom Relocatable Accommodation Buildings — Lower Level\n1 x CIMC Modular 3-Bedroom & Communications Relocatable Accommodation — Lower Level\nFabricated Steel Balcony & Stair Structure\n\n60 Room Two-Storey Complex:\n8 x CIMC Modular 4-Bedroom Relocatable Accommodation Buildings — Upper Level\n6 x CIMC Modular 4-Bedroom Relocatable Accommodation Buildings — Lower Level\n1 x CIMC Modular Relocatable Laundry Services Building — Lower Level\n1 x CIMC Modular 3-Bedroom & Communications Relocatable Accommodation — Lower Level\n1 x CIMC Modular Relocatable Disabled Laundry Services Building — Lower Level\nFabricated Steel Balcony & Stair Structure", 'image' => 'seed/solution-2.png'],
        ];
        $this->createSolutionItems($campFacilities, $campItems);

        $construction = SolutionCategory::create([
            'title' => 'Construction',
            'slug' => 'construction',
            'description' => 'Materials, equipment, and modular building solutions supporting construction programs across mining, energy, and infrastructure projects.',
            'image' => 'seed/solution-2.png',
            'sort_order' => 4,
        ]);

        $constructionItems = [
            ['title' => 'Construction Materials & Equipment', 'description' => 'Sourcing and supply of construction materials and equipment for infrastructure and site development projects.', 'image' => null],
            ['title' => 'Modular & Relocatable Buildings', 'description' => 'Relocatable building solutions delivered through our partnership with CIMC, suited to remote and fast-track construction sites.', 'image' => null],
        ];
        $this->createSolutionItems($construction, $constructionItems);

        // Chin Herr wire and mesh ranges sit at sort_order 5 and 6, ahead of PPE.
        $this->call(ChinHerrWireSolutionsSeeder::class);

        $ppe = SolutionCategory::create([
            'title' => 'Personal Protection Equipment',
            'slug' => 'personal-protection-equipment',
            'description' => 'PPE ranges supplied to keep crews safe across mining, drilling, and construction sites — including Wayne Gumboots.',
            'image' => 'seed/personal-1.png',
            'sort_order' => 7,
        ]);

        $ppeItems = [
            ['title' => 'Wayne Gumboots', 'description' => 'Durable, chemical-resistant safety gumboots built for demanding site conditions.', 'image' => 'seed/personal-1.png'],
            ['title' => 'Protective Workwear', 'description' => 'High-visibility, durable workwear designed for mining and construction environments.', 'image' => 'seed/personal-2.png'],
            ['title' => 'Head & Hand Protection', 'description' => 'Certified helmets, gloves, and related PPE for on-site safety compliance.', 'image' => 'seed/personal-3.png'],
        ];
        $this->createSolutionItems($ppe, $ppeItems);
    }

    private function createSolutionItems(SolutionCategory $category, array $items): void
    {
        foreach ($items as $index => $item) {
            SolutionItem::create([
                'solution_category_id' => $category->id,
                'title' => $item['title'],
                'description' => $item['description'],
                'image' => $item['image'],
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function seedJobs(): void
    {
        $jobs = [
            [
                'title' => 'Field Engineer - Mining Operations',
                'department' => 'Mining Operations',
                'location' => 'Western Australia',
                'employment_type' => 'full_time',
                'summary' => 'Join our dynamic team and support mining operations with technical insight and hands-on problem-solving.',
                'description' => 'As a Field Engineer, you will work directly with site teams to troubleshoot equipment, optimize drilling and production consumable performance, and provide technical support across active mining operations.',
                'requirements' => "Degree in Mining, Geological, or Mechanical Engineering\nMinimum 3 years' field experience in mining or drilling operations\nWillingness to travel to remote site locations",
            ],
            [
                'title' => 'HSE Officer - Oil & Gas',
                'department' => 'Health, Safety & Environment',
                'location' => 'Qatar',
                'employment_type' => 'contract',
                'summary' => 'Lead our on-site safety protocols, conduct training, and ensure compliance with global standards.',
                'description' => 'The HSE Officer is responsible for implementing and monitoring safety systems across oil & gas project sites, running safety inductions and training, and maintaining compliance with international HSE standards.',
                'requirements' => "NEBOSH certification or equivalent\nMinimum 5 years' HSE experience in oil & gas or heavy industry\nStrong incident investigation and reporting skills",
            ],
            [
                'title' => 'Business Development Manager',
                'department' => 'Business Development',
                'location' => 'Dubai / Remote',
                'employment_type' => 'full_time',
                'summary' => 'Drive new partnerships, manage key accounts, and identify growth opportunities across emerging markets.',
                'description' => 'This role owns the growth pipeline across the Middle East and emerging markets — identifying new partnerships, managing key accounts, and representing Xponent Global at industry events.',
                'requirements' => "Proven track record in B2B business development, ideally in mining/drilling/construction supply\nExisting network across Middle East or Asia-Pacific markets preferred\nWillingness to travel regularly",
            ],
        ];

        foreach ($jobs as $job) {
            JobOpening::create($job + [
                'slug' => Str::slug($job['title']),
                'status' => 'open',
                'posted_at' => now()->subDays(rand(1, 20)),
            ]);
        }
    }

    private function seedResources(): void
    {
        $resources = [
            ['category' => 'technical_document', 'title' => 'Company Profile & Capability Statement', 'description' => 'An overview of Xponent Global\'s history, capabilities, and service offering across mining, drilling, oil & gas, construction, and energy.'],
            ['category' => 'technical_document', 'title' => 'Global Quality Standards Overview', 'description' => 'Summary of the AS/NZS structural and safety standards applied across our mining camp facilities and modular buildings.'],
            ['category' => 'datasheet', 'title' => 'High-Pressure Drill Pipe Datasheet', 'description' => 'Technical specifications and performance data for our high-pressure drill pipe range.'],
            ['category' => 'datasheet', 'title' => 'Modular Rig Components Datasheet', 'description' => 'Specifications for modular rig components used across exploration and production drilling.'],
            ['category' => 'safety_compliance', 'title' => 'Health, Safety & Environment (HSE) Protocols', 'description' => 'Our HSE protocols and procedures applied across all project sites.'],
            ['category' => 'safety_compliance', 'title' => 'ISO Certifications Overview', 'description' => 'A summary of Xponent Global\'s current ISO certifications.'],
        ];

        foreach ($resources as $resource) {
            $path = 'seed/resources/'.Str::slug($resource['title']).'.txt';
            Storage::disk('public')->put(
                $path,
                $resource['title']."\n\n".$resource['description']."\n\nThis is placeholder content — replace it with the real document from the admin panel."
            );

            ResourceModel::create($resource + [
                'file' => $path,
                'published' => true,
            ]);
        }
    }

    private function seedPosts(): void
    {
        $posts = [
            [
                'type' => 'case_study',
                'title' => 'Gold Exploration in West Africa',
                'excerpt' => 'How our innovative drilling techniques accelerated exploration timelines by 30%.',
                'body' => "Xponent Global partnered with a mid-tier gold exploration company operating across multiple West African sites to overhaul its drilling consumables strategy.\n\nBy pairing our coring systems with locally supported drilling fluids and additives, the client reduced non-productive time on site and accelerated overall exploration timelines by approximately 30%, while maintaining strict core recovery quality standards.",
                'cover_image' => 'seed/gallery-img-09.jpg',
            ],
            [
                'type' => 'case_study',
                'title' => 'Sustainable Construction in the Middle East',
                'excerpt' => 'Leveraging smart construction materials to cut energy costs by 40%.',
                'body' => "Working alongside a regional construction contractor, Xponent Global supplied modular CIMC accommodation units specified for extreme desert conditions.\n\nImproved insulation and inverter-based climate control across the modular units contributed to an estimated 40% reduction in on-site energy costs compared to legacy demountable accommodation.",
                'cover_image' => 'seed/gallery-img-10.jpg',
            ],
            [
                'type' => 'news',
                'title' => 'The Future of Deep-Well Drilling: Trends to Watch in 2026',
                'excerpt' => 'Deeper reserves, tighter margins, and new tooling are reshaping deep-well drilling programs worldwide.',
                'body' => "As accessible shallow reserves diminish, operators are increasingly turning to deep-well drilling programs that demand more resilient tooling, smarter fluids management, and tighter operational planning.\n\nXponent Global continues to track these trends closely, working with our manufacturing partners to bring more durable bits, casings, and coring systems to market ahead of demand.",
                'cover_image' => 'seed/gallery-img-11.jpg',
            ],
            [
                'type' => 'news',
                'title' => 'Why Energy Transition Matters in Global Construction',
                'excerpt' => 'Construction and energy firms are rethinking materials and equipment sourcing as the energy transition accelerates.',
                'body' => "The global shift toward lower-carbon energy sources is changing how construction and energy projects are specified, built, and equipped.\n\nFrom more efficient modular accommodation to smarter procurement of consumables, Xponent Global is helping clients balance cost, reliability, and sustainability across their project portfolios.",
                'cover_image' => 'seed/gallery-img-12.jpg',
            ],
            [
                'type' => 'news',
                'title' => 'New Tech in Exploration: AI-Driven Surveys',
                'excerpt' => 'AI-assisted core orientation and survey analysis is speeding up exploration decision-making.',
                'body' => "New generations of downhole survey cameras and orientation tools are increasingly paired with AI-assisted analysis, helping geologists interpret structural data faster and with greater confidence.\n\nXponent Global is evaluating these technologies alongside our existing core orientation and survey camera solutions to bring faster turnaround times to exploration clients.",
                'cover_image' => 'seed/gallery-img-13.jpg',
            ],
        ];

        foreach ($posts as $index => $post) {
            Post::create($post + [
                'slug' => Str::slug($post['title']),
                'published' => true,
                'published_at' => now()->subDays($index * 4),
            ]);
        }
    }

    private function seedNewsletterSubscribers(): void
    {
        foreach (['sam.reyes@example.com', 'j.mitchell@example.com', 'priya.nair@example.com'] as $email) {
            NewsletterSubscriber::create(['email' => $email, 'status' => 'subscribed']);
        }
    }

    private function seedContactEnquiries(): void
    {
        ContactEnquiry::create([
            'enquiry_type' => 'Drilling Consumables',
            'region' => 'Australia and Pacific',
            'country' => 'Australia',
            'name' => 'Daniel Foster',
            'email' => 'daniel.foster@example.com',
            'company' => 'Foster Mining Co.',
            'phone' => '+61 400 123 456',
            'message' => 'Could you send pricing for a bulk order of drill rods and casings for our Queensland site?',
            'status' => 'new',
        ]);

        ContactEnquiry::create([
            'enquiry_type' => 'Personal Protection Equipment (PPE) Wayne Gumboots',
            'region' => 'Asia',
            'country' => 'Philippines',
            'name' => 'Maria Santos',
            'email' => 'maria.santos@example.com',
            'company' => 'Santos Exploration Corp.',
            'phone' => '+63 917 123 4567',
            'message' => 'We are looking for a regular PPE supplier for our exploration crews. Please share your catalogue and MOQs.',
            'status' => 'contacted',
        ]);
    }

    private function seedJobApplications(): void
    {
        $fieldEngineer = JobOpening::where('slug', 'field-engineer-mining-operations')->first();

        if (! $fieldEngineer) {
            return;
        }

        $resumePath = 'seed/resumes/sample-resume.txt';
        Storage::disk('public')->put($resumePath, "Sample resume placeholder.\n\nReplace with a real applicant resume upload.");

        JobApplication::create([
            'job_opening_id' => $fieldEngineer->id,
            'name' => 'Andrew Wilson',
            'email' => 'andrew.wilson@example.com',
            'phone' => '+61 400 987 654',
            'cover_letter' => 'I have five years of field engineering experience across WA mine sites and would love to bring that expertise to Xponent Global.',
            'resume' => $resumePath,
            'status' => 'new',
        ]);
    }
}
