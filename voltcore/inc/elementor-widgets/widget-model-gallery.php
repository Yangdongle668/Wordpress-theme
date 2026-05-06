<?php
/**
 * VoltCore Model Gallery — pinned image + scroll-triggered features.
 *
 * Tesla model pages use a left-side sticky image while feature copy
 * scrolls past on the right. As each feature block enters the
 * viewport, the matching media swaps in. This widget reproduces that
 * pattern with full Elementor controls.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Model_Gallery extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-model-gallery'; }
	public function get_title() { return __( 'VoltCore Model Gallery', 'voltcore' ); }
	public function get_icon() { return 'eicon-image-rollover'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'model', 'gallery', 'sticky', 'features' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_features', array( 'label' => __( 'Features', 'voltcore' ) ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'image', array(
			'label'   => __( 'Image', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-pearl.svg' ),
		) );
		$rep->add_control( 'title', array( 'label' => __( 'Title', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Acceleration' ) );
		$rep->add_control( 'desc',  array( 'label' => __( 'Description', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => '0–60 mph in 1.99 sec with Plaid drive system. The fastest production car ever built.' ) );
		$rep->add_control( 'stats', array(
			'label'   => __( 'Stats (one per line: Label|Value)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "0–60 mph|1.99 s\nPeak power|1,020 hp\n¼ mile|9.23 s",
		) );

		$this->add_control( 'features', array(
			'label'       => __( 'Feature blocks', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'title' => 'Acceleration', 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-red.svg' ) ),
				array( 'title' => 'Range',        'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-blue.svg' ) ),
				array( 'title' => 'Interior',     'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/paint-pearl.svg' ) ),
			),
		) );

		$this->add_control( 'preset', array(
			'label'   => __( 'Layout preset', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'image-left',
			'options' => array(
				'image-left'  => __( 'Image left, features right', 'voltcore' ),
				'image-right' => __( 'Image right, features left (mirror)', 'voltcore' ),
				'compact'     => __( 'Compact (smaller image, tighter blocks)', 'voltcore' ),
				'editorial'   => __( 'Editorial (full-bleed image, features over)', 'voltcore' ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$features = is_array( $s['features'] ?? null ) ? $s['features'] : array();
		?>
		<section class="vc-modelgal vc-modelgal--<?php echo esc_attr( $s['preset'] ?? 'image-left' ); ?>" data-vc-modelgal>
			<div class="vc-modelgal__inner">
				<div class="vc-modelgal__media">
					<div class="vc-modelgal__media-stack">
					<?php foreach ( $features as $i => $f ) : if ( empty( $f['image']['url'] ) ) continue; ?>
						<img src="<?php echo esc_url( $f['image']['url'] ); ?>" alt="<?php echo esc_attr( $f['title'] ); ?>"
							class="<?php echo $i === 0 ? 'is-active' : ''; ?>" loading="lazy">
					<?php endforeach; ?>
					</div>
				</div>
				<div class="vc-modelgal__features">
				<?php foreach ( $features as $f ) :
					$stats = array();
					if ( ! empty( $f['stats'] ) ) {
						foreach ( preg_split( '/\r?\n/', (string) $f['stats'] ) as $ln ) {
							$pp = array_map( 'trim', explode( '|', $ln ) );
							if ( count( $pp ) >= 2 ) $stats[] = $pp;
						}
					}
				?>
					<div class="vc-modelgal__feature" data-fade>
						<h3><?php echo esc_html( $f['title'] ); ?></h3>
						<?php if ( ! empty( $f['desc'] ) ) : ?><p><?php echo wp_kses_post( $f['desc'] ); ?></p><?php endif; ?>
						<?php if ( ! empty( $stats ) ) : ?>
							<ul>
							<?php foreach ( $stats as $st ) : ?>
								<li><span><?php echo esc_html( $st[0] ); ?></span><b><?php echo esc_html( $st[1] ); ?></b></li>
							<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
