<?php
/**
 * target edit view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var array $target
 * @var int $target_id
 * @var string $reserved_at
 * @var string $from_name
 * @var string $from_address
 * @var string $reply_address
 * @var string $subject
 * @var string $text_content
 * @var int $send_mail_id
 * @var string $target_name
 */

/** @var BLFPST_Model_Target $target */
$create_code     = $data['create_code'];
$target          = $data['target'];
$target_id       = $data['target_id'];
$reserved_at     = $data['reserved_at'];
$from_name       = $data['from_name'];
$from_address    = $data['from_address'];
$reply_address   = $data['reply_address'];
$subject         = $data['subject'];
$text_content    = $data['text_content'];
$html_content    = $data['html_content'];
$preview_content = $data['preview_content'];
$content_type    = $data['content_type'];

$send_mail_id    = empty( $data['send_mail_id'] ) ? 0 : $data['send_mail_id'];
$create_page     = isset( $data['create_page'] ) ? $data['create_page'] : 1;

$target_name = empty( $target ) ? '' : $target->title;
$is_reserved = ! empty( $reserved_at );
$is_edit     = ! empty( $send_mail_id );

$is_text_content_only = ( 0 == mb_strlen( $html_content ) );
$is_html_content_only = ( 0 == mb_strlen( $text_content ) );
$is_html_mail         = ( ! $is_text_content_only && ! $is_text_content_only );

$text_content_title = $is_html_mail ? esc_html__( 'Alternate text content', 'bluff-post' ) : esc_html__( 'Content', 'bluff-post' );

$display_reply_address = ( '' === $reply_address ) ? '<span class="text-muted">' . esc_html__( 'The same as the from e-mail address.', 'bluff-post' ) . '</span>' : esc_html( stripslashes( $reply_address ) );
$display_text_content  = ( '' === $text_content ) ? '<span class="text-muted">' . esc_html__( 'Unspecified', 'bluff-post' ) . '</span>' : nl2br( esc_html( stripslashes( $text_content ) ) );
?>
<script type="text/template" id="preview-content">
	<?php echo $preview_content ?>
</script>
<script type="text/javascript">
	/* <![CDATA[ */
	(function ($) {
		$(function () {
			var preview_content = $("#preview-content").text().replace(/&gt;/g,'>').replace(/&lt;/g,'<').replace(/&quot;/g,'"').replace(/&#39;/g,"'");

			if ($("#html-content-preview")[0]) {
				var iFrame = document.getElementById('html-content-preview');
				var doc = iFrame.contentWindow.document;
				doc.open();
				doc.write(preview_content);
				doc.close();
			}

            var $mainForm = $('form[name=mainForm]');
            var $startButton = $('#start-button');
            $startButton.on("click", function () {
                $startButton.prop("disabled", true);
                $mainForm.submit();
            });
		});
	})(jQuery);
	/* ]]> */
</script>
<div class="container">
    <h1 class="my-4"><?php echo esc_html( stripslashes( $subject ) ) ?></h1>
    <hr class="my-4">

	<div class="row outer_block mb-2">
		<div class="col">
			<?php if ( ! empty( $reserved_at ) ) : ?>
				<div class="row">
					<div class="col-sm-3 text-right"><strong><?php esc_html_e( 'Reservation date', 'bluff-post' ) ?></strong></div>
					<div class="col-sm-9"><?php echo esc_html( $reserved_at ) ?></div>
				</div>
			<?php endif ?>

			<div class="row">
				<div class="col-sm-3 text-right"><strong><?php esc_html_e( 'Target', 'bluff-post' ) ?></strong></div>

				<div class="col-sm-9">
                    <?php echo esc_html( stripslashes( $target_name ) ) ?>
				</div>
			</div>

            <div class="row">
                <div class="col-sm-3 text-right"><strong><?php esc_html_e( 'Form', 'bluff-post' ) ?></strong></div>

                <div class="col-sm-9">
                    <?php echo esc_html( stripslashes( $from_name ) ) ?>
                    &lt; <?php echo esc_html( $from_address ) ?> &gt;
                </div>
            </div>

			<div class="row">
				<div class="col-sm-3 text-right"><strong><?php esc_html_e( 'Replay', 'bluff-post' ) ?></strong></div>
				<div class="col-sm-9"><?php echo $display_reply_address ?></div>
			</div>
		</div>
	</div>

	<form method="post" class="form-horizontal" name="mainForm">

		<?php if ( ! $is_text_content_only ) : ?>
            <hr class="my-4">
            <h5><?php echo esc_html__( 'HTML mail', 'bluff-post' ) ?></h5>
			<div class="card">
				<div class="card-body">
					<iframe id="html-content-preview" width="100%" height="600"></iframe>
				</div>
			</div>
		<?php endif; ?>
        <hr class="my-4">
        <h5><?php echo esc_html__( 'Text mail', 'bluff-post' ) ?></h5>
		<div class="card">
			<div class="card-body">
				<p class="form-control-static"><?php echo $display_text_content ?>
			</div>
		</div>

		<div class="row mt-5">
			<div class="col-sm-6">
				<button type="button" onclick="history.back()" class="btn btn-secondary"><?php esc_attr_e( 'Back', 'bluff-post' ) ?></button>
			</div>
			<div class="col-sm-6 text-right">
				<?php if ( ! $is_reserved ) : ?>
					<button type="button" class="btn btn-primary" id="start-button"><?php esc_html_e( 'To start transmission', 'bluff-post' ) ?></button>
				<?php else : ?>
					<?php if ( $is_edit ) : ?>
						<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Change reservation', 'bluff-post' ) ?></button>
					<?php else : ?>
						<button type="submit" class="btn btn-primary"><?php esc_html_e( 'Registration reservation', 'bluff-post' ) ?></button>
					<?php endif ?>
				<?php endif ?>

				<?php wp_nonce_field( 'blfpst-send-mail-register', 'blfpst_send_mail_register' ); ?>
				<input type="hidden" name="admin_action" value="register">
				<input type="hidden" name="create_code" value="<?php esc_attr_e( $create_code ) ?>">
				<input type="hidden" name="create_page" value="<?php esc_attr_e( $create_page ) ?>">
				<input type="hidden" name="send_mail_id" value="<?php esc_attr_e( $send_mail_id ) ?>">
				<input type="hidden" name="reserved_at" value="<?php echo esc_attr( $reserved_at ) ?>">
				<input type="hidden" name="target_id" value="<?php echo esc_attr( $target_id ) ?>">
				<input type="hidden" name="from_name" value="<?php echo esc_attr( $from_name ) ?>">
				<input type="hidden" name="from_address" value="<?php echo esc_attr( $from_address ) ?>">
				<input type="hidden" name="reply_address" value="<?php echo esc_attr( $reply_address ) ?>">
				<input type="hidden" name="subject" value="<?php echo esc_attr( $subject ) ?>">
				<input type="hidden" name="content_type" value="<?php echo esc_attr( $content_type ) ?>">
				<input type="hidden" name="text_content" value="<?php echo esc_attr( $text_content ) ?>">
				<input type="hidden" name="htmlcontent" value="<?php echo esc_attr( $html_content ) ?>">
			</div>
		</div>
	</form>
</div>
