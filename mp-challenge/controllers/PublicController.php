<?php
/**
 * Orchestrates the public-facing functionality of the plugin.
 *
 * Plugin Convention:
 * Methods in underscore naming represent registered wordpress hook callbacks
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/controllers
 * @author     Kostas Stathakos <info@e-leven.net>
 */
namespace MeprChallenge\Controllers;

use MeprChallenge\Lib\MeprChallengeShortcode;

class PublicController {

    const SHORTCODE_TAG_NAME = 'my-mp-challenge';

    /**
     * Initialize the class.
     *
     * Register the main hooks related to the public-facing functionality
     * Enqueue scripts and styles
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->registerMeprChallengeShortcode();
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts' ));
    }

    /**
     * Register the plugin's JavaScript and CSS for the entire public-facing side of the site .
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

    }

    /**
     * Register the main (initial) shortcode
     *
     * @since     1.0.0
     * @return    void
     */
    protected function registerMeprChallengeShortcode(){

        new MeprChallengeShortcode();
    }
}
