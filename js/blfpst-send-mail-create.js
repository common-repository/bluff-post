/**
 * Created by Hideaki Oguchi on 2016/05/04.
 */

$jq = jQuery.noConflict();

$jq(document).ready(function () {

    var submitActor = null;

    // Create
    if ($jq("#test_targets")[0]) {
        if (!isReserved()) {
            $jq('#reserved_panel').hide();
            $jq('#not_reserved_panel').show();
        }

        var target_id = $jq('input[name=target_id]').val();
        setRecipientCount('?');
        if ($jq.isNumeric(target_id) && (target_id > 0)) {
            ajax_request_recipient_count(target_id);
        }

        validateTestTargets();

        $jq('#test_targets').bind("change keyup", function () {
            validateTestTargets();
        });

        if ($jq("#selectTemplateModal")[0]) {
            $jq('#selectTemplateModal').on('show.bs.modal', function () {
                $jq('#selectTemplateModalLoadingMessage').hide();
                $jq('#selectTemplateModalTargetList').show();
            });
        }

        setupParsley();
        setContentTextValidation(isCreateTextMail());

        $jq('button[type=submit]').on("click", function () {
            submitActor = $jq(this);

            if (submitActor) {
                var submitName = submitActor.prop('id');
                if (('saveButton' === submitName) || ('testSendButton' === submitName)) {
                    $jq('#reserved_at').parsley().destroy();
                    $jq('#reserved_at').removeAttr("data-parsley-datetime");
                } else {
                    $jq('#reserved_at').parsley().destroy();
                    $jq('#reserved_at').attr("data-parsley-datetime", "");
                }
            }
        });

        $jq('#mainForm').parsley().on('form:validate', function () {
            if (submitActor) {
                var submitName = submitActor.prop('id');

                $jq('#target_id').removeAttr('required');
                $jq('#target_id').removeAttr('data-parsley-type');
                $jq('#target_id').removeAttr('data-parsley-min');

                if ('registerButton' === submitName) {
                    $jq('#target_id').attr("required", "true");
                    $jq('#target_id').attr("data-parsley-type", "digits");
                    $jq('#target_id').attr("data-parsley-min", "1");
                }
            }
        });

        $jq('#mainForm').parsley().on('field:validated', function () {
            var container = this.$element.data('parsley-errors-container');
            if (container) {
                var $containerObj = $jq(container);

                $containerObj.removeClass('has-feedback has-success');
                $containerObj.removeClass('has-feedback has-error');

                if (this.$element.parsley().isValid({force: false})) {
                    $containerObj.addClass('has-feedback has-success');
                }
                else {
                    $containerObj.addClass('has-feedback has-error');
                }
            }
        });

        $jq('#mainForm').parsley().on('form:submit', function () {
            if (submitActor) {
                var submitName = submitActor.prop('id');
                if ('saveButton' === submitName) {
                    saveDraft();
                }
                else if ('testSendButton' === submitName) {
                    sendTestMail();
                    // return false;
                }
            }
        });

        var defaultDate = ('' === $jq('input[name="reserved_default_date"]').val()) ? false : $jq('input[name="reserved_default_date"]').val();
        var defaultTime = ('' === $jq('input[name="reserved_default_time"]').val()) ? false : $jq('input[name="reserved_default_time"]').val();
        var lang = systemLang();

        $jq('#reserved_at').datetimepicker({
            mask: true,
            lang: lang,
            minDate: '-1970/01/01',//yesterday is minimum date(for today use 0 or -1970/01/01)
            defaultDate: defaultDate,
            defaultTime: defaultTime,
            onChangeDateTime: function (dp, $input) {
                $input.parsley().validate({force: true});
            }
        });

        $jq('input[name="content_type"]').on('change', function () {
            setContentTypeText(isCreateTextMail());
        });

        $jq('[data-toggle="tooltip"]').tooltip({
            container: 'body'
        });

        $jq('#html_preview_button').click(function(){
            openHTMLPreview();
        });

        // Media uploader window
        var custom_media_uploader;
        $jq('#media_upload_button').on("click", function (e) {

            e.preventDefault();

            if (!custom_media_uploader) {

                custom_media_uploader = wp.media({
                    title: choose_image_string,
                    button: {
                        text: choose_image_string
                    },
                    multiple: false
                });

                custom_media_uploader.on('select', function () {
                    var media = custom_media_uploader.state().get('selection').first().toJSON();
                    if(media){
                        var $target = $jq('textarea[name=htmlcontent]');

                        if (isCreateTextMail() || (!is_htmlcontent_last_selected)){
                            $target = $jq('textarea[name=text_content]');
                        }

                        var alt = media.alt;
                        var full_size = media.sizes.full;
                        var width = full_size.width;
                        var height = full_size.height;
                        var tag = '<img src="' + media.url + '" title="' + alt + '" alt="' + alt + '" width="' + width + '" height="' + height + '" border="0" style="display:block;" />';

                        insertAtCaret($target, tag);
                    }
                });
            }

            custom_media_uploader.open();
        });

        var is_htmlcontent_last_selected = true;
        $jq('textarea[name=htmlcontent]').on("focus", function (e) {
            is_htmlcontent_last_selected = true;
        });

        $jq('textarea[name=text_content]').on("focus", function (e) {
            is_htmlcontent_last_selected = false;
        });
    }
});

function isCreateTextMail() {
    var content_type = $jq('input[name=content_type]:checked').val();
    return ('content_type_text' === content_type);
}

function isReserved() {
    return ($jq('input[name=send_type]:checked').val() === 'reserved');
}

function setRecipientCount(count) {
    var $recipient_count = jQuery("#recipient_count");
    $recipient_count.empty();
    $recipient_count.append(count);
}

function changeReserved() {
    var reservedPanel = $jq('#reserved_panel');
    var notReservedPanel = $jq('#not_reserved_panel');

    if (isReserved()) {
        reservedPanel.show();
        notReservedPanel.hide();
    } else {
        reservedPanel.hide();
        notReservedPanel.show();
    }
}

function selectMailTemplate(mail_template_id) {
    $jq('#selectTemplateModalLoadingMessage').show();
    $jq('#selectTemplateModalTargetList').hide();
    ajax_request_mail_template(mail_template_id);
}

function saveDraft() {
    $jq('input[name=admin_action]').val('save');
}

function sendTestMail() {
    $jq('input[name=admin_action]').val('test');
    // $jq("#testSendButton").prop("disabled", true);
    // var $form = $jq('#mainForm');
    // var param = {};
    //
    // $jq($form.serializeArray()).each(function (i, v) {
    //     param[v.name] = v.value;
    // });
    //
    // var json = $jq.stringify(param);
    //
    // ajax_request_send_test_mail(json);
}

function showCalendar() {
    $jq('#reserved_at').datetimepicker('show');
}

function notify_mail_template(mail_template) {
    $jq('#selectTemplateModal').modal('hide');

    var title = mail_template['title'];
    var subject = mail_template['subject'];
    var text_content = mail_template['text_content'];
    var html_content = mail_template['html_content'];
    var from_name = mail_template['from_name'];
    var from_address = mail_template['from_address'];
    var reply_address = mail_template['reply_address'];

    $jq('input[name=from_name]').val(from_name);
    $jq('input[name=from_address]').val(from_address);
    $jq('input[name=reply_address]').val(reply_address);
    $jq('input[name=subject]').val(subject);
    $jq('textarea[name=text_content]').val(text_content);
    $jq('textarea[name=htmlcontent]').val(html_content);

    var is_content_type_text = ('' === html_content);
    if (is_content_type_text) {
        $jq('#content_type_html_label').removeClass('active');
        $jq('#content_type_text_label').addClass('active');
        $jq("input[name='content_type']").val(['content_type_text']);

    } else {
        $jq('#content_type_html_label').addClass('active');
        $jq('#content_type_text_label').removeClass('active');
        $jq("input[name='content_type']").val(['content_type_html']);
    }
    setContentTypeText(is_content_type_text);
}

function notify_recipient_count(recipient_count) {
    setRecipientCount(recipient_count);
}

function notify_send_test_mail(errors) {
    $jq('#sendTestMailModal').modal('hide');
    $jq("#testSendButton").prop("disabled", false);

    if(errors.length === 0){
    }else{
    }
}

function setupParsley() {
    var lang = systemLang();

    window.Parsley
        .addValidator('datetime', {
            requirementType: 'string',
            validateString: function (value) {
                if (!isReserved()) {
                    return true;
                }

                if ('____/__/__ __:__' === value) {
                    return false;
                }

                var result = value.match(/(\d+)\/(\d+)\/(\d+) (\d+):(\d+)/);
                var year = result[1];
                var month = result[2];
                var day = result[3];
                var hour = result[4];
                var minute = result[5];

                var nowDate = new Date();
                var inputDate = new Date(year, month - 1, day, hour, minute, 0);

                var nowDateTime = Math.floor(parseInt(nowDate.getTime()) / (60 * 1000));
                var inputDateTime = Math.floor(parseInt(inputDate.getTime()) / (60 * 1000));
                var diff = inputDateTime - nowDateTime;

                return (diff >= 0);
            },
            messages: {
                en: 'Date time is wrong.',
                ja: '正しい日時を入力してください。'
            }
        });

    window.Parsley.setLocale(lang);

    window.Parsley
        .addValidator('requiredTextContent', {
            requirementType: 'boolean',
            validateString: function (value) {
                if (value !== '') {
                    //return true;
                }
                return false;
            },
            messages: {
                en: 'Please input a content.',
                ja: '本文を入力してください。'
            }
        });
}


function validateTestTargets() {

    if ($jq("#test_mail_message")[0]) {

        var test_mail_message_obj = $jq('#test_mail_message');
        var test_targets = $jq('#test_targets').val();

        var recipient_addresses = test_targets.split(/\r\n|\r|\n/);
        var is_enable_address = true;
        var recipient_count = 0;

        for (var i = 0; i < recipient_addresses.length; i++) {
            var recipient_address = recipient_addresses[i];

            if (recipient_address === '') {
                continue;
            }

            var pos = recipient_address.indexOf(';');
            var mail_address = (pos > -1) ? recipient_address.substring(0, pos) : recipient_address;
            if (!mail_address.match(/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+\.([a-zA-Z0-9\._-]+)+$/)) {
                is_enable_address = false;
                break;
            }

            recipient_count++;
        }

        if (!is_enable_address) {
            test_mail_message_obj.text(invalid_email_address_string);
        } else if ((recipient_addresses.length === 0) || (recipient_count === 0)) {
            test_mail_message_obj.text(enter_email_address_string);
        } else {
            test_mail_message_obj.text('');
        }

        if ((recipient_addresses.length === 0) || (!is_enable_address) || (recipient_count === 0)) {
            $jq('#testSendButton').attr("disabled", "disabled");
        } else {
            $jq('#testSendButton').removeAttr("disabled");
        }
    }
}

function openTargetList() {
    var target_id = $jq('input[name=target_id]').val();

    if ($jq.isNumeric(target_id) && (target_id > 0)) {
        var url = target_list_url + target_id;
        window.open(url, 'blfpst_target_list');
    }
}

function selectTarget(n) {

    var title = targets[n]['title'];
    var target_id = targets[n]['id'];

    $jq('#target_name').empty();
    $jq('#target_name').text(title);
    $jq('input[name=target_id]').val(target_id);
    setRecipientCount('?');

    if (target_id > 0) {
        $jq('#target_name_button').removeClass("btn-secondary");
        $jq('#target_name_button').addClass('btn-primary');
    } else {
        $jq('#target_name_button').removeClass("btn-primary");
        $jq('#target_name_button').addClass('btn-secondary');
    }

    // AJax
    ajax_request_recipient_count(target_id);

    $jq('#target_id').parsley().validate({force: true});

    $jq('#selectTargetModal').modal('hide')
}

function selectMailFrom(n) {

    var from_name = mail_froms[n]['from_name'];
    var from_address = mail_froms[n]['from_address'];
    var reply_address = mail_froms[n]['reply_address'];

    $jq('input[name=from_name]').val(from_name);
    $jq('input[name=from_address]').val(from_address);
    $jq('input[name=reply_address]').val(reply_address);

    $jq('input[name=from_name]').parsley().validate({force: true});
    $jq('input[name=from_address]').parsley().validate({force: true});
    $jq('input[name=reply_address]').parsley().validate({force: false});

    $jq('#selectMailFromModal').modal('hide')
}

function setContentTypeText(is_content_type_text) {
    var $html_content = $jq('#html_content_block');
    var $text_content_title = $jq('#text_content_title');

    if (is_content_type_text) {
        $html_content.hide();
        $text_content_title.html(content_string + '<span class="description">*</span>');
    } else {
        $html_content.show();
        $text_content_title.text(alt_text_string);
    }

    setContentTextValidation(is_content_type_text);
}

function openHTMLPreview() {
    var html_content = $jq('textarea[name=htmlcontent]').val();
    var preview_window = window.open(this.href, 'blfpst_html_preview');
    preview_window.document.open();
    preview_window.document.write(html_content);
    preview_window.document.close();
}

function systemLang() {
    var lang = ('' === $jq('input[name=lang]').val()) ? 'en' : $jq('input[name=lang]').val();

    // ja, en
    if((lang !== 'ja') && (lang !== 'en')){
        lang = 'en';
    }

    return lang;
}

function insertAtCaret(target, str) {
    var obj = $jq(target);
    obj.focus();
    if(window.navigator.userAgent.match(/MSIE/)) {
        var r = document.selection.createRange();
        r.text = str;
        r.select();
    } else {
        var s = obj.val();
        var p = obj.get(0).selectionStart;
        var np = p + str.length;
        obj.val(s.substr(0, p) + str + s.substr(p));
        obj.get(0).setSelectionRange(np, np);
    }
}

function setContentTextValidation(is_text_content) {
    $jq("textarea[name=text_content]").attr("data-parsley-required", is_text_content);
    $jq("textarea[name=htmlcontent]").attr("data-parsley-required", !is_text_content);
}