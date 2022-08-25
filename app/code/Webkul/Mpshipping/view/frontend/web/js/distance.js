
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul
 * @copyright   Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */ 
/*jshint jquery:true*/
require(
    [
        'jquery',
        'Magento_Ui/js/modal/alert',
        'jquery/ui'
    ],
    function ($, alert) {
        'use strict';
    var autocomplete = new google.maps.places.Autocomplete($("#wkmpshipping-address")[0], {});
    google.maps.event.addListener(autocomplete, 'place_changed', function() {
        var place = autocomplete.getPlace();
        $("#wkmpshipping-latitude").val(place.geometry.location.lat().toFixed(5));
        $("#wkmpshipping-longitude").val(place.geometry.location.lng().toFixed(5));
    });
});
