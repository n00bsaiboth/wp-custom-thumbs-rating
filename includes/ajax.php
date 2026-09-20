<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'thumbs_rating_add_vote_callback' ) ) :
	function thumbs_rating_add_vote_callback() {

		check_ajax_referer( 'thumbs-rating-nonce', 'nonce' );

		$post_ID = isset( $_POST['postid'] ) ? intval( $_POST['postid'] ) : 0;
		$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';

		if ( ! $post_ID || ! in_array( $type, array( 'up', 'down' ), true ) ) {
			wp_send_json_error( array( 'message' => 'Invalid vote.' ), 400 );
		}

		if ( ! get_post( $post_ID ) ) {
			wp_send_json_error( array( 'message' => 'Post not found.' ), 404 );
		}

		$meta_name = ( $type === 'up' ) ? '_thumbs_rating_up' : '_thumbs_rating_down';

		$current = get_post_meta( $post_ID, $meta_name, true );
		$current = ( $current !== '' ) ? intval( $current ) : 0;
		$current++;

		update_post_meta( $post_ID, $meta_name, $current );

		wp_send_json_success(
			array(
				'html' => thumbs_rating_getlink( $post_ID, $type ),
				'up'   => intval( get_post_meta( $post_ID, '_thumbs_rating_up', true ) ),
				'down' => intval( get_post_meta( $post_ID, '_thumbs_rating_down', true ) ),
			)
		);
	}
	add_action( 'wp_ajax_thumbs_rating_add_vote', 'thumbs_rating_add_vote_callback' );
	add_action( 'wp_ajax_nopriv_thumbs_rating_add_vote', 'thumbs_rating_add_vote_callback' );
endif;