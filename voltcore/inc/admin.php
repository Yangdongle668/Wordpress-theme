<?php
/**
 * VoltCore — WordPress admin panel.
 *
 * Adds a top-level "VoltCore" menu in wp-admin with:
 *  - Dashboard: welcome screen, quick links, status checks
 *  - Theme Options: generic theme settings (accent, variant, social)
 *  - Theme Builder: list of header/footer/single/archive/404 templates
 *  - Import Demo: re-run the installer + import sample content
 *  - Customize: deep link into Customizer
 *  - Menus / Widgets: deep links
 *  - Documentation: inline docs
 *
 * Uses the Settings API for options, nonces for actions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
 * Menu
 * ========================================================= */
function voltcore_admin_menu() {
	$icon = 'dashicons-superhero';

	add_menu_page(
		__( 'VoltCore', 'voltcore' ),
		__( 'VoltCore', 'voltcore' ),
		'manage_options',
		'voltcore',
		'voltcore_admin_page_dashboard',
		$icon,
		3
	);

	add_submenu_page( 'voltcore', __( 'Dashboard', 'voltcore' ),      __( 'Dashboard', 'voltcore' ),      'manage_options', 'voltcore',                 'voltcore_admin_page_dashboard' );
	add_submenu_page( 'voltcore', __( 'Theme Options', 'voltcore' ),  __( 'Theme Options', 'voltcore' ),  'manage_options', 'voltcore-options',         'voltcore_admin_page_options' );
	add_submenu_page( 'voltcore', __( 'Theme Builder', 'voltcore' ),  __( 'Theme Builder', 'voltcore' ),  'manage_options', 'edit.php?post_type=voltcore_tmpl' );
	add_submenu_page( 'voltcore', __( 'Import Demo', 'voltcore' ),    __( 'Import Demo', 'voltcore' ),    'manage_options', 'voltcore-import',          'voltcore_admin_page_import' );
	add_submenu_page( 'voltcore', __( 'Customize', 'voltcore' ),      __( 'Customize', 'voltcore' ),      'manage_options', 'customize.php' );
	add_submenu_page( 'voltcore', __( 'Menus', 'voltcore' ),          __( 'Menus', 'voltcore' ),          'manage_options', 'nav-menus.php' );
	add_submenu_page( 'voltcore', __( 'Widgets', 'voltcore' ),        __( 'Widgets', 'voltcore' ),        'manage_options', 'widgets.php' );
	add_submenu_page( 'voltcore', __( 'Documentation', 'voltcore' ),  __( 'Documentation', 'voltcore' ),  'manage_options', 'voltcore-docs',            'voltcore_admin_page_docs' );
}
add_action( 'admin_menu', 'voltcore_admin_menu' );

/* =========================================================
 * Admin CSS
 * ========================================================= */
function voltcore_admin_assets( $hook ) {
	if ( strpos( (string) $hook, 'voltcore' ) === false ) {
		return;
	}
	wp_register_style( 'voltcore-admin', false );
	wp_enqueue_style( 'voltcore-admin' );
	$css = "
	.voltcore-wrap{max-width:1180px;margin:20px 0;}
	.voltcore-wrap h1{font-size:28px;font-weight:500;margin:0 0 8px;display:flex;align-items:center;gap:10px}
	.voltcore-wrap .voltcore-badge{font-size:11px;background:#111;color:#fff;padding:2px 8px;border-radius:999px;letter-spacing:.08em;text-transform:uppercase;}
	.voltcore-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;margin-top:24px;}
	@media (max-width:900px){.voltcore-grid{grid-template-columns:1fr 1fr;}}
	@media (max-width:640px){.voltcore-grid{grid-template-columns:1fr;}}
	.voltcore-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:18px 20px;}
	.voltcore-card h2{font-size:16px;margin:0 0 6px;font-weight:600;}
	.voltcore-card p{color:#4b5563;margin:0 0 14px;}
	.voltcore-card .row a{margin-right:10px;}
	.voltcore-status{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px;}
	.voltcore-status .pill{background:#f3f4f6;border-radius:6px;padding:6px 12px;font-size:13px;}
	.voltcore-status .pill.ok{background:#e8f7ee;color:#1a7f3c;}
	.voltcore-status .pill.warn{background:#fef4e5;color:#9a6b18;}
	.voltcore-hero{background:linear-gradient(135deg,#101218 0%,#2b3240 100%);color:#fff;border-radius:12px;padding:28px 32px;margin:0 0 6px;}
	.voltcore-hero h1{color:#fff;margin:0 0 4px;}
	.voltcore-hero p{color:#c9ced6;margin:0;}
	.voltcore-form .form-row{margin-bottom:16px;}
	.voltcore-form label{font-weight:600;display:block;margin-bottom:4px;}
	.voltcore-form input[type=text],.voltcore-form input[type=url],.voltcore-form input[type=color],.voltcore-form select,.voltcore-form textarea{width:100%;max-width:560px;}
	";
	wp_add_inline_style( 'voltcore-admin', $css );
}
add_action( 'admin_enqueue_scripts', 'voltcore_admin_assets' );

/* =========================================================
 * Admin notice on first activation
 * ========================================================= */
function voltcore_admin_welcome_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( get_option( 'voltcore_welcome_dismissed' ) ) {
		return;
	}
	if ( isset( $_GET['voltcore_dismiss_welcome'] ) && check_admin_referer( 'voltcore_dismiss_welcome' ) ) {
		update_option( 'voltcore_welcome_dismissed', 1 );
		return;
	}
	$url = esc_url( admin_url( 'admin.php?page=voltcore' ) );
	$dismiss = esc_url( wp_nonce_url( add_query_arg( 'voltcore_dismiss_welcome', 1 ), 'voltcore_dismiss_welcome' ) );
	echo '<div class="notice notice-info is-dismissible"><p><strong>VoltCore</strong> ' .
		esc_html__( 'is installed. Visit the VoltCore dashboard to configure your homepage, theme builder, and import demo content.', 'voltcore' ) .
		' <a href="' . $url . '" class="button button-primary" style="margin-left:8px">' . esc_html__( 'Open Dashboard', 'voltcore' ) . '</a> ' .
		'<a href="' . $dismiss . '" style="margin-left:8px">' . esc_html__( 'Dismiss', 'voltcore' ) . '</a></p></div>';
}
add_action( 'admin_notices', 'voltcore_admin_welcome_notice' );

/* =========================================================
 * Dashboard page
 * ========================================================= */
function voltcore_admin_page_dashboard() {
	$status = voltcore_admin_status_checks();
	?>
	<div class="voltcore-wrap">
		<div class="voltcore-hero">
			<h1><?php esc_html_e( 'VoltCore Dashboard', 'voltcore' ); ?> <span class="voltcore-badge">v<?php echo esc_html( VOLTCORE_VERSION ); ?></span></h1>
			<p><?php esc_html_e( 'Tesla-inspired WordPress theme for battery, EV, and clean-energy brands.', 'voltcore' ); ?></p>
		</div>

		<div class="voltcore-status">
			<?php foreach ( $status as $s ) : ?>
				<span class="pill <?php echo esc_attr( $s['state'] ); ?>"><?php echo esc_html( $s['label'] ); ?></span>
			<?php endforeach; ?>
		</div>

		<div class="voltcore-grid">
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'Homepage Style', 'voltcore' ); ?></h2>
				<p><?php esc_html_e( 'Pick one of 3 homepage layouts — Classic, Grid or Story.', 'voltcore' ); ?></p>
				<div class="row"><a class="button button-primary" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[control]=voltcore_homepage_variant' ) ); ?>"><?php esc_html_e( 'Choose variant', 'voltcore' ); ?></a></div>
			</div>
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'Theme Options', 'voltcore' ); ?></h2>
				<p><?php esc_html_e( 'Accent color, typography, social profiles, homepage content.', 'voltcore' ); ?></p>
				<div class="row"><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=voltcore-options' ) ); ?>"><?php esc_html_e( 'Open options', 'voltcore' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>"><?php esc_html_e( 'Customize', 'voltcore' ); ?></a></div>
			</div>
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'Theme Builder', 'voltcore' ); ?></h2>
				<p><?php esc_html_e( 'Override header, footer, single, archive and 404 templates. Works with or without Elementor.', 'voltcore' ); ?></p>
				<div class="row"><a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=voltcore_tmpl' ) ); ?>"><?php esc_html_e( 'Open builder', 'voltcore' ); ?></a></div>
			</div>
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'Import Demo Content', 'voltcore' ); ?></h2>
				<p><?php esc_html_e( 'Creates sample pages, posts, menu and widgets so the site works out of the box.', 'voltcore' ); ?></p>
				<div class="row"><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=voltcore-import' ) ); ?>"><?php esc_html_e( 'Open importer', 'voltcore' ); ?></a></div>
			</div>
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'Menus & Widgets', 'voltcore' ); ?></h2>
				<p><?php esc_html_e( 'Set up your primary menu and footer columns.', 'voltcore' ); ?></p>
				<div class="row"><a class="button" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>"><?php esc_html_e( 'Menus', 'voltcore' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>"><?php esc_html_e( 'Widgets', 'voltcore' ); ?></a></div>
			</div>
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'SEO Health', 'voltcore' ); ?></h2>
				<p><?php esc_html_e( 'Permalinks, sitemap, structured data and OG tags — VoltCore handles them. Click through to verify.', 'voltcore' ); ?></p>
				<div class="row"><a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( home_url( '/wp-sitemap.xml' ) ); ?>"><?php esc_html_e( 'Open sitemap', 'voltcore' ); ?></a>
				<a class="button" href="<?php echo esc_url( admin_url( 'options-permalink.php' ) ); ?>"><?php esc_html_e( 'Permalinks', 'voltcore' ); ?></a></div>
			</div>
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'Elementor', 'voltcore' ); ?></h2>
				<p>
				<?php if ( voltcore_has_elementor() ) : ?>
					<?php esc_html_e( 'Elementor detected. VoltCore exposes Theme Builder locations and a VoltCore widget category.', 'voltcore' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Elementor is not installed. Installing it is optional — VoltCore works perfectly with the block editor too.', 'voltcore' ); ?>
				<?php endif; ?>
				</p>
				<div class="row">
				<?php if ( voltcore_has_elementor() ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'edit.php?post_type=elementor_library' ) ); ?>"><?php esc_html_e( 'Elementor templates', 'voltcore' ); ?></a>
				<?php else : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=elementor&tab=search&type=term' ) ); ?>"><?php esc_html_e( 'Install Elementor', 'voltcore' ); ?></a>
				<?php endif; ?>
				</div>
			</div>
			<div class="voltcore-card">
				<h2><?php esc_html_e( 'Documentation', 'voltcore' ); ?></h2>
				<p><?php esc_html_e( 'Install guide, customization tips, and frequently asked questions.', 'voltcore' ); ?></p>
				<div class="row"><a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=voltcore-docs' ) ); ?>"><?php esc_html_e( 'Open docs', 'voltcore' ); ?></a></div>
			</div>
		</div>
	</div>
	<?php
}

/* =========================================================
 * Status checks shown as pills on the dashboard.
 * ========================================================= */
function voltcore_admin_status_checks() {
	$out = array();

	$perm = get_option( 'permalink_structure' );
	$out[] = array(
		'label' => $perm ? __( 'Permalinks: pretty', 'voltcore' ) : __( 'Permalinks: plain (SEO risk)', 'voltcore' ),
		'state' => $perm ? 'ok' : 'warn',
	);

	$front = get_option( 'show_on_front' ) === 'page' && get_option( 'page_on_front' );
	$out[] = array(
		'label' => $front ? __( 'Homepage: static page', 'voltcore' ) : __( 'Homepage: not set', 'voltcore' ),
		'state' => $front ? 'ok' : 'warn',
	);

	$menu = has_nav_menu( 'primary' );
	$out[] = array(
		'label' => $menu ? __( 'Primary menu: assigned', 'voltcore' ) : __( 'Primary menu: not set', 'voltcore' ),
		'state' => $menu ? 'ok' : 'warn',
	);

	$demo = (bool) get_option( 'voltcore_installed_v1' );
	$out[] = array(
		'label' => $demo ? __( 'Demo content: imported', 'voltcore' ) : __( 'Demo content: not imported', 'voltcore' ),
		'state' => $demo ? 'ok' : 'warn',
	);

	return $out;
}

/* =========================================================
 * Theme Options page (separate from Customizer — simple form).
 * ========================================================= */
function voltcore_admin_page_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['voltcore_save_options'] ) && check_admin_referer( 'voltcore_save_options' ) ) {
		$accent = isset( $_POST['voltcore_accent_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['voltcore_accent_color'] ) ) : '';
		if ( $accent ) {
			set_theme_mod( 'voltcore_accent_color', $accent );
		}
		$variant = isset( $_POST['voltcore_homepage_variant'] ) ? sanitize_text_field( wp_unslash( $_POST['voltcore_homepage_variant'] ) ) : 'classic';
		set_theme_mod( 'voltcore_homepage_variant', voltcore_sanitize_variant( $variant ) );

		foreach ( array( 'voltcore_social_twitter', 'voltcore_social_linkedin', 'voltcore_social_youtube' ) as $k ) {
			$v = isset( $_POST[ $k ] ) ? esc_url_raw( wp_unslash( $_POST[ $k ] ) ) : '';
			set_theme_mod( $k, $v );
		}
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Options saved.', 'voltcore' ) . '</p></div>';
	}

	$accent  = get_theme_mod( 'voltcore_accent_color', '#cc0000' );
	$variant = get_theme_mod( 'voltcore_homepage_variant', 'classic' );
	$twitter = get_theme_mod( 'voltcore_social_twitter', '' );
	$linkedin = get_theme_mod( 'voltcore_social_linkedin', '' );
	$youtube = get_theme_mod( 'voltcore_social_youtube', '' );
	?>
	<div class="voltcore-wrap">
		<h1><?php esc_html_e( 'Theme Options', 'voltcore' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Quick options. For finer controls (hero images, product cards, etc.) use the Customizer.', 'voltcore' ); ?></p>

		<form method="post" class="voltcore-form">
			<?php wp_nonce_field( 'voltcore_save_options' ); ?>

			<div class="form-row">
				<label for="vc-accent"><?php esc_html_e( 'Accent color', 'voltcore' ); ?></label>
				<input type="color" id="vc-accent" name="voltcore_accent_color" value="<?php echo esc_attr( $accent ); ?>">
			</div>

			<div class="form-row">
				<label for="vc-variant"><?php esc_html_e( 'Homepage variant', 'voltcore' ); ?></label>
				<select id="vc-variant" name="voltcore_homepage_variant">
					<option value="classic" <?php selected( $variant, 'classic' ); ?>><?php esc_html_e( 'Classic — scroll-snap hero stack', 'voltcore' ); ?></option>
					<option value="grid"    <?php selected( $variant, 'grid' ); ?>><?php esc_html_e( 'Grid — product showcase', 'voltcore' ); ?></option>
					<option value="story"   <?php selected( $variant, 'story' ); ?>><?php esc_html_e( 'Story — split-screen narrative', 'voltcore' ); ?></option>
				</select>
			</div>

			<div class="form-row">
				<label for="vc-twitter">X / Twitter URL</label>
				<input type="url" id="vc-twitter" name="voltcore_social_twitter" value="<?php echo esc_attr( $twitter ); ?>">
			</div>
			<div class="form-row">
				<label for="vc-linkedin">LinkedIn URL</label>
				<input type="url" id="vc-linkedin" name="voltcore_social_linkedin" value="<?php echo esc_attr( $linkedin ); ?>">
			</div>
			<div class="form-row">
				<label for="vc-youtube">YouTube URL</label>
				<input type="url" id="vc-youtube" name="voltcore_social_youtube" value="<?php echo esc_attr( $youtube ); ?>">
			</div>

			<p><button type="submit" name="voltcore_save_options" class="button button-primary"><?php esc_html_e( 'Save changes', 'voltcore' ); ?></button></p>
		</form>
	</div>
	<?php
}

/* =========================================================
 * Import Demo page
 * ========================================================= */
function voltcore_admin_page_import() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['voltcore_run_import'] ) && check_admin_referer( 'voltcore_run_import' ) ) {
		delete_option( 'voltcore_installed_v1' );
		voltcore_after_switch_theme();
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Demo content imported.', 'voltcore' ) . '</p></div>';
	}

	if ( isset( $_POST['voltcore_import_elementor'] ) && check_admin_referer( 'voltcore_run_import' ) ) {
		if ( ! voltcore_has_elementor() ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'Elementor is not active. Install and activate Elementor first.', 'voltcore' ) . '</p></div>';
		} else {
			$force = ! empty( $_POST['voltcore_import_force'] );
			$res = voltcore_el_import_all( (bool) $force );
			echo '<div class="notice notice-success"><p>' . esc_html__( 'Elementor templates imported.', 'voltcore' ) . '</p><pre style="max-height:200px;overflow:auto;background:#fff;padding:10px">' . esc_html( print_r( $res, true ) ) . '</pre></div>';
		}
	}
	?>
	<div class="voltcore-wrap">
		<h1><?php esc_html_e( 'Import Demo Content', 'voltcore' ); ?></h1>
		<p><?php esc_html_e( 'Running the importer will create any of the following that are missing:', 'voltcore' ); ?></p>
		<ul class="ul-disc" style="margin-left:20px">
			<li><?php esc_html_e( '5 pages: Home, Blog, About, Products, Contact', 'voltcore' ); ?></li>
			<li><?php esc_html_e( '4 categories: Technology, Engineering, Sustainability, Product News', 'voltcore' ); ?></li>
			<li><?php esc_html_e( '6 sample blog posts with featured images', 'voltcore' ); ?></li>
			<li><?php esc_html_e( 'A primary navigation menu', 'voltcore' ); ?></li>
			<li><?php esc_html_e( 'Pre-filled footer widgets', 'voltcore' ); ?></li>
			<li><?php esc_html_e( 'SEO-friendly permalink structure (/%postname%/)', 'voltcore' ); ?></li>
		</ul>
		<p><em><?php esc_html_e( 'Existing content is never overwritten. Pages and posts with the same slug are skipped.', 'voltcore' ); ?></em></p>

		<form method="post">
			<?php wp_nonce_field( 'voltcore_run_import' ); ?>
			<p><button type="submit" name="voltcore_run_import" class="button button-primary"><?php esc_html_e( 'Run importer now', 'voltcore' ); ?></button></p>
		</form>

		<hr style="margin:32px 0">

		<h2 style="font-size:20px;margin:24px 0 12px"><?php esc_html_e( 'Import Elementor templates', 'voltcore' ); ?></h2>
		<p><?php esc_html_e( "Build every page with Elementor. Writes a full page layout into each seeded page's Elementor data, sideloads images into the Media Library, and creates Theme Builder templates for the header, footer and 404.", 'voltcore' ); ?></p>
		<ul class="ul-disc" style="margin-left:20px">
			<li><?php esc_html_e( 'Home, About, Contact, Careers, Press, Privacy, Terms, Cookies — each gets a tesla.com-style composition built from VoltCore widgets.', 'voltcore' ); ?></li>
			<li><?php esc_html_e( 'Header and Footer land in Elementor → Templates → Theme Builder (Pro only — display conditions auto-assigned when Pro is active).', 'voltcore' ); ?></li>
			<li><?php esc_html_e( '404 template created; wire it up under Elementor → Templates → Theme Builder.', 'voltcore' ); ?></li>
		</ul>

		<form method="post">
			<?php wp_nonce_field( 'voltcore_run_import' ); ?>
			<p>
				<label style="display:block;margin-bottom:10px">
					<input type="checkbox" name="voltcore_import_force" value="1">
					<?php esc_html_e( 'Force — overwrite existing Elementor data on each page', 'voltcore' ); ?>
				</label>
				<button type="submit" name="voltcore_import_elementor" class="button button-primary" <?php disabled( ! voltcore_has_elementor() ); ?>>
					<?php esc_html_e( 'Import Elementor templates', 'voltcore' ); ?>
				</button>
				<?php if ( ! voltcore_has_elementor() ) : ?>
					<span style="color:#a62929;margin-left:10px"><?php esc_html_e( 'Elementor is not active.', 'voltcore' ); ?></span>
				<?php endif; ?>
			</p>
		</form>
	</div>
	<?php
}

/* =========================================================
 * Docs page
 * ========================================================= */
function voltcore_admin_page_docs() {
	?>
	<div class="voltcore-wrap">
		<h1><?php esc_html_e( 'VoltCore Documentation', 'voltcore' ); ?></h1>

		<div class="voltcore-card" style="max-width:820px">
			<h2>1. Homepage</h2>
			<p><?php esc_html_e( 'Pick from 3 variants under Customize → VoltCore Theme → Brand & Colors → Homepage Style. Each variant fills in from the same Customizer fields, so switching is instant.', 'voltcore' ); ?></p>

			<h2>2. Replace images</h2>
			<p><?php esc_html_e( 'Every image is replaceable two ways:', 'voltcore' ); ?></p>
			<ol>
				<li><?php esc_html_e( 'Via the Customizer (preferred).', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'By overwriting files in wp-content/themes/voltcore/assets/images/ with the same filenames (hero-1.jpg, product-1.jpg, story-1.jpg, ...).', 'voltcore' ); ?></li>
			</ol>

			<h2>3. SEO</h2>
			<p><?php esc_html_e( 'VoltCore ships SEO on by default:', 'voltcore' ); ?></p>
			<ul class="ul-disc" style="margin-left:20px">
				<li><?php esc_html_e( 'Automatic meta description from excerpt / content.', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'Open Graph and Twitter Card tags on every page.', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'JSON-LD for Organization, WebSite, Article, WebPage and BreadcrumbList.', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'Canonical tags (via WordPress core rel_canonical).', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'XML sitemap at /wp-sitemap.xml (WordPress 5.5+).', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'Breadcrumbs on every non-front page.', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'Lazy-loaded images with alt fallbacks.', 'voltcore' ); ?></li>
				<li><?php esc_html_e( 'noindex on search results, 404, and paginated archives.', 'voltcore' ); ?></li>
			</ul>

			<h2>4. Theme Builder</h2>
			<p><?php esc_html_e( 'Go to VoltCore → Theme Builder. Create a new template, pick a type (Header / Footer / Single / Archive / 404), and compose it with the block editor or Elementor. Enable it and VoltCore will use it in place of the default template.', 'voltcore' ); ?></p>

			<h2>5. Elementor</h2>
			<p><?php esc_html_e( 'VoltCore is Elementor- and Elementor Pro-ready. Theme Builder locations are registered automatically. A VoltCore widget category is added. Elementor buttons and headings inherit VoltCore design tokens.', 'voltcore' ); ?></p>

			<h2>6. Menus</h2>
			<p><?php esc_html_e( 'Appearance → Menus. Assign your menu to the "Primary Menu" location (for the top nav) and "Footer Menu" (for the footer bar).', 'voltcore' ); ?></p>
		</div>
	</div>
	<?php
}
