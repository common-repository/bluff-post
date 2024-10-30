<?php
/**
 * exclude target list view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var array $exclude_recipients
 * @var integer $page_num
 * @var integer $total_page
 * @var string $message
 * @var WP_Error $errors
 */
$exclude_recipients = $data['exclude_recipients'];
$errors             = $data['errors'];
$page_num           = isset( $data['page_num'] ) ? (int) $data['page_num'] : 0;
$total_page         = isset( $data['total_page'] ) ? (int) $data['total_page'] : 0;
$message            = isset( $data['message'] ) ? $data['message'] : '';
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Exclusion recipients', 'bluff-post' ) ?></h1>
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

	<?php if ( '' !== $message ) : ?>
		<div class="alert alert-success" role="alert"><?php echo esc_html( $message ) ?></div>
	<?php endif ?>

	<form id="addExcludeForm" action="<?php echo admin_url( 'admin.php?page=blfpst-target-exclude-recipient' ) ?>" type="post" data-parsley-validate="">
	<div class="row outer_block" style="margin-bottom: 32px;">
        <div class="col-sm-1"></div>
        <div class="col-sm-6" id="exclude_address_container">
            <div class="input-group mb-3">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">@</span>
                </div>
                <input name="exclude_address" type="email" class="form-control" placeholder="<?php esc_html_e( 'Exclude e-mail address', 'bluff-post' ) ?>"
                       maxlength="255"
                       aria-describedby="basic-addon1"
                       data-parsley-required-message="<?php esc_attr_e( 'Please enter a e-mail address.', 'bluff-post' ) ?>"
                       data-parsley-required="true"
                       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a recipients e-mail address 255 or less characters.', 'bluff-post' ) ?>"
                       data-parsley-errors-container="#exclude_address_container"
                >
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-plus"></i></button>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" name="page" value="blfpst-target-exclude-recipient">
    <input type="hidden" name="admin_action" value="register">
    <?php wp_nonce_field( 'blfpst-target-exclude-recipients', 'blfpst_target_exclude_recipients' ); ?>
	</form>

	<div class="row">
        <div class="col-sm-1"></div>
		<div class="col-sm-8">
			<?php if ( 0 < count( $exclude_recipients ) ) : ?>
				<div class="card">
					<div class="card-header"><?php esc_html_e( 'Exclusion recipients', 'bluff-post' ) ?></div>
					<div class="card-body">
						<table class="table">
							<?php /** @var array $exclude_recipient */ ?>
							<?php foreach ( $exclude_recipients as $exclude_recipient ) : ?>

								<?php $exclude_recipient_id = empty( $exclude_recipient['id'] ) ? 0 : $exclude_recipient['id'] ?>
								<?php $mail_address = empty( $exclude_recipient['mail_address'] ) ? '' : $exclude_recipient['mail_address'] ?>
								<tr>
									<td>
										<?php echo esc_html( $mail_address ) ?>
									</td>
									<?php $delete_url = admin_url( 'admin.php?page=blfpst-target-exclude-recipient&admin_action=delete&exclude_recipient_id=' . $exclude_recipient_id ) ?>
									<td style="width: 32px;">
										<a href="<?php echo esc_url( $delete_url ) ?>"><i class="bi bi-x-circle-fill"></i></a>
									</td>
								</tr>
							<?php endforeach ?>
						</table>
					</div>
				</div>
			<?php else : ?>
				<div class="alert alert-info" role="alert"><?php esc_html_e( 'There is no e-mail address that is registered.', 'bluff-post' ) ?></div>
			<?php endif ?>
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

						$previous_page_url = admin_url( sprintf( 'admin.php?page=blfpst-target-exclude-recipient&page_num=%d', (int) $pre_page ) );
						$next_page_url     = admin_url( sprintf( 'admin.php?page=blfpst-target-exclude-recipient&page_num=%d', (int) $next_page ) );
						?>
						<li>
							<a href="<?php echo esc_url( $previous_page_url ) ?>" aria-label="Previous">
								<span aria-hidden="true">&laquo;</span> </a>
						</li>
						<?php
						for ( $i = $start_page; $i < $stop_page; $i ++ ) {
							?>
							<li <?php if ( $page_num == $i ) { ?>class="active"<?php } ?>>
								<?php $number_page_url = admin_url( sprintf( 'admin.php?page=blfpst-target-exclude-recipient&page_num=%d', (int) $i ) );
								?>
								<a href="<?php echo esc_url( $number_page_url ) ?>"><?php echo( $i + 1 ) ?></a>
							</li>
							<?php
						}
						?>
						<li>
							<a href="<?php echo esc_url( $next_page_url ) ?>"
							   aria-label="Next"> <span aria-hidden="true">&raquo;</span> </a>
						</li>
					</ul>
				</nav>
			<?php endif ?>
		</div>
	</div>
</div>
