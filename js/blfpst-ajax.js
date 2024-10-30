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

function ajax_request_calendar_view(year, month, day, callback) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_calendar_view */
    /** @var {object} blfpst_conf.ajaxActions.request_calendar_view.action */
    ajaxInitializer({
        'success': callback,
        'data': {
            "action": blfpst_conf.ajaxActions.request_calendar_view.action,
            'nonce': blfpst_conf.ajaxNonce,
            'year': year,
            'month': month,
            'day': day
        }
    });
}

function ajax_request_recipient_count(target_id) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_recipient_count */
    /** @var {object} blfpst_conf.ajaxActions.request_recipient_count.action */
    ajaxInitializer({
        'success': recipient_count_success,
        'data': {
            'action': blfpst_conf.ajaxActions.request_recipient_count.action,
            'nonce': blfpst_conf.ajaxNonce,
            'target_id': target_id
        }
    });
}

function ajax_request_sending_status(send_mail_id) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_sending_status */
    /** @var {object} blfpst_conf.ajaxActions.request_sending_status.action */
    ajaxInitializer({
        'success': sending_status_success,
        'data': {
            'action': blfpst_conf.ajaxActions.request_sending_status.action,
            'nonce': blfpst_conf.ajaxNonce,
            'send_mail_id': send_mail_id
        }
    });
}

function ajax_request_mail_template(mail_template_id) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_mail_template */
    /** @var {object} blfpst_conf.ajaxActions.request_mail_template.action */
    ajaxInitializer({
        'success': mail_template_success,
        'data': {
            'action': blfpst_conf.ajaxActions.request_mail_template.action,
            'nonce': blfpst_conf.ajaxNonce,
            'mail_template_id': mail_template_id
        }
    });
}

function ajax_request_recipients_preview(json, page_num, limit) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_recipients_preview */
    /** @var {object} blfpst_conf.ajaxActions.request_recipients_preview.action */
    ajaxInitializer({
        'success': recipients_preview_success,
        'data': {
            'action': blfpst_conf.ajaxActions.request_recipients_preview.action,
            'nonce': blfpst_conf.ajaxNonce,
            'json': json,
            'page_num': page_num,
            'limit': limit
        }
    });
}

function ajax_request_target_sql_preview(json) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_target_sql_preview */
    /** @var {object} blfpst_conf.ajaxActions.request_target_sql_preview.action */
    ajaxInitializer({
        'success': target_sql_preview_success,
        'data': {
            'action': blfpst_conf.ajaxActions.request_target_sql_preview.action,
            'nonce': blfpst_conf.ajaxNonce,
            'json': json
        }
    });
}

function ajax_request_send_test_mail(json) {
    /** @var {object} blfpst_conf */
    /** @var {string} blfpst_conf.ajaxURL */
    /** @var {object} blfpst_conf.ajaxActions */
    /** @var {object} blfpst_conf.ajaxActions.request_send_test_mail */
    /** @var {object} blfpst_conf.ajaxActions.request_send_test_mail.action */
    ajaxInitializer({
        'success': request_send_test_mail_success,
        'data': {
            'action': blfpst_conf.ajaxActions.request_send_test_mail.action,
            'nonce': blfpst_conf.ajaxNonce,
            'json': json
        }
    });
}

var recipient_count_success = function (response_data) {

    response_data = JSON.parse(response_data);
    notify_recipient_count(response_data['count']);
};

var sending_status_success = function (response_data) {

    response_data = JSON.parse(response_data);

    var response_message = response_data['response_message'];

    var send_result = response_data['send_result'];
    var send_request_start_at = response_data['send_request_start_at'];
    var send_request_end_at = response_data['send_request_end_at'];
    var recipient_count = response_data['recipient_count'];
    var success_count = response_data['success_count'];
    var failure_count = response_data['failure_count'];
    var updated_at = response_data['updated_at'];
    var send_mail_id = response_data['send_mail_id'];

    notify_sending_status(send_mail_id,
        send_request_start_at, send_request_end_at,
        recipient_count, success_count, failure_count, send_result,
        updated_at,
        response_message);
};

var mail_template_success = function (response_data) {
    response_data = JSON.parse(response_data);
    var mail_template = response_data['mail_template'];
    notify_mail_template(mail_template);
};

var recipients_preview_success = function (response_data) {
    response_data = JSON.parse(response_data);
    var recipients = response_data['recipients'];
    var page_num = response_data['page_num'];
    var limit = response_data['limit'];
    var total_count = response_data['total_count'];
    var total_page = response_data['total_page'];

    notify_recipients_preview_success(recipients, page_num, limit, total_page, total_count);
};

var target_sql_preview_success = function (response_data) {
    response_data = JSON.parse(response_data);
    var sql = response_data['sql'];
    var result = response_data['result'];
    notify_target_sql_preview(sql, result);
};

var request_send_test_mail_success = function (response_data) {
    response_data = JSON.parse(response_data);
    var errors = response_data['errors'];
    notify_send_test_mail(errors);
};

