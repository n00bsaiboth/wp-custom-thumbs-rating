/* global jQuery, thumbs_rating_ajax */
( function ( $ ) {
	'use strict';

	let STORAGE_PREFIX = 'thumbsrating';

	function storageKey( postId, type ) {
		return STORAGE_PREFIX + postId + '-' + type;
	}

	function hasVoted( postId ) {
		return !! localStorage.getItem( STORAGE_PREFIX + postId );
	}

	function markVoted( $container, type ) {
		$container.find( '.thumbs-rating-' + type ).addClass( 'thumbs-rating-voted' );
	}

	function showAlreadyVoted( $wrapper ) {
		$wrapper.find( '.thumbs-rating-already-voted' )
			.stop( true, true )
			.fadeIn()
			.css( 'display', 'block' );
	}

	function updateCounts( $wrapper, up, down ) {
		$wrapper.find( '.thumbs-rating-up .thumbs-rating-count' ).text( up );
		$wrapper.find( '.thumbs-rating-down .thumbs-rating-count' ).text( down );
	}

	function handleVote( event ) {
		event.preventDefault();

		let $button  = $( this );
		let $wrapper = $button.closest( '.thumbs-rating-wrapper' );
		let $container = $button.closest( '.thumbs-rating-container' );
		let postId   = parseInt( $wrapper.data( 'content-id' ), 10 ) || parseInt( $button.data( 'post-id' ), 10 );
		let type     = $button.data( 'vote' ); // "up" | "down"

		if ( ! postId || ( type !== 'up' && type !== 'down' ) ) {
			return;
		}

		if ( hasVoted( postId ) ) {
			showAlreadyVoted( $wrapper );
			return;
		}

		// Optimistic UI
		markVoted( $container, type );

		let data = {
			action: 'thumbs_rating_add_vote',
			postid: postId,
			type: type,
			nonce: thumbs_rating_ajax.nonce
		};

		$.post( thumbs_rating_ajax.ajax_url, data )
			.done( function ( response ) {
				if ( response && response.success && response.data ) {
					localStorage.setItem( STORAGE_PREFIX + postId, true );
					localStorage.setItem( storageKey( postId, type ), true );

					if ( response.data.html ) {
						// Replace the whole wrapper so the title + buttons stay in sync
						$wrapper.replaceWith( response.data.html );
					} else {
						updateCounts( $wrapper, response.data.up, response.data.down );
					}
				}
			} )
			.fail( function () {
				$container.find( '.thumbs-rating-' + type ).removeClass( 'thumbs-rating-voted' );
			} );
	}

	function restoreVotedState() {
		$( '.thumbs-rating-wrapper' ).each( function () {
			var $wrapper = $( this );
			var postId   = parseInt( $wrapper.data( 'content-id' ), 10 );

			if ( ! postId || ! hasVoted( postId ) ) {
				return;
			}

			if ( localStorage.getItem( storageKey( postId, 'up' ) ) ) {
				markVoted( $wrapper, 'up' );
			}
			if ( localStorage.getItem( storageKey( postId, 'down' ) ) ) {
				markVoted( $wrapper, 'down' );
			}
		} );
	}

	$( function () {
		restoreVotedState();
		$( document ).on( 'click', '.thumbs-rating-up, .thumbs-rating-down', handleVote );
	} );

} )( jQuery );