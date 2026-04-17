<?php
/**
 * VoltCore Legal TOC — renders an empty <ol> that the theme's
 * main.js fills in automatically by scraping h2 headings inside the
 * nearest [data-legal-content] container on the page.
 *
 * Best dropped into the left column of a two-column Elementor section
 * where the right column contains the legal body (h2 + p blocks).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class VoltCore_Legal_TOC extends \Elementor\Widget_Base {

	public function get_name() { return 'voltcore-legal-toc'; }
	public function get_title() { return __( 'VoltCore Legal TOC', 'voltcore' ); }
	public function get_icon() { return 'eicon-bullet-list'; }
	public function get_categories() { return array( 'voltcore' ); }

	protected function register_controls() {
		$this->start_controls_section( 'section_content', array( 'label' => __( 'Content', 'voltcore' ) ) );
		$this->add_control( 'label', array( 'label' => __( 'Label', 'voltcore' ), 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'On this page' ) );
		$this->add_control( 'hint', array( 'type' => \Elementor\Controls_Manager::RAW_HTML,
			'raw'  => '<div style="padding:8px 10px;background:#fff8e1;border-radius:6px;font-size:12px">This widget auto-fills at runtime from h2 headings in the nearest <code>[data-legal-content]</code> block. Make sure your legal body is wrapped in that block (or use the VoltCore — Legal page template which does so automatically).</div>',
		) );
		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();
		?>
		<aside class="legal-toc" aria-label="<?php echo esc_attr( $s['label'] ); ?>">
			<div class="legal-toc__label"><?php echo esc_html( $s['label'] ); ?></div>
			<ol class="legal-toc__list" data-legal-toc></ol>
		</aside>
		<?php
	}
}
