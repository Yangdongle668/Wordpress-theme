<?php
/**
 * VoltCore Panel — tesla.com-style scroll-snap full-viewport panel.
 *
 * Each panel fills the viewport, pins a background image (or video),
 * overlays headline / sub / dual CTAs, and participates in a scroll-snap
 * section list. Multiple panels stacked become the classic tesla.com
 * homepage experience.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Panel extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-panel'; }
	public function get_title() { return __( 'VoltCore Panel', 'voltcore' ); }
	public function get_icon() { return 'eicon-image-rollover'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'panel', 'tesla', 'hero', 'snap', 'pinned' ); }

	protected function register_controls() {

		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'media_type', array(
			'label'   => __( 'Background media', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'image',
			'options' => array(
				'image' => __( 'Image', 'voltcore' ),
				'video' => __( 'Video (autoplay, muted, loop)', 'voltcore' ),
				'color' => __( 'Color only', 'voltcore' ),
			),
		) );

		$this->add_control( 'image', array(
			'label'     => __( 'Background image', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::MEDIA,
			'default'   => array( 'url' => VOLTCORE_URI . '/assets/images/hero-1.jpg' ),
			'condition' => array( 'media_type' => 'image' ),
		) );

		$this->add_control( 'video_url', array(
			'label'       => __( 'Video URL (.mp4)', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::URL,
			'placeholder' => 'https://example.com/bg.mp4',
			'show_external' => false,
			'condition'   => array( 'media_type' => 'video' ),
		) );

		$this->add_control( 'video_poster', array(
			'label'     => __( 'Video poster (while loading)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::MEDIA,
			'condition' => array( 'media_type' => 'video' ),
		) );

		$this->add_control( 'bg_color', array(
			'label'     => __( 'Background color', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::COLOR,
			'default'   => '#111418',
			'selectors' => array( '{{WRAPPER}} .vc-panel' => 'background-color: {{VALUE}};' ),
		) );

		$this->add_control( 'scheme', array(
			'label'   => __( 'Text color scheme', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'light',
			'options' => array(
				'light' => __( 'Light text (on dark bg)', 'voltcore' ),
				'dark'  => __( 'Dark text (on light bg)', 'voltcore' ),
			),
		) );

		$this->add_control( 'align', array(
			'label'   => __( 'Content alignment', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'top-center',
			'options' => array(
				'top-center'    => __( 'Top, centered', 'voltcore' ),
				'bottom-center' => __( 'Bottom, centered', 'voltcore' ),
				'bottom-left'   => __( 'Bottom, left', 'voltcore' ),
				'middle-left'   => __( 'Middle, left (8%)', 'voltcore' ),
			),
		) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Headline', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Model V',
		) );

		$this->add_control( 'heading_tag', array(
			'label'   => __( 'Heading tag', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'h1',
			'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3' ),
		) );

		$this->add_control( 'subtitle', array(
			'label'   => __( 'Sub-headline', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->add_control( 'disclaimer', array(
			'label'   => __( 'Disclaimer (small print)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => '',
		) );

		$this->add_control( 'btn1_label', array( 'label' => __( 'Button 1 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Custom Order' ) );
		$this->add_control( 'btn1_url',   array( 'label' => __( 'Button 1 URL', 'voltcore' ),   'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );
		$this->add_control( 'btn2_label', array( 'label' => __( 'Button 2 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Demo Drive' ) );
		$this->add_control( 'btn2_url',   array( 'label' => __( 'Button 2 URL', 'voltcore' ),   'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );

		$this->add_control( 'show_scroll_hint', array(
			'label'   => __( 'Show scroll-hint chevron', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->add_control( 'overlay', array(
			'label'      => __( 'Media dim overlay (%)', 'voltcore' ),
			'type'       => \Elementor\Controls_Manager::SLIDER,
			'size_units' => array( '%' ),
			'range'      => array( '%' => array( 'min' => 0, 'max' => 80 ) ),
			'default'    => array( 'size' => 15, 'unit' => '%' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$scheme = $s['scheme'] === 'dark' ? 'is-scheme-dark' : 'is-scheme-light';
		$align  = 'is-align-' . esc_attr( $s['align'] );
		$tag    = in_array( $s['heading_tag'], array( 'h1','h2','h3' ), true ) ? $s['heading_tag'] : 'h1';
		$overlay = isset( $s['overlay']['size'] ) ? (int) $s['overlay']['size'] : 15;
		?>
		<section class="vc-panel <?php echo esc_attr( $scheme . ' ' . $align ); ?>" data-vc-panel>
			<div class="vc-panel__media" style="--vc-panel-overlay: <?php echo esc_attr( $overlay / 100 ); ?>;">
				<?php if ( $s['media_type'] === 'image' && ! empty( $s['image']['url'] ) ) : ?>
					<img class="vc-panel__img" src="<?php echo esc_url( $s['image']['url'] ); ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" loading="lazy">
				<?php elseif ( $s['media_type'] === 'video' && ! empty( $s['video_url']['url'] ) ) : ?>
					<video class="vc-panel__video" autoplay muted loop playsinline
						<?php if ( ! empty( $s['video_poster']['url'] ) ) : ?>poster="<?php echo esc_url( $s['video_poster']['url'] ); ?>"<?php endif; ?>
						preload="metadata" data-vc-video>
						<source src="<?php echo esc_url( $s['video_url']['url'] ); ?>" type="video/mp4">
					</video>
					<button class="vc-panel__video-toggle" type="button" aria-label="<?php esc_attr_e( 'Pause video', 'voltcore' ); ?>" data-vc-video-toggle>
						<span class="vc-pause" aria-hidden="true">❚❚</span>
						<span class="vc-play" aria-hidden="true">▶</span>
					</button>
				<?php endif; ?>
				<span class="vc-panel__dim"></span>
			</div>
			<div class="vc-panel__body">
				<div class="vc-panel__content" data-fade>
					<?php if ( $s['eyebrow'] ) : ?><p class="vc-panel__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
					<?php if ( $s['title'] ) : ?><<?php echo $tag; ?> class="vc-panel__title"><?php echo esc_html( $s['title'] ); ?></<?php echo $tag; ?>><?php endif; ?>
					<?php if ( $s['subtitle'] ) : ?><p class="vc-panel__sub"><?php echo esc_html( $s['subtitle'] ); ?></p><?php endif; ?>
				</div>
				<div class="vc-panel__foot" data-fade data-fade-delay="200">
					<div class="vc-panel__actions">
						<?php if ( ! empty( $s['btn1_label'] ) ) : ?>
							<a class="btn btn--light" href="<?php echo esc_url( $s['btn1_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn1_label'] ); ?></a>
						<?php endif; ?>
						<?php if ( ! empty( $s['btn2_label'] ) ) : ?>
							<a class="btn btn--ghost" href="<?php echo esc_url( $s['btn2_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn2_label'] ); ?></a>
						<?php endif; ?>
					</div>
					<?php if ( $s['disclaimer'] ) : ?><p class="vc-panel__disclaimer"><?php echo esc_html( $s['disclaimer'] ); ?></p><?php endif; ?>
				</div>
				<?php if ( $s['show_scroll_hint'] === 'yes' ) : ?>
					<button class="vc-panel__hint" type="button" aria-label="<?php esc_attr_e( 'Scroll to next', 'voltcore' ); ?>" data-scroll-hint>
						<svg viewBox="0 0 24 24" aria-hidden="true" width="24" height="24"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2"/></svg>
					</button>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
