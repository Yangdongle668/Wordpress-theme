<?php
/**
 * Sample blog posts seeded on first activation.
 *
 * Kept separate from install.php so the seed content is easy to read and
 * swap without touching the installer logic.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function voltcore_sample_posts() {
	$today = current_time( 'Y-m-d' );
	return array(
		array(
			'slug'       => 'why-the-4680-tabless-cell-matters',
			'title'      => 'Why the 4680 tabless cell matters',
			'excerpt'    => 'The 4680 is more than a bigger cylinder. The tabless current collector changes how energy flows through the cell — and rewrites the economics of a battery pack.',
			'categories' => array( 'technology', 'engineering' ),
			'tags'       => array( '4680', 'cell', 'manufacturing', 'energy density' ),
			'image'      => 'product-1.jpg',
			'image_alt'  => 'VoltCore 4680 cylindrical battery cell on a studio surface',
			'date'       => gmdate( 'Y-m-d H:i:s', strtotime( $today . ' -2 days' ) ),
			'content'    => <<<HTML
<!-- wp:paragraph --><p>For ten years, the EV industry got better at batteries by building more of the same cell. The 4680 is the first cylindrical cell architecture designed around a different constraint: not energy per cell, but energy per pack and energy per hour of factory time.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>What "tabless" actually means</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>In a conventional cylindrical cell, current has to travel from every point of the electrode to a small metal tab welded near one end. That tab is a bottleneck. It heats up under load, it limits fast-charge rate, and it forces engineers to derate the cell so the tab doesn't become the failure point.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>The 4680 replaces the tab with the electrode edge itself. The entire jelly roll is the current collector. Resistance drops. Heat drops. And suddenly a 15-minute fast charge stops being a thermal-management problem and starts being a supply-chain problem.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Why it changes pack design</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>A single 4680 holds roughly 5× the energy of a 2170. That means fewer cells per pack, fewer welds, fewer wire bonds, fewer points of failure. Our current generation pack uses 70% fewer interconnects than the one it replaced.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Fewer interconnects means the cell can become structural — the pack housing is no longer just a box holding cells, it's part of the vehicle frame. That's where the real range gains come from. It isn't the chemistry; it's the architecture.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>What's still hard</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Dry-coating the electrode at scale. That's the honest answer. Everybody claims a 4680 roadmap; the companies that will actually ship meaningful volume are the ones with dry electrode lines running at yield today. We'll publish our yield numbers when we cross 85%.</p><!-- /wp:paragraph -->
HTML,
		),

		array(
			'slug'       => 'engineering-a-15-minute-fast-charge',
			'title'      => 'Engineering a 15-minute fast charge',
			'excerpt'    => 'Going from 10% to 80% in fifteen minutes is not a charger problem. It is a thermal problem, a chemistry problem, and a business-model problem — all at once.',
			'categories' => array( 'engineering', 'technology' ),
			'tags'       => array( 'charging', 'thermal', 'DC fast charge' ),
			'image'      => 'story-1.jpg',
			'image_alt'  => 'Battery engineering test rig with blue accent lighting',
			'date'       => gmdate( 'Y-m-d H:i:s', strtotime( $today . ' -6 days' ) ),
			'content'    => <<<HTML
<!-- wp:paragraph --><p>Ask a driver what they want from an EV and the answer is almost always the same: charge it as fast as I fill a tank. Ask an engineer and the answer is: please pick two of {speed, lifetime, safety}.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>A 350 kW charge into a 75 kWh pack is not just "more current." It's roughly the thermal load of five household ovens, dumped into a metal box the size of a suitcase, for a quarter of an hour.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Where the heat goes</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Three places. The electrode interface (where lithium ions move across the separator), the internal busbars (where current leaves the cell), and the pack-level connectors and cooling plates (where the system as a whole sheds energy).</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Our 15-minute target required wins on all three. Tabless cells took care of the internal busbar loss. Silicon-blended anodes shifted the electrode interface. And a redesigned cooling plate with direct-to-cell microchannels took pack-level delta-T from 11°C to 3°C.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>The software half</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Half the problem is chemistry. The other half is the charge curve. A naive charger holds 350 kW as long as the cells will take it. A good charger listens: it reads cell impedance every 200 ms and ramps down when it sees the knee of the polarization curve, not when the cell is already overheating.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Our BMS firmware treats fast charge as a closed-loop control problem, not a look-up table. That's what gets you a fifteen-minute session that doesn't cost you cycle life.</p><!-- /wp:paragraph -->
HTML,
		),

		array(
			'slug'       => 'inside-the-gigafactory',
			'title'      => 'Inside the gigafactory: a day on line 3',
			'excerpt'    => 'What a modern cell factory actually looks like — and why the real engineering happens in the 10 centimetres between the coating roll and the drying oven.',
			'categories' => array( 'engineering', 'product-news' ),
			'tags'       => array( 'manufacturing', 'gigafactory', 'dry electrode' ),
			'image'      => 'story-2.jpg',
			'image_alt'  => 'Battery manufacturing line with warm industrial lighting',
			'date'       => gmdate( 'Y-m-d H:i:s', strtotime( $today . ' -10 days' ) ),
			'content'    => <<<HTML
<!-- wp:paragraph --><p>A gigafactory is not a big building full of robots. It's a kilometre-long slurry-mixing, coating, drying, calendering, slitting, winding, welding, aging, and sorting pipeline — and every meter of it is an opportunity to ruin a week of production.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>The four machines that matter</h2><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>The coater.</strong> It lays active material onto copper or aluminium foil at the thickness of a human hair, ±2 microns, for eight kilometers without stopping.</li><li><strong>The calender.</strong> A pair of polished rollers press the coated foil to the exact density the cell design wants. A micron off, and the cell's resistance climbs.</li><li><strong>The winder.</strong> 4680s get jelly-rolled at 100+ units per minute. Any tension variance shows up as a failed cell three weeks later.</li><li><strong>The formation cycler.</strong> Every cell is charged, discharged, and characterised before it leaves the building. This step is quietly half the factory's cap-ex.</li></ul><!-- /wp:list -->
<!-- wp:heading --><h2>Why dry electrode is the bet</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Traditional wet coating uses NMP solvent, requires a 100-meter drying oven, and eats a surprising fraction of a factory's energy budget. Dry-coated electrodes drop the oven entirely. The factory gets shorter. Cap-ex drops. Energy per kWh of capacity drops. When it works.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>"When it works" is doing a lot of work in that sentence. Dry coating at yield is one of the two or three remaining hard problems in cell manufacturing. Our Reno line has been running dry at commercial yields since Q2. The next line is being built around it from day one.</p><!-- /wp:paragraph -->
HTML,
		),

		array(
			'slug'       => 'sodium-ion-what-is-it-good-for',
			'title'      => 'Sodium-ion: what is it actually good for?',
			'excerpt'    => 'Sodium-ion cells are not a lithium replacement. They are a parallel chemistry with its own niche — and that niche is bigger than it looks.',
			'categories' => array( 'technology', 'sustainability' ),
			'tags'       => array( 'sodium-ion', 'chemistry', 'grid storage' ),
			'image'      => 'product-2.jpg',
			'image_alt'  => 'Array of VoltCore battery modules with orange and red terminals',
			'date'       => gmdate( 'Y-m-d H:i:s', strtotime( $today . ' -14 days' ) ),
			'content'    => <<<HTML
<!-- wp:paragraph --><p>Sodium is everywhere. Lithium is not. That sentence is why every big cell maker has a sodium-ion program — and it's also why most of those programs get misinterpreted as "lithium is over."</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>The honest trade-off</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Sodium-ion cells hit roughly 160 Wh/kg today. A good lithium iron phosphate cell hits 180. A high-nickel NMC cell hits 260+. So as a direct replacement in an EV, sodium is a step backward on energy density.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>But sodium makes up the gap in other places. The cathode doesn't need cobalt or nickel. The anode doesn't need graphite. The current collector on the anode side can be aluminium instead of copper — cheaper, lighter, and not supply-constrained. Total bill-of-materials cost can be 25–30% below LFP at scale.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Where it wins</h2><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>Grid storage.</strong> Energy density per kilogram doesn't matter. Dollars per kWh and cycle life do. Sodium-ion is already competitive.</li><li><strong>Small urban EVs.</strong> A 200 km city car doesn't need 260 Wh/kg. It needs a cheap, safe, cold-weather-tolerant battery.</li><li><strong>Backup power.</strong> Stationary UPS, telecom sites, cold storage.</li></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>We're not betting the company on sodium-ion. We're betting one factory on it — because the cost curve and the supply curve point the same direction, and because the grid-storage market is measured in terawatt-hours, not gigawatt-hours.</p><!-- /wp:paragraph -->
HTML,
		),

		array(
			'slug'       => 'battery-safety-thermal-runaway',
			'title'      => 'Battery safety: stopping thermal runaway at the cell',
			'excerpt'    => 'Pack-level fire suppression is a last resort. The real safety work happens inside the cell, long before the first spark.',
			'categories' => array( 'engineering', 'sustainability' ),
			'tags'       => array( 'safety', 'thermal runaway', 'BMS' ),
			'image'      => 'about.jpg',
			'image_alt'  => 'Engineering test chamber with blue indicator lights',
			'date'       => gmdate( 'Y-m-d H:i:s', strtotime( $today . ' -20 days' ) ),
			'content'    => <<<HTML
<!-- wp:paragraph --><p>Thermal runaway is what happens when a cell's internal temperature climbs past the point where the cathode starts releasing oxygen, which feeds the burning electrolyte, which heats the cell further, which releases more oxygen. It's a self-sustaining reaction. Once it starts, suppressing it is hard.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>The goal, then, is never to get there.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>The three lines of defence</h2><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>Chemistry.</strong> Cathode selection (LFP is intrinsically more stable than NMC), electrolyte additives that break down safely, and separators that shut down ionic flow above a threshold temperature.</li><li><strong>Cell-to-cell isolation.</strong> If one cell does go into runaway, fire-retardant materials and engineered vent paths keep neighbours under threshold. A good pack can tolerate a single cell failure without cascade.</li><li><strong>The BMS.</strong> Every cell is monitored every 100 ms. Temperature, voltage, internal resistance — any two of these drifting together is an early signature.</li></ul><!-- /wp:list -->
<!-- wp:heading --><h2>The honest standard</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>A pack should be safer than the vehicle around it. That means nail-penetration tests, overcharge tests, external-short tests, and crush tests — all at full state of charge, all with thermocouples embedded in every cell. We publish our results. You should expect that from every serious manufacturer.</p><!-- /wp:paragraph -->
HTML,
		),

		array(
			'slug'       => 'grid-scale-storage-utility-math',
			'title'      => 'Grid-scale storage and the new utility math',
			'excerpt'    => 'When a battery gets cheap enough to arbitrage the wholesale price twice a day, every assumption a utility ever made about peaking plants stops being true.',
			'categories' => array( 'sustainability', 'product-news' ),
			'tags'       => array( 'grid storage', 'utilities', 'megapack' ),
			'image'      => 'product-3.jpg',
			'image_alt'  => 'VoltCore Grid Node utility-scale battery container',
			'date'       => gmdate( 'Y-m-d H:i:s', strtotime( $today . ' -28 days' ) ),
			'content'    => <<<HTML
<!-- wp:paragraph --><p>For most of the 20th century, the grid operator's toughest problem was peak demand — the two hours on a hot summer afternoon when everyone's air conditioner is on at once. Peaking plants were built to run for those two hours and sit idle for the other 8,758. Expensive insurance.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>A 4-hour battery at current prices solves the same problem for less money, with faster ramp, without emissions, and — crucially — without a site you have to site. Stacks of containers near existing substations, commissioned in months.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>Why the economics flipped</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Two curves crossed in 2023. The levelised cost of lithium iron phosphate storage dropped below the levelised cost of a new gas peaker for first-energy delivery of roughly two hours. Below four hours in many markets. The math now favours the battery on both capital and operating cost.</p><!-- /wp:paragraph -->
<!-- wp:heading --><h2>What that means for us</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Our Grid Node platform is built around this shift. 3.9 MWh per container. Grid-forming inverter. Black-start capable. Plug-and-play integration with major utility SCADA systems. Average install time for a 100 MWh site is 14 weeks from contract signature.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>The interesting thing isn't that batteries can replace peakers. It's that batteries plus renewables are starting to replace baseload too — and that rewrites the operating manual of every utility on the planet.</p><!-- /wp:paragraph -->
HTML,
		),
	);
}
