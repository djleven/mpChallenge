<?php
/**
 * Orchestrates the admin-specific functionality of the plugin.
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/controllers
 * @author     Kostas Stathakos <info@e-leven.net>
 */
namespace MeprChallenge\Controllers;

use MeprChallenge\Lib\MeprChallengeAdminContent;

class AdminController {

    /**
     * Initialize the class.
     *
     * Register the plugin admin pages and related functionality
     *
     * @since    1.0.0
     */
    public function __construct() {

        $this->registerMainAdminPage();

        add_action('admin_menu', array ($this, 'enqueue_scripts'));
    }

    /**
     * Register the plugin's main endpoint with WP API
     *
     * @since     1.0.0
     * @return    void
     */
    protected function registerMainAdminPage() {

        new MeprChallengeAdminContent();
    }

    /**
     * Register the plugin stylesheet for the entire admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {

        $cssFilePath = 'public/css/' . MP_CHALLENGE_WP_NAME . '-admin.css';

        wp_enqueue_style(
            MP_CHALLENGE_WP_NAME, MP_CHALLENGE_PLUGIN_URL . $cssFilePath);

    }
}
