<?php
/**
 * VoltCore Account Drawer — right-side slide-in drawer with login/sign-up
 * tabs. Opens on any element with [data-vc-account-open]. Form posts to
 * wp-login.php / wp-login.php?action=register by default so the drawer is
 * useful on a real install even without a separate account system.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Account_Drawer extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-account-drawer'; }
	public function get_title() { return __( 'VoltCore Account Drawer', 'voltcore' ); }
	public function get_icon() { return 'eicon-user-circle-o'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'account', 'login', 'signup', 'drawer', 'modal' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Sign in' ) );
		$this->add_control( 'intro', array( 'label' => 'Intro', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Sign in to manage your order, book a demo drive and access vehicle services.' ) );
		$this->add_control( 'login_url', array( 'label' => 'Login action URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => wp_login_url() ) ) );
		$this->add_control( 'register_url', array( 'label' => 'Register action URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => wp_registration_url() ) ) );
		$this->add_control( 'lost_url', array( 'label' => 'Lost password URL', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => wp_lostpassword_url() ) ) );
		$this->add_control( 'logged_url', array( 'label' => 'Account home (when logged in)', 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => admin_url( 'profile.php' ) ) ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$logged = is_user_logged_in();
		$user   = wp_get_current_user();
		?>
		<div class="vc-account" data-vc-account hidden role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Account', 'voltcore' ); ?>">
			<span class="vc-account__scrim" data-vc-account-close></span>
			<aside class="vc-account__panel">
				<button type="button" class="vc-account__close" data-vc-account-close aria-label="<?php esc_attr_e( 'Close', 'voltcore' ); ?>">×</button>
				<h2 class="vc-account__heading"><?php echo esc_html( $s['heading'] ); ?></h2>

				<?php if ( $logged ) : ?>
					<p class="vc-account__intro"><?php printf( esc_html__( 'Signed in as %s.', 'voltcore' ), '<strong>' . esc_html( $user->display_name ) . '</strong>' ); ?></p>
					<div class="vc-account__actions">
						<a class="btn btn--dark" href="<?php echo esc_url( $s['logged_url']['url'] ?? admin_url() ); ?>"><?php esc_html_e( 'Go to account', 'voltcore' ); ?></a>
						<a class="btn btn--ghost" href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>"><?php esc_html_e( 'Sign out', 'voltcore' ); ?></a>
					</div>
				<?php else : ?>
					<p class="vc-account__intro"><?php echo esc_html( $s['intro'] ); ?></p>

					<div class="vc-account__tabs" role="tablist">
						<button type="button" role="tab" aria-selected="true"  data-vc-tab="login"><?php esc_html_e( 'Sign in', 'voltcore' ); ?></button>
						<button type="button" role="tab" aria-selected="false" data-vc-tab="register"><?php esc_html_e( 'Create account', 'voltcore' ); ?></button>
					</div>

					<form class="vc-account__form is-active" data-vc-tab-panel="login" action="<?php echo esc_url( $s['login_url']['url'] ?? wp_login_url() ); ?>" method="post">
						<label><span><?php esc_html_e( 'Email or username', 'voltcore' ); ?></span><input type="text" name="log" autocomplete="username" required></label>
						<label><span><?php esc_html_e( 'Password', 'voltcore' ); ?></span><input type="password" name="pwd" autocomplete="current-password" required></label>
						<label class="vc-account__check"><input type="checkbox" name="rememberme" value="forever"> <?php esc_html_e( 'Remember me', 'voltcore' ); ?></label>
						<button class="btn btn--dark" type="submit"><?php esc_html_e( 'Sign in', 'voltcore' ); ?></button>
						<a class="vc-account__alt" href="<?php echo esc_url( $s['lost_url']['url'] ?? wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'voltcore' ); ?></a>
					</form>

					<form class="vc-account__form" data-vc-tab-panel="register" action="<?php echo esc_url( $s['register_url']['url'] ?? wp_registration_url() ); ?>" method="post">
						<label><span><?php esc_html_e( 'Name', 'voltcore' ); ?></span><input type="text" name="user_name" autocomplete="name" required></label>
						<label><span><?php esc_html_e( 'Email', 'voltcore' ); ?></span><input type="email" name="user_email" autocomplete="email" required></label>
						<label class="vc-account__check"><input type="checkbox" required> <?php esc_html_e( 'I agree to the terms and privacy policy', 'voltcore' ); ?></label>
						<button class="btn btn--dark" type="submit"><?php esc_html_e( 'Create account', 'voltcore' ); ?></button>
					</form>
				<?php endif; ?>
			</aside>
		</div>
		<?php
	}
}
