<?php
/**
 * Orchestrates the wp-cli functionality of the plugin.
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/controllers
 * @author     Kostas Stathakos <info@e-leven.net>
 */
namespace MeprChallenge\Controllers;

class WPCLIController {

    /**
     * Initialize the class.
     *
     * Register the wp cli class(es) of the plugin
     *
     * @since    1.0.0
     */
    public function __construct() {

        \WP_CLI::add_command( 'mepr_challenge', 'MeprChallenge\Lib\MeprChallengeTransientWPCLI');

    }
}
