<?php
/**
 * send mail option edit view.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
/**
 * @var array $data
 * @var WP_Error $errors
 * @var string $message
 */
$errors                         = $data['errors'];
$message                        = $data['message'];
$error_address                  = $data['error_address'];
$mailer_type                    = isset( $data['mailer_type'] ) ? $data['mailer_type'] : 'mail';
$sendmail_path                  = $data['sendmail_path'];
$smtp_host                      = $data['smtp_host'];
$smtp_port                      = $data['smtp_port'];
$smtp_secure                    = $data['smtp_secure'];
$smtp_auth                      = $data['smtp_auth'];
$smtp_user_name                 = $data['smtp_user_name'];
$smtp_password                  = $data['smtp_password'];
$mail_content_charset           = $data['mail_content_charset'];
$transmission_speed_limit_count = $data['transmission_speed_limit_count'];
$transmission_speed_limit_time  = $data['transmission_speed_limit_time'];
$reserved_notification_address  = '';
$data_sources                   = isset( $data['data_sources'] ) ? $data['data_sources'] : array();
$target_database_name           = isset( $data['target_database_name'] ) ? $data['target_database_name'] : '';
$theme_name                     = isset( $data['theme_name'] ) ? $data['theme_name'] : 'default';
?>
<div class="container">
    <h1 class="my-4"><?php esc_html_e( 'Bluff Post options', 'bluff-post' ) ?></h1>
    <hr class="my-4">

	<?php if ( 0 < count( $errors->get_error_messages() ) ) : ?>
		<div class="alert alert-danger" role="alert">
			<ul>
				<?php foreach ( $errors->get_error_messages() as $error ) : ?>
				<li><?php echo esc_html( $error ) ?>
					<?php endforeach ?>
			</ul>
		</div>
	<?php endif ?>
	<?php if ( '' !== $message ) : ?>
		<div class="alert alert-success" role="alert"><?php echo esc_html( $message ) ?></div>
	<?php endif ?>

	<form method="post" class="form-horizontal" id="mainForm" data-parsley-validate>
		<?php wp_nonce_field( 'blfpst-target-option', 'blfpst_target_option' ); ?>

		<div class="card my-4">
			<div class="card-header">
				<div class="form-inline">
					<?php esc_html_e( 'failure', 'bluff-post' ) ?>
				</div>
			</div>
			<div class="card-body">
				<div class="row form-group">
					<label for="error_address" class="col-sm-2 control-label"><?php esc_html_e( 'Bounce e-mail address', 'bluff-post' ) ?></label>

					<div class="col-sm-4">
						<input name="error_address" type="email" id="error_address"
						       value="<?php echo esc_attr( $error_address ) ?>" class="form-control"
						       maxlength="255"
						       data-parsley-type-message="<?php esc_attr_e( 'The format of the bounce e-mail address is invalid.', 'bluff-post' ) ?>"
						       data-parsley-maxlength-message="<?php esc_attr_e( 'Please enter a bounce e-mail address 255 or less characters.', 'bluff-post' ) ?>"
						       placeholder="<?php esc_attr_e( 'e-mail address', 'bluff-post' ) ?>"/>
					</div>
				</div>
			</div>
		</div>

		<?php if ( ! empty( $data_sources ) ) : ?>
            <div class="card my-4">
                <div class="card-header">
					<div class="form-inline">
                        <?php esc_html_e( 'DB', 'bluff-post' ) ?>
					</div>
				</div>
				<div class="card-body" id="target_database_name_panel">
					<?php /** @var BLFPST_Abstract_Data_Source $target_database_name */ ?>
					<?php foreach ( $data_sources as $data_source ) : ?>
						<div class="radio">
							<label> <input type="radio" name="target_database_name" id="target_database_name"
							               value="<?php echo esc_html( $data_source->name() ) ?>" <?php echo ( $data_source->name() === $target_database_name ) ? 'checked' : '' ?>><?php echo esc_html( $data_source->display_name() ) ?>
							</label>

                            <div class="row justify-content-md-center">
								<div class="col-sm-8">
									<div class="card">
										<div class="card-body">
                                            <?php echo esc_html( $data_source->description() ) ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php endforeach ?>
				</div>
			</div>
		<?php endif ?>

        <div class="card my-4">
            <div class="card-header">
				<div class="form-inline">
					<?php esc_html_e( 'Send Mail', 'bluff-post' ) ?>
				</div>
			</div>
			<div class="card-body">
				<div class="radio">
					<label> <input type="radio" name="mailer_type" id="mailer_type0"
					               value="mail" <?php echo ( 'mail' === $mailer_type ) ? 'checked' : '' ?>><?php esc_html_e( 'Use PHP mail()', 'bluff-post' ) ?>
					</label>

                    <div class="row justify-content-md-center">
						<div class="col-sm-8">
							<div class="card">
								<div class="card-body">
                                    <?php esc_html_e( 'Using the mail function of PHP to send the e-mail.', 'bluff-post' ) ?>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="radio">
					<label> <input type="radio" name="mailer_type" id="mailer_type_sendmail"
					               value="sendmail" <?php echo ( 'sendmail' === $mailer_type ) ? 'checked' : '' ?>><?php esc_html_e( 'use sendmail command', 'bluff-post' ) ?>
					</label>

					<div class="row justify-content-md-center">
						<div class="col-sm-8">
							<div class="card">
								<div class="card-body">
                                    <div class="mb-2"><?php esc_html_e( 'Use the sendmail command to send mail.', 'bluff-post' ) ?></div>
									<div class="row form-group">
										<label for="sendmail" class="col-sm-2 control-label">sendmail</label>

										<div class="col-sm-10">
											<input name="sendmail" type="text" id="sendmail"
											       value="<?php echo esc_attr( $sendmail_path ) ?>"
											       class="form-control" placeholder="/usr/sbin/sendmail"
											       data-parsley-required-message="<?php esc_attr_e( 'Please enter a path to the sendmail command.', 'bluff-post' ) ?>"
											/>
											<small> <?php esc_html_e( 'Specify the path to the sendmail command.', 'bluff-post' ) ?></small>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="radio">
					<label> <input type="radio" name="mailer_type" id="mailer_type_smtp"
					               value="smtp" <?php echo ( 'smtp' === $mailer_type ) ? 'checked' : '' ?>><?php esc_html_e( 'To use the specified SMTP server', 'bluff-post' ) ?>
					</label>
				</div>
                <div class="row justify-content-md-center">
					<div class="col-sm-8">
						<div class="card">
							<div class="card-body">
                                <div class="mb-2"><?php esc_html_e( 'Using the specified SMTP server to send mail.', 'bluff-post' ) ?></div>
								<div class="row form-group">
									<label for="smtp_host" class="col-sm-3 control-label"><?php esc_html_e( 'Host', 'bluff-post' ) ?></label>

									<div class="col-sm-9">
										<input name="smtp_host" type="text" id="smtp_host" value="<?php echo esc_attr( $smtp_host ) ?>"
										       class="form-control" style="width:50%"
										       data-parsley-required-message="<?php esc_attr_e( 'Please enter a host name or IP address.', 'bluff-post' ) ?>"
										       placeholder="localhost"/>
										<small> <?php esc_html_e( 'Specify the host name or IP address of the SMTP server.', 'bluff-post' ) ?></small>
									</div>
								</div>
								<div class="row form-group">
									<label for="smtp_port" class="col-sm-3 control-label"><?php esc_html_e( 'Port', 'bluff-post' ) ?></label>

									<div class="col-sm-9">
										<input name="smtp_port" type="text" id="smtp_port" value="<?php echo esc_attr( $smtp_port ) ?>"
										       class="form-control" style="width:20%"
										       maxlength="5"
										       data-parsley-type="integer"
										       data-parsley-type-message="<?php esc_attr_e( 'Please enter a numeric port number.', 'bluff-post' ) ?>"
										       data-parsley-required-message="<?php esc_attr_e( 'Please input a port number.', 'bluff-post' ) ?>"
										       placeholder="25"/>
										<small> <?php esc_html_e( 'Specify the port number of the SMTP server.', 'bluff-post' ) ?></small>
									</div>
								</div>

                                <div class="row form-group">
                                    <label for="smtp_auth" class="col-sm-3 control-label"><?php esc_html_e( 'SMTP Auth', 'bluff-post' ) ?></label>

                                    <div class="col-sm-9">
                                        <div class="custom-control custom-switch">
                                            <input name="smtp_auth" type="checkbox" class="custom-control-input" id="smtp_auth"
                                                   value="1" <?php echo $smtp_auth ? 'checked' : '' ?>>
                                            <label class="custom-control-label" for="smtp_auth"><?php esc_html_e( 'Use SMTP Auth', 'bluff-post' ) ?></label>
                                        </div>
                                        <small> <?php esc_html_e( 'Specify SMTP auth.', 'bluff-post' ) ?></small>
                                    </div>
                                </div>

                                <div class="row form-group">
                                    <label for="smtp_secure" class="col-sm-3 control-label"><?php esc_html_e( 'Secure mode', 'bluff-post' ) ?></label>

                                    <div class="col-sm-9">
                                        <select class="form-control" name="smtp_secure" id="smtp_secure" style="width:50%">
                                            <option value="tls" <?php echo $smtp_secure === 'tls' ? 'selected' : '' ?>>TLS</option>
                                            <option value="ssl" <?php echo $smtp_secure === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                        </select>
                                        <small> <?php esc_html_e( 'Specify SMTP secure mode.', 'bluff-post' ) ?></small>
                                    </div>
                                </div>

                                <div class="row form-group">
                                    <label for="smtp_user_name" class="col-sm-3 control-label"><?php esc_html_e( 'User name', 'bluff-post' ) ?></label>

                                    <div class="col-sm-9">
                                        <input name="smtp_user_name" type="text" id="smtp_user_name" value="<?php echo esc_attr( $smtp_user_name ) ?>"
                                               class="form-control" style="width:50%"
                                               data-parsley-required-message="<?php esc_attr_e( 'Please enter a SMTP user name.', 'bluff-post' ) ?>"
                                               placeholder="user name"/>
                                        <small> <?php esc_html_e( 'Specify the SMTP user name.', 'bluff-post' ) ?></small>
                                    </div>
                                </div>

                                <div class="row form-group">
                                    <label for="smtp_password" class="col-sm-3 control-label"><?php esc_html_e( 'Password', 'bluff-post' ) ?></label>

                                    <div class="col-sm-9">
                                        <input name="smtp_password" type="password" id="smtp_password" value="<?php echo esc_attr( $smtp_password ) ?>"
                                               class="form-control" style="width:50%"
                                               data-parsley-required-message="<?php esc_attr_e( 'Please enter a SMTP password.', 'bluff-post' ) ?>"
                                               placeholder="password"/>
                                        <small> <?php esc_html_e( 'Specify the SMTP password.', 'bluff-post' ) ?></small>
                                    </div>
                                </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!--
            <div class="card my-4">
                <div class="card-header">
					<div class="form-inline">
						<span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span> <?php esc_html_e( 'Notification option', 'bluff-post' ) ?>
					</div>
				</div>
				<div class="card-body">
					<div class="form-group">
						<label for="start_send_notification_address" class="col-sm-2 control-label"><?php esc_html_e( 'Notification address', 'bluff-post' ) ?></label>

						<div class="col-sm-10">
							<textarea name="start_send_notification_address" id="start_send_notification_address" class="form-control" rows="3" style="width:50%"></textarea>
							<p><?php esc_html_e( 'When sending mail processing execution, and notifies the execution of the transmission processing by adding any destination to the end of the destination.', 'bluff-post' ) ?>
						</div>
					</div>
				</div>
			</div>
			-->

        <div class="card my-4">
            <div class="card-header">
				<div class="form-inline">
					<?php esc_html_e( 'Charset', 'bluff-post' ) ?>
				</div>
			</div>
			<div class="card-body">
				<div class="row form-group">
					<label for="mail_content_charset" class="col-sm-2 control-label"><?php esc_html_e( 'Mail charset', 'bluff-post' ) ?></label>
					<div class="col-sm-4">
						<select name="mail_content_charset" id="mail_content_charset" class="form-control">
							<option value="UTF-8" <?php echo ( 'UTF-8' === $mail_content_charset ) ? ' selected' : '' ?>>
								UTF-8
							</option>
							<option value="UTF-16" <?php echo ( 'UTF-16' === $mail_content_charset ) ? ' selected' : '' ?>>
								UTF-16
							</option>
							<option value="ISO-8859-1" <?php echo ( 'ISO-8859-1' === $mail_content_charset ) ? ' selected' : '' ?>>
								Latin 1
							</option>
							<option value="ASCII" <?php echo ( 'ASCII' === $mail_content_charset ) ? ' selected' : '' ?>>
								ASCII
							</option>
							<option value="ISO-2022-JP" <?php echo ( 'ISO-2022-JP' === $mail_content_charset ) ? ' selected' : '' ?>>
								JIS(ISO-2022-JP)
							</option>
						</select>
					</div>
				</div>
			</div>
		</div>

        <div class="card my-4">
            <div class="card-header">
				<div class="form-inline">
					<?php esc_html_e( 'Speed limit', 'bluff-post' ) ?>
				</div>
			</div>
			<div class="card-body">
				<div class="row form-group">
					<label for="transmission_speed_limit_count" class="col-sm-2 control-label"><?php esc_html_e( 'Continuous transmission count', 'bluff-post' ) ?></label>
					<div class="col-sm-4">
						<input name="transmission_speed_limit_count" type="text" id="transmission_speed_limit_count" value="<?php echo esc_attr( $transmission_speed_limit_count ) ?>"
						       class="form-control"
						       required
						       data-parsley-type="integer"
						       data-parsley-type-message="<?php esc_attr_e( 'Please enter a numeric continuous transmission count.', 'bluff-post' ) ?>"
						       data-parsley-required-message="<?php esc_attr_e( 'Please enter a continuous transmission count.', 'bluff-post' ) ?>"
						       placeholder="continuous count"/>
						<small> <?php esc_html_e( 'Specify the continuous transmission count. 0 is no limits.', 'bluff-post' ) ?></small>
					</div>
				</div>
				<div class="row form-group">
					<label for="transmission_speed_limit_time" class="col-sm-2 control-label"><?php esc_html_e( 'Transmission interval(sec)', 'bluff-post' ) ?></label>
					<div class="col-sm-4">
						<input name="transmission_speed_limit_time" type="text" id="transmission_speed_limit_time" value="<?php echo esc_attr( $transmission_speed_limit_time ) ?>"
						       class="form-control"
						       required
						       data-parsley-type="integer"
						       data-parsley-type-message="<?php esc_attr_e( 'Please enter a numeric transmission interval.', 'bluff-post' ) ?>"
						       data-parsley-required-message="<?php esc_attr_e( 'Please enter a transmission interval.', 'bluff-post' ) ?>"
						       placeholder="interval sec"/>
						<small> <?php esc_html_e( 'Specify the transmission interval. 0 is no interval.', 'bluff-post' ) ?></small>
					</div>
				</div>
			</div>
		</div>

        <div class="card my-4">
            <div class="card-header">
				<div class="form-inline">
					<?php esc_html_e( 'Appearance', 'bluff-post' ) ?>
				</div>
			</div>
			<div class="card-body" id="theme_name_panel">
				<div class="row form-group">
					<label for="theme_name" class="col-sm-2 control-label"><?php esc_html_e( 'Theme', 'bluff-post' ) ?></label>
					<div class="col-sm-4">
						<select name="theme_name" id="theme_name" class="form-control">
							<option value="default" <?php echo ( 'default' === $theme_name ) ? ' selected' : '' ?>>
								Default
							</option>
							<option value="blfpst_standard" <?php echo ( 'blfpst_standard' === $theme_name ) ? ' selected' : '' ?>>
								Standard
							</option>
						</select>
					</div>
				</div>
			</div>
		</div>

        <div class="card my-4">
            <div class="card-header">
				<div class="form-inline">
					<?php esc_html_e( 'Reservation', 'bluff-post' ) ?>
				</div>
			</div>
			<div class="card-body">
				<div class="row form-group">
					<label for="reserved_notification_address" class="col-sm-2 control-label"><?php esc_html_e( 'CRON', 'bluff-post' ) ?></label>

					<div class="col-sm-10">
                        <p><?php esc_html_e( 'Please set the following execution URL to CRON for the reservation process.', 'bluff-post' ) ?></p>
                        <div class="alert alert-secondary" role="alert"><?php echo esc_html( 'wget –q –O /dev/null http://YOUR_SITE_URL/wp-admin/admin-post.php?action=blfpst_cron' ) ?></div>
						<p><?php _e( 'cron setting example:', 'bluff-post' ) ?></p>
                        <div class="alert alert-secondary" role="alert">*/5 * * * * wget –q –O /dev/null http://www.example.com/wp-admin/admin-post.php?action=blfpst_cron</div>
					</div>
				</div>
				<!--
					<div class="form-group">
						<label for="reserved_notification_address" class="col-sm-2 control-label"><?php esc_html_e( 'Notification', 'bluff-post' ) ?></label>

						<div class="col-sm-10">
							<textarea name="reserved_notification_address" id="reserved_notification_address" class="form-control" rows="3" style="width:50%"><?php echo esc_textarea( $reserved_notification_address ) ?></textarea>
							<?php esc_html_e( 'Enter your email address If you want to notice at the time of booking transmission start.', 'bluff-post' ) ?>
						</div>
					</div>
					-->
			</div>
		</div>

		<p><input type="submit" value="<?php esc_attr_e( 'Saving changes', 'bluff-post' ) ?>"
		          class="button button-primary button-large"></p>
	</form>
</div>
