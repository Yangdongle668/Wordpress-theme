<?php
/**
 * VoltCore Contact Grid — 2×2 (or configurable) mailto cards.
 * Replaces the hard-coded cards in page-contact.php.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Contact_Grid extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-contact-grid'; }
	public function get_title() { return __( 'VoltCore Contact Grid', 'voltcore' ); }
	public function get_icon() { return 'eicon-mail'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$r = new \Elementor\Repeater();
		$r->add_control( 'label', array( 'label' => __( 'Department', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Sales & quotes' ) );
		$r->add_control( 'email', array( 'label' => __( 'Email', 'voltcore' ),       'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'sales@example.com' ) );
		$r->add_control( 'desc',  array( 'label' => __( 'Description', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Pricing and program specification.' ) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Cards', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'default'     => array(
				array( 'label' => 'Sales & quotes', 'email' => 'sales@example.com',       'desc' => 'Specifying VoltCore cells or packs, or pricing a grid-scale project.' ),
				array( 'label' => 'Engineering',    'email' => 'engineering@example.com', 'desc' => 'Integration, firmware, test-bench and field support for active customers.' ),
				array( 'label' => 'Press & media',  'email' => 'press@example.com',       'desc' => 'Interview requests, quotes, imagery and media kit access.' ),
				array( 'label' => 'Careers',        'email' => 'careers@example.com',     'desc' => 'Applications, talent partnerships and recruiter enquiries.' ),
			),
			'title_field' => '{{{ label }}}',
		) );

		$this->add_responsive_control( 'columns', array(
			'label'   => __( 'Columns', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '2',
			'options' => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
			'selectors' => array( '{{WRAPPER}} .contact-cards__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);' ),
		) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'voltcore' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );

		$this->add_control( 'card_bg', array(
			'label'     => __( 'Card background', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#ffffff',
			'selectors' => array( '{{WRAPPER}} .contact-card' => 'background: {{VALUE}};' ),
		) );
		$this->add_control( 'accent', array(
			'label'     => __( 'Arrow color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'selectors' => array( '{{WRAPPER}} .contact-card__arrow' => 'color: {{VALUE}};' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="contact-cards" style="padding: 0; background: transparent;">
			<div class="contact-cards__grid">
				<?php foreach ( $s['cards'] as $i => $c ) :
					$email = sanitize_email( $c['email'] ); ?>
					<a class="contact-card" href="mailto:<?php echo esc_attr( $email ); ?>" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
						<span class="contact-card__label"><?php echo esc_html( $c['label'] ); ?></span>
						<span class="contact-card__email"><?php echo esc_html( $email ); ?></span>
						<p class="contact-card__desc"><?php echo esc_html( $c['desc'] ); ?></p>
						<span class="contact-card__arrow" aria-hidden="true">→</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
