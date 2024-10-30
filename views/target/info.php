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
 * @var BLFPST_Model_Target $target
 * @var WP_Error $errors
 */
$target      = empty( $data['target'] ) ? new BLFPST_Model_Target() : $data['target'];
$errors      = $data['errors'];
$description = $target->description;
$send_mails  = BLFPST_Send_Mails_Controller::load_send_mails_with_target_id( $target->id );
?>
<script type="text/javascript">
	/* <![CDATA[ */
	$jq = jQuery.noConflict();

	function openTargetList(target_id) {
		if ($jq.isNumeric(target_id) && (target_id > 0)) {
			var url = '<?php echo admin_url( 'admin.php?page=blfpst-targets&admin_action=recipients&target_id=' ) ?>' + target_id;
			window.open(url, 'blfpst_target_list');
		}
	}
	/* ]]> */
</script>
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

	<?php if ( ! empty( $target ) ) : ?>
		<div class="row my-4">
            <div class="col-sm-1"></div>
			<div class="col-sm-10">

				<div class="outer_block">
					<h4><?php echo esc_html( $target->title ) ?></h4>
				</div>
				<?php if ( ! empty( $description ) ) : ?>
					<div class="well"><?php echo esc_html( $description ) ?></div>
				<?php endif ?>

				<?php $group_number = 1; ?>
				<?php /** @var BLFPST_Model_Target_Conditional $parent_conditional */ ?>
				<?php foreach ( $target->target_conditionals as $parent_conditional ) : ?>
					<?php if ( $group_number > 1 ) : ?>
						<div class="outer_block my-4">
							<?php echo ( empty( $parent_conditional->and_or ) ) ? '' : esc_html( $parent_conditional->and_or ) ?>
						</div>
					<?php endif; ?>
					<div class="card my-4">
						<div class="card-body">
							<table class="table">
								<tr>
									<th><?php esc_html_e( 'and/or', 'bluff-post' ) ?></th>
									<th class="target_table_column"><?php esc_html_e( 'table', 'bluff-post' ) ?></th>
									<th class="target_table_column"><?php esc_html_e( 'column', 'bluff-post' ) ?></th>
									<th><?php esc_html_e( 'compare', 'bluff-post' ) ?></th>
									<th class="target_table_column"><?php esc_html_e( 'value', 'bluff-post' ) ?></th>
								</tr>
								<?php /** @var BLFPST_Model_Target_Conditional $child_conditional */ ?>
								<?php foreach ( $parent_conditional->target_conditionals as $child_conditional ) : ?>
									<tr>
										<td><?php echo esc_html( $child_conditional->and_or ) ?></td>
										<td><?php echo esc_html( $child_conditional->table_name ) ?></td>
										<td><?php echo esc_html( $child_conditional->column_name ) ?></td>
										<td><?php echo esc_html( $child_conditional->compare ) ?></td>
										<td><?php echo esc_html( $child_conditional->column_value ) ?></td>
									</tr>
								<?php endforeach ?>
							</table>
							<?php $group_number ++; ?>
						</div>
					</div>
				<?php endforeach ?>

			</div>
		</div>

		<div class="row my-4">
            <div class="col-sm-1"></div>
			<div class="col-sm-8">
				<?php $edit_url = admin_url( 'admin.php?page=blfpst-target-register' ) ?>
				<form id="blfpst-edit-target-form" method="post" action="<?php echo esc_url( $edit_url ) ?>" name="main_form" style="display: inline">
					<?php wp_nonce_field( 'blfpst-target-option-edit', 'blfpst_target_option_edit' ); ?>
					<input type="hidden" name="admin_action" value="edit">
					<input type="hidden" name="target_id" value="<?php echo $target->id ?>">
					<button type="submit" class="btn btn-primary">
						<?php esc_html_e( 'Edit', 'bluff-post' ) ?>
					</button>
				</form>
				<a href="#confirmDelete" role="button" class="btn btn-danger ml-4" data-toggle="modal">
					<?php esc_html_e( 'Delete', 'bluff-post' ) ?>
				</a>
			</div>

			<div class="col-sm-2 text-right">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-crate&target_id=' . $target->id ) ) ?>"
				   role="button" class="btn btn-secondary">
					<?php esc_html_e( 'Create e-mail', 'bluff-post' ) ?>
				</a>
			</div>
		</div>
	<?php else : ?>
		<div class="outer_block my-4">
			<?php esc_html_e( 'There is no data that has been registered.', 'bluff-post' ) ?>
		</div>
	<?php endif ?>

    <hr class="my-4">

	<div class="row mt-4">
        <div class="col-sm-1"></div>
		<div class="col-sm-10">
			<div class="outer_block">
				<h4><?php esc_html_e( 'Send data during and transmission contracts that specify this recipients.', 'bluff-post' ) ?><?php echo esc_html( number_format( count( $send_mails ) ) ) ?></h4>
			</div>
			<ul class="list-group">
				<?php foreach ( $send_mails as $mail ) : ?>
					<?php if ( ( 'reserved' === $mail->status ) && ( empty( $mail->send_request_start_at ) ) ) { ?>
						<li class="list-group-item">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-reserves&admin_action=info&send_mail_id=' . $mail->id ) ) ?>"><?php echo esc_html( $mail->subject ) ?></a>
						</li>
					<?php } else if ( 'reserved' === $mail->status ) { ?>
						<li class="list-group-item">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-reserves&admin_action=sending_info&send_mail_id=' . $mail->id ) ) ?>"><?php echo esc_html( $mail->subject ) ?></a>
						</li>
					<?php } else { ?>
						<li class="list-group-item">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=blfpst-send-mail-histories&admin_action=info&send_mail_id=' . $mail->id ) ) ?>"><?php echo esc_html( $mail->subject ) ?></a>
						</li>
					<?php } ?>
				<?php endforeach ?>
			</ul>
		</div>
	</div>

	<div class="row bt-2">
        <div class="col-sm-1"></div>
		<div class="col-sm-10 text-right">
			<button type="button" class="btn btn-secondary" onclick="openTargetList(<?php echo esc_html( $target->id ) ?>)" id="open_target_list_button">
				<?php esc_html_e( 'Preview recipients', 'bluff-post' ) ?>
			</button>
		</div>
	</div>
</div>

<!-- Delete Modal -->
<?php $delete_url = admin_url( 'admin.php?page=blfpst-targets' ) ?>
<form id="blfpst-delete-target-form" method="post" action="<?php echo esc_url( $delete_url ) ?>" name="delete_form" style="display: inline">
	<div id="confirmDelete" class="modal fade">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
							aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><?php esc_html_e( 'Confirm delete', 'bluff-post' ) ?></h4>
				</div>
				<div class="modal-body">
					<p><?php esc_html_e( 'Are you sure you want to delete this recipients?', 'bluff-post' ) ?></p>
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
	<input type="hidden" name="page" value="blfpst-targets"> <input type="hidden" name="admin_action" value="delete">
	<input type="hidden" name="target_id" value="<?php echo esc_html( $target->id ) ?>">
	<?php wp_nonce_field( 'blfpst-target-option-delete', 'blfpst_target_option_delete' ); ?>
</form>
<!-- Delete Modal -->
