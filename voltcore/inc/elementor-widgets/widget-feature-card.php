<?php
/**
 * VoltCore Feature Card Elementor widget.
 * A product/feature card with image, title, description and CTA arrow.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Feature_Card extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-feature-card'; }
	public function get_title() { return __( 'VoltCore Feature Card', 'voltcore' ); }
	public function get_icon() { return 'eicon-image-box'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'image', array(
			'label'   => __( 'Image', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => VOLTCORE_URI . '/assets/images/product-1.jpg' ),
		) );
		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Cell 4680',
		) );
		$this->add_control( 'desc', array(
			'label'   => __( 'Description', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Next-generation cylindrical cell with tabless current collector.',
		) );
		$this->add_control( 'link', array(
			'label'   => __( 'Link URL', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::URL,
			'default' => array( 'url' => '#' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$img = ! empty( $s['image']['url'] ) ? esc_url( $s['image']['url'] ) : '';
		$url = esc_url( $s['link']['url'] ?? '#' );
		?>
		<a class="product-card" href="<?php echo $url; ?>" data-fade>
			<?php if ( $img ) : ?>
				<div class="product-card__media" style="background-image:url('<?php echo $img; ?>');" role="img" aria-label="<?php echo esc_attr( $s['title'] ); ?>">
					<img src="<?php echo $img; ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" class="screen-reader-text">
				</div>
			<?php endif; ?>
			<div class="product-card__body">
				<h3 class="product-card__title"><?php echo esc_html( $s['title'] ); ?></h3>
				<p class="product-card__desc"><?php echo esc_html( $s['desc'] ); ?></p>
				<span class="product-card__arrow" aria-hidden="true">→</span>
			</div>
		</a>
		<?php
	}
}
