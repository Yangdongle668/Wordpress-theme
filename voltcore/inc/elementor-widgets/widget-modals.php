<?php
/**
 * VoltCore Modals — Account (sign-in) + Region/Language picker.
 *
 * Drop this widget once in the page (typically in the footer area or
 * an Elementor Pro footer template). Buttons throughout the page that
 * have data-vc-modal-open="account" or "region" will open the
 * relevant dialog. The Navbar widget already wires its account / globe
 * icons to these IDs.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Modals extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-modals'; }
	public function get_title() { return __( 'VoltCore Modals (Account + Region)', 'voltcore' ); }
	public function get_icon() { return 'eicon-lock-user'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'modal', 'login', 'region', 'language' ); }

	protected function register_controls() {

		$this->start_controls_section( 'section_account', array( 'label' => __( 'Account modal', 'voltcore' ) ) );
		$this->add_control( 'show_account', array( 'label' => __( 'Show account modal', 'voltcore' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'account_title', array( 'label' => __( 'Title', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Sign In' ) );
		$this->add_control( 'account_sub',   array( 'label' => __( 'Subtitle', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Manage your account' ) );
		$this->add_control( 'account_action', array( 'label' => __( 'Form action URL', 'voltcore' ), 'type' => \Elementor\Controls_Manager::URL, 'default' => array( 'url' => wp_login_url() ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'section_region', array( 'label' => __( 'Region modal', 'voltcore' ) ) );
		$this->add_control( 'show_region', array( 'label' => __( 'Show region modal', 'voltcore' ), 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes' ) );
		$this->add_control( 'region_title', array( 'label' => __( 'Title', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Choose your country' ) );
		$this->add_control( 'regions', array(
			'label' => __( 'Regions (one per line: Label|URL)', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "United States|/\nCanada|/ca\nMexico|/mx\nUnited Kingdom|/en_GB\nGermany|/de_DE\nFrance|/fr_FR\nItaly|/it_IT\nSpain|/es_ES\nNetherlands|/nl_NL\nNorway|/no_NO\nSweden|/sv_SE\nChina|/zh_CN\nJapan|/ja_JP\nKorea|/ko_KR\nAustralia|/en_AU",
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$action = $s['account_action']['url'] ?? wp_login_url();

		$regions = array();
		foreach ( preg_split( '/\r?\n/', (string) ( $s['regions'] ?? '' ) ) as $ln ) {
			$pp = array_map( 'trim', explode( '|', $ln ) );
			if ( count( $pp ) >= 2 ) $regions[] = $pp;
		}
		?>
		<?php if ( $s['show_account'] === 'yes' ) : ?>
		<div class="vc-modal" data-vc-modal="account" role="dialog" aria-modal="true" aria-labelledby="vc-modal-account-title">
			<div class="vc-modal__dialog">
				<button type="button" class="vc-modal__close" data-vc-modal-close aria-label="<?php esc_attr_e( 'Close', 'voltcore' ); ?>">&times;</button>
				<h2 class="vc-modal__title" id="vc-modal-account-title"><?php echo esc_html( $s['account_title'] ); ?></h2>
				<p class="vc-modal__sub"><?php echo esc_html( $s['account_sub'] ); ?></p>
				<form method="post" action="<?php echo esc_url( $action ); ?>">
					<div class="vc-modal__field"><label><?php esc_html_e( 'Email', 'voltcore' ); ?></label><input type="email" name="log" required autocomplete="email"></div>
					<div class="vc-modal__field"><label><?php esc_html_e( 'Password', 'voltcore' ); ?></label><input type="password" name="pwd" required autocomplete="current-password"></div>
					<button class="btn btn--dark vc-modal__action" type="submit"><?php esc_html_e( 'Sign In', 'voltcore' ); ?></button>
				</form>
				<p class="vc-modal__alt"><?php esc_html_e( 'New here?', 'voltcore' ); ?> <a href="<?php echo esc_url( wp_registration_url() ); ?>"><?php esc_html_e( 'Create account', 'voltcore' ); ?></a></p>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( $s['show_region'] === 'yes' ) : ?>
		<div class="vc-modal" data-vc-modal="region" role="dialog" aria-modal="true" aria-labelledby="vc-modal-region-title">
			<div class="vc-modal__dialog">
				<button type="button" class="vc-modal__close" data-vc-modal-close aria-label="<?php esc_attr_e( 'Close', 'voltcore' ); ?>">&times;</button>
				<h2 class="vc-modal__title" id="vc-modal-region-title"><?php echo esc_html( $s['region_title'] ); ?></h2>
				<ul class="vc-modal__list">
				<?php foreach ( $regions as $r ) : ?>
					<li><a href="<?php echo esc_url( $r[1] ); ?>"><?php echo esc_html( $r[0] ); ?></a></li>
				<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php endif; ?>
		<?php
	}
}
