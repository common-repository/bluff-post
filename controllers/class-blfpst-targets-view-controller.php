<?php

/**
 * send target view controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Target_View_Controller {
	private $recipients_per_page = 20;
	private $target_per_page = 20;
	private $exclude_target_per_page = 20;

	/**
	 * initialize
	 *
	 * @return void
	 */
	public function initialize() {
	}

	/**
	 * routing index
	 *
	 * @return void
	 */
	public function index_routing() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		if ( isset( $_REQUEST['page'] ) && ( 'blfpst-targets' == $_REQUEST['page'] ) ) {
			$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		} else {
			$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		}

		switch ( $admin_action ) {
			case 'info': {
				$target_id = isset( $_REQUEST['target_id'] ) ? $_REQUEST['target_id'] : '';
				if ( ! empty( $target_id ) ) {
					$this->target_info_view( $target_id );
					return;
				}
			}
				break;

			case 'preview_sql':
				$this->preview_sql_view();
				return;

			case 'delete': {
				$target_id = isset( $_REQUEST['target_id'] ) ? $_REQUEST['target_id'] : '';

				if ( ! empty( $target_id ) ) {
					$this->target_delete_view( $target_id );
					return;
				}
			}
				break;

			case 'recipients': {
				$target_id = isset( $_REQUEST['target_id'] ) ? $_REQUEST['target_id'] : '';
				$page_num  = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;
				if ( ! empty( $target_id ) ) {
					$this->recipients_view( $target_id, $page_num );
					return;
				}
			}
				break;

			default:
				$this->target_list_view( '' );
		}
	}

	/**
	 * target list view
	 *
	 * @param string $message
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	private function target_list_view( $message = '', $errors = null ) {
		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$page_num = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;
		$targets  = BLFPST_Targets_Controller::load_targets( $page_num, $this->target_per_page );

		$total_count = BLFPST_Targets_Controller::execute_query_targets_count();
		$total_count = empty( $total_count ) ? 0 : $total_count;
		$total_page  = empty( $this->target_per_page ) ? 0 : ceil( $total_count / $this->target_per_page );

		BLFPST_Template_Loader::render( 'target/targets', array(
			'targets'    => $targets,
			'message'    => $message,
			'page_num'   => $page_num,
			'total_page' => $total_page,
			'errors'     => $errors,
		) );
	}

	/**
	 * target information
	 *
	 * @param integer $target_id target identifier
	 * @param WP_Error $errors error
	 *
	 * @return void
	 */
	public function target_info_view( $target_id, $errors = null ) {
		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$target = BLFPST_Targets_Controller::load_target_info( $target_id );
		if ( empty( $target ) ) {
			return;
		}

		BLFPST_Template_Loader::render( 'target/info', array( 'target' => $target, 'errors' => $errors ) );
	}

	/**
	 * target information
	 *
	 * @param integer $target_id target identifier
	 * @param integer $page_num page number
	 *
	 * @return void
	 */
	private function recipients_view( $target_id, $page_num ) {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$target = BLFPST_Targets_Controller::load_target_info( $target_id );
		if ( empty( $target ) ) {
			exit;
		}

		$target_results = BLFPST_Targets_Controller::execute_query_recipient( $target->id, $page_num, $this->recipients_per_page, $sql );

		$total_count = BLFPST_Targets_Controller::execute_query_recipients_count( $target_id );
		$total_count = ( false === $total_count ) ? 0 : $total_count;
		$total_page  = empty( $this->recipients_per_page ) ? 0 : ceil( $total_count / $this->recipients_per_page );

		BLFPST_Template_Loader::render( 'target/recipients',
			array(
				'target_id'       => $target_id,
				'target'          => $target,
				'recipient_count' => $total_count,
				'target_results'  => $target_results,
				'page_num'        => $page_num,
				'total_page'      => $total_page,
			)
		);
	}

	/**
	 * register target data
	 *
	 * @return void
	 */
	public function register_routing() {
		$errors = new WP_Error();

		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';

		switch ( $admin_action ) {

			case 'preview_sql':
				$this->preview_sql_view();
				return;

			case 'edit': {
				$target_id = isset( $_REQUEST['target_id'] ) ? $_REQUEST['target_id'] : '';
				if ( ! empty( $target_id ) ) {
					$this->target_edit_view( $target_id );
					return;
				}
			}
				break;

			case 'register':
				$this->target_register_view();
				return;
		}

		// default value
		$target             = new BLFPST_Model_Target();
		$target->class_name = BLFPST::get_option( 'target_database_name', '' );

		// table data
		$tables = BLFPST_Targets_Controller::default_table_data();
		if ( ! empty( $tables ) ) {

			BLFPST_Template_Loader::render( 'target/create',
				array(
					'tables'     => $tables,
					'jsonTables' => json_encode( $tables ),
					'target'     => $target,
					'sql'        => '',
					'errors'     => $errors,
				)
			);
		}
	}

	/**
	 * SQL preview
	 *
	 * @return void
	 */
	public function preview_sql_view() {

		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$result = BLFPST_Targets_Controller::targets_from_post();
		$target = $result['target'];
		$errors = $result['errors'];

		if ( ! empty( $target ) ) {
			$data_source = BLFPST_Targets_Controller::data_source_object( $target );
			$sql         = $data_source->recipient_sql( $target );

			if ( empty( $target->id ) ) {
				$tables = BLFPST_Targets_Controller::default_table_data();
			} else {
				$tables = BLFPST_Targets_Controller::table_data( $target );
			}

			if ( ! empty( $tables ) ) {
				BLFPST_Template_Loader::render( 'target/create',
					array(
						'tables'     => $tables,
						'jsonTables' => json_encode( $tables ),
						'target'     => $target,
						'sql'        => $sql,
						'errors'     => $errors,
					)
				);
			}
		}
	}

	/**
	 * register target data
	 *
	 * @return void
	 */
	private function target_register_view() {

		// error
		$errors = new WP_Error();

		$post_errors = isset( $_REQUEST['errors'] ) ? json_decode( $_REQUEST['errors'] ) : array();

		if ( ! empty( $post_errors ) && ! empty( $post_errors->errors ) ) {
			foreach ( $post_errors->errors->Error as $post_error ) {
				$errors->add( 'Error', $post_error );
			}
		}

		// target
		$target             = new BLFPST_Model_Target();
		$target->class_name = BLFPST::get_option( 'target_database_name', '' );

		$post_target = isset( $_REQUEST['targets'] ) ? json_decode( $_REQUEST['targets'] ) : array();

		if ( ! empty( $post_target ) ) {

			$target->id          = $post_target->id;
			$target->title       = $post_target->title;
			$target->class_name  = $post_target->class_name;
			$target->target_type = BLFPST_Model_Target::$target_type_user;

			foreach ( $post_target->target_conditionals as $post_target_group ) {

				$parent_conditional         = new BLFPST_Model_Target_Conditional();
				$parent_conditional->and_or = $post_target_group->and_or;

				foreach ( $post_target_group->target_conditionals as $target_parameter ) {

					$child_conditional               = new BLFPST_Model_Target_Conditional();
					$child_conditional->and_or       = $target_parameter->and_or;
					$child_conditional->compare      = $target_parameter->compare;
					$child_conditional->table_name   = $target_parameter->table_name;
					$child_conditional->column_name  = $target_parameter->column_name;
					$child_conditional->column_value = $target_parameter->column_value;

					array_push( $parent_conditional->target_conditionals, $child_conditional );
				}

				array_push( $target->target_conditionals, $parent_conditional );
			}
		}

		$data_source = BLFPST_Targets_Controller::data_source_object( $target );
		$sql         = $data_source->recipient_sql( $target );

		if ( empty( $target->id ) ) {
			$tables = BLFPST_Targets_Controller::default_table_data();
		} else {
			$tables = BLFPST_Targets_Controller::table_data( $target );
		}

		if ( ! empty( $tables ) ) {

			BLFPST_Template_Loader::render( 'target/create',
				array(
					'tables'     => $tables,
					'jsonTables' => json_encode( $tables ),
					'target'     => $target,
					'sql'        => $sql,
					'errors'     => $errors,
				)
			);
		}
	}

	/**
	 * edit target data
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return void
	 */
	private function target_edit_view( $target_id ) {
		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		if ( BLFPST_Targets_Controller::load_send_mail_count_with_target_id( $target_id ) > 0 ) {
			$errors = new WP_Error();
			$errors->add( 'Error', esc_html__( 'The e-mail in the reservation or send exists. This data can not be changed.', 'bluff-post' ) );

			$this->target_info_view( $target_id, $errors );
			return;
		}

		$target = BLFPST_Targets_Controller::load_target_info( $target_id );

		if ( ! empty( $target ) ) {

			$tables = BLFPST_Targets_Controller::table_data( $target );

			if ( ! empty( $tables ) ) {

				// View
				BLFPST_Template_Loader::render( 'target/create',
					array(
						'tables'     => $tables,
						'jsonTables' => json_encode( $tables ),
						'target'     => $target,
						'errors'     => array(),
					)
				);
			}
		}
	}

	/**
	 * delete target data
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return void
	 */
	private function target_delete_view( $target_id ) {
		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		if ( empty( $_POST ) || ! isset( $_POST['blfpst_target_option_delete'] ) || ! wp_verify_nonce( $_POST['blfpst_target_option_delete'], 'blfpst-target-option-delete' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		if ( BLFPST_Targets_Controller::load_send_mail_count_with_target_id( $target_id ) > 0 ) {
			$errors = new WP_Error();
			$errors->add( 'Error', esc_html__( 'The e-mail in the reservation or send exists. This data can not be deleted.', 'bluff-post' ) );

			$this->target_info_view( $target_id, $errors );
			return;
		}

		$message = __( 'Successfully deleted the target.', 'bluff-post' );
		BLFPST_Targets_Controller::delete_target( $target_id );
		$this->target_list_view( $message );
	}

	/**
	 * option page routing
	 *
	 * @return void
	 */
	public function option_routing() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$this->edit_option_view();
	}

	/**
	 * show option page
	 *
	 * @return void
	 */
	private function edit_option_view() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$message = isset( $_POST['message'] ) ? stripslashes( $_POST['message'] ) : '';
		$errors  = new WP_Error();
		if ( isset( $_POST['errors'] ) ) {

			$post_errors = json_decode( $_POST['errors'] );

			if ( ! empty( $post_errors->errors ) ) {
				foreach ( $post_errors->errors->Error as $post_error ) {
					$errors->add( 'Error', $post_error );
				}
			}
		}

		// Data Sources
		$data_sources = BLFPST_Targets_Controller::data_sources();

		// Options
		$target_database_name           = BLFPST::get_option( 'target_database_name', '' );
		$error_address                  = BLFPST::get_option( 'error_address', '' );
		$mailer_type                    = BLFPST::get_option( 'mailer_type', 'mail' );
		$sendmail_path                  = BLFPST::get_option( 'sendmail_path', '' );
		$smtp_host                      = BLFPST::get_option( 'smtp_host', '' );
        $smtp_port                      = BLFPST::get_option( 'smtp_port', 25 );
        $smtp_secure                    = BLFPST::get_option( 'smtp_secure', '' );
        $smtp_auth                      = BLFPST::get_option( 'smtp_auth', 'false' );
        $smtp_user_name                 = BLFPST::get_option( 'smtp_user_name', '' );
        $smtp_password                  = BLFPST::get_option( 'smtp_password', '' );
		$transmission_speed_limit_count = BLFPST::get_option( 'transmission_speed_limit_count', 0 );
		$transmission_speed_limit_time  = BLFPST::get_option( 'transmission_speed_limit_time', 0 );
		$blog_charset                   = get_bloginfo( 'charset' );
		$mail_content_charset           = BLFPST::get_option( 'mail_content_charset', $blog_charset );
		$theme_name                     = BLFPST::get_option( 'theme_name', '' );

		BLFPST_Template_Loader::render( 'options/option', array(
			'data_sources'                   => $data_sources,
			'target_database_name'           => $target_database_name,
			'error_address'                  => $error_address,
			'mailer_type'                    => $mailer_type,
			'sendmail_path'                  => $sendmail_path,
			'smtp_host'                      => $smtp_host,
            'smtp_port'                      => $smtp_port,
            'smtp_secure'                    => $smtp_secure,
            'smtp_auth'                      => $smtp_auth === 'true',
            'smtp_user_name'                 => $smtp_user_name,
            'smtp_password'                  => $smtp_password,
			'mail_content_charset'           => $mail_content_charset,
			'transmission_speed_limit_count' => $transmission_speed_limit_count,
			'transmission_speed_limit_time'  => $transmission_speed_limit_time,
			'theme_name'                     => $theme_name,
			'errors'                         => $errors,
			'message'                        => $message,
		) );
	}

	/**
	 * routing for exclude recipient view
	 *
	 * @return void
	 */
	public function exclude_recipient_routing() {
		$message = '';
		$errors  = new WP_Error();

		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';

		switch ( $admin_action ) {
			case 'register': {
				if ( ! empty( $_REQUEST ) && isset( $_REQUEST['blfpst_target_exclude_recipients'] ) && wp_verify_nonce( $_REQUEST['blfpst_target_exclude_recipients'], 'blfpst-target-exclude-recipients' ) ) {
					$mail_address = isset( $_REQUEST['exclude_address'] ) ? $_REQUEST['exclude_address'] : '';
					$mail_address = trim( $mail_address );

					if ( '' === $mail_address ) {
						$errors->add( 'Error', esc_html__( 'Please enter a e-mail address.', 'bluff-post' ) );
					} else if ( ! is_email( $mail_address ) ) {
						$errors->add( 'Error', esc_html__( 'The e-mail address you entered is incorrect.', 'bluff-post' ) );
					}

					if ( 255 < mb_strlen( $mail_address ) ) {
						$errors->add( 'Error', esc_html__( 'Please enter a recipients e-mail address 255 or less characters.', 'bluff-post' ) );
					}

					if ( 0 == count( $errors->get_error_messages() ) ) {
						BLFPST_Targets_Controller::register_exclude_recipients( $mail_address, get_current_user_id() );
						$message = esc_html__( 'E-mail address added.', 'bluff-post' );
					}
				}
			}
				break;

			case 'delete': {
				$exclude_recipient_id = isset( $_REQUEST['exclude_recipient_id'] ) ? $_REQUEST['exclude_recipient_id'] : '';

				if ( ! empty( $exclude_recipient_id ) ) {
					BLFPST_Targets_Controller::delete_exclude_recipients_with_id( $exclude_recipient_id );
					$message = esc_html__( 'E-mail address deleted.', 'bluff-post' );
				}
			}
				break;

			default:
				break;
		}

		$this->exclude_recipient_view( $message, $errors );
	}

	/**
	 * exclude recipient list view
	 *
	 * @param string $message
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	private function exclude_recipient_view( $message = null, $errors = null ) {
		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$page_num           = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;
		$exclude_recipients = BLFPST_Targets_Controller::execute_query_exclude_recipients( $page_num, $this->exclude_target_per_page );

		$total_count = BLFPST_Targets_Controller::execute_query_exclude_recipients_count();
		$total_count = empty( $total_count ) ? 0 : $total_count;
		$total_page  = empty( $this->exclude_target_per_page ) ? 0 : ceil( $total_count / $this->exclude_target_per_page );

		BLFPST_Template_Loader::render( 'target/exclude-recipients', array(
			'exclude_recipients' => $exclude_recipients,
			'page_num'           => $page_num,
			'total_page'         => $total_page,
			'message'            => $message,
			'errors'             => $errors,
		) );
	}

	/**
	 * print javascript at header
	 *
	 * @return void
	 */
	public function print_scripts_create_view() {

		wp_enqueue_script(
			'blfpst_target_create_js',
			BLFPST::plugin_url() . '/js/blfpst-target-create.js',
			array( 'jquery' )
		);

		// table data
		$tables      = BLFPST_Targets_Controller::default_table_data();
		$tables      = ( ! empty( $tables ) ) ? $tables : array();
		$json_tables = json_encode( $tables );

		?>
		<script type="text/javascript">
			/* <![CDATA[ */
			var tables = JSON.parse('<?php echo $json_tables; ?>');
			var table_string = '<?php esc_html_e( 'table', 'bluff-post' ) ?>';
			var field_string = '<?php esc_html_e( 'column', 'bluff-post' ) ?>';
			var value_string = '<?php esc_html_e( 'value', 'bluff-post' ) ?>';
			var group_string = '<?php esc_html_e( 'group', 'bluff-post' ) ?>';
			var receiver_count_string = '<?php esc_html_e( 'Recipients count', 'bluff-post' ) ?>';
			/* ]]> */
		</script>
		<?php
	}
}
