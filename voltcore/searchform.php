<?php
/**
 * Search form.
 *
 * @package VoltCore
 */
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="vc-s"><?php esc_html_e( 'Search', 'voltcore' ); ?></label>
	<input id="vc-s" class="search-field" type="search" placeholder="<?php esc_attr_e( 'Search…', 'voltcore' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
	<button class="btn btn--dark btn--sm" type="submit"><?php esc_html_e( 'Search', 'voltcore' ); ?></button>
</form>
