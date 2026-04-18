<?php
/**
 * VoltCore Spec Ticker — oversized animated spec readouts.
 *
 * Large-format counter set (e.g. "340 mi range · 4.2 s 0-60 · 250 kW
 * charging"). Each value animates in when it scrolls into view, reusing
 * the existing [data-count] animation.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Spec_Ticker extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-spec-ticker'; }
	public function get_title() { return __( 'VoltCore Spec Ticker', 'voltcore' ); }
	public function get_icon() { return 'eicon-counter-circle'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'spec', 'counter', 'ticker', 'stats' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => 'Eyebrow', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Performance' ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Built to move.' ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'value',  array( 'label' => 'Value',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => '340' ) );
		$rep->add_control( 'suffix', array( 'label' => 'Suffix', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$rep->add_control( 'unit',   array( 'label' => 'Unit',   'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'mi' ) );
		$rep->add_control( 'label',  array( 'label' => 'Label',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Range' ) );

		$this->add_control( 'specs', array(
			'label' => 'Specs', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $rep->get_controls(),
			'title_field' => '{{{ label }}}',
			'default' => array(
				array( 'value' => '340', 'unit' => 'mi',  'label' => 'Range (EPA)' ),
				array( 'value' => '4.2', 'unit' => 's',   'label' => '0-60 mph' ),
				array( 'value' => '250', 'unit' => 'kW',  'label' => 'DC charging' ),
				array( 'value' => '162', 'unit' => 'mph', 'label' => 'Top speed' ),
			),
		) );

		$this->add_control( 'scheme', array( 'label' => 'Scheme', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'dark',
			'options' => array( 'dark' => 'Dark background', 'light' => 'Light background' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$specs = (array) ( $s['specs'] ?? array() );
		?>
		<section class="vc-ticker vc-ticker--<?php echo esc_attr( $s['scheme'] ); ?>">
			<div class="vc-ticker__head">
				<?php if ( ! empty( $s['eyebrow'] ) ) : ?><p class="vc-ticker__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( ! empty( $s['heading'] ) ) : ?><h2 class="vc-ticker__heading"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
			</div>
			<div class="vc-ticker__grid" data-vc-ticker>
				<?php foreach ( $specs as $sp ) : ?>
					<div class="vc-ticker__item" data-fade>
						<strong class="vc-ticker__value" data-count="<?php echo esc_attr( $sp['value'] . ( $sp['suffix'] ?? '' ) ); ?>"><?php echo esc_html( $sp['value'] . ( $sp['suffix'] ?? '' ) ); ?></strong>
						<?php if ( ! empty( $sp['unit'] ) ) : ?><span class="vc-ticker__unit"><?php echo esc_html( $sp['unit'] ); ?></span><?php endif; ?>
						<span class="vc-ticker__label"><?php echo esc_html( $sp['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
