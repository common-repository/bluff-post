<?php
/**
 * send history list view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var array $mails
 * @var int $page_num
 * @var int $total_page
 * @var array $messages
 * @var WP_Error $errors
 */
$mails      = isset( $data['mails'] ) ? $data['mails'] : array();
$page_num   = isset( $data['page_num'] ) ? (int) $data['page_num'] : 0;
$total_page = isset( $data['total_page'] ) ? (int) $data['total_page'] : 0;
$messages   = isset( $data['messages'] ) ? $data['messages'] : array();
$errors     = isset( $data['errors'] ) ? $data['errors'] : new WP_Error();
?>
<script type="text/javascript">
	/* <![CDATA[ */
	$jq = jQuery.noConflict();

	function deleteAll() {
		$jq('#confirmDelete').modal();
	}

	function recycleMail(send_mail_id) {
		$jq('form[name=recycle] input[name=send_mail_id]').val(send_mail_id);
		$jq('form[name=recycle]').submit();
	}
	/* ]]> */
</script>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Trash', 'bluff-post' ) ?></h1>
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

	<?php if ( 0 < count( $messages ) ) : ?>
		<div class="alert alert-success" role="alert">
			<ul>
				<?php foreach ( $messages as $message ) : ?>
				<li><?php echo esc_html( $message ) ?>
					<?php endforeach ?>
			</ul>
		</div>
	<?php endif ?>

	<div class="row" style="padding-bottom: 24px;">
		<div class="col-sm-12 text-right">
			<button type="button" class="btn btn-secondary" onclick="deleteAll()">
				<?php esc_html_e( 'Empty Trash', 'bluff-post' ) ?>
			</button>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-body">
					<?php if ( 0 < count( $mails ) ) : ?>
						<table class="table">
							<thead>
							<tr>
								<th><?php esc_html_e( 'Subject', 'bluff-post' ) ?></th>
								<th><?php esc_html_e( 'Recipients', 'bluff-post' ) ?></th>
								<th style="width: 180px;"><?php esc_html_e( 'Deletion date', 'bluff-post' ) ?></th>
								<th style="width: 40px;"></th>
							</tr>
							</thead>
							<tbody>
							<?php foreach ( $mails as $mail ) : ?>
								<?php
								/**
								 * @var BLFPST_Model_Send_Mail $mail
								 */

								$max_length           = 20;
								$send_mail_id         = isset( $mail->id ) ? $mail->id : 0;
								$subject              = isset( $mail->subject ) ? $mail->subject : '';
								$target_name          = empty( $mail->target_name ) ? '' : $mail->target_name;
								?>
								<tr>
                                    <td class="text-overflow-ellipsis">
										<?php echo esc_html( $subject ) ?>
									</td>
									<td>
										<small><?php echo esc_html( $target_name ) ?></small>
									</td>
									<td>
										<small><?php echo esc_html( blfpst_localize_datetime_string( $mail->deleted_at ) ) ?></small>
									</td>
									<td>
                                        <a href="#" role="button" onclick="recycleMail(<?php echo esc_html( $send_mail_id ) ?>)"><i class="bi bi-reply-fill"></i></a>
									</td>
								</tr>
							<?php endforeach ?>
							</tbody>
						</table>
					<?php else : ?>
						<?php esc_html_e( 'There is no mail in the trash.', 'bluff-post' ) ?>
					<?php endif ?>
				</div>
			</div>
			<?php if ( 1 < $total_page ) : ?>
				<nav class="mt-4">
					<ul class="pagination">
						<?php
						// Start
						$start_page = $page_num - 2;
						$start_page = ( $start_page < 0 ) ? 0 : $start_page;

						// Stop
						$stop_page = $start_page + 5;
						$count     = $stop_page - $start_page;
						$stop_page = ( $count > 5 ) ? ( $stop_page - ( $count - 5 ) ) : $stop_page;
						$stop_page = ( $stop_page > $total_page ) ? $total_page : $stop_page;

						$count      = $stop_page - $start_page;
						$start_page = ( $count < 5 ) ? ( $start_page - ( 5 - $count ) ) : $start_page;
						$start_page = ( $start_page < 0 ) ? 0 : $start_page;

						$stop_page = ( $stop_page < $start_page ) ? $start_page : $stop_page;

						// Pre
						$pre_page = $start_page - 1;
						$pre_page = ( $pre_page < 0 ) ? 0 : $pre_page;

						// Next
						$next_page = $stop_page;
						$next_page = ( $next_page >= $total_page ) ? ( $total_page - 1 ) : $next_page;
						$next_page = ( $next_page < 0 ) ? 0 : $next_page;
						?>
						<li class="page-item">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-trashes&page_num=' . $pre_page ) ) ?>"
                               class="page-link" aria-label="Previous"> <span aria-hidden="true">&laquo;</span> </a>
						</li>
						<?php
						for ( $i = $start_page; $i < $stop_page; $i ++ ) {
							?>
                            <li class="page-item<?php if ( $page_num == $i ) { ?> active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-trashes&page_num=' . $i ) ) ?>" class="page-link"><?php echo( $i + 1 ) ?></a>
							</li>
							<?php
						}
						?>
						<li class="page-item">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-trashes&page_num=' . $next_page ) ) ?>"
                               class="page-link" aria-label="Next"> <span aria-hidden="true">&raquo;</span> </a>
						</li>
					</ul>
				</nav>
			<?php endif ?>
		</div>
	</div>
</div>

<form method="post" name="recycle" action="<?php echo admin_url( 'admin.php?page=blfpst-send-mail' ) ?>">
<!-- /.modal -->
	<input type="hidden" name="admin_action" value="recycle"> <input type="hidden" name="send_mail_id" value="0">
	<?php wp_nonce_field( 'blfpst-send-mail-recycle', 'blfpst_send_mail_recycle' ); ?>
</form>

<!-- Delete Modal -->
<form method="post" name="delete">
	<div id="confirmDelete" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php esc_html_e( 'Confirm delete', 'bluff-post' ) ?></h4>
				</div>
				<div class="modal-body">
					<p><?php esc_html_e( 'Are you sure you want to empty the Trash?', 'bluff-post' ) ?></p>
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
	<input type="hidden" name="admin_action" value="clear"> <input type="hidden" name="send_mail_id" value="0">
	<?php wp_nonce_field( 'blfpst-mail-draft-delete', 'blfpst_mail_draft_delete' ); ?>
</form>
<!-- Delete Modal -->
