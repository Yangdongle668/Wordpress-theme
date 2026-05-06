<?php
/**
 * VoltCore Inventory — client-side searchable inventory grid.
 *
 * Editor configures filter options (model, trim, color, max-price, ZIP)
 * and a list of vehicle cards. JS filters cards live as the user
 * adjusts inputs. Counter and empty state are handled automatically.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Inventory extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-inventory'; }
	public function get_title() { return __( 'VoltCore Inventory', 'voltcore' ); }
	public function get_icon() { return 'eicon-search-results'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'inventory', 'search', 'filter' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_filters', array( 'label' => __( 'Filters', 'voltcore' ) ) );

		$this->add_control( 'models', array(
			'label'   => __( 'Model options (one per line)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Any\nModel S\nModel 3\nModel X\nModel Y\nCybertruck",
		) );
		$this->add_control( 'trims', array(
			'label'   => __( 'Trim options (one per line)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Any\nLong Range\nPerformance\nPlaid\nStandard Range",
		) );
		$this->add_control( 'colors', array(
			'label'   => __( 'Color options (one per line)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Any\nWhite\nBlack\nRed\nBlue\nGrey",
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_cards', array( 'label' => __( 'Vehicle cards', 'voltcore' ) ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'image', array( 'label' => __( 'Image', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => VOLTCORE_URI . '/assets/images/product-1.jpg' ) ) );
		$rep->add_control( 'model', array( 'label' => __( 'Model', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model S' ) );
		$rep->add_control( 'trim',  array( 'label' => __( 'Trim', 'voltcore' ),  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Long Range' ) );
		$rep->add_control( 'color', array( 'label' => __( 'Color', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'White' ) );
		$rep->add_control( 'price', array( 'label' => __( 'Price (USD)', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 79990 ) );
		$rep->add_control( 'range', array( 'label' => __( 'Range (mi)', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '405 mi' ) );
		$rep->add_control( 'zip',   array( 'label' => __( 'Delivery ZIP', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '94025' ) );
		$rep->add_control( 'url',   array( 'label' => __( 'Detail URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Cards', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ model }}} — {{{ trim }}}',
			'default'     => array(
				array( 'model' => 'Model S', 'trim' => 'Long Range', 'color' => 'White', 'price' => 79990, 'range' => '405 mi', 'zip' => '94025' ),
				array( 'model' => 'Model S', 'trim' => 'Plaid',      'color' => 'Black', 'price' => 89990, 'range' => '396 mi', 'zip' => '90001' ),
				array( 'model' => 'Model 3', 'trim' => 'Performance', 'color' => 'Red',   'price' => 52990, 'range' => '296 mi', 'zip' => '94025' ),
				array( 'model' => 'Model 3', 'trim' => 'Long Range', 'color' => 'Blue',  'price' => 47990, 'range' => '358 mi', 'zip' => '94025' ),
				array( 'model' => 'Model X', 'trim' => 'Long Range', 'color' => 'Grey',  'price' => 89990, 'range' => '348 mi', 'zip' => '85001' ),
				array( 'model' => 'Model Y', 'trim' => 'Performance', 'color' => 'White', 'price' => 53990, 'range' => '303 mi', 'zip' => '94025' ),
			),
		) );

		$this->end_controls_section();
	}

	private function lines( $raw, $first_blank_label = 'any' ) {
		$out = array();
		foreach ( preg_split( '/\r?\n/', (string) $raw ) as $i => $ln ) {
			$ln = trim( $ln );
			if ( $ln === '' ) continue;
			$value = $i === 0 ? $first_blank_label : strtolower( $ln );
			$out[ $value ] = $ln;
		}
		return $out;
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$models = $this->lines( $s['models'] );
		$trims  = $this->lines( $s['trims'] );
		$colors = $this->lines( $s['colors'] );
		$cards  = is_array( $s['cards'] ?? null ) ? $s['cards'] : array();
		?>
		<section class="vc-inv" data-vc-inv>
			<div class="vc-inv__filters">
				<div><label><?php esc_html_e( 'Model', 'voltcore' ); ?></label>
				<select data-inv-filter="model">
					<?php foreach ( $models as $val => $lab ) : ?><option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $lab ); ?></option><?php endforeach; ?>
				</select></div>
				<div><label><?php esc_html_e( 'Trim', 'voltcore' ); ?></label>
				<select data-inv-filter="trim">
					<?php foreach ( $trims as $val => $lab ) : ?><option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $lab ); ?></option><?php endforeach; ?>
				</select></div>
				<div><label><?php esc_html_e( 'Color', 'voltcore' ); ?></label>
				<select data-inv-filter="color">
					<?php foreach ( $colors as $val => $lab ) : ?><option value="<?php echo esc_attr( $val ); ?>"><?php echo esc_html( $lab ); ?></option><?php endforeach; ?>
				</select></div>
				<div><label><?php esc_html_e( 'Max price', 'voltcore' ); ?></label>
				<input type="number" placeholder="$100,000" data-inv-filter="max-price"></div>
				<div><label><?php esc_html_e( 'ZIP', 'voltcore' ); ?></label>
				<input type="text" inputmode="numeric" maxlength="5" placeholder="94025" data-inv-filter="zip"></div>
			</div>
			<p class="vc-inv__count" data-inv-count></p>
			<div class="vc-inv__grid">
			<?php foreach ( $cards as $c ) : ?>
				<a class="vc-inv__card" href="<?php echo esc_url( $c['url']['url'] ?? '#' ); ?>"
					data-model="<?php echo esc_attr( strtolower( $c['model'] ) ); ?>"
					data-trim="<?php echo esc_attr( strtolower( $c['trim'] ) ); ?>"
					data-color="<?php echo esc_attr( strtolower( $c['color'] ) ); ?>"
					data-price="<?php echo esc_attr( intval( $c['price'] ) ); ?>"
					data-zip="<?php echo esc_attr( $c['zip'] ); ?>">
					<div class="vc-inv__card-img"><?php if ( ! empty( $c['image']['url'] ) ) : ?><img src="<?php echo esc_url( $c['image']['url'] ); ?>" alt="<?php echo esc_attr( $c['model'] ); ?>"><?php endif; ?></div>
					<div class="vc-inv__card-body">
						<h3 class="vc-inv__card-title"><?php echo esc_html( $c['model'] . ' ' . $c['trim'] ); ?></h3>
						<p class="vc-inv__card-meta"><span><?php echo esc_html( $c['color'] ); ?></span><span><?php echo esc_html( $c['range'] ); ?></span><span>ZIP <?php echo esc_html( $c['zip'] ); ?></span></p>
						<p class="vc-inv__card-price">$<?php echo number_format( intval( $c['price'] ) ); ?></p>
					</div>
				</a>
			<?php endforeach; ?>
			</div>
			<p class="vc-inv__empty" data-inv-empty style="display:none;"><?php esc_html_e( 'No vehicles match your filters. Try widening your criteria.', 'voltcore' ); ?></p>
		</section>
		<?php
	}
}
