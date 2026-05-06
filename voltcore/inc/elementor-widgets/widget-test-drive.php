<?php
/**
 * VoltCore Test Drive — multi-step booking flow.
 *
 * Step 1: pick a model. Step 2: pick a date / location. Step 3:
 * contact details. Step 4: confirmation. Submission posts via WP
 * admin-post.php (or just shows success), and the JS guards forward
 * progress with simple required-field checks.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Test_Drive extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-test-drive'; }
	public function get_title() { return __( 'VoltCore Test Drive', 'voltcore' ); }
	public function get_icon() { return 'eicon-form-horizontal'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'tesla', 'test drive', 'demo', 'booking' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_td', array( 'label' => __( 'Models & locations', 'voltcore' ) ) );

		$rep = new \Elementor\Repeater();
		$rep->add_control( 'name',  array( 'label' => __( 'Model', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Model S' ) );
		$rep->add_control( 'image', array( 'label' => __( 'Image', 'voltcore' ), 'type' => \Elementor\Controls_Manager::MEDIA, 'default' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-s.svg' ) ) );

		$this->add_control( 'models', array(
			'label' => __( 'Models', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::REPEATER,
			'fields' => $rep->get_controls(),
			'title_field' => '{{{ name }}}',
			'default' => array(
				array( 'name' => 'Model S', 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-s.svg' ) ),
				array( 'name' => 'Model 3', 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-3.svg' ) ),
				array( 'name' => 'Model X', 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-x.svg' ) ),
				array( 'name' => 'Model Y', 'image' => array( 'url' => VOLTCORE_URI . '/assets/images/tesla/model-y.svg' ) ),
			),
		) );

		$this->add_control( 'preset', array(
			'label'   => __( 'Style preset', 'voltcore' ),
			'type'    => \Elementor\Controls_Manager::SELECT,
			'default' => 'bar',
			'options' => array(
				'bar'      => __( 'Underline step bar (default)', 'voltcore' ),
				'numbers'  => __( 'Numbered circles', 'voltcore' ),
				'sidebar'  => __( 'Vertical sidebar steps', 'voltcore' ),
				'minimal'  => __( 'Minimal (no step indicator)', 'voltcore' ),
			),
		) );

		$this->add_control( 'locations', array(
			'label' => __( 'Locations (one per line)', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'default' => "Palo Alto · 3500 Deer Creek Rd\nSan Francisco · 999 Van Ness Ave\nFremont · 45500 Fremont Blvd\nLos Angeles · 5800 Wilshire Blvd",
		) );

		$this->add_control( 'success_msg', array(
			'label' => __( 'Success message', 'voltcore' ),
			'type'  => \Elementor\Controls_Manager::TEXTAREA,
			'default' => 'Thanks — your demo drive request is in. A specialist will reach out within 24 hours.',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$models    = is_array( $s['models'] ?? null ) ? $s['models'] : array();
		$locations = array();
		foreach ( preg_split( '/\r?\n/', (string) ( $s['locations'] ?? '' ) ) as $ln ) {
			$ln = trim( $ln );
			if ( $ln !== '' ) $locations[] = $ln;
		}
		?>
		<section class="vc-td vc-td--<?php echo esc_attr( $s['preset'] ?? 'bar' ); ?>" data-vc-td>
			<form>
				<div class="vc-td__steps">
					<div class="vc-td__step is-active">1 · <?php esc_html_e( 'Vehicle', 'voltcore' ); ?></div>
					<div class="vc-td__step">2 · <?php esc_html_e( 'When', 'voltcore' ); ?></div>
					<div class="vc-td__step">3 · <?php esc_html_e( 'Details', 'voltcore' ); ?></div>
					<div class="vc-td__step">4 · <?php esc_html_e( 'Confirm', 'voltcore' ); ?></div>
				</div>

				<div class="vc-td__panel is-active">
					<h2 style="text-align:center;margin:0 0 20px;"><?php esc_html_e( 'Choose a vehicle', 'voltcore' ); ?></h2>
					<div class="vc-td__choices">
					<?php foreach ( $models as $i => $m ) : ?>
						<button type="button" class="vc-td__choice<?php echo $i === 0 ? ' is-active' : ''; ?>" data-target="td_model" data-value="<?php echo esc_attr( $m['name'] ); ?>">
							<?php if ( ! empty( $m['image']['url'] ) ) : ?><img src="<?php echo esc_url( $m['image']['url'] ); ?>" alt=""><?php endif; ?>
							<h4><?php echo esc_html( $m['name'] ); ?></h4>
						</button>
					<?php endforeach; ?>
					</div>
					<input type="hidden" name="td_model" value="<?php echo esc_attr( $models[0]['name'] ?? '' ); ?>">
				</div>

				<div class="vc-td__panel">
					<h2 style="text-align:center;margin:0 0 20px;"><?php esc_html_e( 'When and where', 'voltcore' ); ?></h2>
					<div class="vc-td__form">
						<div><label><?php esc_html_e( 'Date', 'voltcore' ); ?></label><input type="date" name="td_date" required></div>
						<div><label><?php esc_html_e( 'Time', 'voltcore' ); ?></label><input type="time" name="td_time" required></div>
						<div><label><?php esc_html_e( 'Location', 'voltcore' ); ?></label>
							<select name="td_location" required>
							<?php foreach ( $locations as $loc ) : ?>
								<option value="<?php echo esc_attr( $loc ); ?>"><?php echo esc_html( $loc ); ?></option>
							<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>

				<div class="vc-td__panel">
					<h2 style="text-align:center;margin:0 0 20px;"><?php esc_html_e( 'Your details', 'voltcore' ); ?></h2>
					<div class="vc-td__form">
						<div><label><?php esc_html_e( 'Full name', 'voltcore' ); ?></label><input type="text"  name="td_name"  required></div>
						<div><label><?php esc_html_e( 'Email',     'voltcore' ); ?></label><input type="email" name="td_email" required></div>
						<div><label><?php esc_html_e( 'Phone',     'voltcore' ); ?></label><input type="tel"   name="td_phone"></div>
						<div><label><?php esc_html_e( 'Notes',     'voltcore' ); ?></label><textarea name="td_notes" rows="3"></textarea></div>
					</div>
				</div>

				<div class="vc-td__panel">
					<div class="vc-td__success">
						<svg viewBox="0 0 24 24" width="56" height="56" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
						<h2><?php esc_html_e( 'You’re booked', 'voltcore' ); ?></h2>
						<p style="color:var(--vc-muted);max-width:420px;margin:8px auto 0;"><?php echo esc_html( $s['success_msg'] ); ?></p>
					</div>
				</div>

				<div class="vc-td__nav">
					<button class="btn btn--ghost-dark btn--sm" type="button" data-td-prev style="visibility:hidden;"><?php esc_html_e( 'Back', 'voltcore' ); ?></button>
					<div style="flex:1"></div>
					<button class="btn btn--dark btn--sm" type="button" data-td-next><?php esc_html_e( 'Continue', 'voltcore' ); ?></button>
					<button class="btn btn--dark btn--sm" type="submit" data-td-submit style="display:none;"><?php esc_html_e( 'Confirm booking', 'voltcore' ); ?></button>
				</div>
			</form>
		</section>
		<?php
	}
}
