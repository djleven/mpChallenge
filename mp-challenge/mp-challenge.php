<?php
/**
 * @wordpress-plugin
 * Plugin Name:       MemberPress Challenge
 * Plugin URI:        https://github.com/djleven/mpChallenge/
 * Description:       A MemberPress challenge plugin
 * Version:           1.0.0
 * Author:            Kostas Stathakos
 * Author URI:        https://e-leven.net/
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mp-challenge
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
if ( defined( 'MP_CHALLENGE_WP_NAME' ) ) {
    die;
}
define( 'MP_CHALLENGE_WP_NAME', 'mp-challenge' );
define( 'MP_CHALLENGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MP_CHALLENGE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * The core plugin entry class
 */
include_once plugin_dir_path( __FILE__ ) . '/MeprChallengeInit.php';

new MeprChallenge\MeprChallengeInit();