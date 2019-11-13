<?php
/**
 * Orchestrates the plugin's WP API endpoints
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/controllers
 */
namespace MeprChallenge\Controllers;

use MeprChallenge\Lib\MeprChallengeEndpoint;

class EndpointController {

    /**
     * Initialize the class.
     *
     * Register the plugin's API endpoints
     *
     * @since      1.0.0
     */
    public function __construct() {

        $this->registerMainChallengeEndpoint();
    }

    /**
     * Register the plugin's main endpoint with WP API
     *
     * @since     1.0.0
     * @return    void
     */
    protected function registerMainChallengeEndpoint() {

        new MeprChallengeEndpoint();
    }
}
