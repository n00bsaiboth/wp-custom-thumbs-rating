<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Columns
 */

if ( ! function_exists( 'thumbs_rating_columns' ) ) :
	function thumbs_rating_columns( $columns ) {
		unset( $columns['author'] );

		return array_merge(
			$columns,
			array(
				'thumbs_rating_up_count'   => __( 'Up Votes', 'thumbs-rating' ),
				'thumbs_rating_down_count' => __( 'Down Votes', 'thumbs-rating' ),
			)
		);
	}

	add_filter( 'manage_posts_columns', 'thumbs_rating_columns' );
	add_filter( 'manage_pages_columns', 'thumbs_rating_columns' );
endif;

if ( ! function_exists( 'thumbs_rating_column_values' ) ) :
	function thumbs_rating_column_values( $column, $post_id ) {
		$post_id = absint( $post_id );

		switch ( $column ) {
			case 'thumbs_rating_up_count':
				$val = get_post_meta( $post_id, '_thumbs_rating_up', true );
				echo $val !== '' ? '+' . intval( $val ) : '0';
				break;

			case 'thumbs_rating_down_count':
				$val = get_post_meta( $post_id, '_thumbs_rating_down', true );
				echo $val !== '' ? '-' . intval( $val ) : '0';
				break;
		}
	}

	add_action( 'manage_posts_custom_column', 'thumbs_rating_column_values', 10, 2 );
	add_action( 'manage_pages_custom_column', 'thumbs_rating_column_values', 10, 2 );
endif;

/**
 * Sortable columns
 */

if ( ! function_exists( 'thumbs_rating_sortable_columns' ) ) :
	function thumbs_rating_sortable_columns( $columns ) {
		$columns['thumbs_rating_up_count']   = 'thumbs_rating_up_count';
		$columns['thumbs_rating_down_count'] = 'thumbs_rating_down_count';

		return $columns;
	}

	add_action( 'admin_init', 'thumbs_rating_sort_all_public_post_types' );

	function thumbs_rating_sort_all_public_post_types() {
		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $post_type_name ) {
			add_action(
				'manage_edit-' . $post_type_name . '_sortable_columns',
				'thumbs_rating_sortable_columns'
			);
		}
	}

	add_filter( 'request', 'thumbs_rating_column_sort_orderby' );

	function thumbs_rating_column_sort_orderby( $vars ) {
		if ( isset( $vars['orderby'] ) && 'thumbs_rating_up_count' === $vars['orderby'] ) {
			$vars['meta_key'] = '_thumbs_rating_up';
			$vars['orderby']  = 'meta_value_num';
		}

		if ( isset( $vars['orderby'] ) && 'thumbs_rating_down_count' === $vars['orderby'] ) {
			$vars['meta_key'] = '_thumbs_rating_down';
			$vars['orderby']  = 'meta_value_num';
		}

		return $vars;
	}
endif;