/* global thumbs_rating_ajax */
( function () {
	'use strict';

	let STORAGE_PREFIX = 'thumbsrating';

	/**
	 * Build a storage key for a post + vote type.
	 */
	function storageKey( postId, type ) {
		return STORAGE_PREFIX + postId + '-' + type;
	}

	/**
	 * Has the current browser already voted on this post?
	 */
	function hasVoted( postId ) {
		return !! localStorage.getItem( STORAGE_PREFIX + postId );
	}

	/**
	 * Add the "voted" class to a button.
	 */
	function markVoted( root, type ) {
		let el = root.querySelector( '.thumbs-rating-' + type );
		if ( el ) {
			el.classList.add( 'thumbs-rating-voted' );
		}
	}

	/**
	 * Remove the "voted" class from a button.
	 */
	function unmarkVoted( root, type ) {
		let el = root.querySelector( '.thumbs-rating-' + type );
		if ( el ) {
			el.classList.remove( 'thumbs-rating-voted' );
		}
	}

	/**
	 * Show the "You already voted!" message inside a wrapper.
	 */
	function showAlreadyVoted( wrapper ) {
		let el = wrapper.querySelector( '.thumbs-rating-already-voted' );
		if ( el ) {
			el.style.display = 'block';
		}
	}

	/**
	 * Update the numeric counts in the DOM.
	 */
	function updateCounts( wrapper, up, down ) {
		let upEl = wrapper.querySelector( '.thumbs-rating-up .thumbs-rating-count' );
		let downEl = wrapper.querySelector( '.thumbs-rating-down .thumbs-rating-count' );
		if ( upEl ) {
			upEl.textContent = up;
		}
		if ( downEl ) {
			downEl.textContent = down;
		}
	}

	/**
	 * Handle a click on either thumbs button.
	 */
	function handleVote( event ) {
		let button = event.target.closest( '.thumbs-rating-up, .thumbs-rating-down' );
		if ( ! button ) {
			return;
		}

		event.preventDefault();

		let wrapper = button.closest( '.thumbs-rating-wrapper' );
		let container = button.closest( '.thumbs-rating-container' );
		if ( ! wrapper || ! container ) {
			return;
		}

		let postId = parseInt( wrapper.dataset.contentId, 10 ) ||
		             parseInt( button.dataset.postId, 10 );
		let type   = button.dataset.vote; // "up" | "down"

		if ( ! postId || ( type !== 'up' && type !== 'down' ) ) {
			return;
		}

		if ( hasVoted( postId ) ) {
			showAlreadyVoted( wrapper );
			return;
		}

		// Optimistic UI
		markVoted( container, type );

		// Build the form data for the AJAX request
		let body = new URLSearchParams();
		body.append( 'action', 'thumbs_rating_add_vote' );
		body.append( 'postid', postId );
		body.append( 'type', type );
		body.append( 'nonce', thumbs_rating_ajax.nonce );

		fetch( thumbs_rating_ajax.ajax_url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			},
			body: body.toString()
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'HTTP ' + response.status );
				}
				return response.json();
			} )
			.then( function ( response ) {
				if ( ! response || ! response.success || ! response.data ) {
					throw new Error( 'Invalid response' );
				}

				localStorage.setItem( STORAGE_PREFIX + postId, 'true' );
				localStorage.setItem( storageKey( postId, type ), 'true' );

				if ( response.data.html ) {
					// Replace the whole wrapper so title + buttons stay in sync
					let temp = document.createElement( 'div' );
					temp.innerHTML = response.data.html.trim();
					let newWrapper = temp.firstChild;
					if ( newWrapper && wrapper.parentNode ) {
						wrapper.parentNode.replaceChild( newWrapper, wrapper );
					}
				} else {
					updateCounts( wrapper, response.data.up, response.data.down );
				}
			} )
			.catch( function () {
				// Roll back optimistic UI
				unmarkVoted( container, type );
			} );
	}

	/**
	 * On page load, restore "voted" state from localStorage.
	 */
	function restoreVotedState() {
		let wrappers = document.querySelectorAll( '.thumbs-rating-wrapper' );
		Array.prototype.forEach.call( wrappers, function ( wrapper ) {
			let postId = parseInt( wrapper.dataset.contentId, 10 );
			if ( ! postId || ! hasVoted( postId ) ) {
				return;
			}

			if ( localStorage.getItem( storageKey( postId, 'up' ) ) ) {
				markVoted( wrapper, 'up' );
			}
			if ( localStorage.getItem( storageKey( postId, 'down' ) ) ) {
				markVoted( wrapper, 'down' );
			}
		} );
	}

	/**
	 * Boot.
	 */
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', restoreVotedState );
	} else {
		restoreVotedState();
	}

	// Event delegation — works for dynamically added wrappers too
	document.addEventListener( 'click', handleVote );
} )();