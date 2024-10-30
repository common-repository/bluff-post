<?php
/**
 * mail template list view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var string $message
 * @var array $mail_template
 * @var int $page_num
 * @var int $total_page
 */
$message        = isset( $data['message'] ) ? $data['message'] : '';
$page_num       = isset( $data['page_num'] ) ? (int) $data['page_num'] : 0;
$total_page     = isset( $data['total_page'] ) ? (int) $data['total_page'] : 0;
$mail_templates = $data['mail_templates'];
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Mail templates', 'bluff-post' ) ?></h1>
    <hr class="my-4">
	<div class="row">
		<?php if ( 0 < count( $mail_templates ) ) : ?>
			<div class="col">
				<?php if ( '' !== $message ) : ?>
					<div class="alert alert-success" role="alert">
						<?php echo esc_html( $message ) ?>
					</div>
				<?php endif ?>

				<div class="card">
					<div class="card-body">
						<table class="table">
							<?php /** @var BLFPST_Model_Template $mail_template */ ?>
							<?php foreach ( $mail_templates as $mail_template ) : ?>
								<tr>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-mail-template&admin_action=info&mail_template_id=' . $mail_template->id ) ) ?>"><?php echo esc_html( $mail_template->title ) ?></a>
									</td>
								</tr>
							<?php endforeach ?>
						</table>
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
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-mail-template&page_num=' . $pre_page ) ) ?>"
                                   class="page-link" aria-label="Previous"> <span aria-hidden="true">&laquo;</span> </a>
							</li>
							<?php
							for ( $i = $start_page; $i < $stop_page; $i ++ ) {
								?>
                                <li class="page-item<?php if ( $page_num == $i ) { ?> active<?php } ?>">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-mail-template&page_num=' . $i ) ) ?>" class="page-link"><?php echo( $i + 1 ) ?></a>
								</li>
								<?php
							}
							?>
							<li class="page-item">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-mail-template&page_num=' . $next_page ) ) ?>"
                                   class="page-link" aria-label="Next"> <span aria-hidden="true">&raquo;</span> </a>
							</li>
						</ul>
					</nav>
				<?php endif ?>
			</div>
		<?php else : ?>
			<?php esc_html_e( 'There is no template that has been registered.', 'bluff-post' ) ?>
		<?php endif ?>
	</div>
</div>
