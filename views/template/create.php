<?php
/**
 * target create view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var string $title
 * @var string $from_name
 * @var string $from_address
 * @var string $reply_address
 * @var string $subject
 * @var string $text_content
 * @var string $html_content
 * @var int $mail_template_id
 * @var BLFPST_Model_Template $mail_template
 * @var WP_Error $errors
 */
$errors = $data['errors'];

if ( empty( $data['mail_template'] ) ) {
	$title            = isset( $_POST['title'] ) ? $_POST['title'] : '';
	$from_name        = isset( $_POST['from_name'] ) ? $_POST['from_name'] : '';
	$from_address     = isset( $_POST['from_address'] ) ? $_POST['from_address'] : '';
	$reply_address    = isset( $_POST['reply_address'] ) ? $_POST['reply_address'] : '';
	$subject          = isset( $_POST['subject'] ) ? $_POST['subject'] : '';
	$text_content     = isset( $_POST['text_content'] ) ? $_POST['text_content'] : '';
	$html_content     = isset( $_POST['htmlcontent'] ) ? $_POST['htmlcontent'] : '';
	$mail_template_id = isset( $_POST['mail_template_id'] ) ? $_POST['mail_template_id'] : 0;

	$errors = new WP_Error();
	if ( isset( $_POST['errors'] ) ) {

		$post_errors = json_decode( $_POST['errors'] );

		if ( ! empty( $post_errors ) ) {

			foreach ( $post_errors->errors->Error as $post_error ) {
				$errors->add( 'Error', $post_error );
			}
		}
	}
} else {
	$mail_template    = empty( $data['mail_template'] ) ? new BLFPST_Model_Template() : $data['mail_template'];
	$title            = $mail_template->title;
	$from_name        = $mail_template->from_name;
	$from_address     = $mail_template->from_address;
	$reply_address    = $mail_template->reply_address;
	$subject          = $mail_template->subject;
	$text_content     = $mail_template->text_content;
	$html_content     = $mail_template->html_content;
	$mail_template_id = $mail_template->id;
	$errors           = $data['errors'];
}

$is_text_mail          = ( ( '' === $html_content ) && ( '' !== $text_content ) );
$insertion_description = $data['insertion_description'];
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Registration template', 'bluff-post' ) ?></h1>
    <hr class="my-4">

	<?php if ( ! empty( $errors ) ) : ?>
		<?php if ( 0 < count( $errors->get_error_messages() ) ) : ?>
			<div class="alert alert-danger" role="alert">
				<ul>
					<?php foreach ( $errors->get_error_messages() as $error ) : ?>
					<li><?php echo esc_html( $error ) ?>
						<?php endforeach ?>
				</ul>
			</div>
		<?php endif ?>
	<?php endif ?>

	<form method="post" class="form-horizontal" id="mainForm" data-parsley-validate="">

		<div class="card">
			<div class="card-header">
				<div class="row">
						<label for="title" class="col-sm-2 control-label text-right"><?php esc_html_e( 'Template name', 'bluff-post' ) ?></label>
						<div class="col-sm-6" id="title_container">
							<input name="title" type="text" id="title" class="form-control" value="<?php echo esc_attr( $title ) ?>"
                                   placeholder="<?php esc_attr_e( 'Template name', 'bluff-post' ) ?>"
							       required
							       data-parsley-required-message="<?php esc_attr_e( 'Please enter a template name.', 'bluff-post' ) ?>"
							       maxlength="255"
							       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a template name 255 or less characters.', 'bluff-post' ) ?>"
							       data-parsley-errors-container="#title_container"
							/>
					</div>
				</div>
			</div>
			<div class="card-body">

				<div class="row form-group">
					<div class="col-sm-2 text-right"><?php esc_html_e( 'Form', 'bluff-post' ) ?></div>
					<div class="col-sm-6">
						<div id="from_name_container">
								<input name="from_name" type="text" id="from_name" class="form-control"
								       value="<?php echo $from_name ?>"
								       placeholder="<?php esc_attr_e( 'From name', 'bluff-post' ) ?>"
								       maxlength="255"
								       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a from name 255 or less characters.', 'bluff-post' ) ?>"
								       data-parsley-errors-container="#from_name_container"
								/>
						</div>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-sm-2 text-right"><?php esc_html_e( 'From address', 'bluff-post' ) ?></div>
					<div class="col-sm-6">
						<div id="from_address_container">
							<input name="from_address" type="email" id="from_address" class="form-control"
							       value="<?php echo $from_address ?>"
							       placeholder="<?php esc_attr_e( 'e-mail address', 'bluff-post' ) ?>"
							       maxlength="255"
							       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a from address 255 or less characters.', 'bluff-post' ) ?>"
							       data-parsley-errors-container="#from_address_container"
							/>
						</div>
					</div>
				</div>

				<div class="row form-group">
					<label for="reply_address" class="col-sm-2 control-label text-right"><?php esc_html_e( 'Replay', 'bluff-post' ) ?></label>

					<div class="col-sm-6" id="reply_address_container">
						<input name="reply_address" type="email" id="reply_address" class="form-control"
						       value="<?php echo esc_attr( $reply_address ) ?>" placeholder="<?php esc_attr_e( 'Replay', 'bluff-post' ) ?>"
						       maxlength="255"
						       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a replay address 255 or less characters.', 'bluff-post' ) ?>"
						       data-parsley-errors-container="#reply_address_container"
						/>
					</div>
				</div>

				<div class="row form-group">
					<label for="subject" class="col-sm-2 control-label text-right"><?php esc_html_e( 'Subject', 'bluff-post' ) ?></label>

					<div class="col-sm-10" id="subject_container">
						<input name="subject" type="text" id="subject" class="form-control"
						       value="<?php echo esc_attr( $subject ) ?>" placeholder="<?php esc_attr_e( 'Subject', 'bluff-post' ) ?>"
						       maxlength="255"
						       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a subject 255 or less characters.', 'bluff-post' ) ?>"
						       data-parsley-errors-container="#subject_container"
						/>
					</div>
				</div>
				<div class="row form-group">
                    <div class="col-sm-2"></div>
                    <div class="col col-sm-8">
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-secondary <?php echo ! $is_text_mail ? 'active' : '' ?>"
                                   id="content_type_html_label">
                                <input type="radio" name="content_type" id="content_type_html"
                                       value="content_type_html" autocomplete="off" <?php echo ! $is_text_mail ? 'checked' : '' ?>> <?php esc_attr_e( 'HTML mail', 'bluff-post' ) ?>
                            </label>
                            <label class="btn btn-secondary <?php echo $is_text_mail ? 'active' : '' ?>"
                                   id="content_type_text_label">
                                <input type="radio" name="content_type" id="content_type_text"
                                       value="content_type_text" autocomplete="off" <?php echo $is_text_mail ? 'checked' : '' ?>> <?php esc_attr_e( 'Text mail', 'bluff-post' ) ?>
                            </label>
                        </div>
                    </div>
					<div class="col-sm-2 text-right">
						<button type="button" class="btn btn-secondary" aria-label="Media upload" id="media_upload_button">
							<span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Insert image', 'bluff-post' ) ?>">
								<i class="bi bi-image"></i>
							</span>
						</button>
						<button type="button" class="btn btn-secondary" aria-label="HTML Preview" id="html_preview_button">
							<span data-toggle="tooltip" data-placement="bottom" title="<?php esc_html_e( 'Preview HTML mail', 'bluff-post' ) ?>">
								<i class="bi bi-globe"></i>
							</span>
						</button>
					</div>
				</div>
                <div id="html_content_block" style="display: <?php echo $is_text_mail ? 'none' : 'block' ?>">
				<div class="row form-group">
					<label for="htmlcontent" class="col-sm-2 control-labelxg text-right">
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
				<div class="row form-group">
					<label for="text_content" class="col-sm-2 control-label text-right" id="text_content_title">
						<?php esc_html_e( 'Alternate text content', 'bluff-post' ) ?>
					</label>
					<div class="col-sm-10" id="text_content_container">
						<textarea name="text_content" id="text_content" class="form-control"
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
		<div class="outer_block">
			<p><?php esc_html_e( '*Input required.', 'bluff-post' ) ?></p>
		</div>

		<div class="row">
			<div class="col-sm-12 text-right">
				<button type="submit" class="btn btn-primary"><?php echo ( empty( $mail_template_id ) ) ? esc_html__( 'Registration', 'bluff-post' ) : esc_html__( 'Update Template', 'bluff-post' ) ?></button>
			</div>
		</div>

		<?php wp_nonce_field( 'blfpst-mail-template-register', 'blfpst_mail_template_register' ); ?>
		<input type="hidden" name="admin_action" value="register">
		<input type="hidden" name="mail_template_id" value="<?php echo esc_attr( $mail_template_id ) ?>">
		<input type="hidden" name="target_id" value="0">
	</form>

	<a class="btn btn-secondary" role="button" data-toggle="collapse" href="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        <i class="bi bi-info-circle-fill"></i> <?php esc_html_e( 'Insert description', 'bluff-post' ) ?>
    </a>
    <div class="collapse" id="collapseExample">
        <div class="card mt-2">
            <div class="card-header">
                <?php esc_html_e( 'Available insert strings.', 'bluff-post' ) ?>
            </div>
            <div class="card-body">
                <?php echo $insertion_description ?>
            </div>
        </div>
	</div>
</div>
