<?php
/**
 * VoltCore Products — custom post type + taxonomy + meta + admin UI.
 *
 * Registers:
 *   - `vc_product` post type (public, Elementor-editable, REST-enabled)
 *   - `vc_product_cat` hierarchical taxonomy (Cells, Packs, Systems)
 *   - Post meta: subtitle, price, specs, CTA — stored with show_in_rest
 *   - Admin meta box on the product edit screen for non-REST inputs
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/* CPT + taxonomy */
function voltcore_products_register() {
	register_post_type( 'vc_product', array(
		'labels' => array(
			'name'               => __( 'Products', 'voltcore' ),
			'singular_name'      => __( 'Product', 'voltcore' ),
			'menu_name'          => __( 'Products', 'voltcore' ),
			'add_new'            => __( 'Add Product', 'voltcore' ),
			'add_new_item'       => __( 'Add new product', 'voltcore' ),
			'edit_item'          => __( 'Edit product', 'voltcore' ),
			'new_item'           => __( 'New product', 'voltcore' ),
			'view_item'          => __( 'View product', 'voltcore' ),
			'search_items'       => __( 'Search products', 'voltcore' ),
			'not_found'          => __( 'No products found', 'voltcore' ),
			'not_found_in_trash' => __( 'No products in Trash', 'voltcore' ),
			'all_items'          => __( 'All products', 'voltcore' ),
		),
		'public'        => true,
		'show_in_rest'  => true,
		'menu_position' => 5,
		'menu_icon'     => 'dashicons-superhero-alt2',
		'has_archive'   => 'products',
		'hierarchical'  => false,
		'rewrite'       => array( 'slug' => 'products', 'with_front' => false ),
		'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields', 'elementor' ),
		'taxonomies'    => array( 'vc_product_cat' ),
	) );

	register_taxonomy( 'vc_product_cat', 'vc_product', array(
		'labels' => array(
			'name'          => __( 'Product Categories', 'voltcore' ),
			'singular_name' => __( 'Product Category', 'voltcore' ),
			'menu_name'     => __( 'Categories', 'voltcore' ),
		),
		'public'            => true,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'product-category', 'with_front' => false ),
	) );
}
add_action( 'init', 'voltcore_products_register' );

/* Register post meta (visible to REST so Elementor can read). */
function voltcore_products_register_meta() {
	$keys = array( '_vc_product_subtitle', '_vc_product_price', '_vc_product_specs', '_vc_product_cta_label', '_vc_product_cta_url' );
	foreach ( $keys as $k ) {
		register_post_meta( 'vc_product', $k, array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'auth_callback'     => function () { return current_user_can( 'edit_posts' ); },
			'sanitize_callback' => 'wp_kses_post',
		) );
	}
}
add_action( 'init', 'voltcore_products_register_meta', 20 );

/* Meta box */
function voltcore_products_meta_box() {
	add_meta_box( 'vc_product_details', __( 'Product details', 'voltcore' ), 'voltcore_products_meta_box_render', 'vc_product', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'voltcore_products_meta_box' );

function voltcore_products_meta_box_render( $post ) {
	wp_nonce_field( 'vc_product_meta', 'vc_product_meta_nonce' );
	$subtitle = get_post_meta( $post->ID, '_vc_product_subtitle', true );
	$price    = get_post_meta( $post->ID, '_vc_product_price', true );
	$specs    = get_post_meta( $post->ID, '_vc_product_specs', true );
	$cta_l    = get_post_meta( $post->ID, '_vc_product_cta_label', true );
	$cta_u    = get_post_meta( $post->ID, '_vc_product_cta_url', true );
	?>
	<style>.vc-meta-row{margin:10px 0}.vc-meta-row label{display:block;font-weight:600;margin-bottom:4px}.vc-meta-row input,.vc-meta-row textarea{width:100%}.vc-meta-row textarea{min-height:70px}.vc-meta-hint{color:#666;font-size:12px;margin:4px 0 0}</style>

	<div class="vc-meta-row">
		<label for="vc_subtitle"><?php esc_html_e( 'Subtitle', 'voltcore' ); ?></label>
		<input type="text" id="vc_subtitle" name="_vc_product_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" placeholder="<?php esc_attr_e( 'Short tagline shown under product name', 'voltcore' ); ?>">
	</div>

	<div class="vc-meta-row">
		<label for="vc_price"><?php esc_html_e( 'Price (display)', 'voltcore' ); ?></label>
		<input type="text" id="vc_price" name="_vc_product_price" value="<?php echo esc_attr( $price ); ?>" placeholder="<?php esc_attr_e( 'From $1,999', 'voltcore' ); ?>">
	</div>

	<div class="vc-meta-row">
		<label for="vc_specs"><?php esc_html_e( 'Specifications', 'voltcore' ); ?></label>
		<textarea id="vc_specs" name="_vc_product_specs" placeholder="Range|640 km, Charge|15 min, Cells|4680"><?php echo esc_textarea( $specs ); ?></textarea>
		<p class="vc-meta-hint"><?php esc_html_e( 'Comma-separated list of key|value pairs. Example: Range|640 km, Charge|15 min, Cells|4680', 'voltcore' ); ?></p>
	</div>

	<div class="vc-meta-row" style="display:grid;grid-template-columns:1fr 2fr;gap:12px">
		<div>
			<label for="vc_cta_label"><?php esc_html_e( 'CTA Label', 'voltcore' ); ?></label>
			<input type="text" id="vc_cta_label" name="_vc_product_cta_label" value="<?php echo esc_attr( $cta_l ); ?>" placeholder="<?php esc_attr_e( 'Pre-order', 'voltcore' ); ?>">
		</div>
		<div>
			<label for="vc_cta_url"><?php esc_html_e( 'CTA URL', 'voltcore' ); ?></label>
			<input type="url" id="vc_cta_url" name="_vc_product_cta_url" value="<?php echo esc_attr( $cta_u ); ?>" placeholder="https://">
		</div>
	</div>
	<?php
}

function voltcore_products_meta_save( $post_id ) {
	if ( ! isset( $_POST['vc_product_meta_nonce'] ) || ! wp_verify_nonce( $_POST['vc_product_meta_nonce'], 'vc_product_meta' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( get_post_type( $post_id ) !== 'vc_product' ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	foreach ( array( '_vc_product_subtitle', '_vc_product_price', '_vc_product_specs', '_vc_product_cta_label', '_vc_product_cta_url' ) as $f ) {
		if ( isset( $_POST[ $f ] ) ) {
			update_post_meta( $post_id, $f, wp_kses_post( wp_unslash( $_POST[ $f ] ) ) );
		}
	}
}
add_action( 'save_post', 'voltcore_products_meta_save' );

/* Template helper */
function voltcore_product_meta( $key, $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return get_post_meta( $post_id, '_vc_product_' . $key, true );
}

/* Archive query tweaks */
function voltcore_products_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) return;
	if ( $query->is_post_type_archive( 'vc_product' ) || $query->is_tax( 'vc_product_cat' ) ) {
		$query->set( 'posts_per_page', 12 );
	}
}
add_action( 'pre_get_posts', 'voltcore_products_archive_query' );
