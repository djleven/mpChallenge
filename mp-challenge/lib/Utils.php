<?php
/**
 * The plugin's helper functions
 *
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/lib
 */

namespace MeprChallenge\Lib;


class Utils
{
    /**
     * Gets the request parameter.
     *
     * @param      string $key The query parameter
     * @param      string $default The default value to return if not found
     *
     * @return     string | array    The request parameter
     */
    public static function getRequestParameter($key, $default = '')
    {

        if (!isset($_REQUEST[$key]) || empty($_REQUEST[$key])) {

            return $default;
        }
        if (is_array($_REQUEST[$key])) {

            return $_REQUEST[$key];
        }

        return strip_tags((string)wp_unslash($_REQUEST[$key]));
    }
}
