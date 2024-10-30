<?php
/**
 * log list view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var string $message
 * @var array $logs
 * @var int $page_num
 * @var int $total_page
 */
$message      = isset( $data['message'] ) ? $data['message'] : '';
$page_num     = isset( $data['page_num'] ) ? (int) $data['page_num'] : 0;
$total_page   = isset( $data['total_page'] ) ? (int) $data['total_page'] : 0;
$logs         = $data['logs'];
$level_labels = [ 'default', 'info', 'info', 'warning', 'danger', 'danger', 'danger', 'danger' ];
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Logs', 'bluff-post' ) ?></h1>
    <hr class="my-4">
	<div class="row">
		<div class="col-sm-12">

			<?php if ( '' !== $message ) : ?>
				<div class="alert alert-success" role="alert">
					<?php echo esc_html( $message ) ?>
				</div>
			<?php endif ?>

			<div class="card">
				<div class="card-header"><?php esc_html_e( 'Operation log', 'bluff-post' ) ?></div>
				<div class="card-body">
					<?php if ( 0 < count( $logs ) ) : ?>
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
									<td>
										<a href="<?php echo esc_url( $delete_url ) ?>" role="button"><i class="bi bi-x-circle-fill"></i></a>
									</td>
								</tr>
							<?php endforeach ?>
						</table>
					<?php else : ?>
						<?php esc_html_e( 'There is no log.', 'bluff-post' ) ?>
					<?php endif ?>
				</div>
			</div>
			<?php if ( 1 < $total_page ) : ?>
				<nav>
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
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-logs&page_num=' . $pre_page ) ) ?>"
							   aria-label="Previous"> <span aria-hidden="true">&laquo;</span> </a>
						</li>
						<?php
						for ( $i = $start_page; $i < $stop_page; $i ++ ) {
							?>
							<li <?php if ( $page_num == $i ) { ?>class="active"<?php } ?>>
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-logs&page_num=' . $i ) ) ?>"><?php echo( $i + 1 ) ?></a>
							</li>
							<?php
						}
						?>
						<li>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-logs&page_num=' . $next_page ) ) ?>"
							   aria-label="Next"> <span aria-hidden="true">&raquo;</span> </a>
						</li>
					</ul>
				</nav>
			<?php endif ?>
		</div>
	</div>
</div>
