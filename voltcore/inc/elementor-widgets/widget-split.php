<?php
/**
 * VoltCore Split Elementor widget — image on one side, text on the other.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Split extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-split'; }
	public function get_title() { return __( 'VoltCore Split Story', 'voltcore' ); }
	public function get_icon() { return 'eicon-image-rollover'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );

		$this->add_control( 'image', array(
			'label'   => __( 'Image', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::MEDIA,
			'default' => array( 'url' => VOLTCORE_URI . '/assets/images/story-1.jpg' ),
		) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => '01 / 02',
		) );

		$this->add_control( 'title', array(
			'label'   => __( 'Title', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXT,
			'default' => 'Built in-house',
		) );

		$this->add_control( 'body', array(
			'label'   => __( 'Body Text', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'We own the full stack — from cell chemistry to battery management software.',
		) );

		$this->add_control( 'flip', array(
			'label'   => __( 'Flip (image on right)', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SWITCHER,
			'default' => '',
		) );

		$this->add_control( 'btn_label', array( 'label' => __( 'Button Label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '' ) );
		$this->add_control( 'btn_url',   array( 'label' => __( 'Button URL', 'voltcore' ),   'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '#' ) ) );

		$this->end_controls_section();
	}

	protected function render() {
		$s   = $this->get_settings_for_display();
		$img = ! empty( $s['image']['url'] ) ? esc_url( $s['image']['url'] ) : '';
		$flip = ! empty( $s['flip'] ) ? ' split--flip' : '';
		?>
		<section class="split<?php echo esc_attr( $flip ); ?>">
			<div class="split__media" data-fade>
				<figure class="split__image" <?php if ( $img ) : ?>style="background-image:url('<?php echo $img; ?>');"<?php endif; ?> role="img" aria-label="<?php echo esc_attr( $s['title'] ); ?>">
					<?php if ( $img ) : ?><img src="<?php echo $img; ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" class="screen-reader-text"><?php endif; ?>
				</figure>
			</div>
			<div class="split__body" data-fade data-fade-delay="150">
				<?php if ( $s['eyebrow'] ) : ?><span class="eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></span><?php endif; ?>
				<h2 class="split__title"><?php echo esc_html( $s['title'] ); ?></h2>
				<p class="split__desc"><?php echo esc_html( $s['body'] ); ?></p>
				<?php if ( ! empty( $s['btn_label'] ) ) : ?>
					<p><a class="btn btn--dark" href="<?php echo esc_url( $s['btn_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['btn_label'] ); ?></a></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
