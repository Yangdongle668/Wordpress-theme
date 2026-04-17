<?php
/**
 * VoltCore Team Grid — portrait + name + role + optional bio + social links.
 * Repeater-driven. Used on About pages.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Team_Grid extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-team-grid'; }
	public function get_title() { return __( 'VoltCore Team Grid', 'voltcore' ); }
	public function get_icon() { return 'eicon-person'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$r = new \Elementor\Repeater();
		$r->add_control( 'image', array( 'label' => __( 'Portrait', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => \Elementor\Utils::get_placeholder_image_src() ) ) );
		$r->add_control( 'name',  array( 'label' => __( 'Name', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Elena Rojas' ) );
		$r->add_control( 'role',  array( 'label' => __( 'Role', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Chief Executive Officer' ) );
		$r->add_control( 'bio',   array( 'label' => __( 'Bio',  'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXTAREA ) );
		$r->add_control( 'linkedin', array( 'label' => __( 'LinkedIn URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL ) );

		$this->add_control( 'team', array(
			'label'       => __( 'Team', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::REPEATER,
			'fields'      => $r->get_controls(),
			'default'     => array(
				array( 'name' => 'Elena Rojas', 'role' => 'Chief Executive Officer' ),
				array( 'name' => 'Mark Chen',   'role' => 'Chief Technology Officer' ),
				array( 'name' => 'Priya Narayan', 'role' => 'VP Engineering' ),
				array( 'name' => 'David Kim',   'role' => 'VP Manufacturing' ),
			),
			'title_field' => '{{{ name }}}',
		) );

		$this->add_responsive_control( 'columns', array(
			'label'   => __( 'Columns', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '4',
			'options' => array( '2' => '2', '3' => '3', '4' => '4' ),
			'selectors' => array( '{{WRAPPER}} .vc-team__grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<section class="vc-team">
			<div class="vc-team__grid">
				<?php foreach ( $s['team'] as $i => $m ) :
					$img = $m['image']['url'] ?? ''; ?>
					<article class="vc-team__member" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
						<div class="vc-team__portrait">
							<?php if ( $img ) : ?>
								<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $m['name'] ); ?>" loading="lazy">
							<?php else : ?>
								<span class="vc-team__placeholder" aria-hidden="true"></span>
							<?php endif; ?>
						</div>
						<h3 class="vc-team__name"><?php echo esc_html( $m['name'] ); ?></h3>
						<p class="vc-team__role"><?php echo esc_html( $m['role'] ); ?></p>
						<?php if ( ! empty( $m['bio'] ) ) : ?><p class="vc-team__bio"><?php echo esc_html( $m['bio'] ); ?></p><?php endif; ?>
						<?php if ( ! empty( $m['linkedin']['url'] ) ) : ?>
							<a class="vc-team__link" href="<?php echo esc_url( $m['linkedin']['url'] ); ?>" rel="nofollow noopener" target="_blank">LinkedIn →</a>
						<?php endif; ?>
					</article>
				<?php endforeach; ?>
			</div>
		</section>
		<?php
	}
}
