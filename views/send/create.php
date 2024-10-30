<?php
/**
 * mail create view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var BLFPST_Model_Send_Mail $send_mail
 * @var array $mail_templates
 * @var array $json_mail_templates
 * @var array $targets
 * @var array $mail_froms
 * @var array $test_targets
 * @var int $send_mail_id
 * @var int $target_id
 * @var string $target_name
 * @var WP_Error $errors
 */

$create_code               = isset( $data['create_code'] ) ? $data['create_code'] : '';
$send_mail                 = isset( $data['send_mail'] ) ? $data['send_mail'] : null;
$mail_templates            = $data['mail_templates'];
$targets                   = $data['targets'];
$mail_froms                = $data['mail_froms'];
$errors                    = $data['errors'];
$test_targets              = $data['test_targets'];
$send_mail_id              = empty( $data['send_mail_id'] ) ? 0 : (int) $data['send_mail_id'];
$insertion_description     = isset( $data['insertion_description'] ) ? $data['insertion_description'] : '';
$mail_tracking_description = isset( $data['mail_tracking_description'] ) ? $data['mail_tracking_description'] : '';
$create_page               = empty( $data['create_page'] ) ? 1 : (int) $data['create_page'];

$target_id   = empty( $send_mail ) ? 0 : $send_mail->target_id;
$target_id   = ( empty( $target_id ) && ! empty( $_GET['target_id'] ) ) ? (int) $_GET['target_id'] : $target_id;
$target_name = esc_html__( 'Please select a recipients.', 'bluff-post' );

$reserved_at = empty( $send_mail ) ? '' : $send_mail->reserved_at;
$reserved_at = ( '____/__/__ __:__' === $reserved_at ) ? '' : $reserved_at;
$reserved_at = ( empty( $reserved_at ) && ! empty( $_GET['reserved_at'] ) ) ? esc_attr( $_GET['reserved_at'] ) : $reserved_at;
$reserved_at = ( '____/__/__ __:__' === $reserved_at ) ? '' : $reserved_at;

$send_type = ( ! empty( $_GET['reserved_at'] ) ) ? 'reserved' : '';
$send_type = ( empty( $send_type ) && empty( $send_mail ) ) ? 'reserved' : $send_type;
$send_type = ( empty( $send_type ) && ! empty( $send_mail ) ) ? $send_mail->send_type : $send_type;

$content_type = empty( $send_mail ) ? 'content_type_html' : $send_mail->content_type;

$time_zone             = blfpst_get_wp_timezone();
$reserved_datetime     = empty( $reserved_at ) ? null : new DateTime( $reserved_at, $time_zone );
$reserved_default_date = empty( $reserved_datetime ) ? '' : $reserved_datetime->format( 'Y/m/d' );
$reserved_default_time = empty( $reserved_datetime ) ? '' : $reserved_datetime->format( 'H:i' );
$reserved_at = empty( $reserved_datetime ) ? '' : $reserved_datetime->format( 'Y/m/d H:i' );

$from_name     = empty( $send_mail ) ? '' : $send_mail->from_name;
$from_address  = empty( $send_mail ) ? '' : $send_mail->from_address;
$reply_address = empty( $send_mail ) ? '' : $send_mail->reply_address;
$subject       = empty( $send_mail ) ? '' : $send_mail->subject;
$html_content  = empty( $send_mail ) ? '' : $send_mail->html_content;
$text_content  = empty( $send_mail ) ? '' : $send_mail->text_content;

if ( ! empty( $target_id ) ) {
	/** @var BLFPST_Model_Target $target */
	foreach ( $targets as $target ) {
		if ( $target->id == $target_id ) {
			$target_name = $target->title;
			break;
		}
	}
}

$is_text_mail = (( '' === $html_content ) && ( '' !== $text_content ));
$is_text_mail = ('content_type_text' === $content_type);

$text_content_name = $is_text_mail ? esc_html__( 'Content', 'bluff-post' ) : esc_html__( 'Alternate text content', 'bluff-post' );

$locale = get_locale();
$locale = ( 'en_US' === $locale ) ? 'en' : $locale;
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Create e-mail', 'bluff-post' ) ?></h1>
    <hr class="my-4">
    <?php if ( ! empty( $errors ) ) : ?>
		<?php if ( 0 < count( $errors->get_error_messages() )  ) : ?>
			<div class="alert alert-danger" role="alert">
				<ul>
					<?php foreach ( $errors->get_error_messages() as $error ) : ?>
					<li><?php echo esc_html( $error ) ?>
						<?php endforeach ?>
				</ul>
			</div>
		<?php endif ?>
	<?php endif ?>
	<form method="post" class="form-horizontal" id="mainForm"
	      data-parsley-validate
	      data-parsley-excluded="input[type=button], input[type=submit], input[type=reset], [disabled]">
		<div class="row">
			<div class="col-sm-10">
				<div class="form-group mb-4">
                    <div class="row">
                        <div class="col-sm-3 text-right">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-primary">
                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'reservation on/off', 'bluff-post' ) ?>">
                                        <input type="radio" name="send_type" id="change_reserved_now" value=""
                                           onclick="changeReserved()" <?php echo ( '' === $send_type ) ? 'checked' : '' ?>
                                           data-parsley-excluded="true"> <?php esc_html_e( 'Immediate', 'bluff-post' ) ?>
                                    </span>
                                </label>
                                <label class="btn btn-primary">
                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'reservation on/off', 'bluff-post' ) ?>">
                                        <input type="radio" name="send_type" id="change_reserved_reserved" value="reserved"
                                               onclick="changeReserved()" <?php echo ( 'reserved' === $send_type ) ? 'checked' : '' ?>
                                               data-parsley-excluded="true"> <?php esc_html_e( 'Reservation', 'bluff-post' ) ?>
                                    </span>
                                </label>
                            </div>
                            <span data-toggle="tooltip" data-placement="left" title="<?php esc_html_e( 'reservation on/off', 'bluff-post' ) ?>"></span>
                        </div>
                        <div class="col-sm-9" id="reserved_at_container">
                            <div class="input-group" id="reserved_panel">
                                <div class="input-group-prepend">
                                    <button class="btn btn-primary" onclick="showCalendar()" type="button">
                                        <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'select the reservation date', 'bluff-post' ) ?>">
                                            <i class="bi bi-clock-fill"></i>
                                        </span>
                                    </button>
                                </div>
                                <label for="reserved_at"></label>
                                <input name="reserved_at" type="text" id="reserved_at" class="form-control" aria-describedby="basic-addon"
                                       value="<?php echo $reserved_at ?>"
                                       data-parsley-datetime
                                       data-parsley-errors-container="#reserved_at_container"
                                />
                            </div>
                            <div id="not_reserved_panel" style="display: none; padding-top: 6px;"><?php esc_html_e( 'To transmit immediately', 'bluff-post' ) ?></div>
                        </div>
                    </div>
				</div>

                <div class="form-group">
                    <div class="row">
                        <div class="col-sm-3 text-right">
                            <a href="#selectTargetModal" role="button" class="btn btn-primary" data-toggle="modal" id="target_name_button">
                                <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'select recipients', 'bluff-post' ) ?>">
                                    <?php esc_html_e( 'Target', 'bluff-post' ) ?>
                                </span>
                            </a>
                        </div>
                        <div class="col-sm-9" id="target_name_container">
                            <a href="javascript:void(0)" onclick="openTargetList()">
                                <span id="recipient_count" class="badge badge-secondary"></span>
                            </a>
                            <span id="target_name"><?php echo esc_html( $target_name ) ?></span>

                            <input type="hidden" name="target_id" id="target_id" value="<?php esc_attr_e( $target_id ) ?>"
                                   required
                                   data-parsley-type="digits"
                                   data-parsley-min="1"
                                   data-parsley-required-message="<?php esc_attr_e( 'Please select a recipients.', 'bluff-post' ) ?>"
                                   data-parsley-type-message="<?php esc_attr_e( 'Please select a recipients.', 'bluff-post' ) ?>"
                                   data-parsley-min-message="<?php esc_attr_e( 'Please select a recipients.', 'bluff-post' ) ?>"
                                   data-parsley-errors-container="#target_name_container"
                            >
                        </div>
                    </div>
                </div>

				<div class="form-group">
                    <div class="row">
                        <div class="col-sm-3 text-right">
                            <a href="#selectMailFromModal" role="button" class="btn btn-secondary" data-toggle="modal">
                                <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'select the from', 'bluff-post' ) ?>">
                                    <?php esc_html_e( 'Form', 'bluff-post' ) ?>
                                </span>
                            </a>
                        </div>
                        <div class="col" id="from_name_container">
                            <input name="from_name" type="text" id="from_name" class="form-control"
                                   value="<?php echo esc_attr( $from_name ) ?>"
                                   placeholder="<?php esc_attr_e( 'From name', 'bluff-post' ) ?>"
                                   maxlength="255"
                                   required
                                   data-parsley-required-message="<?php esc_attr_e( 'Please enter a from name.', 'bluff-post' ) ?>"
                                   data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a from name 255 or less characters.', 'bluff-post' ) ?>"
                                   data-parsley-errors-container="#from_name_container"/>
                        </div>
                        <div class="col" id="from_address_container">
                            <input name="from_address" type="email" id="from_address" class="form-control"
                                   value="<?php echo esc_attr( $from_address ) ?>"
                                   placeholder="<?php esc_attr_e( 'e-mail address', 'bluff-post' ) ?>"
                                   maxlength="255"
                                   required
                                   data-parsley-required-message="<?php esc_attr_e( 'Please enter a from address.', 'bluff-post' ) ?>"
                                   data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a from address 255 or less characters.', 'bluff-post' ) ?>"
                                   data-parsley-errors-container="#from_address_container"
                            />
                        </div>
                    </div>
				</div>
				<div class="form-group mb-4">
                    <div class="row">
                        <div class="col-sm-3 text-right">
                            <label for="reply_address" class="control-label"><?php esc_html_e( 'Replay', 'bluff-post' ) ?></label>
                        </div>
                        <div class="col" id="reply_address_container">
                            <input name="reply_address" type="email" id="reply_address" class="form-control"
                                   value="<?php echo esc_attr( $reply_address ) ?>"
                                   maxlength="255"
                                   data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a replay address 255 or less characters.', 'bluff-post' ) ?>"
                                   data-parsley-errors-container="#reply_address_container"
                                   placeholder="<?php esc_attr_e( 'Replay', 'bluff-post' ) ?>"/>
                        </div>
                        <div class="col">
                        </div>
                    </div>
				</div>
				<div class="form-group">
                    <div class="row">
                        <div class="col-sm-3 text-right">
                            <label for="create_page" class="control-label"><?php esc_html_e( 'Page publish', 'bluff-post' ) ?></label>
                        </div>
                        <div class="col-sm-4" id="page_publish_container">
                            <select class="form-control" name="create_page" id="create_page">
                                <option value="0" <?php echo ( 0 == $create_page ) ? 'selected' : '' ?>><?php esc_html_e( 'not create', 'bluff-post' ) ?></option>
                                <option value="1" <?php echo ( 1 == $create_page ) ? 'selected' : '' ?>><?php esc_html_e( 'create draft', 'bluff-post' ) ?></option>
                                <option value="2" <?php echo ( 2 == $create_page ) ? 'selected' : '' ?>><?php esc_html_e( 'publish page', 'bluff-post' ) ?></option>
                            </select>
                        </div>
                    </div>
				</div>
			</div>
		</div>

		<div class="card">
			<div class="card-body">
				<div>
					<div class="form-group">
                        <div class="row">
                            <div class="col-sm-2 text-right">
                                <a href="#selectTemplateModal" role="button" class="btn btn-secondary" data-toggle="modal">
                                    <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'select a template', 'bluff-post' ) ?>">
                                    <?php esc_html_e( 'Subject', 'bluff-post' ) ?>
                                        <span class="description">*</span>
                                    </span>
                                </a>
                            </div>
                            <div class="col-sm-10" id="subject_container">
                                <div class="input-group mb-3">
                                    <input name="subject" type="text" id="subject" class="form-control"
                                           value="<?php echo esc_attr( $subject ) ?>"
                                           maxlength="255"
                                           required
                                           data-parsley-required-message="<?php esc_attr_e( 'Please enter a subject.', 'bluff-post' ) ?>"
                                           data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a subject 255 or less characters.', 'bluff-post' ) ?>"
                                           data-parsley-errors-container="#subject_container"
                                           placeholder="<?php esc_attr_e( 'Subject', 'bluff-post' ) ?>">
                                </div>
                            </div>
                        </div>
					</div>
				</div>
				<div class="row mb-2">
                    <div class="col col-sm-2">
                    </div>
					<div class="col col-sm-7">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-secondary <?php echo !$is_text_mail ? 'active' : '' ?>"
                                   id="content_type_html_label" for="content_type_html">
                                <input type="radio" name="content_type" id="content_type_html"
                                       value="content_type_html" <?php echo !$is_text_mail ? 'checked' : '' ?>> <?php esc_attr_e( 'HTML mail', 'bluff-post' ) ?>
                            </label>
                            <label class="btn btn-secondary <?php echo $is_text_mail ? 'active' : '' ?>"
                                   id="content_type_text_label" for="content_type_text">
                                <input type="radio" name="content_type" id="content_type_text"
                                       value="content_type_text" <?php echo $is_text_mail ? 'checked' : '' ?>> <?php esc_attr_e( 'Text mail', 'bluff-post' ) ?>
                            </label>
                        </div>
					</div>
					<div class="col col-sm-3 text-right">
						<button type="button" class="btn btn-secondary" aria-label="Media upload" id="media_upload_button">
							<span data-toggle="tooltip" data-placement="left" title="<?php esc_html_e( 'Insert image', 'bluff-post' ) ?>">
								<i class="bi bi-image"></i>
							</span>
						</button>
						<button type="button" class="btn btn-secondary" aria-label="HTML Preview" id="html_preview_button">
							<span data-toggle="tooltip" data-placement="left" title="<?php esc_html_e( 'Preview HTML mail', 'bluff-post' ) ?>">
								<i class="bi bi-globe"></i>
							</span>
						</button>
						<a href="http://foundation.zurb.com/emails/inliner-v2.html" class="btn btn-secondary" target="_blank"><span data-toggle="tooltip" data-placement="left" title="<?php esc_html_e( 'Inliner', 'bluff-post' ) ?>">
								<i class="bi bi-arrow-right-circle-fill"></i>
							</span></a>
					</div>
				</div>

				<div class="form-group" id="html_content_block" style="display: <?php echo $is_text_mail ? 'none' : 'block' ?>">
                    <div class="row">
                        <label for="htmlcontent" class="col-sm-2 col-form-label text-right">
                            <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Input HTML code', 'bluff-post' ) ?>">
                                <?php esc_html_e( 'HTML code', 'bluff-post' ) ?>
                            </span>
                            <span class="description">*</span> </label>
                        <div class="col-sm-10" id="html_content_container">
                            <textarea id="htmlcontent" name="htmlcontent" class="form-control"
                                      data-parsley-required-message="<?php esc_attr_e( 'Please enter a content.', 'bluff-post' ) ?>"
                                      data-parsley-required="true"
                                      data-parsley-errors-container="#html_content_container"
                                      rows="18"><?php echo esc_textarea( $html_content ) ?></textarea>
                        </div>
                    </div>
				</div>
				<div class="form-group">
                    <div class="row">
                        <label for="text_content" class="col-sm-2 control-label text-right" id="text_content_title">
                            <?php echo $text_content_name ?>
                            </label>
                        <div class="col-sm-10" id="text_content_container">
                            <textarea id="text_content" name="text_content" class="form-control"
                                      maxlength="5000"
                                      data-parsley-required-message="<?php esc_attr_e( 'Please enter a content.', 'bluff-post' ) ?>"
                                      data-parsley-required="true"
                                      data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a text content 5,000 or less characters.', 'bluff-post' ) ?>"
                                      data-parsley-errors-container="#text_content_container"
                                      rows="18"><?php echo esc_textarea( $text_content ) ?></textarea>
                        </div>
					</div>
				</div>
			</div>
		</div>
		<div class="outer_block">
			<p><?php esc_html_e( '*Input required.', 'bluff-post' ) ?></p>
		</div>

        <div class="row">
            <div class="col text-right">
                <a href="#sendTestMailModal" role="button" class="btn btn-secondary" data-toggle="modal">
                    <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'to send a test e-mail', 'bluff-post' ) ?>">
                        <?php esc_html_e( 'Send test mail', 'bluff-post' ) ?>
                    </span>
                </a>

                <button type="submit" class="btn btn-secondary mx-3" id="saveButton">
                    <span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'save to draft', 'bluff-post' ) ?>">
                        <?php esc_html_e( 'Save', 'bluff-post' ) ?>
                    </span>
                </button>

                <button type="submit" class="btn btn-primary" id="registerButton">
                    <?php esc_html_e( 'Confirm', 'bluff-post' ) ?>
                </button>
            </div>
        </div>

 		<a class="btn btn-secondary" role="button" data-toggle="collapse" href="#collapseInsertDescription" aria-expanded="false" aria-controls="collapseInsertDescription">
            <i class="bi bi-info-circle-fill"></i> <?php esc_html_e( 'Insert description', 'bluff-post' ) ?>
		</a>
		<div class="collapse" id="collapseInsertDescription">
            <div class="card card-body">
				<p><?php esc_html_e( 'Available insert strings.', 'bluff-post' ) ?></p>
				<?php echo $insertion_description ?>
			</div>
		</div>

		<a class="btn btn-secondary" role="button" data-toggle="collapse" href="#collapseMailTracking" aria-expanded="false" aria-controls="collapseMailTracking">
            <i class="bi bi-info-circle-fill"></i> <?php esc_html_e( 'Mail Tracking', 'bluff-post' ) ?>
		</a>
		<div class="collapse" id="collapseMailTracking">
            <div class="card card-body">
				<ul>
					<?php if ( 'ja' === get_bloginfo( 'language' ) ) { ?>
					<li>
						<a href="https://developers.google.com/analytics/devguides/collection/protocol/v1/email?hl=ja" target="_blank">メール トラッキング - Measurement Protocol</a></li>
					<li><a href="https://support.google.com/analytics/answer/1033867?hl=ja" target="_blank">URL 生成ツール</a>
					<?php } else { ?>
					<li>
						<a href="https://developers.google.com/analytics/devguides/collection/protocol/v1/email" target="_blank">
							Measurement Protocol for Email</a>
					<li><a href="https://support.google.com/analytics/answer/1033867" target="_blank">URL builder</a>
						<?php } ?>
				</ul>
			</div>
		</div>

		<?php wp_nonce_field( 'blfpst-send-mail-conf', 'blfpst_send_mail_conf' ); ?>
		<input type="hidden" name="admin_action" value="conf"
		       data-parsley-excluded="true">
		<input type="hidden" name="send_mail_id" value="<?php esc_attr_e( $send_mail_id ) ?>"
		       data-parsley-excluded="true">
		<input type="hidden" name="create_code" value="<?php esc_attr_e( $create_code ) ?>"
		       data-parsley-excluded="true">

		<div class="modal fade" id="sendTestMailModal" tabindex="-1" aria-labelledby="sendTestMailModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
                        <h5 class="modal-title" id="selectTemplateModalLabel"><?php esc_html_e( 'Send test mail', 'bluff-post' ) ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
					</div>
					<div class="modal-body">
						<?php esc_html_e( 'The receiver e-mail address of the test mail', 'bluff-post' ) ?>
						<div id="test_mail_message"></div>
						<textarea name="test_targets" title="<?php esc_attr_e( 'The receiver e-mail address of the test mail', 'bluff-post' ) ?>" class="form-control" id="test_targets" rows="10"><?php echo $test_targets ?></textarea>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal">
							<?php esc_html_e( 'Cancel', 'bluff-post' ) ?>
						</button>
						<button type="submit" class="btn btn-primary" id="testSendButton">
							<?php esc_html_e( 'Send test mail', 'bluff-post' ) ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>

	<div class="modal fade" id="selectTemplateModal" tabindex="-1" aria-labelledby="selectTemplateModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
                    <h5 class="modal-title" id="selectTemplateModalLabel"><?php esc_html_e( 'select a template', 'bluff-post' ) ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
				</div>
				<div class="modal-body">
					<div id="selectTemplateModalLoadingMessage" style="display:none;">
						<div class="alert alert-info" role="alert"><?php esc_html_e( 'Loading template....', 'bluff-post' ) ?></div>
					</div>
					<?php if ( 0 < count( $mail_templates ) ) : ?>
						<ul class="list-group" id="selectTemplateModalTargetList">
							<?php for ( $i = 0; $i < count( $mail_templates ); $i ++ ) : ?>
								<?php /** @var BLFPST_Model_Template $mail_template */ ?>
								<?php $mail_template = $mail_templates[ $i ] ?>
								<li class="list-group-item"><a href="javascript:void(0)"
								                               onclick="selectMailTemplate(<?php echo $mail_template->id ?>)"><?php echo esc_html( $mail_template->title ) ?></a>
								</li>
							<?php endfor ?>
						</ul>
					<?php else : ?>
						<?php esc_html_e( 'There is no template that has been registered.', 'bluff-post' ) ?>
					<?php endif ?>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="selectTargetModal" tabindex="-1" aria-labelledby="selectTargetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectTargetModalLabel"><?php esc_html_e( 'select a recipients', 'bluff-post' ) ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ( 0 < count( $targets ) ) : ?>
                        <ul class="list-group">
                            <?php for ( $i = 0; $i < count( $targets ); $i ++ ) : ?>
                                <?php /** @var BLFPST_Model_Target $target */ ?>
                                <?php $target = $targets[ $i ] ?>
                                <li class="list-group-item">
                                    <span class="badge"><?php echo esc_html( number_format( $target->count ) ) ?></span><a
                                            href="javascript:void(0)"
                                            onclick="selectTarget(<?php echo $i ?>)"><?php echo esc_html( $target->title ) ?></a>
                                </li>
                            <?php endfor ?>
                        </ul>
                    <?php else : ?>
                        <?php esc_html_e( 'Please select a target.', 'bluff-post' ) ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="selectMailFromModal" tabindex="-1" aria-labelledby="selectMailFromModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectMailFromModalLabel"><?php esc_html_e( 'select a from', 'bluff-post' ) ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php if ( 0 < count( $mail_froms ) ) : ?>
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-sm-8"><?php esc_html_e( 'Form', 'bluff-post' ) ?></div>
                                    <div class="col-sm-4"><?php esc_html_e( 'Replay', 'bluff-post' ) ?></div>

                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php for ( $i = 0; $i < count( $mail_froms ); $i ++ ) : ?>
                                        <?php $mail_from = $mail_froms[ $i ] ?>
                                        <div class="col-sm-8"><a href="javascript:void(0)"
                                                                 onclick="selectMailFrom(<?php echo $i ?>)"><?php echo esc_html( $mail_from->from_name ) ?>
                                                &lt;<?php echo esc_html( $mail_from->from_address ) ?>&gt;</a></div>
                                        <div class="col-sm-4"><?php echo esc_html( $mail_from->reply_address ) ?></div>
                                    <?php endfor ?>
                                </div>
                            </div>
                        </div>
                    <?php else : ?>
                        <?php esc_html_e( 'There is no from that has been registered.', 'bluff-post' ) ?>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="reserved_default_date" value="<?php echo $reserved_default_date ?>">
<input type="hidden" name="reserved_default_time" value="<?php echo $reserved_default_time ?>">
<input type="hidden" name="lang" value="<?php echo $locale ?>">
