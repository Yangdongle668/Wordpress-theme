<?php
/**
 * VoltCore Timeline — vertical company milestones with year + title + body.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Timeline extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-timeline'; }
	public function get_title() { return __( 'VoltCore Timeline', 'voltcore' ); }
	public function get_icon() { return 'eicon-time-line'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$r = new \Elementor\Repeater();
		$r->add_control( 'year',  array( 'label' => __( 'Year', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '2015' ) );
		$r->add_control( 'title', array( 'label' => __( 'Headline', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Founded' ) );
		$r->add_control( 'body',  array( 'label' => __( 'Body', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );

		$this->add_control( 'events', array(
			'label'       => __( 'Events', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'default'     => array(
				array( 'year' => '2015', 'title' => 'VoltCore founded', 'body' => 'Three engineers, one cell, one building in Reno.' ),
				array( 'year' => '2018', 'title' => 'First pack shipped', 'body' => 'P-100 pack ships to a commercial vehicle OEM.' ),
				array( 'year' => '2021', 'title' => 'Dry electrode line', 'body' => 'First commercial dry-coated 4680 cells off Line 3.' ),
				array( 'year' => '2024', 'title' => '12 GWh milestone', 'body' => '12 gigawatt-hours of cells shipped to 18 countries.' ),
			),
			'title_field' => '{{{ year }}} — {{{ title }}}',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="vc-timeline">
			<ol class="vc-timeline__list">
				<?php foreach ( $s['events'] as $i => $e ) : ?>
					<li class="vc-timeline__item" data-fade data-fade-delay="<?php echo esc_attr( $i * 100 ); ?>">
						<div class="vc-timeline__year"><?php echo esc_html( $e['year'] ); ?></div>
						<div class="vc-timeline__body">
							<h3 class="vc-timeline__title"><?php echo esc_html( $e['title'] ); ?></h3>
							<?php if ( ! empty( $e['body'] ) ) : ?><p><?php echo esc_html( $e['body'] ); ?></p><?php endif; ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
		<?php
	}
}
