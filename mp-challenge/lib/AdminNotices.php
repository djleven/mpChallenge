<?php
/**
 * The abstract class responsible for the plugin's admin notices.
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge/lib
 * @author     Kostas Stathakos <info@e-leven.net>
 */
namespace MeprChallenge\Lib;

abstract class AdminNotices {

    /**
     * Message class that displays with a red border
     */
    const NOTICE_ERROR = 'notice-error';

    /**
     * Message class that displays with a yellow border
     */
    const NOTICE_WARNING = 'notice-warning';

    /**
     * Message class that displays with a green border
     */
    const NOTICE_SUCCESS = 'notice-success';

    /**
     * Message class that displays with a blue border
     */
    const NOTICE_INFO = 'notice-info';

    /**
     * Display a message
     *
     *
     * @param string $message        The message to display
     * @param string $class          The message wrapper class
     * @param string $second_message The error $second_message to display
     */
    protected function renderMessage($message, $class = '', $second_message = null) {
        ?>
        <div class="mp-challenge-notice <?php echo $class;?>">
            <p>
                <?php _e( $message, MP_CHALLENGE_WP_NAME );?>
            </p>
            <?php if($second_message) : ?>
                <p>
                    <?php _e( $second_message, MP_CHALLENGE_WP_NAME );?>
                </p>
            <?php endif; ?>
        </div>
        <?

    }
}