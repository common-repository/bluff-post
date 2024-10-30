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
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Outbox', 'bluff-post' ) ?></h1>
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

	<div class="row">
		<div class="col-sm-12">
			<div class="card">
				<div class="card-body">
					<?php if ( 0 < count( $mails ) ) : ?>
						<table class="table">
							<thead>
							<tr>
								<th><?php esc_html_e( 'Subject', 'bluff-post' ) ?></th>
								<th><?php esc_html_e( 'Content', 'bluff-post' ) ?></th>
								<th><?php esc_html_e( 'Recipients', 'bluff-post' ) ?></th>
								<th><?php esc_html_e( 'Sent count', 'bluff-post' ) ?></th>
								<th><?php esc_html_e( 'Start date', 'bluff-post' ) ?></th>
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
								$is_show_text_content = ( isset( $mail->text_content ) && ( '' !== $mail->text_content ) );
								$text_content         = blfpst_shortcut_string( isset( $mail->text_content ) ? $mail->text_content : '', $max_length, false );
								$target_name          = empty( $mail->target_name ) ? '' : $mail->target_name;
								?>
								<tr>
									<td class="text-overflow-ellipsis">
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-histories&admin_action=info&send_mail_id=' . $send_mail_id ) ) ?>"><?php echo esc_html( $subject ) ?></a>
									</td>
									<td>
										<?php if ( $is_show_text_content ) { ?>
											<small><?php echo esc_html( $text_content ) ?></small>
										<?php } else { ?>
											<em>HTMLメール</em>
										<?php } ?>
									</td>
									<td>
										<small><?php echo esc_html( $target_name ) ?></small>
									</td>
									<td class="text-right">
										<small><?php echo esc_html( number_format( $mail->recipient_count ) ) ?></small>
									</td>
									<td class="text-nowrap">
										<small><?php echo empty( $mail->send_request_start_at ) ? '' : esc_html( blfpst_localize_datetime_string( $mail->send_request_start_at ) ) ?></small>
									</td>
								</tr>
							<?php endforeach ?>
							</tbody>
						</table>
					<?php else : ?>
						<?php esc_html_e( 'There is no transmission history.', 'bluff-post' ) ?>
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
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-histories&page_num=' . $pre_page ) ) ?>"
                               class="page-link" aria-label="Previous"> <span aria-hidden="true">&laquo;</span> </a>
						</li>
						<?php
						for ( $i = $start_page; $i < $stop_page; $i ++ ) {
							?>
                            <li class="page-item<?php if ( $page_num == $i ) { ?> active<?php } ?>">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-histories&page_num=' . $i ) ) ?>" class="page-link"><?php echo( $i + 1 ) ?></a>
							</li>
							<?php
						}
						?>
						<li class="page-item">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-histories&page_num=' . $next_page ) ) ?>"
                               class="page-link" aria-label="Next"> <span aria-hidden="true">&raquo;</span> </a>
						</li>
					</ul>
				</nav>
			<?php endif ?>
		</div>
	</div>
</div>
