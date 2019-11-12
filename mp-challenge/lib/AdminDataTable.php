<?php
/**
 * The WP_List_Table child class responsible for the plugin's admin page list table
 *
 * Note:
 * Methods in underscore reflect the inherited parent methods
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/lib
 */

namespace MeprChallenge\Lib;

use MeprChallenge\Vendor\WP_List_Table;
use MeprChallenge\Traits\ErrorLoggerTrait;

class AdminDataTable extends WP_List_Table {

    use ErrorLoggerTrait;

    /**
     * The object containing the data to display
     * False for no data
     *
     * @var object | bool
     */
    private $table_data;

    /**
     * Table data items (rows)
     *
     * @var array
     */
    private $all_items;

    /**
     * The list of table columns
     *
     * @@var array
     */
    private $columns;

    public function __construct($table_data = false) {

        if($table_data) {
            $this->table_data = $table_data;
            $this->all_items = $this->getAllItems();
            $this->columns = $this->get_columns();
            $this->processActions();

            parent::__construct(
                array(
                    'singular'=> 'wp_list_mepr_challenge',
                    'plural' => 'wp_list_mepr_challenges',
                    'ajax'  => false
                )
            );
        }

    }

    /**
     * Get the list of columns
     *
     * The format is:
     * 'internal-name' => 'Title'
     *
     * @return array
     */
    public function get_columns() {

        $columns = array();

        if(isset($this->table_data->headers) && isset($this->table_data->rows[1])) {
            $column_labels = $this->table_data->headers;
            $firstRowItem = $this->table_data->rows[1];
            $count = 0;
            foreach ($firstRowItem as $key => $value) {
                $columns[$key] = __(sanitize_text_field($column_labels[$count]), MP_CHALLENGE_WP_NAME);
                $count++;
            }
            if($count === count($column_labels)) {
                // add checkbox column tot op
                $columns = array('cb' => '<input type="checkbox" />') + $columns;

            } else {
                // log error
                $this->logEndpointErrors(
                    'column - row count mismatch',
                    'AdminDataTable:get_columns'
                );
            }

            return $columns;
        }

        $this->logEndpointErrors(
            'headers and / or first row object(s) incompatible with data model',
            'AdminDataTable:get_columns'
        );
        return $columns;
    }

    /**
     * Get the list of sortable columns.
     *
     * The format is:
     * 'internal-name' => 'orderby'
     * or
     * 'internal-name' => array( 'orderby', true )
     *
     * The second format will make the initial sorting order be descending
     *
     * This implementation makes all columns sortable - except for the 'cb' (checkbox)
     *
     * @return array
     */
    protected function get_sortable_columns() {

        $columns = array();
        foreach ($this->columns as $key=>$value) {

            if($key !== 'cb') {

                $columns[$key] =  array($key, true);
            }
        }

        return $columns;
    }

    /**
     * Get an associative array ( option_name => option_title ) with the list
     * of bulk actions available on this table.
     *
     * @return array
     */
    protected function get_bulk_actions() {
        $actions = array(
            'export'    => __( 'Export to CSV', MP_CHALLENGE_WP_NAME )
        );

        return $actions;
    }

    /**
     * Default method for getting column field values to display
     *
     * @param object $item
     * @param string $column_name
     *
     * @return string
     */
    protected function column_default( $item, $column_name ) {

        if( $column_name ) {

            return sanitize_text_field( $item[$column_name] );
        }

        $this->logEndpointErrors(
            '$column_name error for item: ' . $item . '$column_name value: ' . $column_name,
            'AdminDataTable:get_columns'
        );
    }

    /**
     * Get the cb column value to display
     *
     * @param object $item
     *
     * @return string
     */
    protected function column_cb($item) {

        return sprintf(
            '<input type="checkbox" name="mp_item[]" value="%s" />', (int) $item['id']
        );
    }

    /**
     * Get the email column value to display
     *
     * @param object $item
     *
     * @return string
     */
    protected function column_email($item) {

        return sanitize_email($item['email']);
    }

    /**
     * Message to be displayed when there are no items
     */
    public function no_items() {
        _e( 'Sorry, no people were found.', MP_CHALLENGE_WP_NAME );
    }

    public function prepare_items() {

        $all_items = $this->getSearchResultsFiltered();
        $per_page = $this->get_items_per_page('people_per_page', 5);
        $current_page = $this->get_pagenum();

        $total_items = count($all_items);
        usort( $all_items,  array( $this, 'usortReorder' ));

        $page_data = array_slice($all_items,(($current_page-1)*$per_page),$per_page);

        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page
        ) );

        $hidden = array();
        $this->_column_headers = array($this->columns, $hidden, $this->get_sortable_columns());

        $this->items = $page_data;

    }

    /**
     * Gets all data items (rows) before filtering.
     *
     * @return   array  The table data items.
     */
    protected function getAllItems() {

        $items = array();

        if(isset($this->table_data->rows)) {

            foreach ($this->table_data->rows as $row) {
                $items[] = (array) $row;
            }
        }

        return $items;
    }

    /**
     * Gets the table title.
     *
     * @return   string  The table title
     */
    public function getTableTitle() {

        if(isset($this->table_data->title)) {

            return $this->table_data->title;
        }

        return 'Memberpress Challenge Table';
    }

    /**
     * Gets the request parameter.
     *
     * @param      string  $key      The query parameter
     * @param      string  $default  The default value to return if not found
     *
     * @return     string | array    The request parameter
     */
    protected function getRequestParameter( $key, $default = '' ) {

        if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {

            return $default;
        }
        if( is_array($_REQUEST[ $key ]) ) {

            return $_REQUEST[ $key ];
        }

        return strip_tags( (string) wp_unslash( $_REQUEST[ $key ] ) );
    }

    /**
     * Process table actions
     *
     * @return    void
     */
    protected function processActions() {

        if ($this->current_action() === 'export') {

            $request_data_ids = $this->getRequestParameter('mp_item');

            if($request_data_ids) {
                $request_data = array();

                foreach ($this->all_items as $item) {

                    if (in_array($item['id'], $request_data_ids)) {
                        $request_data[] = (array)$item;
                    }
                }

                wp_add_inline_script(
                    MP_CHALLENGE_WP_NAME, '
                    var MeprChallengeExportData = ' . json_encode($request_data),
                    'before'
                );
            }

        }
    }

    /**
     * Filter items based on search field input
     *
     * @return   array
     */
    protected function getSearchResultsFiltered() {

        $filtered_data = array();
        $search_key = sanitize_text_field($this->getRequestParameter('s'));
        if($search_key) {
            foreach ($this->all_items as $item) {
                foreach ($item as $key=>$value) {

                    if($key === 'date') {
                        $value = $this->column_date($item);
                    }
                    if (strpos(
                        strtolower((string)$value),
                        strtolower($search_key)
                        ) !== false) {
                        $filtered_data[] = $item;
                        break;
                    }
                }
            }
            return $filtered_data;
        }


        return $this->all_items;
    }

    /**
     * usort callback to sort items
     *
     * @return int
     */
    protected function usortReorder( $a, $b ) {

        $order_by = $this->getRequestParameter('orderby', 'id');
        $order = $this->getRequestParameter('order', 'asc');

        $result = strcmp( $a[$order_by], $b[$order_by] );

        return ( $order === 'asc' ) ? $result : -$result;
    }

}
