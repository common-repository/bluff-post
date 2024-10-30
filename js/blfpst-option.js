/**
 * Created by Hideaki Oguchi on 2016/06/23.
 */
(function ($) {
    $(function () {
        $('input[name="mailer_type"]').on('change', function () {
            var mailer_type = $(this).val();
            selectMailerType(mailer_type);
        });

        $('input[name="smtp_auth"]').on('change', function () {
            var smtp_auth = $('input[name=smtp_auth]:checked').val();
            enableSMTPSecureField(smtp_auth === '1');
        });

        function selectMailerType(mailer_type) {

            var $sendmail = $('input[name="sendmail"]');
            var $smtp_host = $('input[name="smtp_host"]');
            var $smtp_port = $('input[name="smtp_port"]');
            var $smtp_auth = $('input[name="smtp_auth"]');
            var $smtp_secure = $('select[name="smtp_secure"]');
            var $smtp_user_name= $('input[name="smtp_user_name"]');
            var $smtp_password = $('input[name="smtp_password"]');

            $sendmail.attr('required', false);
            $smtp_host.attr('required', false);
            $smtp_port.attr('required', false);
            $smtp_port.removeAttr('data-parsley-type');
            $smtp_auth.attr('required', false);
            $smtp_secure.attr('required', false);
            $smtp_user_name.attr('required', false);
            $smtp_password.attr('required', false);

            switch (mailer_type) {
                case 'sendmail':
                    enableSendmailField(true);
                    enableSMTPField(false);
                    $sendmail.attr('required', true);
                    break;

                case 'mail':
                    enableSendmailField(false);
                    enableSMTPField(false);
                    break;

                case 'smtp':
                    enableSendmailField(false);
                    enableSMTPField(true);
                    $smtp_host.attr('required', true);
                    $smtp_port.attr("data-parsley-type", "integer");
                    $smtp_port.attr('required', true);

                    var smtp_auth = $('input[name=smtp_auth]:checked').val();
                    $smtp_user_name.attr('required', smtp_auth === '1');
                    $smtp_password.attr('required', smtp_auth === '1');
                    break;
            }
        }

        function enableSendmailField(enable) {
            $('input[name="sendmail"]').prop('disabled', !enable);
        }

        function enableSMTPField(enable) {
            $('input[name="smtp_host"]').prop('disabled', !enable);
            $('input[name="smtp_port"]').prop('disabled', !enable);
            $('input[name="smtp_auth"]').prop('disabled', !enable);

            var smtp_auth = $('input[name=smtp_auth]:checked').val() === '1';
            enableSMTPSecureField(enable && smtp_auth);
        }

        function enableSMTPSecureField(enable) {
            var $smtp_secure = $('select[name="smtp_secure"]');
            var $smtp_user_name= $('input[name="smtp_user_name"]');
            var $smtp_password = $('input[name="smtp_password"]');

            $smtp_secure.prop('disabled', !enable);
            $smtp_user_name.prop('disabled', !enable);
            $smtp_password.prop('disabled', !enable);

            $smtp_user_name.attr('required', enable);
            $smtp_password.attr('required', enable);
        }

        // Default
        var mailer_type = $('input[name=mailer_type]:checked').val();
        selectMailerType(mailer_type);
    });
})(jQuery);
