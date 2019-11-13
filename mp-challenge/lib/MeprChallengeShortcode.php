<?php
/**
 * The file that defines the MeprChallengeShortCode class
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

class MeprChallengeShortCode {

    const SHORTCODE_TAG_NAME = 'my-mp-challenge';

    /**
     * Initialize the class and set its properties.
     *
     * @since      1.0.0
     */
    public function __construct() {

        add_shortcode( self::SHORTCODE_TAG_NAME, array( $this, 'shortcode_content_view_hook'));
    }

    /**
     * Render the mp-challenge shortcode - Shortcode hook callback
     *
     * In the future, we would also handle the shortcode user input here -> $atts
     *
     * @since     1.0.0
     * @param     $atts    array | string
     *                     associative array of attributes, or an empty string if no attributes given
     *
     * @return    string   The shortcode view
     */
    public function shortcode_content_view_hook($atts) {

        $this->enqueueScripts();

        return $this->contentOutput();
    }

    /**
     * Register the JavaScript and CSS for pages that render the shortcode.
     *
     * @since    1.0.0
     */
    protected function enqueueScripts() {

        $pluginPublicFolder = 'public/';
        $cssFilePath = $pluginPublicFolder . 'css/' . MP_CHALLENGE_WP_NAME . '.css';
        $jsFilePath = $pluginPublicFolder . 'js/' . MP_CHALLENGE_WP_NAME . '.js';

        wp_enqueue_script(
            MP_CHALLENGE_WP_NAME, MP_CHALLENGE_PLUGIN_URL . $jsFilePath, array('jquery'), 0.1, true);
        wp_enqueue_style(
            MP_CHALLENGE_WP_NAME, MP_CHALLENGE_PLUGIN_URL . $cssFilePath);

    }

    /**
     * Return the output
     *
     * @since     1.0.0
     * @return    string	The generated output
     */
    protected function contentOutput() {

        return '<div class="mp-challenge"></div>';
    }

}
