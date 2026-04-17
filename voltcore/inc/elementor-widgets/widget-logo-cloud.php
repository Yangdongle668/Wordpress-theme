<?php
/**
 * VoltCore Logo Cloud — horizontal grid of partner / customer logos.
 * Grayscale by default, fades to color on hover (tesla.com style).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Logo_Cloud extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-logo-cloud'; }
	public function get_title() { return __( 'VoltCore Logo Cloud', 'voltcore' ); }
	public function get_icon() { return 'eicon-logo'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Trusted by',
		) );

		$repeater = new \Elementor\Repeater();
		$repeater->add_control( 'image', array( 'label' => __( 'Logo', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ) ) );
		$repeater->add_control( 'alt',   array( 'label' => __( 'Alt text', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT ) );
		$repeater->add_control( 'url',   array( 'label' => __( 'Link URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL ) );

		$this->add_control( 'logos', array(
			'label'       => __( 'Logos', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'default'     => array(
				array( 'alt' => 'Partner 1' ), array( 'alt' => 'Partner 2' ),
				array( 'alt' => 'Partner 3' ), array( 'alt' => 'Partner 4' ),
				array( 'alt' => 'Partner 5' ), array( 'alt' => 'Partner 6' ),
			),
			'title_field' => '{{{ alt }}}',
		) );

		$this->add_responsive_control( 'columns', array(
			'label'   => __( 'Columns', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '6',
			'options' => array( '3' => '3', '4' => '4', '5' => '5', '6' => '6' ),
			'selectors' => array( '{{WRAPPER}} .vc-logos__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);' ),
		) );

		$this->add_control( 'grayscale', array(
			'label'   => __( 'Grayscale logos', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$gray = $s['grayscale'] === 'yes' ? ' is-grayscale' : '';
		?>
		<section class="vc-logos<?php echo esc_attr( $gray ); ?>">
			<?php if ( $s['eyebrow'] ) : ?>
				<p class="eyebrow vc-logos__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p>
			<?php endif; ?>
			<div class="vc-logos__grid">
				<?php foreach ( $s['logos'] as $logo ) :
					$url = ! empty( $logo['image']['url'] ) ? $logo['image']['url'] : '';
					$alt = $logo['alt'] ?: '';
					$href = ! empty( $logo['url']['url'] ) ? $logo['url']['url'] : '';
					$tag = $href ? 'a' : 'div';
					$href_attr = $href ? 'href="' . esc_url( $href ) . '"' : '';
				?>
					<<?php echo $tag; ?> class="vc-logos__item" <?php echo $href_attr; ?>>
						<?php if ( $url ) : ?>
							<img src="<?php echo esc_url( $url ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy" decoding="async">
						<?php else : ?>
							<span class="vc-logos__placeholder"><?php echo esc_html( $alt ); ?></span>
						<?php endif; ?>
					</<?php echo $tag; ?>>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
