<?php
/**
 * target list view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var string $message
 * @var array $targets
 * @var WP_Error $errors
 */
$targets    = $data['targets'];
$message    = isset( $data['message'] ) ? $data['message'] : '';
$errors     = $data['errors'];
$page_num   = isset( $data['page_num'] ) ? (int) $data['page_num'] : 0;
$total_page = isset( $data['total_page'] ) ? (int) $data['total_page'] : 0;
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Recipients', 'bluff-post' ) ?></h1>
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

	<div class="row">
		<div class="col-sm-12">
			<?php if ( '' !== $message ) : ?>
				<div class="alert alert-success" role="alert"><?php echo esc_html( $message ) ?></div>
			<?php endif ?>

			<?php if ( 0 < count( $targets ) ) : ?>
				<div class="card">
					<div class="card-body">
						<table class="table">
							<?php /** @var BLFPST_Model_Target $target */ ?>
							<?php foreach ( $targets as $target ) : ?>
								<tr>
									<td>
										<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-targets&admin_action=info&target_id=' . $target->id ) ) ?>"><?php echo esc_html( $target->title ) ?></a>
									</td>
								</tr>
							<?php endforeach ?>
						</table>
					</div>
				</div>
			<?php else : ?>
				<?php esc_html_e( 'There is no recipients conditions that have been registered.', 'bluff-post' ) ?>
			<?php endif ?>
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

						$previous_page_url = admin_url( sprintf( 'admin.php?page=blfpst-targets&page_num=%d', (int) $pre_page ) );
						$next_page_url     = admin_url( sprintf( 'admin.php?page=blfpst-targets&page_num=%d', (int) $next_page ) );
						?>
						<li class="page-item">
							<a href="<?php echo esc_url( $previous_page_url ) ?>" class="page-link" aria-label="Previous">
								<span aria-hidden="true">&laquo;</span> </a>
						</li>
						<?php
						for ( $i = $start_page; $i < $stop_page; $i ++ ) {
							?>
							<li class="page-item<?php if ( $page_num == $i ) { ?> active<?php } ?>">
								<?php $number_page_url = admin_url( sprintf( 'admin.php?page=blfpst-targets&page_num=%d', (int) $i ) );
								?>
								<a href="<?php echo esc_url( $number_page_url ) ?>" class="page-link"><?php echo( $i + 1 ) ?></a>
							</li>
							<?php
						}
						?>
						<li class="page-item">
							<a href="<?php echo esc_url( $next_page_url ) ?>"
                               class="page-link" aria-label="Next"> <span aria-hidden="true">&raquo;</span> </a>
						</li>
					</ul>
				</nav>
			<?php endif ?>
		</div>
	</div>
</div>
