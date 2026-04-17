<?php
/**
 * VoltCore FAQ — accessible accordion (<details> elements) with
 * optional "all open" default.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_FAQ extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-faq'; }
	public function get_title() { return __( 'VoltCore FAQ', 'voltcore' ); }
	public function get_icon() { return 'eicon-help-o'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$r = new \Elementor\Repeater();
		$r->add_control( 'q', array( 'label' => __( 'Question', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'How do you ship?' ) );
		$r->add_control( 'a', array( 'label' => __( 'Answer',   'voltcore' ), 'type' => \Elementor\Controls_Manager::WYSIWYG, 'default' => 'Europalette, insured, tracked.' ) );

		$this->add_control( 'items', array(
			'label'       => __( 'Items', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'default'     => array(
				array( 'q' => 'How long from order to delivery?', 'a' => 'Cells ship within 12 weeks. Packs are quoted per program. Grid systems deploy in 14 weeks from contract.' ),
				array( 'q' => 'Do you publish datasheets?',       'a' => 'Yes, signed NDA. Request via <a href="/contact/">sales</a>.' ),
				array( 'q' => 'What warranty do you offer?',     'a' => '10-year / 80% capacity retention on cells. Program-specific for packs and grid systems.' ),
			),
			'title_field' => '{{{ q }}}',
		) );

		$this->add_control( 'open_first', array(
			'label'   => __( 'Open first item by default', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="vc-faq">
			<?php foreach ( $s['items'] as $i => $it ) :
				$open = ( $i === 0 && $s['open_first'] === 'yes' ) ? 'open' : ''; ?>
				<details class="vc-faq__item" data-fade data-fade-delay="<?php echo esc_attr( $i * 50 ); ?>" <?php echo $open; ?>>
					<summary class="vc-faq__q"><?php echo esc_html( $it['q'] ); ?><span class="vc-faq__icon" aria-hidden="true">+</span></summary>
					<div class="vc-faq__a"><?php echo wp_kses_post( $it['a'] ); ?></div>
				</details>
			<?php endforeach; ?>
		</section>
		<?php
	}
}
