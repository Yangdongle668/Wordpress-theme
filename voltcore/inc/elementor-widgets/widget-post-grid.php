<?php
/**
 * VoltCore Post Grid Elementor widget — latest blog posts with filter.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Post_Grid extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-post-grid'; }
	public function get_title() { return __( 'VoltCore Post Grid', 'voltcore' ); }
	public function get_icon() { return 'eicon-posts-grid'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'heading', array(
			'label'   => __( 'Section Heading', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Latest from the Blog',
		) );

		$this->add_control( 'kicker', array(
			'label'   => __( 'Kicker (subtitle)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '',
		) );

		$this->add_control( 'posts_per_page', array(
			'label'   => __( 'Number of posts', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::NUMBER,
			'default' => 3,
			'min'     => 1,
			'max'     => 12,
		) );

		$this->add_control( 'category', array(
			'label'       => __( 'Category slug (optional)', 'voltcore' ),
			'type'        => \Elementor\Controls_Manager::TEXT,
			'default'     => '',
			'description' => __( 'Leave blank for all categories. Example: engineering', 'voltcore' ),
		) );

		$this->add_control( 'columns', array(
			'label'   => __( 'Columns', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => '3',
			'options' => array( '2' => '2', '3' => '3' ),
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		$args = array(
			'post_type'           => 'post',
			'posts_per_page'      => max( 1, (int) $s['posts_per_page'] ),
			'ignore_sticky_posts' => true,
		);
		if ( ! empty( $s['category'] ) ) {
			$args['category_name'] = sanitize_title( $s['category'] );
		}

		$q = new WP_Query( $args );
		if ( ! $q->have_posts() ) {
			echo '<p>' . esc_html__( 'No posts found.', 'voltcore' ) . '</p>';
			return;
		}

		$grid_class = 'post-grid' . ( $s['columns'] === '2' ? ' post-grid--blog' : '' );
		?>
		<section class="section section--light latest-posts">
			<?php if ( $s['heading'] || $s['kicker'] ) : ?>
			<header class="section__head" data-fade>
				<?php if ( $s['heading'] ) : ?><h2 class="section__title"><?php echo esc_html( $s['heading'] ); ?></h2><?php endif; ?>
				<?php if ( $s['kicker'] ) : ?><p class="section__kicker"><?php echo esc_html( $s['kicker'] ); ?></p><?php endif; ?>
			</header>
			<?php endif; ?>
			<div class="<?php echo esc_attr( $grid_class ); ?>">
				<?php $i = 0; while ( $q->have_posts() ) : $q->the_post(); $i++; ?>
					<article class="post-card" data-fade data-fade-delay="<?php echo esc_attr( $i * 80 ); ?>">
						<a class="post-card__media" href="<?php the_permalink(); ?>">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'voltcore-card' ); ?>
							<?php else : ?>
								<span class="post-card__placeholder" aria-hidden="true"></span>
							<?php endif; ?>
						</a>
						<div class="post-card__body">
							<?php voltcore_entry_categories(); ?>
							<h3 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p class="post-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>
							<div class="post-card__meta"><?php voltcore_posted_on(); ?></div>
						</div>
					</article>
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		</section>
		<?php
	}
}
