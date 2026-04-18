<?php
/**
 * VoltCore Test Drive — multi-step demo-drive booking form.
 *
 * Steps: (1) vehicle, (2) location, (3) date/time, (4) contact.
 * Submissions are sent to admin-ajax (action=voltcore_test_drive) and
 * emailed to the site admin. A confirmation step is shown inline.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Test_Drive extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-test-drive'; }
	public function get_title() { return __( 'VoltCore Test Drive', 'voltcore' ); }
	public function get_icon() { return 'eicon-form-horizontal'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'test drive', 'demo', 'booking', 'form' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Book a Demo Drive' ) );
		$this->add_control( 'intro',   array( 'label' => 'Intro',   'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Four steps. You\'ll hear back within one business day.' ) );
		$this->add_control( 'vehicles', array(
			'label' => 'Vehicles (one per line: Label|slug)', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Model V|model-v\nModel V Performance|model-v-perf\nPowerwall Demo|powerwall",
		) );
		$this->add_control( 'locations', array(
			'label' => 'Locations (one per line: Label|slug)', 'type' => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Reno, NV|reno\nAustin, TX|austin\nBerlin, DE|berlin\nSingapore|sg",
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$vehicles = $this->parse_pairs( $s['vehicles'] );
		$locs     = $this->parse_pairs( $s['locations'] );
		$action   = esc_url( admin_url( 'admin-ajax.php' ) );
		$nonce    = wp_create_nonce( 'voltcore_test_drive' );
		?>
		<section class="vc-td" data-vc-test-drive>
			<header class="vc-td__head">
				<h2><?php echo esc_html( $s['heading'] ); ?></h2>
				<?php if ( ! empty( $s['intro'] ) ) : ?><p><?php echo esc_html( $s['intro'] ); ?></p><?php endif; ?>
			</header>
			<div class="vc-td__steps" role="tablist">
				<?php foreach ( array( 'Vehicle', 'Location', 'Date & time', 'Contact' ) as $i => $lbl ) : ?>
					<span class="vc-td__step<?php echo $i === 0 ? ' is-active' : ''; ?>" data-vc-td-step="<?php echo $i; ?>"><?php echo ( $i + 1 ) . '. ' . esc_html( $lbl ); ?></span>
				<?php endforeach; ?>
			</div>

			<form class="vc-td__form" action="<?php echo $action; ?>" method="post" data-vc-td-form>
				<input type="hidden" name="action" value="voltcore_test_drive">
				<input type="hidden" name="_vc_nonce" value="<?php echo esc_attr( $nonce ); ?>">

				<fieldset class="vc-td__panel is-active" data-vc-td-panel="0">
					<legend><?php esc_html_e( 'Which vehicle?', 'voltcore' ); ?></legend>
					<div class="vc-td__opts">
						<?php foreach ( $vehicles as $v ) : ?>
							<label class="vc-td__opt">
								<input type="radio" name="vehicle" value="<?php echo esc_attr( $v[1] ); ?>" required>
								<span><?php echo esc_html( $v[0] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="vc-td__nav">
						<span></span>
						<button type="button" class="btn btn--dark" data-vc-td-next><?php esc_html_e( 'Next', 'voltcore' ); ?></button>
					</div>
				</fieldset>

				<fieldset class="vc-td__panel" data-vc-td-panel="1">
					<legend><?php esc_html_e( 'Where?', 'voltcore' ); ?></legend>
					<div class="vc-td__opts vc-td__opts--wide">
						<?php foreach ( $locs as $l ) : ?>
							<label class="vc-td__opt">
								<input type="radio" name="location" value="<?php echo esc_attr( $l[1] ); ?>" required>
								<span><?php echo esc_html( $l[0] ); ?></span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="vc-td__nav">
						<button type="button" class="btn btn--ghost" data-vc-td-prev><?php esc_html_e( 'Back', 'voltcore' ); ?></button>
						<button type="button" class="btn btn--dark" data-vc-td-next><?php esc_html_e( 'Next', 'voltcore' ); ?></button>
					</div>
				</fieldset>

				<fieldset class="vc-td__panel" data-vc-td-panel="2">
					<legend><?php esc_html_e( 'When?', 'voltcore' ); ?></legend>
					<label class="vc-td__field"><span><?php esc_html_e( 'Date', 'voltcore' ); ?></span><input type="date" name="date" required></label>
					<label class="vc-td__field"><span><?php esc_html_e( 'Time', 'voltcore' ); ?></span>
						<select name="time" required>
							<option value="">—</option>
							<?php foreach ( array( '09:00', '11:00', '13:00', '15:00', '17:00' ) as $t ) : ?>
								<option value="<?php echo esc_attr( $t ); ?>"><?php echo esc_html( $t ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<div class="vc-td__nav">
						<button type="button" class="btn btn--ghost" data-vc-td-prev><?php esc_html_e( 'Back', 'voltcore' ); ?></button>
						<button type="button" class="btn btn--dark" data-vc-td-next><?php esc_html_e( 'Next', 'voltcore' ); ?></button>
					</div>
				</fieldset>

				<fieldset class="vc-td__panel" data-vc-td-panel="3">
					<legend><?php esc_html_e( 'Your details', 'voltcore' ); ?></legend>
					<label class="vc-td__field"><span><?php esc_html_e( 'Full name', 'voltcore' ); ?></span><input type="text" name="name" autocomplete="name" required></label>
					<label class="vc-td__field"><span><?php esc_html_e( 'Email', 'voltcore' ); ?></span><input type="email" name="email" autocomplete="email" required></label>
					<label class="vc-td__field"><span><?php esc_html_e( 'Phone', 'voltcore' ); ?></span><input type="tel" name="phone" autocomplete="tel" required></label>
					<label class="vc-td__field vc-td__field--wide"><span><?php esc_html_e( 'Notes', 'voltcore' ); ?></span><textarea name="notes" rows="3"></textarea></label>
					<label class="vc-td__check"><input type="checkbox" required> <?php esc_html_e( 'I agree to be contacted about my demo drive.', 'voltcore' ); ?></label>
					<div class="vc-td__nav">
						<button type="button" class="btn btn--ghost" data-vc-td-prev><?php esc_html_e( 'Back', 'voltcore' ); ?></button>
						<button type="submit" class="btn btn--dark"><?php esc_html_e( 'Request demo drive', 'voltcore' ); ?></button>
					</div>
					<p class="vc-td__error" data-vc-td-error hidden></p>
				</fieldset>
			</form>

			<div class="vc-td__done" data-vc-td-done hidden>
				<h3><?php esc_html_e( 'Request received.', 'voltcore' ); ?></h3>
				<p><?php esc_html_e( "We'll be in touch within one business day to confirm your demo drive.", 'voltcore' ); ?></p>
			</div>
		</section>
		<?php
	}

	private function parse_pairs( $text ) {
		$out = array();
		foreach ( preg_split( '/\r?\n/', (string) $text ) as $line ) {
			$line = trim( $line );
			if ( ! $line ) continue;
			$parts = array_map( 'trim', explode( '|', $line, 2 ) );
			$out[] = array( $parts[0], $parts[1] ?? sanitize_title( $parts[0] ) );
		}
		return $out;
	}
}
