<?php
/**
 * VoltCore Legal Hero — quiet typography hero for Privacy/Terms/Cookies.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Legal_Hero extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-legal-hero'; }
	public function get_title() { return __( 'VoltCore Legal Hero', 'voltcore' ); }
	public function get_icon() { return 'eicon-document-file'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'eyebrow',  array( 'label' => __( 'Eyebrow', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Legal' ) );
		$this->add_control( 'title',    array( 'label' => __( 'Title', 'voltcore' ),   'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Privacy Policy' ) );
		$this->add_control( 'updated',  array( 'label' => __( 'Last updated', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'April 2026' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<header class="legal-hero">
			<div class="legal-hero__inner" data-fade>
				<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<h1 class="legal-hero__title"><?php echo esc_html( $s['title'] ); ?></h1>
				<?php if ( $s['updated'] ) : ?>
					<div class="legal-hero__meta">
						<span class="legal-hero__pill"><?php
							/* translators: %s: last updated date */
							printf( esc_html__( 'Last updated %s', 'voltcore' ), esc_html( $s['updated'] ) );
						?></span>
					</div>
				<?php endif; ?>
			</div>
		</header>
		<?php
	}
}
