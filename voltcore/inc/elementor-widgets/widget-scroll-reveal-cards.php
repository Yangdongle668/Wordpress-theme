<?php
/**
 * VoltCore Scroll Reveal Cards — staggered reveal card grid.
 *
 * Renders 2-4 linkable image cards with eyebrow, title and small CTA.
 * Each card fades + translates up on scroll with a stagger delay driven
 * by its index. Used on homepage / product pages to showcase lineup.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Scroll_Reveal_Cards extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-scroll-reveal-cards'; }
	public function get_title() { return __( 'VoltCore Reveal Cards', 'voltcore' ); }
	public function get_icon() { return 'eicon-posts-grid'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'cards', 'reveal', 'scroll', 'grid' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Section heading', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Our range',
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
		) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'image', array( 'label' => 'Image', 'type' => \Elementor\Controls_Manager::MEDIA ) );
		$rep->add_control( 'eyebrow', array( 'label' => 'Eyebrow', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'New' ) );
		$rep->add_control( 'title', array( 'label' => 'Title', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model V' ) );
		$rep->add_control( 'sub', array( 'label' => 'Sub', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'From $39,990' ) );
		$rep->add_control( 'cta_label', array( 'label' => 'CTA label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Explore' ) );
		$rep->add_control( 'cta_url', array( 'label' => 'CTA URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '#' ) ) );

		$this->add_control( 'cards', array(
			'label'       => __( 'Cards', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $rep->get_controls(),
			'title_field' => '{{{ title }}}',
			'default'     => array(
				array( 'title' => 'Model V',   'sub' => 'From $39,990', 'eyebrow' => 'New',  'cta_label' => 'Explore', 'cta_url' => array( 'url' => '#' ) ),
				array( 'title' => 'Powerwall', 'sub' => 'From $6,500',  'eyebrow' => '',     'cta_label' => 'Explore', 'cta_url' => array( 'url' => '#' ) ),
				array( 'title' => 'Megapack',  'sub' => 'Request quote','eyebrow' => 'Fleet','cta_label' => 'Explore', 'cta_url' => array( 'url' => '#' ) ),
			),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$cards = $s['cards'] ?? array();
		if ( empty( $cards ) ) return;
		?>
		<section class="vc-reveal">
			<?php if ( ! empty( $s['heading'] ) ) : ?>
				<h2 class="vc-reveal__heading" data-fade><?php echo esc_html( $s['heading'] ); ?></h2>
			<?php endif; ?>
			<div class="vc-reveal__grid vc-reveal__grid--<?php echo esc_attr( $s['columns'] ); ?>">
				<?php foreach ( $cards as $i => $c ) : $img = $c['image']['url'] ?? ''; ?>
					<a class="vc-reveal__card" href="<?php echo esc_url( $c['cta_url']['url'] ?? '#' ); ?>"
					   data-fade data-fade-delay="<?php echo (int) ( $i * 120 ); ?>">
						<?php if ( $img ) : ?>
							<span class="vc-reveal__img" style="background-image:url('<?php echo esc_url( $img ); ?>');"></span>
						<?php else : ?>
							<span class="vc-reveal__img vc-reveal__img--placeholder"></span>
						<?php endif; ?>
						<span class="vc-reveal__meta">
							<?php if ( ! empty( $c['eyebrow'] ) ) : ?><span class="vc-reveal__eyebrow"><?php echo esc_html( $c['eyebrow'] ); ?></span><?php endif; ?>
							<span class="vc-reveal__title"><?php echo esc_html( $c['title'] ); ?></span>
							<?php if ( ! empty( $c['sub'] ) ) : ?><span class="vc-reveal__sub"><?php echo esc_html( $c['sub'] ); ?></span><?php endif; ?>
							<?php if ( ! empty( $c['cta_label'] ) ) : ?><span class="vc-reveal__cta"><?php echo esc_html( $c['cta_label'] ); ?> <span aria-hidden="true">→</span></span><?php endif; ?>
						</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
