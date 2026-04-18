<?php
/**
 * VoltCore Cookie Banner — bottom banner with accept / decline /
 * customise buttons. Preference is stored in localStorage.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Cookie_Banner extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-cookie-banner'; }
	public function get_title() { return __( 'VoltCore Cookie Banner', 'voltcore' ); }
	public function get_icon() { return 'eicon-lock-user'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'cookie', 'gdpr', 'consent' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'message', array(
			'label' => 'Message', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'We use cookies to improve your experience, analyse traffic and personalise content. See our cookie policy.',
		) );
		$this->add_control( 'accept_label',  array( 'label' => 'Accept label',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Accept all' ) );
		$this->add_control( 'decline_label', array( 'label' => 'Decline label', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Reject non-essential' ) );
		$this->add_control( 'policy_label',  array( 'label' => 'Policy label',  'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Cookie policy' ) );
		$this->add_control( 'policy_url',    array( 'label' => 'Policy URL',    'type' => \Elementor\Controls_Manager::URL,  'default' => array( 'url' => '/cookies/' ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<div class="vc-cookie" data-vc-cookie role="region" aria-label="<?php esc_attr_e( 'Cookie consent', 'voltcore' ); ?>" hidden>
			<div class="vc-cookie__inner">
				<p class="vc-cookie__msg">
					<?php echo esc_html( $s['message'] ); ?>
					<a class="vc-cookie__policy" href="<?php echo esc_url( $s['policy_url']['url'] ?? '#' ); ?>"><?php echo esc_html( $s['policy_label'] ); ?></a>
				</p>
				<div class="vc-cookie__actions">
					<button type="button" class="btn btn--ghost" data-vc-cookie-decide="decline"><?php echo esc_html( $s['decline_label'] ); ?></button>
					<button type="button" class="btn btn--dark"  data-vc-cookie-decide="accept"><?php echo esc_html( $s['accept_label'] ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}
}
