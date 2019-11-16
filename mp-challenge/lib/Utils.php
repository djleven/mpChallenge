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
     * @param   string $key               The query parameter
     * @param   string $default           The default value to return if not found
     * @param   string $validation_type   The type of validation to perform - 'int' or 'string'
     * @param   string $validation_length The max length of 'string' / 'int' validation
     *
     * @return     string | array    The request parameter
     */
    public static function getRequestParameter(
        $key,
        $default = '',
        $validation_type = 'string',
        $validation_length = null
    )
    {

        if (!isset($_REQUEST[$key]) || empty($_REQUEST[$key])) {

            return $default;
        }
        if (is_array($_REQUEST[$key])) {

            return wp_unslash($_REQUEST[$key]);
        }
        if($validation_type === 'int') {
            if($validation_length) {

                return substr(
                    intval($_REQUEST[$key]), 0, intval($validation_length)
                );
            }

            return intval($_REQUEST[$key]);

        }

        if($validation_type === 'string') {
            if($validation_length) {

                return substr(
                    sanitize_text_field($_REQUEST[$key]), 0, intval($validation_length)
                );
            }

            return sanitize_text_field($_REQUEST[$key]);

        }

        return strip_tags((string)wp_unslash($_REQUEST[$key]));
    }


    /**
     * Determine if value is valid timestamp
     *
     * @param    $timestamp  string | int  valid unix timestamp
     * @return   bool
     */
    public static function isValidTimeStamp($timestamp)
    {
        return ((string) (int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
    }

    /**
     * Get the base date (i18n) filter format of a timestamp (ex: Mar-2019)
     *
     * @param    $timestamp  string | int  valid unix timestamp
     * @return   string
     */
    public static function getDateFilterFormat($timestamp) {

        return date_i18n("M-Y", strtotime( date_i18n( 'Y-m-01' , (string) $timestamp)));
    }
}

