<?php
/**
 * The site header.
 *
 * @package VoltCore
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="theme-color" content="#000000">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'voltcore' ); ?></a>

<?php
/*
 * Theme Builder override: if a user has built a custom header via
 * Elementor Pro OR the VoltCore built-in Theme Builder, use it.
 */
$vc_header_rendered = false;
if ( function_exists( 'voltcore_elementor_location' ) && voltcore_elementor_location( 'header' ) ) {
	$vc_header_rendered = true;
}
if ( ! $vc_header_rendered && function_exists( 'voltcore_tmpl_render' ) ) {
	$vc_header_rendered = voltcore_tmpl_render( 'header' );
}

if ( ! $vc_header_rendered ) : ?>
<header id="masthead" class="site-header" role="banner" data-header itemscope itemtype="https://schema.org/WPHeader">
	<div class="site-header__inner">

		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<?php if ( is_front_page() && is_home() ) : ?>
					<h1 class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					</h1>
				<?php else : ?>
					<p class="site-title">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					</p>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<nav class="site-nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary', 'voltcore' ); ?>" itemscope itemtype="https://schema.org/SiteNavigationElement">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu(
					array(
						'theme_location' => 'primary',
						'container'      => false,
						'menu_class'     => 'site-menu',
						'depth'          => 2,
					)
				);
			} else {
				echo '<ul class="site-menu">';
				wp_list_pages( array( 'title_li' => '', 'depth' => 1 ) );
				echo '</ul>';
			}
			?>
		</nav>

		<div class="site-header__right">
			<a class="site-header__cta" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" rel="nofollow"><?php esc_html_e( 'Contact', 'voltcore' ); ?></a>
			<button class="nav-toggle" aria-expanded="false" aria-controls="mobile-drawer" data-nav-toggle>
				<span class="nav-toggle__bar"></span>
				<span class="nav-toggle__bar"></span>
				<span class="nav-toggle__bar"></span>
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle menu', 'voltcore' ); ?></span>
			</button>
		</div>
	</div>
</header>

<aside id="mobile-drawer" class="mobile-drawer" data-drawer hidden>
	<?php
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'mobile-menu',
			)
		);
	}
	?>
</aside>
<?php endif; // end default header ?>

<main id="main" class="site-main" role="main">
