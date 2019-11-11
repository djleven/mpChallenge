<?php
/**
 * The class responsible for the transient related wp-cli commands of the plugin.
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/lib
 * @author     Kostas Stathakos <info@e-leven.net>
 */

namespace MeprChallenge\Lib;

class MeprChallengeTransientWPCLI {

    const MAIN_URL = 'http://localhost/wp-json/mp-challenge/v2/get-data';


    public function purgeTransient() {

        delete_transient('mepr_challenge_data');

        \WP_CLI::success( 'Transient successfully purged from database' );

    }

}
