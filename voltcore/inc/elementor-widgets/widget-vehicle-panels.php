<?php
/**
 * VoltCore Vehicle Panels — tesla.com homepage clone widget.
 *
 * Renders a stack of full-viewport (100vh) panels with a background
 * image OR looping video, eyebrow + title + sub + price, and dual CTA
 * buttons pinned to the bottom of each panel. Snap-scroll between
 * panels for that signature feel.
 *
 * Each panel is a repeater row so editors can reuse this single widget
 * for the whole homepage (Model S / 3 / X / Y / Cybertruck / Solar /
 * Powerwall / Accessories).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Vehicle_Panels extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-vehicle-panels'; }
	public function get_title() { return __( 'VoltCore Vehicle Panels', 'voltcore' ); }
	public function get_icon() { return 'eicon-slides'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'panels', 'homepage', 'vehicle', 'scroll' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_panels', array( 'label' => __( 'Panels', 'voltcore' ) ) );

		$rep = new \Elementor\Repeater();

		$rep->add_control( 'media_type', array(
			'label'   => __( 'Background type', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'image',
			'options' => array(
				'image' => __( 'Image', 'voltcore' ),
				'video' => __( 'Looping video', 'voltcore' ),
			),
		) );
		$rep->add_control( 'image', array(
			'label'     => __( 'Image', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::MEDIA,
			'default'   => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-s.svg' ),
			'condition' => array( 'media_type' => 'image' ),
		) );

		$rep->add_control( 'preset', array(
			'label'   => __( 'Layout preset', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'centered',
			'options' => array(
				'centered'    => __( 'Centered (default)', 'voltcore' ),
				'bottom-left' => __( 'Bottom-left', 'voltcore' ),
				'cinematic'   => __( 'Cinematic (large title, dim overlay)', 'voltcore' ),
				'minimal'     => __( 'Minimal (no overlay, no eyebrow)', 'voltcore' ),
			),
		) );
		$rep->add_control( 'video_url', array(
			'label'     => __( 'Video URL (mp4/webm)', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::TEXT,
			'condition' => array( 'media_type' => 'video' ),
		) );
		$rep->add_control( 'video_poster', array(
			'label'     => __( 'Video poster image', 'voltcore' ),
			'type'      => \Elementor\Controls_Manager::MEDIA,
			'condition' => array( 'media_type' => 'video' ),
		) );

		$rep->add_control( 'theme', array(
			'label'   => __( 'Text colour', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'dark',
			'options' => array(
				'dark'  => __( 'Light text on dark image', 'voltcore' ),
				'light' => __( 'Dark text on light image', 'voltcore' ),
			),
		) );

		$rep->add_control( 'eyebrow',  array( 'label' => __( 'Eyebrow', 'voltcore' ),  'type' => \Elementor\Controls_Manager::TEXT ) );
		$rep->add_control( 'title',    array( 'label' => __( 'Title', 'voltcore' ),    'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model S' ) );
		$rep->add_control( 'subtitle', array( 'label' => __( 'Subtitle', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
		$rep->add_control( 'price',    array( 'label' => __( 'Price line', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT ) );

		$rep->add_control( 'btn1_label', array( 'label' => __( 'Button 1 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Custom Order' ) );
		$rep->add_control( 'btn1_url',   array( 'label' => __( 'Button 1 URL',   'voltcore' ), 'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );
		$rep->add_control( 'btn2_label', array( 'label' => __( 'Button 2 label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Demo Drive' ) );
		$rep->add_control( 'btn2_url',   array( 'label' => __( 'Button 2 URL',   'voltcore' ), 'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );

		$rep->add_control( 'legal', array(
			'label' => __( 'Footnote / legal', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
		) );

		$this->add_control( 'panels', array(
			'label'       => __( 'Panels', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'title' => 'Model S',     'subtitle' => 'Plaid · 1,020 hp',          'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-s.svg' ),    'theme' => 'light' ),
				array( 'title' => 'Model 3',     'subtitle' => 'Lease starting at $349/mo', 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-3.svg' ),    'theme' => 'light' ),
				array( 'title' => 'Model X',     'subtitle' => 'Up to 348 mi range',         'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-x.svg' ),    'theme' => 'light' ),
				array( 'title' => 'Model Y',     'subtitle' => 'Most popular SUV',           'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-y.svg' ),    'theme' => 'light' ),
				array( 'title' => 'Cybertruck',  'subtitle' => 'Built for any planet',       'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/cybertruck.svg' ), 'theme' => 'dark', 'preset' => 'cinematic' ),
				array( 'title' => 'Solar Panels','subtitle' => 'Power your home with the sun','image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/solar-panels.svg' ), 'theme' => 'light' ),
				array( 'title' => 'Powerwall',   'subtitle' => 'Energy storage for the home', 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/powerwall.svg' ),  'theme' => 'light' ),
				array( 'title' => 'Accessories', 'subtitle' => 'For Tesla owners',            'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/wallconnector.svg' ), 'theme' => 'light' ),
			),
		) );

		$this->add_control( 'snap', array(
			'label'   => __( 'Scroll-snap between panels', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => 'yes',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$panels = is_array( $s['panels'] ?? null ) ? $s['panels'] : array();
		$wrap_class = 'vc-vpanels' . ( $s['snap'] === 'yes' ? '' : ' vc-vpanels--no-snap' );
		?>
		<div class="<?php echo esc_attr( $wrap_class ); ?>">
		<?php foreach ( $panels as $p ) :
			$theme  = $p['theme'] === 'light' ? 'vc-vpanel--white' : 'vc-vpanel--dark';
			$type   = $p['media_type'];
			$preset = ! empty( $p['preset'] ) ? 'vc-vpanel--' . sanitize_html_class( $p['preset'] ) : 'vc-vpanel--centered';
		?>
			<section class="vc-vpanel <?php echo esc_attr( $theme . ' ' . $preset ); ?>">
				<div class="vc-vpanel__media">
					<?php if ( $type === 'video' && ! empty( $p['video_url'] ) ) : ?>
						<video autoplay muted loop playsinline preload="metadata"
							<?php if ( ! empty( $p['video_poster']['url'] ) ) : ?>poster="<?php echo esc_url( $p['video_poster']['url'] ); ?>"<?php endif; ?>>
							<source src="<?php echo esc_url( $p['video_url'] ); ?>">
						</video>
					<?php elseif ( ! empty( $p['image']['url'] ) ) : ?>
						<img src="<?php echo esc_url( $p['image']['url'] ); ?>" alt="<?php echo esc_attr( $p['title'] ); ?>" loading="lazy">
					<?php endif; ?>
				</div>
				<div class="vc-vpanel__content">
					<?php if ( ! empty( $p['eyebrow'] ) ) : ?><span class="vc-vpanel__eyebrow"><?php echo esc_html( $p['eyebrow'] ); ?></span><?php endif; ?>
					<?php if ( ! empty( $p['title'] ) ) : ?><h2 class="vc-vpanel__title"><?php echo esc_html( $p['title'] ); ?></h2><?php endif; ?>
					<?php if ( ! empty( $p['subtitle'] ) ) : ?><p class="vc-vpanel__subtitle"><?php echo wp_kses_post( $p['subtitle'] ); ?></p><?php endif; ?>
					<?php if ( ! empty( $p['price'] ) ) : ?><p class="vc-vpanel__price"><?php echo esc_html( $p['price'] ); ?></p><?php endif; ?>
				</div>
				<div class="vc-vpanel__actions">
					<?php if ( ! empty( $p['btn1_label'] ) ) : ?>
						<a class="btn btn--light" href="<?php echo esc_url( $p['btn1_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $p['btn1_label'] ); ?></a>
					<?php endif; ?>
					<?php if ( ! empty( $p['btn2_label'] ) ) : ?>
						<a class="btn <?php echo $p['theme'] === 'light' ? 'btn--ghost-dark' : 'btn--ghost'; ?>" href="<?php echo esc_url( $p['btn2_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $p['btn2_label'] ); ?></a>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $p['legal'] ) ) : ?>
					<div class="vc-vpanel__legal"><?php echo wp_kses_post( $p['legal'] ); ?></div>
				<?php endif; ?>
			</section>
		<?php endforeach; ?>
		</div>
		<?php
	}
}
