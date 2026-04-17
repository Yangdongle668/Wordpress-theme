<?php
/**
 * VoltCore Hero Elementor widget.
 *
 * A full-bleed hero with background image, headline, subheadline and
 * two buttons — matches the homepage hero sections so Elementor-built
 * pages can reuse the same aesthetic.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Hero extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-hero'; }
	public function get_title() { return __( 'VoltCore Hero', 'voltcore' ); }
	public function get_icon() { return 'eicon-banner'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'hero', 'banner', 'tesla', 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'image', array(
			'label'   => __( 'Background Image', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => VOLTCORE_URI . '/assets/images/hero-1.jpg' ),
		) );

		$this->add_control( 'eyebrow', array(
			'label'       => __( 'Eyebrow', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'placeholder' => __( 'Our newest platform', 'voltcore' ),
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Headline', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Model V',
		) );

		$this->add_control( 'heading_tag', array(
			'label'   => __( 'Headline HTML tag', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'h2',
			'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3' ),
		) );

		$this->add_control( 'subtitle', array(
			'label'   => __( 'Subheadline', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'The most energy-dense battery pack we have ever built.',
		) );

		$this->add_control( 'align', array(
			'label'   => __( 'Alignment', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'center',
			'options' => array(
				'center' => __( 'Center', 'voltcore' ),
				'left'   => __( 'Left (8%)', 'voltcore' ),
			),
		) );

		$this->add_control( 'height', array(
			'label'   => __( 'Height', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'full',
			'options' => array(
				'full'  => __( '100 vh (full)', 'voltcore' ),
				'short' => __( '80 vh (short)', 'voltcore' ),
			),
		) );

		$this->add_control( 'btn1_label', array( 'label' => __( 'Button 1 Label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Order now' ) );
		$this->add_control( 'btn1_url',   array( 'label' => __( 'Button 1 URL', 'voltcore' ),   'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'btn2_label', array( 'label' => __( 'Button 2 Label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Learn more' ) );
		$this->add_control( 'btn2_url',   array( 'label' => __( 'Button 2 URL', 'voltcore' ),   'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );

		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$img = ! empty( $s['image']['url'] ) ? esc_url( $s['image']['url'] ) : '';
		$hero_class = 'hero' . ( $s['height'] === 'short' ? ' hero--short' : ' hero--full' );
		$inner_class = 'hero__inner' . ( $s['align'] === 'left' ? ' hero__inner--left' : ' hero__inner--center' );
		$heading_tag = in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3' ), true ) ? $s['heading_tag'] : 'h2';
		?>
		<section class="<?php echo esc_attr( $hero_class ); ?>" <?php if ( $img ) : ?>style="background-image:url('<?php echo $img; ?>');"<?php endif; ?>>
			<?php if ( $img ) : ?><img class="single-hero__seo-img" src="<?php echo $img; ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" width="2000" height="1200" loading="eager"><?php endif; ?>
			<div class="<?php echo esc_attr( $inner_class ); ?>">
				<?php if ( $s['eyebrow'] ) : ?><p class="eyebrow" data-fade><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
				<?php if ( $s['title'] ) : ?><<?php echo $heading_tag; ?> class="hero__title" data-fade data-fade-delay="80"><?php echo esc_html( $s['title'] ); ?></<?php echo $heading_tag; ?>><?php endif; ?>
				<?php if ( $s['subtitle'] ) : ?><p class="hero__subtitle" data-fade data-fade-delay="160"><?php echo esc_html( $s['subtitle'] ); ?></p><?php endif; ?>
				<div class="hero__actions" data-fade data-fade-delay="240">
					<?php if ( ! empty( $s['btn1_label'] ) ) : ?>
						<a class="btn btn--light" href="<?php echo esc_url( $s['btn1_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn1_label'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $s['btn2_label'] ) ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( $s['btn2_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn2_label'] ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
