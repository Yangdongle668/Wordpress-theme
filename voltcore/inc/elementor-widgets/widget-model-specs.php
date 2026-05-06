<?php
/**
 * VoltCore Model Specs — tabbed specifications matrix.
 *
 * Each tab (e.g. Performance, Long Range, Standard Range) reveals a
 * grid of value/label cells. Editors configure tabs as repeater rows
 * and drop key|value pairs in a textarea per tab — no more rigid
 * 4-column controls.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Model_Specs extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-model-specs'; }
	public function get_title() { return __( 'VoltCore Model Specs', 'voltcore' ); }
	public function get_icon() { return 'eicon-table'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'specs', 'tabs', 'comparison' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_specs', array( 'label' => __( 'Tabs & specs', 'voltcore' ) ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'label', array( 'label' => __( 'Tab label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Performance' ) );
		$rep->add_control( 'cells', array(
			'label'   => __( 'Cells (Value|Label per line)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "1.99 s|0–60 MPH\n200 mph|TOP SPEED\n396 mi|RANGE (EPA est.)\n1,020 hp|PEAK POWER",
		) );

		$this->add_control( 'tabs', array(
			'label'       => __( 'Tabs', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ label }}}',
			'default'     => array(
				array( 'label' => 'Plaid' ),
				array( 'label' => 'Long Range', 'cells' => "3.1 s|0–60 MPH\n149 mph|TOP SPEED\n405 mi|RANGE (EPA est.)\n670 hp|PEAK POWER" ),
			),
		) );

		$this->add_control( 'heading', array(
			'label' => __( 'Section heading', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::TEXT,
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$tabs = is_array( $s['tabs'] ?? null ) ? $s['tabs'] : array();
		?>
		<section class="vc-mspecs" data-vc-mspecs>
			<?php if ( ! empty( $s['heading'] ) ) : ?><h2 style="text-align:center;margin-bottom:32px;"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
			<div class="vc-mspecs__tabs">
			<?php foreach ( $tabs as $i => $t ) : ?>
				<button class="vc-mspecs__tab<?php echo $i === 0 ? ' is-active' : ''; ?>" type="button"><?php echo esc_html( $t['label'] ); ?></button>
			<?php endforeach; ?>
			</div>
			<div class="vc-mspecs__panels">
			<?php foreach ( $tabs as $i => $t ) :
				$cells = array();
				if ( ! empty( $t['cells'] ) ) {
					foreach ( preg_split( '/\r?\n/', (string) $t['cells'] ) as $ln ) {
						$pp = array_map( 'trim', explode( '|', $ln ) );
						if ( count( $pp ) >= 2 ) $cells[] = $pp;
					}
				}
			?>
				<div class="vc-mspecs__panel<?php echo $i === 0 ? ' is-active' : ''; ?>">
					<div class="vc-mspecs__grid">
					<?php foreach ( $cells as $c ) : ?>
						<div class="vc-mspecs__cell">
							<p class="vc-mspecs__cell-value" data-count="<?php echo esc_attr( $c[0] ); ?>"><?php echo esc_html( $c[0] ); ?></p>
							<p class="vc-mspecs__cell-label"><?php echo esc_html( $c[1] ); ?></p>
						</div>
					<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
