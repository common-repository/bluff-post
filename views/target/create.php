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
 * @var array $tables
 * @var array $jsonTables
 * @var BLFPST_Model_Target $target
 * @var string $sql
 * @var int $target_id
 * @var WP_Error $errors
 */
$tables                     = $data['tables'];
$target                     = $data['target'];
$sql                        = isset( $data['sql'] ) ? $data['sql'] : '';
$target_id                  = $target->id;
$class_name                 = $target->class_name;
$errors                     = $data['errors'];
$default_recipient_per_page = 20;
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Registration recipients', 'bluff-post' ) ?></h1>
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

	<form id="target-form" method="post" action="<?php echo admin_url( 'admin.php?page=blfpst-target-register' ) ?>" data-parsley-validate="">
	<?php wp_nonce_field( 'blfpst-target-option', 'blfpst_target_option' ); ?>
		<input type="hidden" name="admin_action" value="register">
		<input type="hidden" name="target_id" value="<?php echo esc_html( $target_id ) ?>">
		<input type="hidden" name="class_name" value="<?php echo esc_html( $class_name ) ?>">

		<?php $target_groups = $target->target_conditionals ?>
		<input type="hidden" name="conditional_count" value="<?php echo count( $target_groups ) ?>">

		<div class="row outer_block" id="title_container" style="padding-bottom: 24px;">
            <div class="col">
                <div class="form-horizontal">
                    <div class="form-group row">
                            <label for="title"
                                   class="col-sm-2 control-label text-right"><?php esc_html_e( 'Recipients name', 'bluff-post' ) ?></label>
                            <div class="col-sm-8">
                                <input type="text" id="title" name="title" class="form-control"
                                       maxlength="255"
                                       data-parsley-required="true"
                                       data-parsley-required-message="<?php esc_attr_e( 'Please enter a recipients name.', 'bluff-post' ) ?>"
                                       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a recipients name 255 or less characters.', 'bluff-post' ) ?>"
                                       value="<?php echo esc_html( $target->title ) ?>">
                            </div>
                    </div>
                    <div class="form-group row">
                        <label for="class_name"
                               class="col-sm-2 control-label text-right"><?php esc_html_e( 'DB', 'bluff-post' ) ?></label>
                        <div class="col-sm-8">
                            <p class="form-control-static"><?php echo esc_html( $class_name ) ?></p>
                        </div>
                    </div>
                </div>
            </div>
		</div>

		<?php // グループ ?>
		<?php for (
			$i = 0;
			$i < count( $target_groups );
			$i ++
		) : ?>

			<?php /** @var BLFPST_Model_Target_Conditional $parent_conditional */ ?>
			<?php $parent_conditional = $target_groups[ $i ] ?>

			<?php if ( 0 < $i ) : ?>
				<select name="and_or<?php echo $i ?>" title="and or" class="my-2">
					<option value="AND" <?php echo ( 'AND' === $parent_conditional->and_or ) ? 'selected' : '' ?>>
						AND
					</option>
					<option value="OR" <?php echo ( 'OR' === $parent_conditional->and_or ) ? 'selected' : '' ?>>OR
					</option>
				</select>
			<?php endif ?>

			<div class="card" id="group<?php echo $i ?>">
				<div class="card-header">
					<span id="group_title<?php echo $i ?>"><?php esc_html_e( 'group', 'bluff-post' ) ?><?php echo( $i + 1 ) ?></span>
					<?php if ( 0 < $i ) : ?>
						<a href="javascript:void(0)" onclick="deleteGroup(<?php echo( $i ) ?>)" id="delete<?php echo( $i ) ?>"><i class="bi bi-x-circle-fill"></i></a>
					<?php endif ?>
				</div>
				<div class="card-body">
					<?php $target_conditionals = $parent_conditional->target_conditionals ?>
					<input type="hidden" name="conditional_count<?php echo $i ?>"
					       value="<?php echo count( $target_conditionals ) ?>">

					<?php // 条件 ?>
					<?php for ( $j = 0; $j < count( $target_conditionals ); $j ++ ) : ?>
						<?php /** @var BLFPST_Model_Target_Conditional $target_conditional */ ?>
						<?php $target_conditional = $target_conditionals[ $j ] ?>
						<?php $param = sprintf( '%d-%d', $i, $j ); ?>

						<div id="group<?php echo $param ?>" class="target_condition_line">
							<div class="form-inline">

								<?php if ( 0 == $j ) : ?>
									<div class="form-group target_and_or_select">
										<input type="hidden" name="and_or<?php echo $param ?>" value="">
									</div>
								<?php else : ?>
									<div class="form-group target_and_or_select">
										<select name="and_or<?php echo $param ?>" class="form-control">
											<option
												value="AND" <?php echo ( 'AND' === $target_conditional->and_or ) ? 'selected' : '' ?>>
												AND
											</option>
											<option
												value="OR" <?php echo ( 'OR' === $target_conditional->and_or ) ? 'selected' : '' ?>>
												OR
											</option>
										</select>
									</div>
								<?php endif ?>

								<div class="form-group">
									<label for="table_name<?php echo $param ?>" id="label_table_name<?php echo $param ?>"><?php esc_html_e( 'table', 'bluff-post' ) ?></label>:
									<select name="table_name<?php echo $param ?>" id="table_name<?php echo $param ?>"
									        class="target_table form-control">
										<?php $current_table = null; ?>
										<?php foreach ( $tables as $table ) : ?>
											<option
												value="<?php echo esc_attr( $table['name'] ) ?>" <?php echo ( $target_conditional->table_name === $table['name'] ) ? 'selected' : '' ?>><?php echo $table['name'] ?></option>
											<?php $current_table = ( $target_conditional->table_name === $table['name'] ) ? $table : $current_table ?>
										<?php endforeach ?>
									</select>
								</div>

								<div class="form-group">
									<label for="column_name<?php echo $param ?>" id="label_column_name<?php echo $param ?>"><?php esc_html_e( 'column', 'bluff-post' ) ?></label>:
									<select name="column_name<?php echo $param ?>" id="column_name<?php echo $param ?>"
									        id="column_name<?php echo $param ?>"
									        class="form-control">
										<?php $fields = $current_table ? $current_table['fields'] : $tables[0]['fields'] ?>
										<?php foreach ( $fields as $field ) : ?>
											<?php echo $target_conditional->column_name ?>
											<option
												value="<?php echo esc_attr( $field ) ?>" <?php echo ( $target_conditional->column_name === $field ) ? 'selected' : '' ?>><?php echo $field ?></option>
										<?php endforeach ?>
									</select>
								</div>

								<div class="form-group">
									<select name="compare<?php echo $param ?>" class="form-control">
										<option
											value="=" <?php echo ( '=' === $target_conditional->compare ) ? 'selected' : '' ?>>
											=
										</option>
										<option
											value="&lt;&gt;" <?php echo ( '<>' === $target_conditional->compare ) ? 'selected' : '' ?>>
											&lt;&gt;
										</option>
										<option
											value="&lt;" <?php echo ( '<' === $target_conditional->compare ) ? 'selected' : '' ?>>
											&lt;</option>
										<option
											value="&lt;=" <?php echo ( '<=' === $target_conditional->compare ) ? 'selected' : '' ?>>
											&lt;=
										</option>
										<option
											value="&gt;" <?php echo ( '>' === $target_conditional->compare ) ? 'selected' : '' ?>>
											&gt;</option>
										<option
											value="&gt;=" <?php echo ( '>=' === $target_conditional->compare ) ? 'selected' : '' ?>>
											&gt;=
										</option>
										<option
											value="LIKE" <?php echo ( 'LIKE' === $target_conditional->compare ) ? 'selected' : '' ?>>
											%LIKE%
										</option>
										<option
											value="NOTLIKE" <?php echo ( 'NOTLIKE' === $target_conditional->compare ) ? 'selected' : '' ?>>
											NOT %LIKE%
										</option>
										<option
											value="ISNULL" <?php echo ( 'ISNULL' === $target_conditional->compare ) ? 'selected' : '' ?>>
											IS NULL
										</option>
										<option
											value="ISNOTNULL" <?php echo ( 'ISNOTNULL' === $target_conditional->compare ) ? 'selected' : '' ?>>
											IS NOT NULL
										</option>
									</select>
								</div>

								<div class="form-group">
									<label for="column_value<?php echo $param ?>" id="label_column_value<?php echo $param ?>"><?php esc_attr_e( 'value', 'bluff-post' ) ?></label>:
									<input type="text"
									       name="column_value<?php echo $param ?>"
									       value="<?php echo esc_attr( $target_conditional->column_value ) ?>">
								</div>
								<?php if ( 0 < $j ) : ?>
									<a href="javascript:void(0)" onclick="deleteConditional(<?php echo esc_attr( $i ) ?>, <?php echo esc_attr( $j ) ?>)" id="delete<?php echo $param ?>"><i class="bi bi-x-circle-fill"></i></a>
								<?php endif ?>

							</div>
						</div>
						<?php if ( ( count( $target_conditionals ) - 1 ) == $j ) : ?>
							<button type="button" class="btn btn-secondary btn-sm"
							        id="addConditional<?php echo $i ?>"
							        onclick="addConditional(<?php echo $i ?>)">
                                <i class="bi bi-plus"></i>
                            </button>
						<?php endif ?>
					<?php endfor // $j ?>
				</div>
			</div>
		<?php endfor // $i ?>

		<div class="row my-4" style="margin-top: 8px;">
			<div class="col-sm-6">
				<button type="button" class="btn btn-secondary" onclick="addGroup()"><?php esc_html_e( 'Add group', 'bluff-post' ) ?></button>
			</div>
			<div class="col-sm-6 text-right">
				<button type="button" class="btn btn-secondary" id="recipientsPreviewButton" onclick="onRecipientsPreviewButton(0, <?php echo esc_html( $default_recipient_per_page ) ?>)">
					<?php esc_html_e( 'Preview recipients', 'bluff-post' ) ?>
				</button>
				<button type="button" class="btn btn-secondary mx-3" id="sqlPreviewButton" onclick="requestPreviewSQL()"><?php esc_html_e( 'Preview SQL', 'bluff-post' ) ?></button>
				<button type="submit" class="btn btn-primary" id="registerButton"><?php echo empty( $target_id ) ? esc_html__( 'Registration', 'bluff-post' ) : esc_html__( 'Update recipients', 'bluff-post' ) ?></button>
			</div>
		</div>
	</form>

	<div class="card" style="margin-top: 32px;" id="sql_preview_box">
		<div class="card-body">
			<pre id="sql_preview"></pre>
		</div>
	</div>
	<div class="row justify-content-md-center" style="margin-top: 32px;">
		<div class="col-sm-10">
			<div class="outer_block">
				<h4 id="recipient_count"><?php esc_html_e( 'Recipients 0', 'bluff-post' ) ?></h4>
				<div id="recipient_loading_message" style="display: none">
                    <div class="d-flex align-items-center">
                        <strong><?php esc_html_e( 'Loading....', 'bluff-post' ) ?></strong>
                        <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
                    </div>
				</div>
			</div>
			<ul class="list-group" id="recipient_list"></ul>
			<div id="pagenation"></div>
		</div>
	</div>
</div>
