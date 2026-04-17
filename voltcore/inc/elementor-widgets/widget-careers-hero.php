<?php
/**
 * VoltCore Careers Hero — recruiting-style full-bleed hero with
 * left-aligned headline and optional CTAs. Derived from page-careers.php
 * but fully customisable in Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Careers_Hero extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-careers-hero'; }
	public function get_title() { return __( 'VoltCore Careers Hero', 'voltcore' ); }
	public function get_icon() { return 'eicon-user-circle-o'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'image', array( 'label' => __( 'Background', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => VOLTCORE_URI . '/assets/images/story-2.jpg' ) ) );
		$this->add_control( 'eyebrow', array( 'label' => __( 'Eyebrow', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Careers' ) );
		$this->add_control( 'title',   array( 'label' => __( 'Headline', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Build the battery decade.' ) );
		$this->add_control( 'sub',     array( 'label' => __( 'Subheadline', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'The bottleneck on electrifying the world is batteries. We build them. Come help.' ) );
		$this->add_control( 'btn1_l', array( 'label' => __( 'Button 1 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'See open roles' ) );
		$this->add_control( 'btn1_u', array( 'label' => __( 'Button 1 URL',   'voltcore' ), 'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#open-roles' ) ) );
		$this->add_control( 'btn2_l', array( 'label' => __( 'Button 2 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'About us' ) );
		$this->add_control( 'btn2_u', array( 'label' => __( 'Button 2 URL',   'voltcore' ), 'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '/about/' ) ) );

		$this->end_controls_section();

		$this->start_controls_section( 'section_style', array( 'label' => __( 'Style', 'voltcore' ), 'tab' => \Elementor\Controls_Manager::TAB_STYLE ) );
		$this->add_control( 'overlay', array(
			'label'     => __( 'Overlay gradient', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::TEXTAREA,
			'default'   => 'linear-gradient(110deg, rgba(0,0,0,.7) 0%, rgba(0,0,0,.4) 40%, rgba(0,0,0,0) 80%)',
			'selectors' => array( '{{WRAPPER}} .careers-hero::before' => 'background: {{VALUE}};' ),
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$img = $s['image']['url'] ?? '';
		?>
		<header class="careers-hero" <?php if ( $img ) : ?>style="background-image:url('<?php echo esc_url( $img ); ?>');"<?php endif; ?>>
			<div class="careers-hero__inner" data-fade>
				<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<h1 class="careers-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
				<?php if ( $s['sub'] ) : ?><p class="careers-hero__sub"><?php echo esc_html( $s['sub'] ); ?></p><?php endif; ?>
				<div class="hero__actions">
					<?php if ( $s['btn1_l'] ) : ?><a class="btn btn--light" href="<?php echo esc_url( $s['btn1_u']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn1_l'] ); ?></a><?php endif; ?>
					<?php if ( $s['btn2_l'] ) : ?><a class="btn btn--ghost" href="<?php echo esc_url( $s['btn2_u']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn2_l'] ); ?></a><?php endif; ?>
				</div>
			</div>
		</header>
		<?php
	}
}
