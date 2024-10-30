<?php
/**
 * mail template information view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var string $message
 * @var BLFPST_Model_Template $mail_template
 * @var WP_Error $errors
 */
$message          = isset( $data['message'] ) ? $data['message'] : '';
$errors           = $data['errors'];
$mail_template    = $data['mail_template'];
$mail_template_id = $mail_template->id;
$title            = $mail_template->title;
$from_name        = $mail_template->from_name;
$from_address     = $mail_template->from_address;
$reply_address    = $mail_template->reply_address;
$subject          = $mail_template->subject;
$text_content     = $mail_template->text_content;
$html_content     = $mail_template->html_content;
$description      = $mail_template->description;
$preview_content  = $data['preview_content'];
$edit_url         = admin_url( 'admin.php?page=blfpst-mail-template' );

$no_data       = '<span class="text-muted">' . esc_html__( 'Unspecified', 'bluff-post' ) . '</span>';
$is_html_mail  = ( '' !== $mail_template->html_content );
$from_name     = ( '' === $mail_template->from_name ) ? $no_data : esc_html( $from_name );
$from_address  = ( '' === $mail_template->from_address ) ? $no_data : esc_html( $from_address );
$reply_address = ( '' === $mail_template->reply_address ) ? $no_data : esc_html( $reply_address );
$subject       = ( '' === $mail_template->subject ) ? $no_data : esc_html( $subject );
$text_content  = ( '' === $mail_template->text_content ) ? $no_data : esc_html( $text_content );
$html_content  = ( '' === $mail_template->html_content ) ? $no_data : $html_content;
?>
<script type="text/template" id="preview-content">
	<?php echo $preview_content ?>
</script>
<script type="text/javascript">
	/* <![CDATA[ */
	(function ($) {
		$(function () {
			var preview_content = $("#preview-content").text().replace(/&gt;/g, '>').replace(/&lt;/g, '<').replace(/&quot;/g, '"').replace(/&#39;/g, "'");

			if ($("#html-content-preview")[0]) {
				var iFrame = document.getElementById('html-content-preview');
				var doc = iFrame.contentWindow.document;
				doc.open();
				doc.write(preview_content);
				doc.close();
			}
		});
	})(jQuery);
	/* ]]> */
</script>
<div class="container">
    <h1 class="my-4"><?php echo esc_html( $title ) ?></h1>
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

	<?php if ( ! empty( $description ) ) : ?>
        <div class="alert alert-secondary" role="alert">
            <?php echo esc_html( $description ) ?>
        </div>
	<?php endif ?>

	<div class="row">
		<div class="col-md-12">

			<?php if ( '' !== $message ) : ?>
				<div class="alert alert-success" role="alert">
					<?php echo esc_html( $message ) ?>
				</div>
			<?php endif ?>

			<div class="card">
				<div class="card-body">

					<div class="row">
						<div class="col-sm-2 text-right"><strong><?php esc_html_e( 'Form', 'bluff-post' ) ?></strong>
						</div>

						<div class="col-sm-10">
							<p><?php echo $from_name ?>
								&lt; <?php echo $from_address ?> &gt;</p>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-2 text-right"><strong><?php esc_html_e( 'Replay', 'bluff-post' ) ?></strong>
						</div>

						<div class="col-sm-10">
							<p><?php echo $reply_address ?></p>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-2 text-right"><strong><?php esc_html_e( 'Subject', 'bluff-post' ) ?></strong>
						</div>

						<div class="col-sm-10">
							<p><?php echo $subject ?></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

    <hr class="my-4">
    <h5><?php echo esc_html__( 'HTML mail', 'bluff-post' ) ?></h5>

	<div class="card mt-4">
		<div class="card-body">
			<?php if ( $is_html_mail ) : ?>
				<iframe id="html-content-preview" width="100%" height="600"></iframe>
			<?php else : ?>
				<div class="row">
					<div class="col-sm-2 text-right"><strong><?php esc_html_e( 'HTML code', 'bluff-post' ) ?></strong>
					</div>
					<div class="col-sm-10">
						<p><?php echo $no_data ?></p>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>

    <hr class="my-4">
    <h5><?php echo esc_html__( 'Text mail', 'bluff-post' ) ?></h5>

	<div class="card mt-4">
		<div class="card-body">
			<p class="form-control-static"><?php echo nl2br( $text_content ) ?>
		</div>
	</div>

	<div class="row mt-4">
		<div class="col-md-12 text-right">

			<form method="post" style="display: inline" action="<?php echo esc_url( $edit_url ) ?>">
				<button type="submit" class="btn btn-primary">
					<?php esc_html_e( 'Edit', 'bluff-post' ) ?>
				</button>
				<input type="hidden" name="admin_action" value="edit">
				<input type="hidden" name="mail_template_id" value="<?php echo esc_html( $mail_template_id ) ?>">
			</form>

			<a href="#confirmDelete" role="button" class="btn btn-danger ml-4" data-toggle="modal">
				<?php esc_html_e( 'Delete', 'bluff-post' ) ?>
			</a>
		</div>
	</div>
</div>

<!-- Delete Modal -->
<form method="post" style="display: inline">
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
					<p><?php esc_html_e( 'Are you sure you want to delete this template condition?', 'bluff-post' ) ?></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'No', 'bluff-post' ) ?></button>
					<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Yes', 'bluff-post' ) ?></button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->
	<input type="hidden" name="admin_action" value="delete">
	<input type="hidden" name="mail_template_id" value="<?php echo esc_html( $mail_template_id ) ?>">
	<?php wp_nonce_field( 'blfpst-mail-template-delete', 'blfpst_mail_template_delete' ); ?>
</form>
<!-- Delete Modal -->
