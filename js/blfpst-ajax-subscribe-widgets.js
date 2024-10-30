$jq = jQuery.noConflict();

var ajaxInitializer = function (options) {

    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */

    /** @param {string} type */
    /** @param {string} url */
    /** @param {object} data */
    /** @param {object} success */
    /** @param {object} error */
    var defaults = {
        type: 'POST',
        url: blfpst_conf.ajaxURL,
        data: {},
        beforeSend: "",
        success: "",
        error: ""
    };

    var settings = $jq.extend({}, defaults, options);

    $jq.ajax(settings);
};


function ajax_request_subscribe(email, callback) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_subscribe */
    /** @var {object} blfpst_conf.ajaxActions.request_subscribe.action */
    ajaxInitializer({
        'success': callback,
        'data': {
            "action": blfpst_conf.ajaxActions.request_subscribe.action,
            'nonce': blfpst_conf.ajaxNonce,
            'email': email
        }
    });
}

function ajax_request_unsubscribe(email, callback) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_unsubscribe */
    /** @var {object} blfpst_conf.ajaxActions.request_unsubscribe.action */
    ajaxInitializer({
        'success': callback,
        'data': {
            "action": blfpst_conf.ajaxActions.request_unsubscribe.action,
            'nonce': blfpst_conf.ajaxNonce,
            'email': email
        }
    });
}

(function ($) {
    $(document).ready(function () {
        $('.blfpst-subscribe-form').on('click', function () {

            $('#blfpst_subscribe_success_message').hide();
            $('#blfpst_subscribe_error_message').hide();

            var email = $('input[name=subscribe-email]').val();

            ajax_request_subscribe(email, function (response_data) {

                var $success_message_field = $('#blfpst_subscribe_success_message');
                var $error_message_field = $('#blfpst_subscribe_error_message');

                response_data = JSON.parse(response_data);
                var result = response_data['result'];

                if ('error' === result) {
                    var message = response_data['message'];
                    $error_message_field.text(message);
                    $success_message_field.hide();
                    $error_message_field.show();
                }else{
                    $success_message_field.show();
                    $error_message_field.hide();
                }
            });

            return false;
        });

        $('.blfpst-unsubscribe-form').on('click', function () {

            $('#blfpst_unsubscribe_success_message').hide();
            $('#blfpst_unsubscribe_error_message').hide();


            var email = $('input[name=unsubscribe-email]').val();

            ajax_request_unsubscribe(email, function (response_data) {
                var $message_field = $('#blfpst_unsubscribe_success_message');
                var $error_message_field = $('#blfpst_unsubscribe_error_message');

                response_data = JSON.parse(response_data);
                var result = response_data['result'];

                if ('error' === result) {
                    var message = response_data['message'];
                    $error_message_field.text(message);
                    $message_field.hide();
                    $error_message_field.show();
                }else{
                    $message_field.show();
                    $error_message_field.hide();
                }
            });

            return false;
        });
    });
})(jQuery);