/**
 * The js file for the admin facing views of the plugin
 *
 * @link       https://e-leven.net/
 * @since      1.0.0
 *
 * @package    mp-challenge
 * @subpackage mp-challenge/public/js
 */

(function($) {
    jQuery(document).ready(function() {

        if(typeof MeprChallengeExportData !== 'undefined') {
            let csvContent = "data:text/csv;charset=utf-8,";
            csvContent += arrayToCSV(MeprChallengeExportData);

            var encodedUri = encodeURI(csvContent);
            window.open(encodedUri);

            function arrayToCSV(objArray) {
                const array = typeof objArray !== 'object' ? JSON.parse(objArray) : objArray;
                let str = `${Object.keys(array[0]).map(value => `"${value}"`).join(",")}` + '\r\n';

                return array.reduce((str, next) => {
                    str += `${Object.values(next).map(value => `"${value}"`).join(",")}` + '\r\n';
                    return str;
                }, str);
            }
        }
    });
})(jQuery);