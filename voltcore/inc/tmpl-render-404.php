<?php
/**
 * Custom 404 renderer used by voltcore_tmpl_404_override().
 *
 * @package VoltCore
 */

get_header();
voltcore_tmpl_render( '404' );
get_footer();
