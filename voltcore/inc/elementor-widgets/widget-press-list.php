<?php
/**
 * VoltCore Press Releases List — tabular list of latest press posts.
 * Pulls from a category (default: "press"). Read-only widget.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Press_List extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-press-list'; }
	public function get_title() { return __( 'VoltCore Press List', 'voltcore' ); }
	public function get_icon() { return 'eicon-post-list'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading',  array( 'label' => __( 'Heading',  'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Latest announcements' ) );
		$this->add_control( 'category', array( 'label' => __( 'Category slug', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'press' ) );
		$this->add_control( 'count',    array( 'label' => __( 'How many', 'voltcore' ), 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 6, 'min' => 1, 'max' => 20 ) );
		$this->add_control( 'see_all_url', array( 'label' => __( '"See all" URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => '/blog/' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$q = new WP_Query( array(
			'post_type'           => 'post',
			'posts_per_page'      => max( 1, (int) $s['count'] ),
			'category_name'       => sanitize_title( $s['category'] ),
			'ignore_sticky_posts' => true,
		) );
		if ( ! $q->have_posts() ) {
			printf( '<p style="color:var(--vc-muted)">%s</p>', esc_html__( 'No posts in that category yet.', 'voltcore' ) );
			return;
		}
		?>
		<section class="press-releases" style="padding: 0; background: transparent; border: 0;">
			<?php if ( $s['heading'] ) : ?>
				<header class="section__head" data-fade>
					<h2 class="section__title"><?php echo esc_html( $s['heading'] ); ?></h2>
					<?php if ( ! empty( $s['see_all_url']['url'] ) ) : ?>
						<a class="section__link" href="<?php echo esc_url( $s['see_all_url']['url'] ); ?>"><?php esc_html_e( 'See all →', 'voltcore' ); ?></a>
					<?php endif; ?>
				</header>
			<?php endif; ?>
			<ul class="press-list">
				<?php while ( $q->have_posts() ) : $q->the_post(); ?>
					<li class="press-list__item">
						<time class="press-list__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
						<a class="press-list__title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						<span class="press-list__arrow" aria-hidden="true">→</span>
					</li>
				<?php endwhile; wp_reset_postdata(); ?>
			</ul>
		</section>
		<?php
	}
}
