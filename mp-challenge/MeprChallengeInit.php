<?php
/**
 * This class serves as the entry point for the plugin.
 *
 * It is used to:
 * - define internationalization (text domain, lang folder),
 * - load dependencies,
 * - instantiate the core plugin controllers for the front-end and admin area of the plugin.
 *
 * Plugin Convention:
 * Methods in underscore naming represent registered wordpress hook callbacks
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @author     Kostas Stathakos <info@e-leven.net>
 */
namespace MeprChallenge;

use MeprChallenge\Controllers\EndpointController;
use MeprChallenge\Controllers\AdminController;
//use MeprChallenge\Controllers\PublicController;
use MeprChallenge\Controllers\WPCLIController;

class MeprChallengeInit {

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    const WP_MP_ADMIN_DIR = MP_CHALLENGE_PLUGIN_DIR . 'admin/';
    const WP_MP_CONTROLLERS_DIR = MP_CHALLENGE_PLUGIN_DIR . 'controllers/';
    const WP_MP_LIB_DIR = MP_CHALLENGE_PLUGIN_DIR . 'lib/';
    const WP_MP_TRAITS_DIR = MP_CHALLENGE_PLUGIN_DIR . 'traits/';
    const WP_MP_VENDOR_DIR = MP_CHALLENGE_PLUGIN_DIR . 'vendor/';

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin version, define the plugin text domain and lang folder,
     * load the dependencies and run the core controllers.
     *
     * @since    1.0.0
     */

    public function __construct() {

        $this->version = "1.0.0";
        $this->setLocale();
        $this->loadAndRun();
    }

    /**
     * Callback for loading the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {

        load_plugin_textdomain(
            MP_CHALLENGE_WP_NAME,
            false,
            MP_CHALLENGE_PLUGIN_DIR . '/languages/'
        );
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the I18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function setLocale() {

        add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain'));
    }

    /**
     * Load the required common dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadCommonDependencies() {

        /**
         * The trait responsible for the plugin error logging.
         */
        require_once self::WP_MP_TRAITS_DIR . '/ErrorLoggerTrait.php';

        /**
         * The class responsible for the plugin API endpoints.
         */
        require_once self::WP_MP_CONTROLLERS_DIR . 'EndpointController.php';

    }

    /**
     * Load the required admin side dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadAdminDependencies() {

        /**
         * The class responsible for orchestrating actions in the admin-facing side of the site.
         */
        require_once self::WP_MP_CONTROLLERS_DIR . 'AdminController.php';

        /**
         * The (parent) class responsible for the wp list table - clone of the 'famous' wp internal class.
         */
        require_once self::WP_MP_VENDOR_DIR . 'class-wp-list-table.php';

        /**
         * The WP_List_Table child class responsible for the plugin's admin list table .
         */
        require_once self::WP_MP_LIB_DIR . 'AdminDataTable.php';

        /**
         * The class responsible for the plugin core admin side view / functionality
         */
        require_once self::WP_MP_LIB_DIR . '/MeprChallengeAdminContent.php';

    }

    /**
     * Load the required public facing side dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadPublicDependencies() {

        /**
         * The class responsible for orchestrating actions on the public-facing side of the site.
         */
//        require_once self::WP_MP_CONTROLLERS_DIR . 'PublicController.php';

        /**
         * The class responsible for the plugin core shortcode view / functionality
         */
//        require_once self::WP_MP_LIB_DIR . '/MeprChallengeShortcode.php';

    }

    /**
     * Load the required WP CLI dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadWPCLIDependencies() {

        /**
         * The controller class responsible for registering the wp-cli related classes.
         */
        require_once self::WP_MP_CONTROLLERS_DIR . 'WPCLIController.php';

        /**
         * The class responsible for main wp-cli (transient related) commands of the plugin.
         */
        require_once self::WP_MP_LIB_DIR . '/MeprChallengeTransientWPCLI.php';
    }

    /**
     * Load dependencies and instantiate the plugin controller classes
     *
     * @since    1.0.0
     * @access   private
     */
    private function loadAndRun() {

        $this->loadCommonDependencies();
        new EndpointController();

        if(is_admin()) {

            $this->loadAdminDependencies();
            new AdminController();
        } else {

            $this->loadPublicDependencies();
//            new PublicController();
        }

        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            $this->loadWPCLIDependencies();
            new WPCLIController();
        }
    }
}

