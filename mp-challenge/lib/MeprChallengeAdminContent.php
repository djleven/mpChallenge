<?php
/**
 * The file that defines the MeprChallengeAdminContent class
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


class MeprChallengeAdminContent {

    const MAIN_URL = 'http://localhost/wp-json/mp-challenge/v2/get-data';
    const MP_CHALLENGE_IMAGES_URL = MP_CHALLENGE_PLUGIN_URL.'public/img';

    /**
     * The object containing the data to display
     *
     * @since 1.0.0
     * @var object | bool
     */
    protected $mp_challenge_data;

    /**
     * Initialize the class and set its properties.
     *
     * @since      1.0.0
     */
    public function __construct() {

        add_action('admin_menu', array ($this, 'mb_challenge_plugin_menu'));
        add_filter('set-screen-option', array ($this, 'challenge_page_set_option'), 10, 3);
        $this->mp_challenge_data = $this->getChallengeData();
    }

    /**
     * Callback that registers the plugin admin menu
     *
     * @since     1.0.0
     * @return    void
     */
    public function mb_challenge_plugin_menu(){

        $hook = add_menu_page(
            'MemberPress Challenge Page',
            'MemberPress Challenge',
            'manage_options',
            'mb-challenge-plugin',
            array($this, 'mb_challenge_admin_view')
        );

        add_action( "load-$hook", array( $this, 'mb_challenge_plugin_page_hook' ));
    }


    /**
     * Callback hook for the plugin admin page
     *
     * @since     1.0.0
     * @return    void
     */
    public function mb_challenge_plugin_page_hook() {

        add_action('in_admin_header', array($this, 'mp_challenge_admin_header'), 0);
        $this->enqueuePageScripts();
        $this->add_options();
    }

    /**
     * Callback that registers the plugin admin page options
     *
     * @since     1.0.0
     * @return    void
     */
    public function add_options() {

        $option = 'per_page';
        $args = array(
            'label' => 'People',
            'default' => 5,
            'option' => 'people_per_page'
        );

        add_screen_option( $option, $args );

        new AdminDataTable($this->mp_challenge_data);
    }

    /**
     * Callback that renders the plugin admin page header
     *
     * @since     1.0.0
     * @return    void
     */
    public function mp_challenge_admin_header() {
        ?>
            <div id="mp-challenge-admin-header">
                <img class="mp-logo"
                     src="<?php echo self::MP_CHALLENGE_IMAGES_URL . '/mp-logo-horiz-RGB-icon.jpg'; ?>" />
            </div>
        <?php
    }
    /**
     * Callback for saving and loading the page screen options
     *
     * @since     1.0.0
     *
     * @param     bool         $keep    Whether to save or skip saving the screen option value. Default false.
     * @param     string       $option  The option name
     * @param     int | string $value   The number of rows to use.
     * @return    string
     */
    public function challenge_page_set_option($keep, $option, $value) {

        return sanitize_option( $option, $value );
    }

    /**
     * Callback that renders the plugin admin page HTML content
     *
     * Could be moved to a (new) views folder should we want to thin out this class file in the future,
     * We would need to implement a render template / view type functionality for the plugin
     *
     * @since     1.0.0
     * @return    void
     */
    public function mb_challenge_admin_view() {

        $myListTable = new AdminDataTable($this->mp_challenge_data);
        $myListTable->prepare_items();
        ?>

        <div class="wrap">
            <h2><?php echo $myListTable->getTableTitle(); ?></h2>

            <div class="meta-box-sortables ui-sortable">
                <form method="get">
                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                    <?php
                    $myListTable->search_box('search', 'search_id'); ?>

                    <?php $myListTable->display();?>
                </form>

            </div>
        </div>
        <?
    }

    /**
     * Call and get the challenge data from plugin WP API
     *
     * Return false on fail
     *
     * @since     1.0.0
     * @return    object | string
     */
    private function getChallengeData() {

        $response = wp_remote_get( self::MAIN_URL, array(
                'timeout' => 30
            )
        );

        $data = wp_remote_retrieve_body($response);

        if( empty($data) || is_wp_error($response)) {

            return false;
        }

        return json_decode($data);
    }

    /**
     * Register the JavaScript and the stylesheets for the plugin admin page exclusively.
     *
     * @since    1.0.0
     */
    public function enqueuePageScripts() {

        $pluginPublicFolder = 'public/';
        $cssFilePath = $pluginPublicFolder . 'css/' . MP_CHALLENGE_WP_NAME . '-page-admin.css';
        $jsFilePath = $pluginPublicFolder . 'js/' . MP_CHALLENGE_WP_NAME . '-admin.js';

        wp_enqueue_script(
            MP_CHALLENGE_WP_NAME, MP_CHALLENGE_PLUGIN_URL . $jsFilePath, array('jquery'), 0.1, true);
        wp_enqueue_style(
            MP_CHALLENGE_WP_NAME . '-page', MP_CHALLENGE_PLUGIN_URL . $cssFilePath);

    }


}
