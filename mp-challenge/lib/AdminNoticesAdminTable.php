<?php
/**
 * The class responsible for the admin table's admin notices.
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge/lib
 * @author     Kostas Stathakos <info@e-leven.net>
 */
namespace MeprChallenge\Lib;

class AdminNoticesAdminTable extends AdminNotices {


    public function __construct() {

    }

    /**
     * Message to be displayed when there are no items selected for a bulk action
     */
    public function no_items_selected_for_bulk_action () {

        $this->renderMessage(
            'You must select at least one item to perform the operation!',
            static::NOTICE_WARNING
            );
    }
    /**
     * Message to be displayed when data is refreshed
     */
    public function data_refresh_success() {

        $this->renderMessage(
            'The data has been refreshed!',
            static::NOTICE_SUCCESS
        );
    }

    /**
     * Message to be displayed with successful export action
     */
    public function export_success() {

        $this->renderMessage(
            'The export has been completed successfully!',
            static::NOTICE_SUCCESS
        );
    }
}