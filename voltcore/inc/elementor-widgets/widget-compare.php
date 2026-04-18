<?php
/**
 * VoltCore Compare — side-by-side comparison table.
 *
 * Up to 4 columns (models / products). Shared row labels on the left.
 * On mobile, columns become a horizontal-scrolling carousel.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Compare extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-compare'; }
	public function get_title() { return __( 'VoltCore Compare', 'voltcore' ); }
	public function get_icon() { return 'eicon-table'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'compare', 'table', 'products' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Compare models' ) );
		$this->add_control( 'rows', array(
			'label' => 'Row labels (one per line)', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Starting price\nRange (EPA)\n0-60 mph\nTop speed\nSeating\nDrivetrain\nCharging",
		) );

		$col = new \Elementor\Repeater();
		$col->add_control( 'image', array( 'label' => 'Image',  'type' => \Elementor\Controls_Manager::MEDIA ) );
		$col->add_control( 'title', array( 'label' => 'Title',  'type' => \Elementor\Controls_Manager::TEXT,  'default' => 'Model V' ) );
		$col->add_control( 'values', array( 'label' => 'Values (one per row, matching order above)', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "$39,990\n340 mi\n4.2 s\n130 mph\n5\nAll-Wheel Drive\n250 kW DC",
		) );
		$col->add_control( 'cta_label', array( 'label' => 'CTA label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Custom Order' ) );
		$col->add_control( 'cta_url',   array( 'label' => 'CTA URL',   'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$this->add_control( 'columns', array(
			'label' => 'Columns', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $col->get_controls(),
			'title_field' => '{{{ title }}}',
			'default' => array(
				array( 'title' => 'Model V Standard', 'values' => "$39,990\n272 mi\n5.5 s\n120 mph\n5\nRear-Wheel Drive\n170 kW DC", 'cta_label' => 'Custom Order' ),
				array( 'title' => 'Model V Long Range', 'values' => "$48,990\n340 mi\n4.2 s\n130 mph\n5\nAll-Wheel Drive\n250 kW DC", 'cta_label' => 'Custom Order' ),
				array( 'title' => 'Model V Performance', 'values' => "$53,990\n310 mi\n2.9 s\n162 mph\n5\nAll-Wheel Drive\n250 kW DC", 'cta_label' => 'Custom Order' ),
			),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$rows = array_values( array_filter( array_map( 'trim', explode( "\n", (string) $s['rows'] ) ) ) );
		$cols = (array) ( $s['columns'] ?? array() );
		if ( empty( $cols ) ) return;
		?>
		<section class="vc-cmp" data-vc-compare>
			<?php if ( ! empty( $s['heading'] ) ) : ?><h2 class="vc-cmp__heading"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
			<div class="vc-cmp__scroll">
				<table class="vc-cmp__table">
					<thead>
						<tr>
							<th></th>
							<?php foreach ( $cols as $c ) : ?>
								<th>
									<?php if ( ! empty( $c['image']['url'] ) ) : ?><img src="<?php echo esc_url( $c['image']['url'] ); ?>" alt="<?php echo esc_attr( $c['title'] ); ?>" loading="lazy"><?php endif; ?>
									<span><?php echo esc_html( $c['title'] ); ?></span>
								</th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $ri => $rlabel ) : ?>
							<tr>
								<th scope="row"><?php echo esc_html( $rlabel ); ?></th>
								<?php foreach ( $cols as $c ) :
									$vals = array_values( array_map( 'trim', explode( "\n", (string) ( $c['values'] ?? '' ) ) ) );
									$v = $vals[ $ri ] ?? '—';
								?>
									<td><?php echo esc_html( $v ); ?></td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
						<tr>
							<th scope="row"></th>
							<?php foreach ( $cols as $c ) : ?>
								<td>
									<?php if ( ! empty( $c['cta_label'] ) ) : ?>
										<a class="btn btn--ghost" href="<?php echo esc_url( $c['cta_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $c['cta_label'] ); ?></a>
									<?php endif; ?>
								</td>
							<?php endforeach; ?>
						</tr>
					</tbody>
				</table>
			</div>
		</section>
		<?php
	}
}
