<?php
/**
 * VoltCore — test-drive AJAX handler + CPT.
 *
 * Registers a private CPT "vc_testdrive" so booking submissions are
 * stored in the WP admin for review. The frontend Test Drive widget
 * posts to admin-ajax (action=voltcore_test_drive); this file validates
 * the nonce + required fields, stores the request and emails the admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* =========================================================
 * CPT: vc_testdrive (private, admin-only)
 * ========================================================= */
function voltcore_register_testdrive_cpt() {
	register_post_type( 'vc_testdrive', array(
		'labels' => array(
			'name'          => __( 'Test Drives', 'voltcore' ),
			'singular_name' => __( 'Test Drive', 'voltcore' ),
			'menu_name'     => __( 'Test Drives', 'voltcore' ),
			'add_new_item'  => __( 'Add Test Drive', 'voltcore' ),
			'edit_item'     => __( 'Edit Test Drive', 'voltcore' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_menu'      => false, // surfaced via VoltCore admin menu
		'show_in_nav_menus' => false,
		'supports'          => array( 'title', 'custom-fields' ),
		'capability_type'   => 'post',
		'map_meta_cap'      => true,
	) );
}
add_action( 'init', 'voltcore_register_testdrive_cpt' );

/* =========================================================
 * AJAX: voltcore_test_drive
 * ========================================================= */
function voltcore_ajax_test_drive() {
	$nonce = isset( $_POST['_vc_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['_vc_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'voltcore_test_drive' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please reload and try again.', 'voltcore' ) ), 400 );
	}

	$fields = array(
		'vehicle'  => sanitize_text_field( $_POST['vehicle']  ?? '' ),
		'location' => sanitize_text_field( $_POST['location'] ?? '' ),
		'date'     => sanitize_text_field( $_POST['date']     ?? '' ),
		'time'     => sanitize_text_field( $_POST['time']     ?? '' ),
		'name'     => sanitize_text_field( $_POST['name']     ?? '' ),
		'email'    => sanitize_email(      $_POST['email']    ?? '' ),
		'phone'    => sanitize_text_field( $_POST['phone']    ?? '' ),
		'notes'    => sanitize_textarea_field( $_POST['notes'] ?? '' ),
	);

	foreach ( array( 'vehicle', 'location', 'date', 'time', 'name', 'email', 'phone' ) as $k ) {
		if ( $fields[ $k ] === '' ) {
			wp_send_json_error( array( 'message' => sprintf( __( 'Missing required field: %s', 'voltcore' ), $k ) ), 400 );
		}
	}
	if ( ! is_email( $fields['email'] ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email.', 'voltcore' ) ), 400 );
	}

	// Simple honeypot could be added later.

	$title = sprintf( '%s — %s %s (%s)', $fields['name'], $fields['date'], $fields['time'], $fields['vehicle'] );

	$post_id = wp_insert_post( array(
		'post_type'   => 'vc_testdrive',
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_content'=> $fields['notes'],
	), true );

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Could not save request. Please try again later.', 'voltcore' ) ), 500 );
	}

	foreach ( $fields as $k => $v ) {
		update_post_meta( $post_id, '_vc_td_' . $k, $v );
	}
	update_post_meta( $post_id, '_vc_td_ip',    sanitize_text_field( $_SERVER['REMOTE_ADDR']     ?? '' ) );
	update_post_meta( $post_id, '_vc_td_ua',    sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ?? '' ) );
	update_post_meta( $post_id, '_vc_td_ref',   esc_url_raw(         $_SERVER['HTTP_REFERER']    ?? '' ) );

	// Notify the admin.
	$admin = get_option( 'admin_email' );
	if ( $admin ) {
		$lines = array(
			'New Demo Drive request',
			'--',
			'Name:     ' . $fields['name'],
			'Email:    ' . $fields['email'],
			'Phone:    ' . $fields['phone'],
			'Vehicle:  ' . $fields['vehicle'],
			'Location: ' . $fields['location'],
			'When:     ' . $fields['date'] . ' ' . $fields['time'],
			'Notes:    ' . $fields['notes'],
			'',
			'Admin: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
		);
		wp_mail(
			$admin,
			'[' . get_bloginfo( 'name' ) . '] Demo drive request: ' . $fields['name'],
			implode( "\n", $lines )
		);
	}

	wp_send_json_success( array( 'id' => $post_id ) );
}
add_action( 'wp_ajax_voltcore_test_drive',        'voltcore_ajax_test_drive' );
add_action( 'wp_ajax_nopriv_voltcore_test_drive', 'voltcore_ajax_test_drive' );

/* =========================================================
 * Admin: expose Test Drives via the VoltCore menu.
 * ========================================================= */
function voltcore_testdrive_admin_menu() {
	add_submenu_page(
		'voltcore',
		__( 'Test Drives', 'voltcore' ),
		__( 'Test Drives', 'voltcore' ),
		'manage_options',
		'edit.php?post_type=vc_testdrive'
	);
}
add_action( 'admin_menu', 'voltcore_testdrive_admin_menu', 30 );

/* =========================================================
 * List-table columns for vc_testdrive.
 * ========================================================= */
function voltcore_testdrive_columns( $cols ) {
	return array(
		'cb'       => $cols['cb'] ?? '',
		'title'    => __( 'Request', 'voltcore' ),
		'vehicle'  => __( 'Vehicle', 'voltcore' ),
		'when'     => __( 'When', 'voltcore' ),
		'location' => __( 'Location', 'voltcore' ),
		'contact'  => __( 'Contact', 'voltcore' ),
		'date'     => __( 'Submitted', 'voltcore' ),
	);
}
add_filter( 'manage_vc_testdrive_posts_columns', 'voltcore_testdrive_columns' );

function voltcore_testdrive_column_content( $col, $post_id ) {
	$get = static function( $k ) use ( $post_id ) { return get_post_meta( $post_id, '_vc_td_' . $k, true ); };
	switch ( $col ) {
		case 'vehicle':  echo esc_html( $get( 'vehicle' ) ); break;
		case 'location': echo esc_html( $get( 'location' ) ); break;
		case 'when':     echo esc_html( $get( 'date' ) . ' ' . $get( 'time' ) ); break;
		case 'contact':  echo esc_html( $get( 'name' ) . ' · ' . $get( 'email' ) . ' · ' . $get( 'phone' ) ); break;
	}
}
add_action( 'manage_vc_testdrive_posts_custom_column', 'voltcore_testdrive_column_content', 10, 2 );
