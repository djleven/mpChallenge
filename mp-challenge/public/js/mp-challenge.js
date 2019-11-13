/**
 * The js file for the plugin's shortcode view(s)
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/public/js
 */
(function($) {
    $(document).ready(function(){

        var list = {
            /**
             * Class variables
             */
            pageSelector$: '.mp-challenge',
            tableSelector: 'mp-challenge-table',
            headerClass: 'mp-challenge-header',
            bodyClass: 'mp-challenge-body',
            /**
             * AJAX call - retrieve the table data
             * Add event listener for sorting
             */
            init: function() {
                var _this = this
                $('body').on('click', 'th', this.sortColumns)
                $.ajax({
                    url: 'http://localhost/wp-json/mp-challenge/v2/get-data',
                    type: 'GET',
                    error: function() {
                        _this.displayError()
                    },
                    success: function(data) {
                        _this.loadTableData(data)
                    }
                });
            },
            /**
             * Display error loading table view
             */
            displayError: function() {

                $(this.pageSelector$).html('<p>An error has occurred</p>');
            },
            /** Create the table and add to DOM
             *
             * @param  response object  The table data object
             */
            loadTableData: function(response) {
                if(response.hasOwnProperty("rows") &&
                    response.hasOwnProperty("headers")
                ) {
                    var tableColumns = response.headers;
                    var tableRows = response.rows;
                    var element = response.title ? '<h3>' + response.title + '</h3>' : '';

                    element += '<table class="' + this.tableSelector + '">';
                    element += this.getTableHead(tableColumns)
                    element += this.getTableBody(tableRows, tableColumns)
                    element += '</table>';

                    return $(this.pageSelector$).html(element);
                }

                this.displayError()
            },
            /** Get the table head
             *
             * @param  tableColumns array   The table columns
             */
            getTableHead: function( tableColumns ) {
                var tableHead = '<thead class="' + this.headerClass + '"><tr>';

                $.each(tableColumns, function( index, value ) {

                    tableHead += '<th>' + value + '</th>';
                });

                tableHead += '</tr></thead>';

                return tableHead;
            },
            /** Get the table body
             *
             * @param  tableRows  object  The table rows
             */
            getTableBody: function( tableRows, tableColumns ) {
                var tableBody = '<tbody class="' + this.bodyClass + '">';
                var dataKeysToLabels = this.mapDataKeysToLabels(tableRows, tableColumns);

                $.each(tableRows, function( index, row ) {
                    tableBody+='<tr>';

                    $.each(row, function( key ) {

                        tableBody +=
                            '<td class="' + key + '">' +
                            '<span class="mobile-label"> ' + dataKeysToLabels[key] + ':  </span>'
                            + row[key] + '</td>';
                    });

                    tableBody+='</tr>';
                });

                tableBody += '</tbody>';

                return tableBody;
            },
            /** Map row data keys to header table labels
             *
             *  Used to display alternative labels on stacked table (mobile) view
             *
             * Assumes that (first) row data attributes are equivalent to the labels
             *
             * @param  tableRows    object  The table rows
             * @param  tableColumns array   The table columns
             */
            mapDataKeysToLabels: function( tableRows, tableColumns ) {
                var dataKeysToLabels = {}
                var counter = 0
                $.each(tableRows[0], function( key ) {

                    dataKeysToLabels[key] = tableColumns[counter]
                    counter++;
                });

                return dataKeysToLabels
            },
            /**
             * Sort rows - callback on table header column click
             *
             * Sorts descending and then toggles asc/desc on subsequent column clicks
             *
             */
            sortColumns: function() {
                var compareCellValues = function(index) {
                    var getCellValue = function (row, index) {

                        return $(row).children('td').eq(index).text()
                    }

                    return function (a, b) {
                        var valA = getCellValue(a, index)
                        var valB = getCellValue(b, index)

                        if($.isNumeric(valA) && $.isNumeric(valB)) {
                            return valA - valB
                        } else {
                            return valA.toString().localeCompare(valB)
                        }
                    }
                }
                var table = $(this).parents('table').eq(0)
                var rows = table.find('tr:gt(0)')  // exclude first row (headers)
                    .toArray()
                    .sort(compareCellValues($(this).index()))

                // assign 'asc' boolean value to clicked element
                this.asc = !this.asc

                if (!this.asc){
                    rows = rows.reverse()
                }
                for (var i = 0; i < rows.length; i++) {
                    table.append(rows[i])
                }
            }
        }

        list.init();

    });
}(jQuery));
