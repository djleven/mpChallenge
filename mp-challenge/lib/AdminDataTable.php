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

    const TIMESTAMP_KEY = 'timestamp';
    const DATE_KEY = 'date';

    use ErrorLoggerTrait;

    /**
     * The object containing the data to display
     * False for no data
     *
     * @var object | bool
     */
    protected $table_data;

    /**
     * Table data items (rows)
     *
     * @var array
     */
    protected $all_items;

    /**
     * Table data items (rows) filtered by user input
     *
     * @var array
     */
    protected $filtered_items;

    /**
     * The list of table columns
     *
     * @var array
     */
    protected $columns;

    /**
     * Class for the table admin messages
     *
     * @var AdminNoticesAdminTable
     */
    protected $admin_msg;

    public function __construct($table_data = false) {

        if($table_data) {
            $this->admin_msg = new AdminNoticesAdminTable();
            $this->table_data = $table_data;
            $this->all_items = $this->getAllItems();
            $this->filtered_items = $this->getFilteredItems();
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

        $hidden_data_keys = array(self::TIMESTAMP_KEY);
        $columns = array();

        if(isset($this->table_data->headers) && isset($this->table_data->rows[1])) {
            $column_labels = $this->table_data->headers;
            $firstRowItem = $this->table_data->rows[1];
            $count = 0;
            foreach ($firstRowItem as $key => $value) {
                // exclude timestamp data
                if(!in_array ($key, $hidden_data_keys)) {
                    $columns[$key] = __(sanitize_text_field($column_labels[$count]), MP_CHALLENGE_WP_NAME);
                    $count++;
                }
            }
            if($count === count($column_labels)) {
                // add checkbox column to top
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

    /**
     * Generate the table navigation above or below the table and remove the
     * _wp_http_referrer because it generates a URL that is too large
     *
     * @param string $which
     * @return void
     */
    protected function display_tablenav( $which ) {
        if ( 'top' === $which ) {
            wp_nonce_field( 'bulk-' . $this->_args['plural'], 'nonce-mp-list-table', false );
        }
        ?>

        <div class="tablenav <?php echo esc_attr( $which ); ?>">

            <?php if ( $this->has_items() ) : ?>
                <div class="alignleft actions bulkactions">
                    <?php $this->bulk_actions( $which ); ?>
                </div>
            <?php
            endif;
            $this->extra_tablenav( $which );
            $this->pagination( $which );
            ?>

            <br class="clear" />
        </div>
        <?php
    }


    function extra_tablenav( $which ) {

        if ( $which == "top" ){
            ?>
            <div class="alignleft actions bulkactions">
                <select name="date-filter">
                    <option value=""><?php _e( 'All Dates.', MP_CHALLENGE_WP_NAME ) ;?></option>
                    <?php
                    $months = $this->getAvailableDateFilterOptions();

                    foreach( $months as $key => $value ){
                        $selected =
                            Utils::getRequestParameter('date-filter') === $key ?
                                ' selected = "selected"' : '';
                        ?>
                        <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $value; ?></option>
                        <?php
                    } ?>
                </select>
                <button class="button-secondary" name="refresh" type="submit">
                    <?php _e( 'Filter', MP_CHALLENGE_WP_NAME ) ;?>
                </button>
            </div>
            <?php
        }
        if ( $which == "bottom" ){
            //After the table
        }
    }

    public function prepare_items() {
        $all_items = $this->filtered_items;
        $per_page = $this->get_items_per_page('people_per_page', 5);
        $current_page = $this->get_pagenum();

        usort( $all_items,  array( $this, 'usortReorder' ));

        $page_data = array_slice($all_items,(($current_page-1)*$per_page),$per_page);

        $this->set_pagination_args( array(
            'total_items' => count($all_items),
            'per_page'    => $per_page
        ) );

        $hidden = get_hidden_columns( $this->screen );
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
     * Filter row items based on available search filters
     *
     * @return   array
     */
    protected function getFilteredItems() {

        $all_items = $this->getSearchResultsFiltered();

        return $this->filterItemsByDate($all_items);
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

        return __( 'Memberpress Challenge Table', MP_CHALLENGE_WP_NAME );
    }

    /**
     * Process table actions
     *
     * @return    void
     */
    protected function processActions() {

        if (Utils::getRequestParameter('reset', false,'int', 1)) {

            wp_redirect( '/wp-admin/admin.php?page=' . MP_CHALLENGE_WP_NAME );
            exit;
        }
        else if (Utils::getRequestParameter('refresh', false,'int', 1)) {

            add_action( 'admin_notices', array($this->admin_msg, 'data_refresh_success') );
        }
        else if ($this->current_action() === 'export') {

            $request_data_ids = Utils::getRequestParameter('mp_item', false, 'int', 1);

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
                add_action( 'admin_notices', array($this->admin_msg, 'export_success') );

            } else {

                add_action( 'admin_notices', array($this->admin_msg, 'no_items_selected_for_bulk_action') );
            }
        }
    }

    /**
     * Get the months available as date filters
     *
     * Depending on preference this can be done based on
     *
     * a-) items already filtered by search input
     *
     * b-) non-filtered results (all items before filters)
     * The latter is the way wp date filters work for pages/posts
     *
     * TODO: This could be made into a page option
     *
     * @return   array
     */
    protected function getAvailableDateFilterOptions() {

        // option a
        // $items = $this->filtered_items;

        //option b
        $items = $this->all_items;
        $months = array();

        foreach( $items as $key => $value ) {
            if( isset($value[self::TIMESTAMP_KEY]) &&
                Utils::isValidTimeStamp((string) $value[self::TIMESTAMP_KEY])
            ) {
                $month = Utils::getDateFilterFormat($value[self::TIMESTAMP_KEY]);
                $month_label = str_replace("-", " ", $month);
                $months[$month] = $month_label;
            }
        }

        return array_unique($months);
    }

    /**
     * Filter row items based on search field input
     *
     * @return   array
     */
    protected function getSearchResultsFiltered() {

        $exclude_data_keys = array(self::TIMESTAMP_KEY);
        $filtered_data = array();
        $search_key = Utils::getRequestParameter('s');
        if($search_key) {
            foreach ($this->all_items as $item) {
                foreach ($item as $key=>$value) {
                    // exclude timestamp data from search
                    if(!in_array ($key, $exclude_data_keys)) {
                        if (strpos(strtolower((string)$value), strtolower($search_key))
                            !== false)
                        {
                            $filtered_data[] = $item;
                            break;
                        }
                    }
                }
            }
            return $filtered_data;
        }

        return $this->all_items;
    }

    /**
     * Filter row items based on date filter input
     *
     * @param   $items array
     * @return   array
     */
    protected function filterItemsByDate($items) {

        $filtered_data = array();
        $search_key = Utils::getRequestParameter('date-filter');
        if($search_key) {
            foreach ($items as $item) {
                if( isset($item[self::TIMESTAMP_KEY]) &&
                    Utils::isValidTimeStamp((string) $item[self::TIMESTAMP_KEY])
                ) {
                    $month = Utils::getDateFilterFormat($item[self::TIMESTAMP_KEY]);
                    if($month === $search_key) {
                        $filtered_data[] = $item;
                    }
                }
            }
            return $filtered_data;
        }

        return $items;
    }

    /**
     * usort callback to sort items - comparing the two strings
     *
     * @param $a  string   The first string.
     * @param $b  string   The second string.
     * @return    int
     */
    protected function usortReorder( $a, $b ) {

        $order_by = Utils::getRequestParameter('orderby', 'id');
        $order = Utils::getRequestParameter('order', 'asc');

        if($order_by === self::DATE_KEY) {
            $order_by = self::TIMESTAMP_KEY;
        }
        $result = strcmp( $a[$order_by], $b[$order_by] );

        return ( $order === 'asc' ) ? $result : -$result;
    }
}
