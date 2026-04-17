<?php
/**
 * VoltCore — built-in Theme Builder.
 *
 * Registers a private CPT `voltcore_tmpl` that is edited with the block
 * editor (or Elementor, if installed). A meta box lets the admin choose
 * a "template type": header, footer, single, archive, page, or 404. When
 * a template is set to active for a type, VoltCore will render its
 * content instead of the default template — for every post/page that
 * matches that type.
 *
 * This gives the user tesla.com-style theme-builder freedom without
 * needing Elementor Pro. It coexists with Elementor Pro's own Theme
 * Builder: if a Pro location is available for a given slot, it wins.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
 * Register the CPT.
 * ========================================================= */
function voltcore_tmpl_register() {
	register_post_type(
		'voltcore_tmpl',
		array(
			'labels'              => array(
				'name'               => __( 'Theme Builder', 'voltcore' ),
				'singular_name'      => __( 'Template', 'voltcore' ),
				'add_new'            => __( 'New template', 'voltcore' ),
				'add_new_item'       => __( 'New VoltCore template', 'voltcore' ),
				'edit_item'          => __( 'Edit template', 'voltcore' ),
				'all_items'          => __( 'All templates', 'voltcore' ),
				'menu_name'          => __( 'Theme Builder', 'voltcore' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => false,
			'show_in_rest'        => true,
			'menu_icon'           => 'dashicons-layout',
			'supports'            => array( 'title', 'editor', 'elementor', 'revisions', 'author', 'custom-fields' ),
			'capability_type'     => 'post',
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'has_archive'         => false,
		)
	);
}
add_action( 'init', 'voltcore_tmpl_register' );

/* =========================================================
 * Template types.
 * ========================================================= */
function voltcore_tmpl_types() {
	return array(
		'header'  => __( 'Header (site top)',        'voltcore' ),
		'footer'  => __( 'Footer (site bottom)',     'voltcore' ),
		'single'  => __( 'Single post',              'voltcore' ),
		'page'    => __( 'Single page',              'voltcore' ),
		'archive' => __( 'Archive / blog',           'voltcore' ),
		'404'     => __( '404 — Not Found',          'voltcore' ),
	);
}

/* =========================================================
 * Meta box — pick type + enable.
 * ========================================================= */
function voltcore_tmpl_meta_box() {
	add_meta_box(
		'voltcore_tmpl_settings',
		__( 'VoltCore template settings', 'voltcore' ),
		'voltcore_tmpl_meta_box_render',
		'voltcore_tmpl',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'voltcore_tmpl_meta_box' );

function voltcore_tmpl_meta_box_render( $post ) {
	wp_nonce_field( 'voltcore_tmpl_meta', 'voltcore_tmpl_meta_nonce' );
	$type   = get_post_meta( $post->ID, '_voltcore_tmpl_type', true ) ?: 'single';
	$active = (bool) get_post_meta( $post->ID, '_voltcore_tmpl_active', true );
	?>
	<p>
		<label for="vc-type"><strong><?php esc_html_e( 'Template type', 'voltcore' ); ?></strong></label>
		<select id="vc-type" name="_voltcore_tmpl_type" style="width:100%">
			<?php foreach ( voltcore_tmpl_types() as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p>
		<label><input type="checkbox" name="_voltcore_tmpl_active" value="1" <?php checked( $active ); ?>>
		<?php esc_html_e( 'Active — use this template', 'voltcore' ); ?></label>
	</p>
	<p class="description">
		<?php esc_html_e( 'Only one template can be active per type. Activating this one will deactivate the others of the same type.', 'voltcore' ); ?>
	</p>
	<?php
}

function voltcore_tmpl_save( $post_id ) {
	if ( ! isset( $_POST['voltcore_tmpl_meta_nonce'] ) || ! wp_verify_nonce( $_POST['voltcore_tmpl_meta_nonce'], 'voltcore_tmpl_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'voltcore_tmpl' !== get_post_type( $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$allowed = array_keys( voltcore_tmpl_types() );
	$type    = isset( $_POST['_voltcore_tmpl_type'] ) ? sanitize_text_field( wp_unslash( $_POST['_voltcore_tmpl_type'] ) ) : 'single';
	if ( ! in_array( $type, $allowed, true ) ) {
		$type = 'single';
	}
	update_post_meta( $post_id, '_voltcore_tmpl_type', $type );

	$active = ! empty( $_POST['_voltcore_tmpl_active'] );
	update_post_meta( $post_id, '_voltcore_tmpl_active', $active ? 1 : 0 );

	if ( $active ) {
		// Deactivate any other active template of the same type.
		$existing = get_posts( array(
			'post_type'      => 'voltcore_tmpl',
			'post__not_in'   => array( $post_id ),
			'meta_query'     => array(
				array( 'key' => '_voltcore_tmpl_type',   'value' => $type ),
				array( 'key' => '_voltcore_tmpl_active', 'value' => 1 ),
			),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		foreach ( $existing as $id ) {
			update_post_meta( $id, '_voltcore_tmpl_active', 0 );
		}
	}
}
add_action( 'save_post', 'voltcore_tmpl_save' );

/* =========================================================
 * Columns on the CPT list screen.
 * ========================================================= */
function voltcore_tmpl_list_columns( $cols ) {
	$out = array();
	foreach ( $cols as $k => $v ) {
		$out[ $k ] = $v;
		if ( 'title' === $k ) {
			$out['vc_type']   = __( 'Type', 'voltcore' );
			$out['vc_active'] = __( 'Active', 'voltcore' );
		}
	}
	return $out;
}
add_filter( 'manage_voltcore_tmpl_posts_columns', 'voltcore_tmpl_list_columns' );

function voltcore_tmpl_list_column_render( $col, $post_id ) {
	if ( 'vc_type' === $col ) {
		$type  = get_post_meta( $post_id, '_voltcore_tmpl_type', true );
		$types = voltcore_tmpl_types();
		echo esc_html( $types[ $type ] ?? '—' );
	}
	if ( 'vc_active' === $col ) {
		echo get_post_meta( $post_id, '_voltcore_tmpl_active', true )
			? '<span style="color:#1a7f3c;font-weight:600">● ' . esc_html__( 'Active', 'voltcore' ) . '</span>'
			: '<span style="color:#777">○ ' . esc_html__( 'Inactive', 'voltcore' ) . '</span>';
	}
}
add_action( 'manage_voltcore_tmpl_posts_custom_column', 'voltcore_tmpl_list_column_render', 10, 2 );

/* =========================================================
 * Find the active template post for a type.
 * Cached for the request.
 * ========================================================= */
function voltcore_tmpl_active( $type ) {
	static $cache = array();
	if ( isset( $cache[ $type ] ) ) {
		return $cache[ $type ];
	}
	$posts = get_posts( array(
		'post_type'      => 'voltcore_tmpl',
		'posts_per_page' => 1,
		'meta_query'     => array(
			'relation' => 'AND',
			array( 'key' => '_voltcore_tmpl_type',   'value' => $type ),
			array( 'key' => '_voltcore_tmpl_active', 'value' => 1 ),
		),
	) );
	$cache[ $type ] = $posts ? $posts[0] : null;
	return $cache[ $type ];
}

/**
 * Render the active template's content for a slot.
 */
function voltcore_tmpl_render( $type ) {
	$post = voltcore_tmpl_active( $type );
	if ( ! $post ) {
		return false;
	}

	// Prefer Elementor-rendered content if the template was built with Elementor.
	if ( voltcore_has_elementor() && class_exists( '\Elementor\Plugin' ) ) {
		$plugin   = \Elementor\Plugin::$instance;
		$document = $plugin->documents->get( $post->ID );
		if ( $document && method_exists( $document, 'is_built_with_elementor' ) && $document->is_built_with_elementor() ) {
			echo $plugin->frontend->get_builder_content_for_display( $post->ID ); // phpcs:ignore WordPress.Security.EscapeOutput
			return true;
		}
	}

	$content = apply_filters( 'the_content', $post->post_content );
	echo $content; // phpcs:ignore WordPress.Security.EscapeOutput
	return true;
}

/* =========================================================
 * 404 override — route 404 requests through a custom template
 * if one is active.
 * ========================================================= */
function voltcore_tmpl_404_override( $template ) {
	if ( is_404() && voltcore_tmpl_active( '404' ) ) {
		$custom = VOLTCORE_DIR . '/inc/tmpl-render-404.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( '404_template', 'voltcore_tmpl_404_override' );
