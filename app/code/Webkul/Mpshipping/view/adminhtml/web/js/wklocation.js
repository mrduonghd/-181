/**
 * Webkul Mpshipping requirejs.
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
require(
    [
        'jquery',
        'mage/translate',
    ],
    function ($) {
        $("#carriers_webkulshipping_latitude").attr('readonly','readonly');
        $("#carriers_webkulshipping_longitude").attr('readonly','readonly');
        
        var autocomplete = new google.maps.places.Autocomplete($("#carriers_webkulshipping_location")[0], {});
        google.maps.event.addListener(autocomplete, 'place_changed', function() {
            var place = autocomplete.getPlace();
            $("#carriers_webkulshipping_latitude").val(place.geometry.location.lat().toFixed(5));
            $("#carriers_webkulshipping_longitude").val(place.geometry.location.lng().toFixed(5));
        });
    }
);
