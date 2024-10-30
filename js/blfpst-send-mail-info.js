/**
 * Created by Hideaki Oguchi  on 2016/08/16.
 */

$jq = jQuery.noConflict();

$jq(document).ready(function () {
    $jq('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    });
});