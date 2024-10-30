<?php
/**
 * mail information view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var BLFPST_Model_Send_Mail $mail
 */

$mail = empty( $data['mail'] ) ? new BLFPST_Model_Send_Mail() : $data['mail'];

if ( 0 == $mail->id ) {
	return;
}
$recipient_count = empty( $data['recipient_count'] ) ? 0 : $data['recipient_count'];

$send_mail_id                 = $mail->id;
$target_id                    = empty( $mail->target_id ) ? 0 : $mail->target_id;
$target_name                  = $mail->target_name;
$subject                      = $mail->subject;
$is_waiting                   = $mail->is_waiting();
$is_failure                   = $mail->is_send_result_failure();
$recipient_count              = $mail->recipient_count;
$success_count                = $mail->success_count;
$failure_count                = $mail->failure_count;
$response_message             = $is_failure ? esc_html__( 'There was a problem with the transmission request to the mail server.', 'bluff-post' ) : '';
$response_message_box_display = $is_failure ? 'inline' : 'none';
?>
<script type="text/javascript">
	/* <![CDATA[ */
	$jq = jQuery.noConflict();

	function notify_sending_status(send_mail_id,
	                               send_request_start_at, send_request_end_at,
	                               recipient_count, success_count, failure_count, send_result,
	                               updated_at,
	                               response_message) {

		recipient_count = parseFloat(recipient_count);
		success_count = parseFloat(success_count);
		failure_count = parseFloat(failure_count);

		var percent = 100; // recipient_count = 0 -> 100% finish
		if(0 < recipient_count){
			percent = ((success_count + failure_count) / recipient_count) * 100;
		}

		var $progress_bar = $jq("#progress_bar");
		$progress_bar.prop('aria-valuemax', recipient_count);
		$progress_bar.prop('aria-valuenow', success_count + failure_count);
		$progress_bar.css('width', percent + '%');

		var isFinish = (percent === 100) && (send_result !== 'wait');

		if (isFinish) {
			$jq(location).attr('href', '<?php echo admin_url( 'admin.php?page=blfpst-send-mail-histories&admin_action=info&send_mail_id=' . $send_mail_id ) ?>');
		} else {
			$progress_bar.addClass("active");

			var send_request_datetime = '<i class="bi bi-hourglass-bottom"></i> ' + send_request_start_at + ' 〜 ' + send_request_end_at;

			if ($jq('#response_message').length) {
				$jq("#response_message").text(response_message);
			}

			$jq("#send_result").text(send_result);
			$jq("#send_request_datetime").html(send_request_datetime);

			$jq("#recipient_count").text(recipient_count);
			$jq("#success_count").text(success_count);
			$jq("#failure_count").text(failure_count);
			$jq("#updated_at").text(updated_at);

			set_progress(recipient_count, success_count, failure_count);

			setTimeout(function () {
				ajax_request_sending_status('<?php echo esc_html( $send_mail_id ) ?>');
			}, 5000);
		}
	}

	function set_progress(recipient_count, success_count, failure_count) {

		recipient_count = parseFloat(recipient_count);
		success_count = parseFloat(success_count);
		failure_count = parseFloat(failure_count);

		var percent = ((success_count + failure_count) / recipient_count) * 100;
		var $progress_bar = $jq("#progress_bar");
		$progress_bar.prop('aria-valuemax', recipient_count);
		$progress_bar.prop('aria-valuenow', success_count + failure_count);
		$progress_bar.css('width', percent + '%');

		var isFinish = (percent === 100);

		if (isFinish) {
			$progress_bar.removeClass("active");
		} else {
			$progress_bar.addClass("active");
		}
	}

	$jq(document).ready(function () {
		ajax_request_sending_status('<?php echo esc_html( $send_mail_id ) ?>');
	});
	/* ]]> */
</script>
<div class="container">
    <h1 class="my-4"><?php echo esc_html( $subject ) ?></h1>
    <hr class="my-4">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <i class="bi bi-bar-chart-line-fill"></i> <?php esc_html_e( 'It just have a transmission request.', 'bluff-post' ) ?>
                    <div id="send_request_datetime"></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="row outer_block">
                <div class="col text-center">
                    <div><i class="bi bi-person-fill" style="font-size: 40px;"></i></div>
                    <div style="font-size: 32px;">
                        <span id="recipient_count"><?php echo esc_html( number_format( $recipient_count ) ) ?></span></div>
                        <p class="form-control-static"><?php echo esc_html( stripslashes( $target_name ) ) ?></p>
                </div>
                <div class="col text-center text-success">
                    <div><i class="bi bi-envelope-fill" style="font-size: 40px;"></i></div>
                    <div style="font-size: 32px;">
                        <span id="success_count"><?php echo esc_html( number_format( $success_count ) ) ?></span>
                    </div>
                </div>
                <div class="col text-center text-danger">
                    <div><i class="bi bi-exclamation-circle-fill" style="font-size: 40px;"></i></div>
                    <div style="font-size: 32px;">
                        <span id="failure_count"><?php echo esc_html( number_format( $failure_count ) ) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

	<div class="row justify-content-md-center outer_block">
		<div class="col-8">
			<div class="row justify-content-md-center" id="response_message_box" style="display: <?php echo $response_message_box_display ?>">
				<div class="col-10">
					<div class="alert alert-danger" role="alert">
						<?php echo $response_message ?>
						<div id="response_message"></div>
						<p id="alert_link">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-histories&admin_action=info&send_mail_id=' . $send_mail_id ) ) ?>" class="alert-link"><?php esc_html_e( 'More information', 'bluff-post' ) ?></a>
						</p>
					</div>
				</div>
			</div>

			<div class="row justify-content-md-center">
				<div class="col-10">
					<div class="progress">
						<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0" id="progress_bar">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
