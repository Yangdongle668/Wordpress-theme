<?php
/**
 * VoltCore Financing Calculator — simple loan calculator with sliders
 * (price, down payment, term, APR) and live monthly-payment output.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Financing_Calc extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-financing-calc'; }
	public function get_title() { return __( 'VoltCore Financing Calc', 'voltcore' ); }
	public function get_icon() { return 'eicon-price-table'; }
	public function get_categories() { return array( 'voltcore' ); }
	public function get_keywords() { return array( 'finance', 'loan', 'calculator', 'payment' ); }

	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'heading', array( 'label' => 'Heading', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Estimate your monthly payment' ) );
		$this->add_control( 'currency', array( 'label' => 'Currency', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => '$' ) );
		$this->add_control( 'price', array( 'label' => 'Starting price', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 39990 ) );
		$this->add_control( 'down', array( 'label' => 'Default down', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 4500 ) );
		$this->add_control( 'term', array( 'label' => 'Default term (months)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 72 ) );
		$this->add_control( 'apr',  array( 'label' => 'Default APR (%)', 'type' => \Elementor\Controls_Manager::NUMBER, 'default' => 5.99 ) );
		$this->add_control( 'disclaimer', array( 'label' => 'Disclaimer', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'Actual rates vary based on credit. Estimate only.' ) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		$c = $s['currency'] ?: '$';
		?>
		<section class="vc-finance" data-vc-finance data-currency="<?php echo esc_attr( $c ); ?>">
			<h2 class="vc-finance__heading"><?php echo esc_html( $s['heading'] ); ?></h2>
			<div class="vc-finance__grid">
				<div class="vc-finance__inputs">
					<label>
						<span class="vc-finance__label"><?php esc_html_e( 'Vehicle price', 'voltcore' ); ?> <output data-vc-finance-out="price"><?php echo esc_html( $c . number_format_i18n( $s['price'] ) ); ?></output></span>
						<input type="range" min="15000" max="200000" step="500" value="<?php echo (int) $s['price']; ?>" data-vc-finance="price">
					</label>
					<label>
						<span class="vc-finance__label"><?php esc_html_e( 'Down payment', 'voltcore' ); ?> <output data-vc-finance-out="down"><?php echo esc_html( $c . number_format_i18n( $s['down'] ) ); ?></output></span>
						<input type="range" min="0" max="80000" step="250" value="<?php echo (int) $s['down']; ?>" data-vc-finance="down">
					</label>
					<label>
						<span class="vc-finance__label"><?php esc_html_e( 'Term', 'voltcore' ); ?> <output data-vc-finance-out="term"><?php echo (int) $s['term']; ?> mo</output></span>
						<input type="range" min="24" max="84" step="12" value="<?php echo (int) $s['term']; ?>" data-vc-finance="term">
					</label>
					<label>
						<span class="vc-finance__label"><?php esc_html_e( 'APR', 'voltcore' ); ?> <output data-vc-finance-out="apr"><?php echo esc_html( $s['apr'] ); ?>%</output></span>
						<input type="range" min="0" max="15" step="0.1" value="<?php echo esc_attr( $s['apr'] ); ?>" data-vc-finance="apr">
					</label>
				</div>
				<div class="vc-finance__result">
					<p class="vc-finance__sub"><?php esc_html_e( 'Estimated monthly payment', 'voltcore' ); ?></p>
					<p class="vc-finance__big"><strong data-vc-finance-out="monthly">—</strong><span>/mo</span></p>
					<ul class="vc-finance__meta">
						<li><?php esc_html_e( 'Financed', 'voltcore' ); ?>: <strong data-vc-finance-out="financed">—</strong></li>
						<li><?php esc_html_e( 'Total interest', 'voltcore' ); ?>: <strong data-vc-finance-out="interest">—</strong></li>
						<li><?php esc_html_e( 'Total cost', 'voltcore' ); ?>: <strong data-vc-finance-out="total">—</strong></li>
					</ul>
					<?php if ( ! empty( $s['disclaimer'] ) ) : ?><p class="vc-finance__disclaim"><?php echo esc_html( $s['disclaimer'] ); ?></p><?php endif; ?>
				</div>
			</div>
		</section>
		<?php
	}
}
