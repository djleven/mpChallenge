<?php
/**
 * The class that defines the plugin's main WP API endpoint
 *
 * Plugin Convention:
 * Methods in underscore naming represent registered wordpress hook callbacks
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/lib
 */
namespace MeprChallenge\Lib;

use MeprChallenge\Traits\ErrorLoggerTrait;

class MeprChallengeEndpoint {

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
     * Callback to register the plugin's endpoint with WP API
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
     * @return array | \WP_Error
     */
    public function getChallengeDataFromRemoteServer(\WP_REST_Request $request) {

        // $request var currently not used

        $url = 'https://cspf-dev-challenge.herokuapp.com/';

        $response = wp_remote_get( $url, array(
                'timeout' => 30,
            )
        );
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            $this->logEndpointErrors($error_message, 'EndpointController:getChallengeDataFromRemoteServer');

            return new \WP_Error(
                'generic_error',
                $error_message,
                array(
                    'status' => 400,
                )
            );
        }

        return $response;
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
     * @since     1.0.0
     *
     * @param   \WP_REST_Request   $request
     * @return   \WP_Error | \WP_REST_Response
     */
    public function get_challenge_data(\WP_REST_Request $request) {

        // Check database (saves expensive HTTP requests)
        $meprChallengeData = get_transient(self::MEPR_CHALLENGE_TRANSIENT);

        if( !($meprChallengeData === false) ) {

            return $meprChallengeData;
        }

        // HTTP request to get data since no transient version exists
        $response = $this->getChallengeDataFromRemoteServer($request);

        if( !(is_wp_error($response)) ) {
            $response = json_decode(wp_remote_retrieve_body($response));

            // Process and sanitise data
            $response = $this->processChallengeData($response);

            // Store in database for an hour
            set_transient( self::MEPR_CHALLENGE_TRANSIENT, $response, HOUR_IN_SECONDS );

            return new \WP_REST_Response($response, 200);
        }

        return $response;
    }

    /**
     * Process and sanitise the challenge data
     *
     * a-) Iterate the response object and process data into a simpler array-only model
     * comprised of 'title', 'headers'. 'rows'
     *
     * b-) Sanitise values accordingly
     * - sanitize_text_field for title and header array values
     * - $sanitize_row_args filters for use with php's filter_var_array to sanitise row arrays
     *
     * c-) Format the date data according to wp settings format and current locale
     *
     * New model format:
     *
     * array (
     * 'title' => 'This amazing table',
     * 'headers' => array (
     *    0 => 'ID',
     *    1 => 'First Name',
     *    2 => 'Last Name',
     *    3 => 'Email',
     *    4 => 'Date',
     * ),
     * 'rows' => array (
     *    0 => array (
     *       'id' => 66,
     *       'fname' => 'Chris',
     *       'lname' => 'Test',
     *       'email' => 'chris@test.com',
     *       'date' => 1552944355,
     *    ),
     *    1 => array (
     *       ... etc
     *    )
     * )
     *
     * @since     1.0.0
     *
     * @param    object   $response
     * @return   array
     */
    public function processChallengeData($response) {

        $sanitize_row_args =
            array(
                'id' => FILTER_VALIDATE_INT,
                'fname' => FILTER_SANITIZE_STRING,
                'lname' => FILTER_SANITIZE_STRING,
                'email' => FILTER_SANITIZE_EMAIL,
                'date' => FILTER_VALIDATE_INT
            );
        $row_count = 1;
        $new_model = array();
        $rows = array();
        foreach ($response as $key => $value) {

            if($key ==='title' && is_string($value)) {
                // sanitise and add title to new array model
                $new_model[$key] = sanitize_text_field($value);

            } elseif($key === 'data') {
                foreach($value as $data_key=>$data_value) {

                    if($data_key ==='headers' && is_array($data_value)) {
                        // sanitise and add headers to new array model
                        $new_model[$data_key] =
                            array_filter($value->headers, function ($val) {
                                return sanitize_text_field($val);
                            }
                            );

                    } elseif($data_key ==='rows') {

                        foreach($data_value as $rows_key => $rows_value) {
                            if(isset($data_value->{$row_count})) {
                                $current_row = (array) $data_value->{$row_count};


                                // validate row array item values and exclude not expected / not validated items
                                $current_row = filter_var_array($current_row, $sanitize_row_args);

                                if(isset($current_row['date'])) {
                                    // convert timestamp to date format as per wp settings and locale
                                    $current_row['date'] =
                                        date_i18n(get_option( 'date_format' ), (int) $current_row['date']);
                                }

                                // add row array to new array model
                                $rows[] = $current_row;
                            }
                            $row_count++;
                        }

                        $new_model[$data_key] = $rows;
                    }
                }
            }
        }

        return $new_model;
    }
}
