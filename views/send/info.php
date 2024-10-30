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
 * @var int $send_mail_id
 * @var BLFPST_Model_Send_Mail $mail
 * @var string $target_name
 * @var string $status
 * @var string $from_name
 * @var string $from_address
 * @var string $reply_address
 * @var string $subject
 * @var string $text_content
 * @var string $send_request_start_at
 * @var string $reserved_at
 */

if ( empty( $data['mail'] ) ) {
	return;
}

$mail = empty( $data['mail'] ) ? new BLFPST_Model_Send_Mail() : $data['mail'];

// basic
$send_mail_id          = $mail->id;
$target_name           = $mail->target_name;
$status                = $mail->status;
$from_name             = $mail->from_name;
$from_address          = $mail->from_address;
$reply_address         = $mail->reply_address;
$subject               = $mail->subject;
$text_content          = $mail->text_content;
$html_content          = $mail->html_content;
$send_request_start_at = empty( $mail->send_request_start_at ) ? '' : blfpst_localize_datetime_string( $mail->send_request_start_at );
$reserved_at           = empty( $mail->reserved_at ) ? '' : blfpst_localize_datetime_string( $mail->reserved_at );

$reply_address = ( '' === $reply_address ) ? $from_address : $reply_address;

$is_reserved          = $mail->is_reserved();
$is_history           = $mail->is_history();
$is_sending           = $mail->is_sending() || ( $mail->is_waiting() && ! $mail->is_reserved() );
$is_text_content_only = $mail->is_text_content_only();
$is_html_content_only = $mail->is_html_content_only();
$is_html_mail         = $mail->is_html_mail();
$is_failure           = $mail->is_send_result_failure();

$recipient_count       = isset( $data['recipient_count'] ) ? $data['recipient_count'] : $mail->recipient_count;
$success_count         = $mail->success_count;
$failure_count         = $mail->failure_count;
$send_request_start_at = empty( $mail->send_request_start_at ) ? '' : blfpst_localize_datetime_string( $mail->send_request_start_at );
$send_request_end_at   = empty( $mail->send_request_end_at ) ? '' : blfpst_localize_datetime_string( $mail->send_request_end_at );
$target_id             = empty( $mail->target_id ) ? 0 : $mail->target_id;
$delete_button_string  = $is_reserved ? esc_html__( 'Cancel reservation', 'bluff-post' ) : esc_html__( 'Delete', 'bluff-post' );

// log
$logs         = BLFPST_Logs_Controller::load_logs( BLFPST_Logs_Controller::$info, - 1, 0, $send_mail_id, - 1, false );
$error_logs   = BLFPST_Logs_Controller::load_error_logs( - 1, 0, $send_mail_id, - 1, false );
$level_labels = [ 'default', 'info', 'info', 'warning', 'danger', 'danger', 'danger', 'danger' ];
?>
<script type="text/javascript">
	/* <![CDATA[ */
	$jq = jQuery.noConflict();

	function registerTemplate(send_mail_id) {
		$jq('form#form_duplicate input[name=send_mail_id]').val(send_mail_id);
		$jq('form#form_duplicate input[name=admin_action]').val('edit_from_send_mail');
		$jq('form#form_duplicate').attr('action', '<?php echo admin_url( 'admin.php?page=blfpst-mail-template' ) ?>');
		$jq('form#form_duplicate').submit();
	}

	function editReservedMail(send_mail_id) {
		$jq('form#form_duplicate input[name=send_mail_id]').val(send_mail_id);
		$jq('form#form_duplicate input[name=admin_action]').val('edit_reserved');
	}

    function deleteMail(send_mail_id) {
        $jq('input[name=send_mail_id]').val(send_mail_id);
        $jq('#confirmDelete').modal();
    }

    function cancelMail(send_mail_id) {
        $jq('input[name=send_mail_id]').val(send_mail_id);
        $jq('#confirmCancel').modal();
    }

	<?php if ( $is_sending ) { ?>
	function notify_sending_status(send_mail_id,
	                               send_request_start_at, send_request_end_at,
	                               recipient_count, success_count, failure_count, send_result,
	                               updated_at,
	                               response_message) {
		recipient_count = parseFloat(recipient_count);
		success_count = parseFloat(success_count);
		failure_count = parseFloat(failure_count);

		var percent = ((success_count + failure_count) / recipient_count) * 100;
		var $progress_bar = $jq("#progress_bar");
		$progress_bar.prop('aria-valuemax', recipient_count);
		$progress_bar.prop('aria-valuenow', success_count + failure_count);
		$progress_bar.css('width', percent + '%');

		var isFinish = ((percent == 100) && (send_result !== 'wait'));

		if (isFinish) {
			$jq(location).attr('href', '<?php echo admin_url( 'admin.php?page=blfpst-send-mail-histories&admin_action=info&send_mail_id=' . $send_mail_id ) ?>');
		} else {
			$progress_bar.addClass("active");

			var send_request_datetime = send_request_start_at + ' 〜 ' + send_request_end_at;

			if ($jq('#response_message').length) {
				$jq("#response_message").text(response_message);
			}

			$jq("#send_result").text(send_result);
			$jq("#send_request_datetime").text(send_request_datetime);

			$jq("#recipient_count").text(recipient_count);
			$jq("#success_count").text(success_count);
			$jq("#failure_count").text(failure_count);
			$jq("#updated_at").text(updated_at);

			set_progress(100, 100, 0);
			//set_progress(recipient_count, success_count, failure_count);

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

		var isFinish = (percent == 100);

		if (isFinish) {
			$progress_bar.removeClass("active");
		} else {
			$progress_bar.addClass("active");
		}
	}

	$jq(document).ready(function () {
		ajax_request_sending_status('<?php echo esc_html( $send_mail_id ) ?>');
	});
	<?php } ?>
	$jq(document).ready(function () {
		<?php $html_content = str_replace( array( "\r\n", "\r", "\n" ), '', $html_content ); ?>
		<?php $html_content = str_replace( array( '</' ), '<\/', $html_content ); ?>
		<?php $html_content = str_replace( array( '\'' ), '\\\'', $html_content ); ?>
		var html_content = '<?php echo $html_content ?>';

		if ($jq("#html-content-preview")[0]) {
			var iFrame = document.getElementById('html-content-preview');
			var doc = iFrame.contentWindow.document;
			doc.open();
			doc.write(html_content);
			doc.close();
		}
	});
	/* ]]> */
</script>
<div class="container">
    <h1 class="my-4"><?php echo esc_html( stripslashes( $subject ) ) ?></h1>
    <hr class="my-4">
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col">
                            <?php if ( $is_reserved ) : ?>
                                <?php echo esc_html( $reserved_at ) ?>
                            <?php endif ?>
                        </div>
                    </div>

                    <?php if ( !empty( $send_request_start_at ) ) : ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <?php echo esc_html( $send_request_start_at ) ?> 〜
                                <?php echo empty( $send_request_end_at ) ? esc_html__( 'Sending request...', 'bluff-post' ) : esc_html( $send_request_end_at ) ?>
                            </div>
                        </div>
                    <?php endif ?>

                    <div class="row">
                        <div class="col">
                            <small class="text-secondary"><?php esc_html_e( 'Form', 'bluff-post' ) ?></small>
                            <?php echo esc_html( stripslashes( $from_name ) ) ?>
                            &lt; <?php echo esc_html( $from_address ) ?> &gt;
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <small class="text-secondary"><?php esc_html_e( 'Replay', 'bluff-post' ) ?></small>
                            <?php echo esc_html( stripslashes( $reply_address ) ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <?php if ( $is_sending || $is_history ) : ?>
                <div class="row outer_block">
                    <div class="col text-center">
                        <div>
                            <span data-toggle="tooltip" data-placement="bottom"
                                  title="<?php esc_html_e( 'Destination count', 'bluff-post' ) ?>">
                            <i class="bi bi-person-fill" style="font-size: 40px;"></i>
                            </span>
                        </div>
                        <div style="font-size: 32px;">
                            <span data-toggle="tooltip" data-placement="bottom"
                                  title="<?php esc_html_e( 'Destination count', 'bluff-post' ) ?>">
                <?php echo esc_html( number_format( $recipient_count ) ) ?>
                            </span>
                        </div>
                        <p class="form-control-static"><?php echo esc_html( stripslashes( $target_name ) ) ?>
                        </p>
                    </div>
                    <div class="col text-center text-success">
                        <div>
                            <span data-toggle="tooltip" data-placement="bottom"
                                  title="<?php esc_html_e( 'Transmission count', 'bluff-post' ) ?>">
                            <i class="bi bi-envelope-fill" style="font-size: 40px;"></i>
                            </span>
                        </div>
                        <div style="font-size: 32px;">
                            <span data-toggle="tooltip" data-placement="bottom"
                                  title="<?php esc_html_e( 'Transmission count', 'bluff-post' ) ?>">
                                <?php echo esc_html( number_format( $success_count ) ) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col text-center text-danger">
                        <div>
                            <span data-toggle="tooltip" data-placement="bottom"
                                  title="<?php esc_html_e( 'Failure count', 'bluff-post' ) ?>">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 40px;"></i>
                            </span>
                        </div>
                        <div style="font-size: 32px;">
                            <span data-toggle="tooltip" data-placement="bottom"
                                                            title="<?php esc_html_e( 'Failure count', 'bluff-post' ) ?>">
					        <?php echo esc_html( number_format( $failure_count ) ) ?>
				            </span>
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>

	<?php if ( ! empty( $error_logs ) ) : ?>
		<?php if ( 0 < count( $error_logs ) ) : ?>
			<div class="row outer_block my-4">
				<div class="col-sm-8 col-sm-offset-2">
					<div class="alert alert-danger" role="alert">
						<ul>
							<?php foreach ( $error_logs as $error_log ) : ?>
								<li>
                                    <i class="bi bi-exclamation-triangle-fill"></i> <strong><?php echo esc_html( $error_log->summary ) ?></strong> <?php echo esc_html( $error_log->detail ) ?>
								</li>
							<?php endforeach ?>
						</ul>
					</div>
				</div>
			</div>
		<?php endif ?>
	<?php endif ?>

	<?php if ( $is_sending || $is_history ) : ?>

		<?php if ( $is_failure ) : ?>
			<div class="row outer_block my-4">
				<div class="col-sm-8 col-sm-offset-2">
					<div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong><?php esc_html_e( 'Failure', 'bluff-post' ) ?></strong>
						<?php esc_html_e( 'There was a problem with the transmission request to the mail server.', 'bluff-post' ) ?>
					</div>
				</div>
			</div>
		<?php endif ?>
	<?php endif ?>

	<?php if ( $is_sending ) : ?>
		<div class="row outer_block my-4">
			<div class="col-sm-8 col-sm-offset-2">
				<div class="progress">
					<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0" id="progress_bar">
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>

	<?php if ( ! empty( $logs ) ) : ?>
	<div class="row outer_block my-4">
		<div class="col">
		<table class="table">
			<?php /** @var BLFPST_Model_Log $log */ ?>
			<?php foreach ( $logs as $log ) : ?>
				<?php $delete_url = admin_url( 'admin.php?page=blfpst-logs&admin_action=delete&log_id=' . $log->id ) ?>
				<tr>
					<td class="text-nowrap">
						<small><?php echo esc_html( blfpst_localize_datetime_min_string( $log->created_at ) ) ?></small>
					</td>
					<td>
						<span class="badge badge-<?php echo $level_labels[ $log->level ] ?>"><?php echo esc_html( $log->get_level_name() ) ?></span>
					</td>
					<td>
						<b><?php echo esc_html( $log->summary ) ?></b><br>
						<?php echo esc_html( $log->detail ) ?>
					</td>
				</tr>
			<?php endforeach ?>
		</table>
		</div>
	</div>
	<?php endif ?>

	<?php if ( ! $is_text_content_only ) : ?>
        <hr class="my-4">
        <h5><?php echo esc_html__( 'HTML mail', 'bluff-post' ) ?></h5>
        <div class="card">
            <div class="card-body">
                <iframe id="html-content-preview" width="100%" height="600"></iframe>
            </div>
        </div>
	<?php endif ?>

	<?php if ( ! $is_html_content_only ) : ?>
        <hr class="my-4">
        <h5><?php echo esc_html__( 'Text mail', 'bluff-post' ) ?></h5>
		<div class="card">
			<div class="card-body">
				<p class="form-control-static"><?php echo nl2br( esc_html( stripslashes( $text_content ) ) ) ?>
			</div>
		</div>
	<?php endif ?>

	<form method="post" id="form_duplicate" action="<?php echo admin_url( 'admin.php?page=blfpst-send-mail' ) ?>">
		<div class="row justify-content-end my-4">
            <div class="col text-right">
				<?php if ( $is_history ) : ?>
					<button type="submit" class="btn btn-primary mx-2">
						<?php esc_html_e( 'Copy to create', 'bluff-post' ) ?>
					</button>
					<button type="button" class="btn btn-primary mx-2"
					        onclick="registerTemplate(<?php echo esc_html( $send_mail_id ) ?>)">
					<?php esc_html_e( 'Registration template', 'bluff-post' ) ?>
					</button>
				<?php endif ?>

				<?php if ( $is_reserved ) : ?>
					<button type="submit" class="btn btn-primary mx-2"
					        onclick="editReservedMail(<?php echo esc_html( $send_mail_id ) ?>)">
						<?php esc_html_e( 'Change reservation', 'bluff-post' ) ?>
					</button>
				<?php endif ?>

                <?php if ( $is_reserved ) : ?>
                    <button type="button" class="btn btn-danger ml-4"
                            onclick="cancelMail(<?php echo esc_html( $send_mail_id ) ?>)">
                        <?php echo $delete_button_string ?>
                    </button>
                <?php elseif ( ! $is_sending ) : ?>
                    <button type="button" class="btn btn-danger ml-4"
                            onclick="deleteMail(<?php echo esc_html( $send_mail_id ) ?>)">
                        <?php echo $delete_button_string ?>
                    </button>
                <?php endif ?>
			</div>
		</div>

		<?php wp_nonce_field( 'blfpst-send-mail-duplicate', 'blfpst_send_mail_duplicate' ); ?>
		<input type="hidden" name="admin_action" value="duplicate">
		<input type="hidden" name="send_mail_id" value="<?php echo esc_attr( $send_mail_id ) ?>">
	</form>
</div>

<!-- Delete Modal -->
<form method="post">
	<div id="confirmDelete" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title"><?php esc_html_e( 'Confirm delete', 'bluff-post' ) ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
				</div>
				<div class="modal-body">
					<p><?php esc_html_e( 'Are you sure you want to delete?', 'bluff-post' ) ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'No', 'bluff-post' ) ?></button>
					<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Yes', 'bluff-post' ) ?></button>
				</div>
			</div>
			<!-- /.modal-content -->
		</div>
		<!-- /.modal-dialog -->
	</div>
	<!-- /.modal -->
	<input type="hidden" name="admin_action" value="delete"> <input type="hidden" name="send_mail_id" value="<?php echo esc_html( $send_mail_id ) ?>">
	<?php wp_nonce_field( 'blfpst-send-mail-delete', 'blfpst_send_mail_delete' ); ?>
</form>
<!-- Delete Modal -->

<!-- Cancel Modal -->
<form method="post">
    <div id="confirmCancel" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php esc_html_e( 'Confirm cancel', 'bluff-post' ) ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p><?php esc_html_e( 'Are you sure you want to cancel?', 'bluff-post' ) ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'No', 'bluff-post' ) ?></button>
                    <button type="submit" class="btn btn-primary"><?php esc_html_e( 'Yes', 'bluff-post' ) ?></button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="admin_action" value="delete"> <input type="hidden" name="send_mail_id" value="<?php echo esc_html( $send_mail_id ) ?>">
    <?php wp_nonce_field( 'blfpst-send-mail-delete', 'blfpst_send_mail_delete' ); ?>
</form>
<!-- Cancel Modal -->
