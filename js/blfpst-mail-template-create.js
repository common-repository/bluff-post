/**
 * Created by Hideaki Oguchi on 2016/05/05.
 */
$jq = jQuery.noConflict();

$jq(document).ready(function () {

    if ($jq("#mainForm")[0]) {

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

        setContentTextValidation(isCreateTextMail());

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

function isCreateTextMail() {
    var content_type = $jq('input[name=content_type]:checked').val();
    return ('content_type_text' === content_type);
}

function setContentTextValidation(is_text_content) {
    $jq("textarea[name=text_content]").attr("data-parsley-required", is_text_content);
    $jq("textarea[name=htmlcontent]").attr("data-parsley-required", !is_text_content);
}
