<?php
/**
 * VoltCore Press Kit — dark downloadable card + press contact card
 * in a 1.6:1 two-column layout (stacks on mobile).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Press_Kit extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-press-kit'; }
	public function get_title() { return __( 'VoltCore Press Kit', 'voltcore' ); }
	public function get_icon() { return 'eicon-download-bold'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_kit', array( 'label' => __( 'Media kit', 'voltcore' ) ) );
		$this->add_control( 'kit_label', array( 'label' => __( 'Label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Media kit' ) );
		$this->add_control( 'kit_title', array( 'label' => __( 'Title', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Logos, product photography, factory b-roll and brand guidelines.' ) );
		$this->add_control( 'kit_url',   array( 'label' => __( 'File URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'kit_size',  array( 'label' => __( 'File size', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '42 MB' ) );
		$this->add_control( 'kit_format',array( 'label' => __( 'Format label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'ZIP' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_contact', array( 'label' => __( 'Press contact', 'voltcore' ) ) );
		$this->add_control( 'contact_label', array( 'label' => __( 'Label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Press inquiries' ) );
		$this->add_control( 'contact_email', array( 'label' => __( 'Email', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'press@example.com' ) );
		$this->add_control( 'contact_desc',  array( 'label' => __( 'Description', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Response within one business day. Please share deadline and outlet in your first message.' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'voltcore' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'kit_bg', array(
			'label' => __( 'Kit card background', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'linear-gradient(135deg, #0e1014 0%, #2a2f36 100%)',
			'selectors' => array( '{{WRAPPER}} .press-kit' => 'background: {{VALUE}};' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$kit_url = $s['kit_url']['url'] ?? '#';
		$email   = sanitize_email( $s['contact_email'] );
		?>
		<section class="press-row" style="padding: 0; background: transparent;">
			<div class="press-row__inner">
				<a class="press-kit" href="<?php echo esc_url( $kit_url ); ?>" <?php echo $kit_url !== '#' ? 'download' : ''; ?> data-fade>
					<div class="press-kit__label"><?php echo esc_html( $s['kit_label'] ); ?></div>
					<div class="press-kit__title"><?php echo esc_html( $s['kit_title'] ); ?></div>
					<div class="press-kit__meta">
						<span class="press-kit__size"><?php echo esc_html( $s['kit_format'] . ' · ' . $s['kit_size'] ); ?></span>
						<span class="press-kit__cta"><?php esc_html_e( 'Download →', 'voltcore' ); ?></span>
					</div>
				</a>
				<div class="press-contact" data-fade data-fade-delay="100">
					<div class="press-contact__label"><?php echo esc_html( $s['contact_label'] ); ?></div>
					<a class="press-contact__email" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
					<p class="press-contact__desc"><?php echo esc_html( $s['contact_desc'] ); ?></p>
				</div>
			</div>
		</section>
		<?php
	}
}
