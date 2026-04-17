<?php
/**
 * VoltCore Product Specs — key-value spec grid.
 *
 * Dynamic mode: reads `_vc_product_specs` from the current vc_product
 * post ("Range|640 km, Charge|15 min" format).
 * Manual mode: editor adds rows in a repeater.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Product_Specs extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-product-specs'; }
	public function get_title() { return __( 'VoltCore Product Specs', 'voltcore' ); }
	public function get_icon() { return 'eicon-table'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'source', array(
			'label'   => __( 'Source', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'dynamic',
			'options' => array(
				'dynamic' => __( 'From current product (post meta)', 'voltcore' ),
				'manual'  => __( 'Manual rows', 'voltcore' ),
			),
		) );

		$r = new \Elementor\Repeater();
		$r->add_control( 'value', array( 'label' => __( 'Value', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '640 km' ) );
		$r->add_control( 'label', array( 'label' => __( 'Label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Range' ) );

		$this->add_control( 'rows', array(
			'label'       => __( 'Rows', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'default'     => array(
				array( 'value' => '640 km',  'label' => 'Range' ),
				array( 'value' => '15 min',  'label' => 'Fast charge' ),
				array( 'value' => '4680',    'label' => 'Form factor' ),
				array( 'value' => 'NMC',     'label' => 'Chemistry' ),
			),
			'title_field' => '{{{ label }}}: {{{ value }}}',
			'condition'   => array( 'source' => 'manual' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$rows = array();

		if ( $s['source'] === 'dynamic' && is_singular( 'vc_product' ) ) {
			$raw = get_post_meta( get_the_ID(), '_vc_product_specs', true );
			if ( $raw ) {
				foreach ( array_filter( array_map( 'trim', explode( ',', $raw ) ) ) as $pair ) {
					$parts = array_map( 'trim', explode( '|', $pair ) );
					if ( count( $parts ) === 2 ) {
						$rows[] = array( 'label' => $parts[0], 'value' => $parts[1] );
					}
				}
			}
		} else {
			foreach ( $s['rows'] as $r ) {
				$rows[] = array( 'label' => $r['label'], 'value' => $r['value'] );
			}
		}

		if ( ! $rows ) return;
		?>
		<section class="product-specs" data-fade>
			<ul class="spec-list">
				<?php foreach ( $rows as $r ) : ?>
					<li><span class="spec-value"><?php echo esc_html( $r['value'] ); ?></span><span class="spec-label"><?php echo esc_html( $r['label'] ); ?></span></li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	}
}
