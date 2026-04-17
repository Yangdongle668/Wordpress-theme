<?php
/**
 * VoltCore Product Hero — CPT-aware single-product hero.
 *
 * If dropped into a vc_product single template via Elementor Pro Theme
 * Builder, it reads the current product's title, featured image,
 * subtitle, price and CTA. All fields can be manually overridden in
 * the panel.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Product_Hero extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-product-hero'; }
	public function get_title() { return __( 'VoltCore Product Hero', 'voltcore' ); }
	public function get_icon() { return 'eicon-product-title'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'use_dynamic', array(
			'label'       => __( 'Use current product', 'voltcore' ),
			'description' => __( 'When ON and the page is a vc_product single, fields auto-fill from post + meta.', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::SWITCHER,
			'default'     => 'yes',
		) );

		$this->add_control( 'image', array( 'label' => __( 'Background image', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => VOLTCORE_URI . '/assets/images/product-1.jpg' ) ) );
		$this->add_control( 'eyebrow',  array( 'label' => __( 'Eyebrow',  'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Cell' ) );
		$this->add_control( 'title',    array( 'label' => __( 'Title',    'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Cell 4680' ) );
		$this->add_control( 'subtitle', array( 'label' => __( 'Subtitle', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Next-generation cylindrical cell with tabless current collector.' ) );
		$this->add_control( 'price',    array( 'label' => __( 'Price',    'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'From $0.072 / Wh' ) );
		$this->add_control( 'cta_label',array( 'label' => __( 'CTA label','voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Request samples' ) );
		$this->add_control( 'cta_url',  array( 'label' => __( 'CTA URL',  'voltcore' ), 'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '/contact/' ) ) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$img = $s['image']['url'] ?? '';
		$eyebrow  = $s['eyebrow'];
		$title    = $s['title'];
		$subtitle = $s['subtitle'];
		$price    = $s['price'];
		$cta_l    = $s['cta_label'];
		$cta_u    = $s['cta_url']['url'] ?? '#';

		// Auto-fill from current vc_product
		if ( $s['use_dynamic'] === 'yes' && is_singular( 'vc_product' ) ) {
			$pid = get_the_ID();
			$title = get_the_title( $pid );
			if ( has_post_thumbnail( $pid ) ) {
				$img = get_the_post_thumbnail_url( $pid, 'voltcore-hero' );
			}
			$subtitle = get_post_meta( $pid, '_vc_product_subtitle', true ) ?: $subtitle;
			$price    = get_post_meta( $pid, '_vc_product_price', true ) ?: $price;
			$cta_l_p  = get_post_meta( $pid, '_vc_product_cta_label', true );
			$cta_u_p  = get_post_meta( $pid, '_vc_product_cta_url', true );
			if ( $cta_l_p ) $cta_l = $cta_l_p;
			if ( $cta_u_p ) $cta_u = $cta_u_p;

			$terms = get_the_terms( $pid, 'vc_product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$eyebrow = $terms[0]->name;
			}
		}
		?>
		<header class="product-hero" <?php if ( $img ) : ?>style="background-image:url('<?php echo esc_url( $img ); ?>');"<?php endif; ?>>
			<div class="product-hero__inner" data-fade>
				<?php if ( $eyebrow ) : ?><p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
				<h1 class="product-hero__title"><?php echo esc_html( $title ); ?></h1>
				<?php if ( $subtitle ) : ?><p class="product-hero__subtitle"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
				<div class="product-hero__meta">
					<?php if ( $price ) : ?><div class="product-hero__price"><?php echo esc_html( $price ); ?></div><?php endif; ?>
					<?php if ( $cta_l ) : ?><a class="btn btn--light" href="<?php echo esc_url( $cta_u ); ?>"><?php echo esc_html( $cta_l ); ?></a><?php endif; ?>
				</div>
			</div>
		</header>
		<?php
	}
}
