/**
 * Created by Hideaki Oguchi on 2016/05/16.
 */

$jq = jQuery.noConflict();

$jq(document).ready(function () {
    // change table
    $jq(document).on('change', 'select.target_table', function () {

        var object_name = this.name;
        var table_name = $jq(this).val();
        var fields;

        for (var i = 0; i < tables.length; i++) {
            if (tables[i]['name'] === table_name) {
                fields = tables[i]['fields'];
                break;
            }
        }

        var param_name = object_name.substr('table_name'.length);
        var $fieldObj = $jq('select[name=column_name' + param_name + ']');

        $fieldObj.empty();

        for (i = 0; i < fields.length; i++) {
            $fieldObj.append('<option>' + fields[i] + '</option>');
        }

    });

    // submit button
    var submitActor = null;
    $jq('button[type=submit]').on("click", function () {
        submitActor = $jq(this);
    });

    $jq('#sql_preview_box').hide();
});

function setupTables(group_num, param_num) {

    var table_name = 'table_name' + group_num + '-' + param_num;
    var $tableObj = $jq('select[name=' + table_name + ']');

    $tableObj.empty();

    for (var i = 0; i < tables.length; i++) {
        $tableObj.append('<option>' + tables[i]['name'] + '</option>');
    }
}

function setupFields(group_num, param_num, table_name) {
    var fields;

    for (var i = 0; i < tables.length; i++) {
        if (tables[i]['name'] === table_name) {
            fields = tables[i]['fields'];
            break;
        }
    }

    var field_name = 'column_name' + group_num + '-' + param_num;
    var $fieldObj = $jq('select[name=' + field_name + ']');

    $fieldObj.empty();

    for (i = 0; i < fields.length; i++) {
        $fieldObj.append('<option>' + fields[i] + '</option>');
    }
}

function addConditional(group_num) {

    var group_count = group_num;
    var param_count = parseInt($jq('input[name=conditional_count' + group_num + ']').val());
    var new_param = param_count;
    var param_name = group_count + '-' + new_param;
    var last_param = param_count - 1;
    if (last_param < 0) {
        last_param = 0;
    }

    var $groupObj = $jq('div#group' + group_num + '-' + last_param);
    $groupObj.after(conditionalLine(param_name, new_param, group_count));

    $jq('input[name=conditional_count' + group_num + ']').val(param_count + 1);

    setupTables(group_count, new_param);
    setupFields(group_count, new_param, tables[0]['name']);
}

function deleteConditional(group_num, delete_param) {

    var $line = $jq('div#group' + group_num + '-' + delete_param);
    $line.remove();

    // renumbering index
    var param_count = $jq('input[name=conditional_count' + group_num + ']').val();

    for (var i = delete_param + 1; i < param_count; i++) {
        var param_name = group_num + '-' + i;
        $line = $jq('div#group' + param_name);
        if ($line[0]) {
            var new_param_name = group_num + '-' + (i - 1);

            var $table_label_obj = $jq('#label_table_name' + param_name);
            var $table_obj = $jq('#table_name' + param_name);
            var $field_label_obj = $jq('#label_column_name' + param_name);
            var $field_obj = $jq('#column_name' + param_name);
            var $and_or_obj = $jq('select[name=and_or' + param_name + ']');
            var $compare_obj = $jq('select[name=compare' + param_name + ']');
            var $value_label_obj = $jq('#label_column_value' + param_name);
            var $value_obj = $jq('input[name=column_value' + param_name + ']');
            var $delete_obj = $jq('#delete' + param_name);

            $line.attr('id', 'group' + new_param_name);
            $and_or_obj.attr('name', 'and_or' + new_param_name);
            $table_label_obj.attr('id', 'label_table_name' + new_param_name);
            $table_label_obj.attr('for', 'table_name' + new_param_name);
            $table_obj.attr('id', 'table_name' + new_param_name);
            $table_obj.attr('name', 'table_name' + new_param_name);
            $field_label_obj.attr('id', 'label_column_name' + new_param_name);
            $field_label_obj.attr('for', 'column_name' + new_param_name);
            $field_obj.attr('id', 'column_name' + new_param_name);
            $field_obj.attr('name', 'column_name' + new_param_name);
            $compare_obj.attr('name', 'compare' + new_param_name);
            $value_label_obj.attr('id', 'label_column_value' + new_param_name);
            $value_label_obj.attr('for', 'column_value' + new_param_name);
            $value_obj.attr('id', 'column_value' + new_param_name);
            $value_obj.attr('name', 'column_value' + new_param_name);
            $delete_obj.attr('id', 'delete' + new_param_name);
            $delete_obj.attr('onclick', 'deleteConditional(' + group_num + ',' + (i - 1) + ')');
        }
    }

    param_count--;
    if (param_count <= 0) {
        // delete group
    } else {
        // param count
        $jq('input[name=conditional_count' + group_num + ']').val(param_count);
    }
}

function conditionalLine(param_name, new_param, group_count) {

    var and_or_string;
    var delete_command = '';

    if (new_param > 0) {
        var and_or_name = 'and_or' + param_name;

        and_or_string = '<select name="' + and_or_name + '" class="form-control">' +
            '<option value="AND">AND</option>' +
            '<option value="OR">OR</option>' +
            '</select>';

        delete_command = ' <a href="javascript:void(0)" onclick="deleteConditional(' + group_count + ', ' + new_param + ')" id="delete' + param_name + '" class="ml-2"><i class="bi bi-x-circle-fill"></i></a>';
    } else {
        and_or_string = '<input type="hidden" name="and_or' + param_name + '" value="">';
    }

    var label_table_name = 'label_table_name' + param_name;
    var label_field_name = 'label_column_name' + param_name;
    var table_name = 'table_name' + param_name;
    var field_name = 'column_name' + param_name;
    var compare_name = 'compare' + param_name;
    var value_name = 'column_value' + param_name;

    return '<div id="group' + param_name + '" class="target_condition_line">' +
        '<div class="form-inline">' +
        '<div class="form-group target_and_or_select">' +
        and_or_string +
        '</div>' +
        "\n" +

        '<div class="form-group mx-2">' +
        '<label for="' + table_name + '" id="' + label_table_name + '" >' + table_string + '</label>:' +
        '<select name="' + table_name + '" id = "' + table_name + '" class="target_table form-control">' +
        '</select>' +
        '</div>' +
        "\n" +
        '<div class="form-group mx-2">' +
        '<label for="' + field_name + '" id="' + label_field_name + '" >' + field_string + '</label>:' +
        '<select name="' + field_name + '" id="' + field_name + '" class="form-control">' +
        '</select>' +
        '</div>' +
        "\n" +
        '<div class="form-group mx-2">' +
        '<select name="' + compare_name + '" class="form-control">' +
        '<option value="=">=</option>' +
        '<option value="&lt;&gt;">&lt;&gt;</option>' +
        '<option value="&lt;">&lt;</option>' +
        '<option value="&lt;=">&lt;=</option>' +
        '<option value="&gt;">&gt;</option>' +
        '<option value="&gt;=">&gt;=</option>' +
        '<option value="LIKE">%LIKE%</option>' +
        '<option value="NOTLIKE">NOT %LIKE%</option>' +
        '<option value="ISNULL">IS NULL</option>' +
        '<option value="ISNOTNULL">IS NOT NULL</option>' +
        '</select>' +
        '</div>' +
        "\n" +
        '<div class="form-group mx-2">' +
        '<label for="' + value_name + '" id="label_column_value' + param_name + '" >' + value_string + '</label>:' +
        '<input type="text" name="' + value_name + '" id="' + value_name + '">' +
        '</div>' +
        delete_command +
        '</div>' +
        '</div>';
}

function reindexCondition(old_group_idx, new_group_idx, param_idx) {
    var old_param_name = old_group_idx + '-' + param_idx;
    var new_param_name = new_group_idx + '-' + param_idx;

    var $line = $jq('div#group' + old_param_name);

    if ($line[0]) {
        var $table_label_obj = $jq('#label_table_name' + old_param_name);
        var $table_obj = $jq('#table_name' + old_param_name);
        var $field_label_obj = $jq('#label_column_name' + old_param_name);
        var $field_obj = $jq('#column_name' + old_param_name);
        var $hidden_and_or_obj = $jq('input[name=and_or' + old_param_name + ']');
        var $and_or_obj = $jq('select[name=and_or' + old_param_name + ']');
        var $compare_obj = $jq('select[name=compare' + old_param_name + ']');
        var $value_label_obj = $jq('#label_column_value' + old_param_name);
        var $value_obj = $jq('input[name=column_value' + old_param_name + ']');
        var $delete_obj = $jq('#delete' + old_param_name);

        $line.attr('id', 'group' + new_param_name);
        $hidden_and_or_obj.attr('name', 'and_or' + new_param_name);
        $and_or_obj.attr('name', 'and_or' + new_param_name);
        $table_label_obj.attr('id', 'label_table_name' + new_param_name);
        $table_label_obj.attr('for', 'table_name' + new_param_name);
        $table_obj.attr('id', 'table_name' + new_param_name);
        $table_obj.attr('name', 'table_name' + new_param_name);
        $field_label_obj.attr('id', 'label_column_name' + new_param_name);
        $field_label_obj.attr('for', 'column_name' + new_param_name);
        $field_obj.attr('id', 'column_name' + new_param_name);
        $field_obj.attr('name', 'column_name' + new_param_name);
        $compare_obj.attr('name', 'compare' + new_param_name);
        $value_label_obj.attr('id', 'label_column_value' + new_param_name);
        $value_label_obj.attr('for', 'column_value' + new_param_name);
        $value_obj.attr('id', 'column_value' + new_param_name);
        $value_obj.attr('name', 'column_value' + new_param_name);
        $delete_obj.attr('id', 'delete' + new_param_name);
        $delete_obj.attr('onclick', 'deleteConditional(' + new_group_idx + ',' + param_idx + ')');
    }
}

function addGroup() {
    var group_count = parseInt($jq('input[name=conditional_count]').val());
    var last_group = group_count - 1;
    if (last_group < 0) {
        last_group = 0;
    }
    var new_param = 0;
    var new_group = group_count;

    var param_name = new_group + '-' + new_param;

    var conditional = conditionalLine(param_name, new_param, group_count);
    var $beforeObj = $jq('div#group' + last_group);

    // new
    if ($beforeObj.length === 0) {
        $beforeObj = $jq('div#title_container');
    }

    var html = '<input type="hidden" name="conditional_count' + new_group + '" value="1">';
    var delete_command = '';

    if (group_count === 0) {
        html += '<input type="hidden" name="and_or' + new_group + '" value="AND">';
    } else {
        html += '<select name="and_or' + new_group + '" class="my-2">' +
            '<option value="AND">AND</option>' +
            '<option value="OR">OR</option>' +
            '</select>';
        delete_command = ' <a href="javascript:void(0)" onclick="deleteGroup(' + group_count + ')" id="delete' + group_count + '" class="ml-2"><i class="bi bi-x-circle-fill"></i></a>';
    }

    html += '<div class="card" id="group' + new_group + '">' +
        '<div class="card-header">' +
        '<span id="group_title' + new_group + '">' + group_string + (new_group + 1) + '</span>' +
        delete_command +
        '</div>' +
        '<div class="card-body">' +

        conditional +

        '<button type="button" class="btn btn-primary btn-xs" id="addConditional' + new_group + '" onclick="addConditional(' + new_group + ')">' +
        '<i class="bi bi-plus"></i></button>' +
        '</div>' + // panel-body
        '</div>';

    $beforeObj.after(html);

    $jq('input[name=conditional_count]').val(group_count + 1);

    setupTables(new_group, new_param);
    setupFields(new_group, new_param, tables[0]['name']);
}

function deleteGroup(delete_group) {
    var group_count = $jq('input[name=conditional_count]').val();

    var $line = $jq('div#group' + delete_group);
    var $group_count_obj = $jq('input[name=conditional_count' + delete_group + ']');
    var $and_or_obj = $jq('select[name=and_or' + delete_group + ']');

    $line.remove();
    $group_count_obj.remove();
    $and_or_obj.remove();

    for (var i = delete_group + 1; i < group_count; i++) {

        var old_group_idx = i;
        var new_group_idx = i - 1;
        var $div_obj = $jq('div#group' + old_group_idx);
        $group_count_obj = $jq('input[name=conditional_count' + old_group_idx + ']');
        $and_or_obj = $jq('select[name=and_or' + old_group_idx + ']');
        var $delete_obj = $jq('#delete' + old_group_idx);
        var $add_obj = $jq('#addConditional' + old_group_idx);
        var $title_obj = $jq('#group_title' + old_group_idx);

        $div_obj.attr('id', 'group' + new_group_idx);
        $group_count_obj.attr('name', 'conditional_count' + new_group_idx);
        $and_or_obj.attr('name', 'and_or' + new_group_idx);
        $delete_obj.attr('id', 'delete' + new_group_idx);
        $delete_obj.attr('onclick', 'deleteGroup(' + new_group_idx + ')');
        $add_obj.attr('id', 'addConditional' + new_group_idx);
        $add_obj.attr('onclick', 'addConditional(' + new_group_idx + ')');

        var title = group_string + (new_group_idx + 1);
        $title_obj.text(title);
        $title_obj.attr('id', 'group_title' + new_group_idx);

        var param_count = $jq('input[name=conditional_count' + new_group_idx + ']').val();

        for (var j = 0; j < param_count; j++) {
            reindexCondition(old_group_idx, new_group_idx, j);
        }
    }

    $jq('input[name=conditional_count]').val(group_count - 1);
}

function onRecipientsPreviewButton(page_num, limit) {

    setTitleValidation(false);

    if ($jq('#target-form').parsley().validate({force: false})) {

        $jq('#recipientsPreviewButton').prop("disabled", true);
        $jq('#recipient_loading_message').show();

        var $form = $jq('#target-form');
        var param = {};

        $jq($form.serializeArray()).each(function (i, v) {
            param[v.name] = v.value;
        });

        var json = $jq.stringify(param);
        ajax_request_recipients_preview(json, page_num, limit);
    }

    setTitleValidation(true);
}

function build_recipients_preview(recipients, page_num, limit, total_page, total_count) {
    $jq('#recipient_count').text(receiver_count_string + ' ' + total_count);

    var $target_element = $jq('#recipient_list');
    $target_element.empty();

    for (var i = 0; i < recipients.length; i++) {
        var recipient = recipients[i];
        var recipient_id = recipient['recipient_id'];
        var email = recipient['email'];
        $target_element.append('<li class="list-group-item">' + email);
    }

    $target_element = $jq('#pagenation');
    $target_element.empty();

    if (total_page > 1) {

        var $nav = $jq('<nav>').appendTo($target_element);
        var $pagenation = $jq('<ul class="pagination">').appendTo($nav);

        // Start
        var start_page = page_num - 2;
        start_page = (start_page < 0) ? 0 : start_page;

        // Stop
        var stop_page = start_page + 5;
        var count = stop_page - start_page;
        stop_page = (count > 5) ? (stop_page - (count - 5)) : stop_page;
        stop_page = (stop_page > total_page) ? total_page : stop_page;

        count = stop_page - start_page;
        start_page = (count < 5) ? (start_page - (5 - count)) : start_page;
        start_page = (start_page < 0) ? 0 : start_page;

        stop_page = (stop_page < start_page) ? start_page : stop_page;

        // Pre
        var pre_page = start_page - 1;
        pre_page = (pre_page < 0) ? 0 : pre_page;

        // Next
        var next_page = stop_page;
        next_page = (next_page >= total_page) ? (total_page - 1) : next_page;
        next_page = (next_page < 0) ? 0 : next_page;

        var $li = $jq('<li class="page-item">').appendTo($pagenation);
        $li.append('<a href="javascript:void(0)" aria-label="Previous" onclick="onRecipientsPreviewButton(' + pre_page + ', ' + limit + ')" class="page-link"> <span aria-hidden="true">&laquo;</span> </a>');

        for (i = start_page; i < stop_page; i++) {
            if (page_num === i) {
                $li = $jq('<li class="page-item active">').appendTo($pagenation);
            } else {
                $li = $jq('<li class="page-item">').appendTo($pagenation);
            }

            $li.append('<a href="javascript:void(0)" onclick="onRecipientsPreviewButton(' + i + ', ' + limit + ')" class="page-link">' + (i + 1) + '</a>');
        }

        $li = $jq('<li class="page-item">').appendTo($pagenation);
        $li.append('<a href="javascript:void(0)" aria-label="Next" onclick="onRecipientsPreviewButton(' + next_page + ', ' + limit + ')" class="page-link"> <span aria-hidden="true">&raquo;</span> </a>');
    }
}

function notify_recipients_preview_success(recipients, page_num, limit, total_page, total_count) {

    recipients = (recipients instanceof Array) ? recipients : [];

    for (var i = 0; i < recipients.length; i++) {
        var recipient = recipients[i];
        var recipient_id = recipient['recipient_id'];
        var email = recipient['email'];
    }

    build_recipients_preview(recipients, page_num, limit, total_page, total_count);

    $jq('#recipientsPreviewButton').prop("disabled", false);
    $jq('#recipient_loading_message').hide();
}

function requestPreviewSQL() {
    var $form = $jq('#target-form');
    var param = {};

    $jq('#sql_preview_box').hide();
    $jq('#sql_preview').text('');

    $jq($form.serializeArray()).each(function (i, v) {
        param[v.name] = v.value;
    });

    var json = $jq.stringify(param);
    ajax_request_target_sql_preview(json);
}

function notify_target_sql_preview(sql) {
    $jq('#sql_preview').text(sql);
    $jq('#sql_preview_box').show();
}

function setTitleValidation(enableValidation) {
    $jq("input[name=title]").attr("data-parsley-required", enableValidation);
}