<?php
/**
 * VoltCore Inventory — filterable card grid.
 *
 * Renders cards from either:
 *   - the vc_product CPT, or
 *   - an inline JSON list (when "manual" source is used).
 *
 * Exposes client-side filter chips (trim/model, exterior color), a price
 * range slider and a sort dropdown. Filters work purely on the DOM — no
 * AJAX — so the widget stays snappy and editable in Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Inventory extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-inventory'; }
	public function get_title() { return __( 'VoltCore Inventory', 'voltcore' ); }
	public function get_icon() { return 'eicon-filter'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'inventory', 'filter', 'products', 'search' ); }

	protected function register_controls() {

		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Available now' ) );
		$this->add_control( 'source', array(
			'label'   => 'Source', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'products',
			'options' => array( 'products' => 'vc_product CPT', 'manual' => 'Manual list' ),
		) );
		$this->add_control( 'per_page', array( 'label' => 'Max items', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 12 ) );
		$this->add_control( 'currency', array( 'label' => 'Currency', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '$' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'manual', array( 'label' => __( 'Manual items', 'voltcore' ), 'condition' => array( 'source' => 'manual' ) ) );
		$rep = new \Elementor\Repeater();
		$rep->add_control( 'image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$rep->add_control( 'title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model V Long Range' ) );
		$rep->add_control( 'trim',  array( 'label' => 'Trim', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Long Range' ) );
		$rep->add_control( 'color', array( 'label' => 'Color', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Pearl White' ) );
		$rep->add_control( 'price', array( 'label' => 'Price', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 42990 ) );
		$rep->add_control( 'range', array( 'label' => 'Range (mi)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 340 ) );
		$rep->add_control( 'location', array( 'label' => 'Location', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Reno, NV' ) );
		$rep->add_control( 'url',   array( 'label' => 'URL',   'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'items', array(
			'label' => 'Items', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'title' => 'Model V Long Range', 'trim' => 'Long Range',  'color' => 'Pearl White',      'price' => 42990, 'range' => 340, 'location' => 'Reno, NV' ),
				array( 'title' => 'Model V Performance','trim' => 'Performance', 'color' => 'Solid Black',      'price' => 53990, 'range' => 310, 'location' => 'Austin, TX' ),
				array( 'title' => 'Model V Long Range', 'trim' => 'Long Range',  'color' => 'Midnight Silver',  'price' => 44490, 'range' => 340, 'location' => 'Berlin, DE' ),
				array( 'title' => 'Model V Standard',   'trim' => 'Standard',    'color' => 'Deep Blue',        'price' => 39990, 'range' => 270, 'location' => 'Reno, NV' ),
			),
		) );
		$this->end_controls_section();
	}

	private function get_items( $s ) {
		$out = array();
		$source = $s['source'] ?? 'products';
		$c = $s['currency'] ?: '$';

		if ( $source === 'manual' ) {
			foreach ( (array) ( $s['items'] ?? array() ) as $it ) {
				$out[] = array(
					'img'   => $it['image']['url'] ?? '',
					'title' => $it['title'] ?? '',
					'trim'  => $it['trim']  ?? '',
					'color' => $it['color'] ?? '',
					'price' => (int) ( $it['price'] ?? 0 ),
					'range' => (int) ( $it['range'] ?? 0 ),
					'loc'   => $it['location'] ?? '',
					'url'   => $it['url']['url'] ?? '#',
				);
			}
		} else {
			$q = new \WP_Query( array(
				'post_type'      => 'vc_product',
				'posts_per_page' => (int) ( $s['per_page'] ?? 12 ),
				'post_status'    => 'publish',
			) );
			foreach ( $q->posts as $p ) {
				$img   = get_the_post_thumbnail_url( $p, 'voltcore-card' ) ?: '';
				$sub   = get_post_meta( $p->ID, '_vc_product_subtitle', true );
				$price = (int) preg_replace( '/\D/', '', (string) get_post_meta( $p->ID, '_vc_product_price', true ) );
				$specs = (string) get_post_meta( $p->ID, '_vc_product_specs', true );
				$range = 0;
				if ( preg_match( '/Range\s*\|\s*(\d+)/i', $specs, $m ) ) $range = (int) $m[1];
				$terms = get_the_terms( $p, 'vc_product_cat' );
				$cat   = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : '';
				$out[] = array(
					'img' => $img, 'title' => get_the_title( $p ), 'trim' => $cat, 'color' => $sub,
					'price' => $price, 'range' => $range, 'loc' => '', 'url' => get_permalink( $p ),
				);
			}
			wp_reset_postdata();
		}
		return $out;
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$items = $this->get_items( $s );
		if ( empty( $items ) ) {
			echo '<p class="vc-inv__empty">' . esc_html__( 'No inventory to display.', 'voltcore' ) . '</p>';
			return;
		}
		$c = $s['currency'] ?: '$';
		$trims  = array_values( array_unique( array_filter( array_column( $items, 'trim' ) ) ) );
		$colors = array_values( array_unique( array_filter( array_column( $items, 'color' ) ) ) );
		$max_price = max( array_column( $items, 'price' ) );
		$min_price = min( array_column( $items, 'price' ) );
		?>
		<section class="vc-inv" data-vc-inventory data-currency="<?php echo esc_attr( $c ); ?>">
			<?php if ( ! empty( $s['heading'] ) ) : ?>
				<header class="vc-inv__head">
					<h2><?php echo esc_html( $s['heading'] ); ?></h2>
					<p class="vc-inv__count"><span data-vc-inv-count><?php echo count( $items ); ?></span> <?php esc_html_e( 'matches', 'voltcore' ); ?></p>
				</header>
			<?php endif; ?>

			<div class="vc-inv__filters">
				<?php if ( $trims ) : ?>
					<div class="vc-inv__filter">
						<span class="vc-inv__filter-label"><?php esc_html_e( 'Trim', 'voltcore' ); ?></span>
						<div class="vc-inv__chips">
							<button type="button" class="vc-inv__chip is-active" data-vc-inv-chip="trim" data-value=""><?php esc_html_e( 'All', 'voltcore' ); ?></button>
							<?php foreach ( $trims as $t ) : ?>
								<button type="button" class="vc-inv__chip" data-vc-inv-chip="trim" data-value="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $t ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<?php if ( $colors ) : ?>
					<div class="vc-inv__filter">
						<span class="vc-inv__filter-label"><?php esc_html_e( 'Color', 'voltcore' ); ?></span>
						<div class="vc-inv__chips">
							<button type="button" class="vc-inv__chip is-active" data-vc-inv-chip="color" data-value=""><?php esc_html_e( 'All', 'voltcore' ); ?></button>
							<?php foreach ( $colors as $co ) : ?>
								<button type="button" class="vc-inv__chip" data-vc-inv-chip="color" data-value="<?php echo esc_attr( $co ); ?>"><?php echo esc_html( $co ); ?></button>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="vc-inv__filter vc-inv__filter--price">
					<span class="vc-inv__filter-label"><?php esc_html_e( 'Max price', 'voltcore' ); ?>: <output data-vc-inv-price-out><?php echo esc_html( $c . number_format_i18n( $max_price ) ); ?></output></span>
					<input type="range" min="<?php echo (int) $min_price; ?>" max="<?php echo (int) $max_price; ?>" step="500" value="<?php echo (int) $max_price; ?>" data-vc-inv-price>
				</div>

				<div class="vc-inv__filter">
					<span class="vc-inv__filter-label"><?php esc_html_e( 'Sort', 'voltcore' ); ?></span>
					<select class="vc-inv__sort" data-vc-inv-sort>
						<option value="price-asc"><?php esc_html_e( 'Price: Low to high', 'voltcore' ); ?></option>
						<option value="price-desc"><?php esc_html_e( 'Price: High to low', 'voltcore' ); ?></option>
						<option value="range-desc"><?php esc_html_e( 'Longest range', 'voltcore' ); ?></option>
					</select>
				</div>
			</div>

			<div class="vc-inv__grid" data-vc-inv-grid>
				<?php foreach ( $items as $it ) : ?>
					<a class="vc-inv__card"
					   href="<?php echo esc_url( $it['url'] ); ?>"
					   data-trim="<?php echo esc_attr( $it['trim'] ); ?>"
					   data-color="<?php echo esc_attr( $it['color'] ); ?>"
					   data-price="<?php echo (int) $it['price']; ?>"
					   data-range="<?php echo (int) $it['range']; ?>">
						<?php if ( $it['img'] ) : ?><img src="<?php echo esc_url( $it['img'] ); ?>" alt="<?php echo esc_attr( $it['title'] ); ?>" loading="lazy"><?php else : ?><span class="vc-inv__card-ph"></span><?php endif; ?>
						<div class="vc-inv__body">
							<strong><?php echo esc_html( $it['title'] ); ?></strong>
							<span class="vc-inv__meta"><?php echo esc_html( $it['trim'] ); ?><?php if ( $it['color'] ) echo ' · ' . esc_html( $it['color'] ); ?></span>
							<?php if ( $it['range'] ) : ?><span class="vc-inv__meta"><?php echo (int) $it['range']; ?> mi <?php esc_html_e( 'range', 'voltcore' ); ?></span><?php endif; ?>
							<?php if ( $it['loc'] ) : ?><span class="vc-inv__meta"><?php echo esc_html( $it['loc'] ); ?></span><?php endif; ?>
							<?php if ( $it['price'] ) : ?><span class="vc-inv__price"><?php echo esc_html( $c . number_format_i18n( $it['price'] ) ); ?></span><?php endif; ?>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
			<p class="vc-inv__none" hidden data-vc-inv-none><?php esc_html_e( 'No vehicles match your filters.', 'voltcore' ); ?></p>
		</section>
		<?php
	}
}
