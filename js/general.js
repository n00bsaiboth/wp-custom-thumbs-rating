/* global jQuery, thumbs_rating_ajax */
( function ( $ ) {
	'use strict';

	var STORAGE_PREFIX = 'thumbsrating';

	/**
	 * Get the localStorage key for a given post + type.
	 */
	function storageKey( postId, type ) {
		return STORAGE_PREFIX + postId + '-' + type;
	}

	/**
	 * Check if the user has already voted on a given post.
	 */
	function hasVoted( postId ) {
		return !! localStorage.getItem( STORAGE_PREFIX + postId );
	}

	/**
	 * Mark a container as voted (visual state).
	 */
	function markVoted( $container, type ) {
		$container.find( '.thumbs-rating-' + type ).addClass( 'thumbs-rating-voted' );
	}

	/**
	 * Show the "already voted" message.
	 */
	function showAlreadyVoted( $container ) {
		$container.find( '.thumbs-rating-already-voted' ).stop( true, true ).fadeIn().css( 'display', 'block' );
	}

	/**
	 * Update the counts in place (no full HTML replacement).
	 */
	function updateCounts( $container, up, down ) {
		$container.find( '.thumbs-rating-up .thumbs-rating-count' ).text( up );
		$container.find( '.thumbs-rating-down .thumbs-rating-count' ).text( down );
	}

	/**
	 * Handle a vote click.
	 */
	function handleVote( event ) {
		event.preventDefault();

		var $button    = $( this );
		var $container = $button.closest( '.thumbs-rating-container' );
		var postId     = parseInt( $container.data( 'content-id' ), 10 ) || parseInt( $button.data( 'post-id' ), 10 );
		var type       = $button.data( 'vote' ); // "up" | "down"

		if ( ! postId || ( type !== 'up' && type !== 'down' ) ) {
			return;
		}

		// Already voted?
		if ( hasVoted( postId ) ) {
			showAlreadyVoted( $container );
			return;
		}

		// Optimistic UI
		markVoted( $container, type );

		var data = {
			action: 'thumbs_rating_add_vote',
			postid: postId,
			type: type,
			nonce: thumbs_rating_ajax.nonce
		};

		$.post( thumbs_rating_ajax.ajax_url, data )
			.done( function ( response ) {
				if ( response && response.success && response.data ) {
					// Persist only after success
					localStorage.setItem( STORAGE_PREFIX + postId, true );
					localStorage.setItem( storageKey( postId, type ), true );

					updateCounts( $container, response.data.up, response.data.down );

					if ( response.data.html ) {
						// Optional: full refresh (keeps markup in sync)
						$container.replaceWith( response.data.html );
					}
				}
			} )
			.fail( function () {
				// Roll back optimistic UI
				$container.find( '.thumbs-rating-' + type ).removeClass( 'thumbs-rating-voted' );
			} );
	}

	/**
	 * On page load, restore voted state from localStorage.
	 */
	function restoreVotedState() {
		$( '.thumbs-rating-container' ).each( function () {
			var $container = $( this );
			var postId     = parseInt( $container.data( 'content-id' ), 10 );

			if ( ! postId || ! hasVoted( postId ) ) {
				return;
			}

			if ( localStorage.getItem( storageKey( postId, 'up' ) ) ) {
				markVoted( $container, 'up' );
			}
			if ( localStorage.getItem( storageKey( postId, 'down' ) ) ) {
				markVoted( $container, 'down' );
			}
		} );
	}

	$( function () {
		restoreVotedState();

		// Event delegation — works for dynamically added containers too
		$( document ).on( 'click', '.thumbs-rating-up, .thumbs-rating-down', handleVote );
	} );

} )( jQuery );