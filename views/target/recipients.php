<?php
/**
 * recipients view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var BLFPST_Model_Target $target
 * @var object $target_result
 * @var array $target_results
 */
$target_id       = isset( $data['target_id'] ) ? $data['target_id'] : 0;
$target          = empty( $data['target'] ) ? new BLFPST_Model_Target() : $data['target'];
$target_results  = empty( $data['target_results'] ) ? array() : $data['target_results'];
$recipient_count = isset( $data['recipient_count'] ) ? (int) $data['recipient_count'] : 0;
$page_num        = isset( $data['page_num'] ) ? (int) $data['page_num'] : 0;
$total_page      = isset( $data['total_page'] ) ? (int) $data['total_page'] : 0;
$errors          = isset( $data['errors'] ) ? $data['errors'] : new WP_Error();

$data_source = BLFPST_Targets_Controller::data_source_object( $target );
?>
<div class="container">
    <h1 class="my-4"><?php echo esc_html( $target->title ) ?></h1>
    <hr class="my-4">
	<div class="row">
		<div class="col-sm-6">
		</div>
		<div class="col-sm-6 text-right">
			<button type="button" class="btn btn-secondary" onclick="window.close();">
                <span aria-hidden="true">&times;</span>
            </button>
		</div>
	</div>
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
        <div class="col-sm-1"></div>
            <div class="col-sm-10">
                <div class="outer_block">
                    <p><?php esc_html_e( 'Recipients', 'bluff-post' ) ?> <?php echo esc_html( number_format( $recipient_count ) ) ?></p>
                </div>
                <ul class="list-group">
                    <?php foreach ( $target_results as $target_result ) : ?>
                        <li class="list-group-item"><?php echo esc_html( $target_result->{$data_source->email_field_name()} ) ?></li>
                    <?php endforeach ?>
                </ul>
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

						$previous_page_url = admin_url( sprintf( 'admin.php?page=blfpst-targets&admin_action=recipients&target_id=%d&page_num=%d', (int) $target_id, (int) $pre_page ) );
						$next_page_url     = admin_url( sprintf( 'admin.php?page=blfpst-targets&admin_action=recipients&target_id=%d&page_num=%d', (int) $target_id, (int) $next_page ) );
						?>
						<li class="page-item">
							<a href="<?php echo esc_url( $previous_page_url ) ?>" class="page-link" aria-label="Previous">
								<span aria-hidden="true">&laquo;</span> </a>
						</li>
						<?php
						for ( $i = $start_page; $i < $stop_page; $i ++ ) {
							?>
                            <li class="page-item<?php if ( $page_num == $i ) { ?> active<?php } ?>">
								<?php $number_page_url = admin_url( sprintf( 'admin.php?page=blfpst-targets&admin_action=recipients&target_id=%d&page_num=%d', (int) $target_id, (int) $i ) );
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
