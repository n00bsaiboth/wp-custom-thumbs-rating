<?php
/*
Plugin Name: Thumbs Rating
Plugin URI:  https://wordpress.org/plugins/thumbs-rating/
Description: Add thumbs up/down rating to your content.
Author:      Ricard Torres (modernized)
Version:     3.0.0
Author URI:  http://php.quicoto.com/
Text Domain: thumbs-rating
Requires PHP: 7.4
License:     GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*-----------------------------------------------------------------------------------*/
/* Constants */
/*-----------------------------------------------------------------------------------*/

define( 'THUMBS_RATING_VERSION', '3.0.0' );
define( 'THUMBS_RATING_FILE', __FILE__ );
define( 'THUMBS_RATING_URL', plugin_dir_url( __FILE__ ) );
define( 'THUMBS_RATING_PATH', plugin_dir_path( __FILE__ ) );

/*-----------------------------------------------------------------------------------*/
/* Load includes (order matters: helpers first) */
/*-----------------------------------------------------------------------------------*/

require_once THUMBS_RATING_PATH . 'includes/helpers.php';
require_once THUMBS_RATING_PATH . 'includes/enqueue.php';
require_once THUMBS_RATING_PATH . 'includes/ajax.php';
require_once THUMBS_RATING_PATH . 'includes/admin.php';
require_once THUMBS_RATING_PATH . 'includes/settings.php'; 
require_once THUMBS_RATING_PATH . 'includes/shortcodes.php';
