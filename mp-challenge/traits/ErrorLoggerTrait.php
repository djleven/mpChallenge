<?php
/**
 * The trait responsible for the plugin's error logging scheme.
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge/traits
 * @author     Kostas Stathakos <info@e-leven.net>
 */
namespace MeprChallenge\Traits;

trait ErrorLoggerTrait
{
    /**
     * Log an error to the plugin's debug file.
     *
     *
     * @param string $error_message     The error message to log
     * @param string $method            The method source or location of the error
     *
     */
    protected function logEndpointErrors($error_message, $method = '') {
        $plugin_log = MP_CHALLENGE_PLUGIN_DIR . 'logs/debug.log';
        $date = date("y/m/d G.i:s", time()) . ' ';
        if($method) {
            $method = PHP_EOL . ' Error location: ' .  $method;
        }

        $message = $date . 'Error message: ' .$error_message . $method .  PHP_EOL;

        error_log($message, 3, $plugin_log);
    }
}