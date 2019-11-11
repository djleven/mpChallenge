<?php
/**
 * The class that defines the plugin WP API endpoint
 *
 * Plugin Convention:
 * Methods in underscore naming represent registered wordpress hook callbacks
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/controllers
 */
namespace MeprChallenge\Controllers;

use MeprChallenge\Traits\ErrorLoggerTrait;

class EndpointController {

    use ErrorLoggerTrait;

    const MP_CHALLENGE_WP_API_NAMESPACE = '/mp-challenge';
    const MP_CHALLENGE_WP_API_VERSION = '/v2';
    const MP_CHALLENGE_GET_DATA_WP_API_ENDPOINT = '/get-data';
    const MEPR_CHALLENGE_TRANSIENT = 'mepr_challenge_data';

    /**
     * Whether to display the endpoint int the WP public index
     *
     * @since 1.0.0
     * @var bool
     */
    protected $show_in_index;

    /**
     * Whether endpoint access requires user log-in or not
     *
     * @since 1.0.0
     * @var bool
     */
    protected $require_log_in;

    /**
     * Initialize the class and set its properties.
     *
     * @since      1.0.0
     * @param      bool $show_in_index
     * @param      bool $require_log_in
     */
    public function __construct($show_in_index = true, $require_log_in = false) {
        $this->show_in_index = $show_in_index;
        $this->require_log_in = $require_log_in;
        add_action( 'rest_api_init', array($this, 'register_challenge_endpoint'));
    }

    /**
     * Register the plugin's endpoint with WP API
     *
     * @since     1.0.0
     * @return    void
     */
    public function register_challenge_endpoint() {

        register_rest_route(
            self::MP_CHALLENGE_WP_API_NAMESPACE . self::MP_CHALLENGE_WP_API_VERSION,
            self::MP_CHALLENGE_GET_DATA_WP_API_ENDPOINT,
            array(
                'methods' => \WP_REST_Server::READABLE, // which === 'GET'
                'callback' => array($this, 'get_challenge_data'),
                'show_in_index'  =>  $this->show_in_index,
                'permission_callback' => array($this, 'require_logged_in_permission')
            )
        );
    }

    /**
     * Determine if log-in permissions are met
     *
     * @since     1.0.0
     * @return    bool
     */
    public function require_logged_in_permission() {

        if($this->require_log_in) {

            return is_user_logged_in();
        }

        return true;
    }

    /**
     * Call and get response from remote API endpoint
     *
     * @param  \WP_REST_Request   $request
     *
     * @return \WP_REST_Response | \WP_Error
     */
    public function get_challenge_data_remote_API(\WP_REST_Request $request) {

        // $request var currently not used

        $url = 'https://cspf-dev-challenge.herokuapp.com/';

        $response = wp_remote_get( $url, array(
                'timeout' => 30,
            )
        );
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $this->logEndpointErrors($error_message, 'EndpointController:get_challenge_data_remote_API');

            return new \WP_Error(
                'generic_error',
                $error_message,
                array(
                    'status' => 400,
                )
            );
        }

        return new \WP_REST_Response(json_decode( wp_remote_retrieve_body($response)), 200);
    }

    /**
     * Get the challenge data
     *
     * Check first if cached data exists in database (as transient)
     *
     * If not, call the remote server endpoint to retrieve the data
     *
     * If data exists and is not a response error save as transient
     *
     * Else return false
     *
     * @since     1.0.0
     *
     * @param   \WP_REST_Request   $request
     * @return   bool | string
     */
    public function get_challenge_data(\WP_REST_Request $request) {

        // Check database (saves expensive HTTP requests)
        if( empty($meprChallengeData) ) {
            $meprChallengeData = get_transient(self::MEPR_CHALLENGE_TRANSIENT);
        }

        if( !($meprChallengeData === false) ) {

            return $meprChallengeData;
        }

        // HTTP request to get data since no transient version exists
        $response = $this->get_challenge_data_remote_API($request);

        if( !(is_wp_error($response)) ) {
            // Store in database for an hour
            set_transient( self::MEPR_CHALLENGE_TRANSIENT, $response, HOUR_IN_SECONDS );
        }

        return $response;
    }
}
