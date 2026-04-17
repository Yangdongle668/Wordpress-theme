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
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'voltcore' ); ?></a>

<header id="masthead" class="site-header" data-header>
	<div class="site-header__inner">

		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>">
					<?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<nav class="site-nav" aria-label="<?php esc_attr_e( 'Primary', 'voltcore' ); ?>">
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
			<a class="site-header__cta" href="#"><?php esc_html_e( 'Sign In', 'voltcore' ); ?></a>
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

<main id="main" class="site-main">
