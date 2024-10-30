<?php

/**
 * Ajax class.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Ajax {

	public $ajax_actions;

	/*
	 * Configuring and initialize ajax files and actions
	 *
	 * @param  -
	 * @return -
	*/
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'include_scripts' ) );
		$this->configure_actions();
	}

	public function initialize() {
	}

	/*
	 * Configure the application specific AJAX actions array and
	 * load the AJAX actions bases on supplied parameters
	 *
	 * @param  -
	 * @return -
	*/
	public function configure_actions() {
		$this->ajax_actions = array(
			'request_calendar_view'      => array(
				'action'   => 'blfpst_calendar_view_action',
				'function' => 'request_calendar_view',
			),
			'request_recipient_count' => array(
				'action'   => 'blfpst_recipient_count_action',
				'function' => 'request_recipient_count',
			),
			'request_sending_status'     => array(
				'action'   => 'blfpst_sending_status_action',
				'function' => 'request_sending_status',
			),
			'request_mail_template'      => array(
				'action'   => 'blfpst_mail_template_action',
				'function' => 'request_mail_template',
			),
			'request_recipients_preview' => array(
				'action'   => 'blfpst_recipients_preview_action',
				'function' => 'request_recipients_preview',
			),
			'request_target_sql_preview' => array(
				'action'   => 'blfpst_target_sql_preview_action',
				'function' => 'request_target_sql_preview',
			),
			'request_send_test_mail' => array(
				'action'   => 'blfpst_send_test_mail_action',
				'function' => 'request_send_test_mail',
			),
		);

		/*
		 * Add the AJAX actions into WordPress
		 */
		foreach ( $this->ajax_actions as $custom_key => $custom_action ) {

			if ( isset( $custom_action['logged'] ) && $custom_action['logged'] ) {

				// Actions for users who are logged in
				add_action( 'wp_ajax_' . $custom_action['action'], array( $this, $custom_action['function'] ) );

			} else if ( isset( $custom_action['logged'] ) && ! $custom_action['logged'] ) {
				// Actions for users who are not logged in
				add_action( 'wp_ajax_nopriv_' . $custom_action['action'], array( $this, $custom_action['function'] ) );

			} else {
				// Actions for users who are logged in and not logged in
				add_action( 'wp_ajax_nopriv_' . $custom_action['action'], array( $this, $custom_action['function'] ) );
				add_action( 'wp_ajax_' . $custom_action['action'], array( $this, $custom_action['function'] ) );
			}
		}
	}

	public function include_scripts( $hook_suffix ) {

		//echo $hook_suffix;

		if ( ( preg_match( '/blfpst-send-mail$/', $hook_suffix ) )
		     || ( preg_match( '/blfpst-send-mail-crate$/', $hook_suffix ) )
		     || ( preg_match( '/blfpst-send-mail-histories$/', $hook_suffix ) )
		     || ( preg_match( '/blfpst-target-register$/', $hook_suffix ) )
		     || ( preg_match( '/blfpst-send-mail-sending$/', $hook_suffix ) )
		) {

			wp_enqueue_script( 'jquery' );

			wp_register_script( 'blfpst_ajax', BLFPST::plugin_url() . '/js/blfpst-ajax.js', array( 'jquery' ) );
			wp_enqueue_script( 'blfpst_ajax' );

			$nonce = wp_create_nonce( 'blfpst-ajax' );

			$config_array = array(
				'ajaxURL'     => admin_url( 'admin-ajax.php' ),
				'ajaxActions' => $this->ajax_actions,
				'ajaxNonce'   => $nonce,
			);

			wp_localize_script( 'blfpst_ajax', 'blfpst_conf', $config_array );
		}
	}

	public function request_calendar_view() {

		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		// 1.引数に指定された日にちをカレンダーの最初の週とする
		// 2.引数に指定された週数表示

		$week_count       = 5;
		$time_zone        = blfpst_get_wp_timezone();
		$current_datetime = new DateTime( 'now', $time_zone );

		// 表示開始週の決定
		$year  = empty( $_REQUEST['year'] ) ? $current_datetime->format( 'Y' ) : $_REQUEST['year'];
		$month = empty( $_REQUEST['month'] ) ? $current_datetime->format( 'm' ) : $_REQUEST['month'];
		$day   = empty( $_REQUEST['day'] ) ? $current_datetime->format( 'd' ) : $_REQUEST['day'];

		$start_datetime = new DateTime( "${year}-${month}-${day}", $time_zone );

		$response = BLFPST_Send_Mails_Controller::request_calendar_data( $start_datetime, $week_count );

		$return_object = array( 'data' => $response );

		echo json_encode( $return_object );

		exit;
	}

	public function request_recipient_count() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$target_id = empty( $_REQUEST['target_id'] ) ? 0 : $_REQUEST['target_id'];

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		$count = 0;

		if ( $target_id > 0 ) {
			$result = BLFPST_Targets_Controller::execute_query_recipients_count( $target_id );
			if ( false !== $result ) {
				$count = $result;
			}
		}

		$return_object = array( 'count' => $count );
		echo json_encode( $return_object );

		exit;
	}

	public function request_sending_status() {
		$recipient_count       = 0;
		$success_count         = 0;
		$failure_count         = 0;
		$send_result           = 'failure';
		$send_request_start_at = '';
		$send_request_end_at   = '';
		$updated_at            = '';
		$response_message      = '';

		$nonce        = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$send_mail_id = empty( $_REQUEST['send_mail_id'] ) ? 0 : $_REQUEST['send_mail_id'];

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			$response_message = 'error. unauthorized request.';

			$return_object = array(
				'send_result'           => $send_result,
				'recipient_count'       => $recipient_count,
				'success_count'         => $success_count,
				'failure_count'         => $failure_count,
				'send_request_start_at' => blfpst_localize_datetime_string( $send_request_start_at ),
				'send_request_end_at'   => blfpst_localize_datetime_string( $send_request_end_at ),
				'$updated_at'           => $updated_at,
				'response_message'      => $response_message,
			);

			echo json_encode( $return_object );

			die( 'Unauthorized request!' );
		}

		if ( 0 == $send_mail_id ) {
			$response_message = esc_html__( 'Invalid call has occurred.', 'bluff-post' );
		} else {

			$mail = BLFPST_Send_Mails_Controller::load_mail( $send_mail_id );

			if ( $mail->id > 0 ) {

				$send_result           = $mail->send_result;
				$send_request_start_at = $mail->send_request_start_at;
				$send_request_end_at   = $mail->send_request_end_at;
				$recipient_count       = $mail->recipient_count;
				$success_count         = $mail->success_count;
				$failure_count         = $mail->failure_count;
				$updated_at            = $mail->updated_at;
			} else {
				$response_message = esc_html__( 'A problem occurred while reading a send data.', 'bluff-post' );
			}
		}

		$return_object = array(
			'send_result'           => $send_result,
			'recipient_count'       => $recipient_count,
			'success_count'         => $success_count,
			'failure_count'         => $failure_count,
			'send_request_start_at' => blfpst_localize_datetime_string( $send_request_start_at ),
			'send_request_end_at'   => blfpst_localize_datetime_string( $send_request_end_at ),
			'$updated_at'           => $updated_at,
			'response_message'      => $response_message,
		);

		echo json_encode( $return_object );

		exit;
	}

	public function request_mail_template() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		$mail_template_id = empty( $_REQUEST['mail_template_id'] ) ? 0 : $_REQUEST['mail_template_id'];

		if ( empty( $mail_template_id ) ) {
			$mail_template = new BLFPST_Model_Template();
		} else {
			$mail_template = BLFPST_Mail_Templates_Controller::load_mail_template( $mail_template_id );
		}

		$return_object = array( 'mail_template' => $mail_template );
		echo json_encode( $return_object );

		exit;
	}

	public function request_recipients_preview() {
		$nonce                      = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$default_recipient_per_page = 20;

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		$page_num = isset( $_REQUEST['page_num'] ) ? $_REQUEST['page_num'] : 0;
		$limit    = isset( $_REQUEST['limit'] ) ? $_REQUEST['limit'] : $default_recipient_per_page;

		$recipients  = array();
		$target      = new BLFPST_Model_Target();
		$total_count = 0;

		$json = isset( $_REQUEST['json'] ) ? $_REQUEST['json'] : '';

		if ( ! empty( $json ) ) {
			$form = json_decode( stripslashes( $json ), true );

			$title       = empty( $form['title'] ) ? '' : $form['title'];
			$total_count = empty( $form['conditional_count'] ) ? 0 : $form['conditional_count'];

			$target->title = $title;
			$target->type  = 'builder';
			$target->count = $total_count;

			for ( $i = 0; $i < $total_count; $i ++ ) {

				$conditional_count = empty( $form[ 'conditional_count' . $i ] ) ? 0 : $form[ 'conditional_count' . $i ];
				$group_and_or      = empty( $form[ 'and_or' . $i ] ) ? '' : $form[ 'and_or' . $i ];

				$parent_conditional              = new BLFPST_Model_Target_Conditional();
				$parent_conditional->order_index = $i;
				$parent_conditional->and_or      = $group_and_or;
				array_push( $target->target_conditionals, $parent_conditional );

				for ( $j = 0; $j < $conditional_count; $j ++ ) {
					$key          = $i . '-' . $j;
					$child_and_or = empty( $form[ 'and_or' . $key ] ) ? '' : $form[ 'and_or' . $key ];
					$table_name   = empty( $form[ 'table_name' . $key ] ) ? '' : $form[ 'table_name' . $key ];
					$column_name  = empty( $form[ 'column_name' . $key ] ) ? '' : $form[ 'column_name' . $key ];
					$compare      = empty( $form[ 'compare' . $key ] ) ? '' : $form[ 'compare' . $key ];
					$column_value = empty( $form[ 'column_value' . $key ] ) ? '' : $form[ 'column_value' . $key ];

					$child_conditional               = new BLFPST_Model_Target_Conditional();
					$child_conditional->order_index  = $j;
					$child_conditional->and_or       = $child_and_or;
					$child_conditional->table_name   = $table_name;
					$child_conditional->column_name  = $column_name;
					$child_conditional->compare      = $compare;
					$child_conditional->column_value = stripslashes( $column_value );
					array_push( $parent_conditional->target_conditionals, $child_conditional );
				}
			}

			if ( ! empty( $target ) ) {

				/** @var wpdb $wpdb */
				global $wpdb;

				$target->class_name = ! empty( $form['class_name'] ) ? $form['class_name'] : BLFPST::get_option( 'target_database_name', '' );
				$class = 'BLFPST_Data_Source_' . $target->class_name;

				/** @var BLFPST_Abstract_Data_Source $data_source */
				$data_source = new $class();
				$sql         = $data_source->recipient_sql( $target );

				if ( $page_num >= 0 ) {
					$sql .= $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
				}

				if ( ! empty( $sql ) ) {
					$results = $wpdb->get_results( $sql );

					foreach ( $results as $result ) {
						$recipient = array(
							'recipient_id' => $result->{$data_source->id_field_name()},
							'email'        => $result->{$data_source->email_field_name()},
						);
						array_push( $recipients, $recipient );
					}
				} else {
					error_log( 'MySQL Error: ' . $wpdb->last_error . ' in BLFPST_Ajax::request_recipients_preview.' );
					$recipients = false;
				}

				$sql = $data_source->recipient_count_sql( $target );

				if ( ! empty( $sql ) ) {
					$total_count = $wpdb->get_var( $sql );
				}
			} else {
				$recipients = array();
			}
		}

		$total_page = ceil( $total_count / $limit );

		$return_object = array(
			'recipients'  => $recipients,
			'page_num'    => $page_num,
			'limit'       => $limit,
			'total_count' => $total_count,
			'total_page'  => $total_page,
		);
		echo json_encode( $return_object );

		exit;
	}

	public function request_target_sql_preview() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		$json  = isset( $_REQUEST['json'] ) ? stripslashes( $_REQUEST['json'] ) : '';

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		$result = 'failure';
		$sql    = '';

		if ( ! empty( $json ) ) {
			$form = json_decode( $json );
			foreach ( $form as $key => $value ) {
				$_POST[ $key ] = $value;
			}

			$result = BLFPST_Targets_Controller::targets_from_post();
			$target = $result['target'];
			$result = 'success';

			if ( ! empty( $target ) ) {
				$data_source = BLFPST_Targets_Controller::data_source_object( $target );
				$sql         = $data_source->recipient_sql( $target );
			}
		}

		$return_object = array(
			'sql'    => $sql,
			'result' => $result,
		);
		echo json_encode( $return_object );
		exit;
	}

	public function request_send_test_mail() {

		$errors = new WP_Error();
		$nonce  = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		$json  = isset( $_REQUEST['json'] ) ? stripslashes( $_REQUEST['json'] ) : '';
		$json = str_replace( "\r", "\\r", $json );
		$json = str_replace( "\n", "\\n", $json );

		if ( ! empty( $json ) ) {
			$form = json_decode( $json );
			foreach ( $form as $key => $value ) {
				$_POST[ $key ] = $value;
			}

			$send_mail                = new BLFPST_Model_Send_Mail();
			$send_mail->target_id     = isset( $form->{'target_id'} ) ? $form->{'target_id'} : '';
			$send_mail->from_name     = isset( $form->{'from_name'} ) ? $form->{'from_name'} : '';
			$send_mail->from_address  = isset( $form->{'from_address'} ) ? $form->{'from_address'} : '';
			$send_mail->reply_address = isset( $form->{'reply_address'} ) ? $form->{'reply_address'} : '';
			$send_mail->subject       = isset( $form->{'subject'} ) ? $form->{'subject'} : '';
			$send_mail->text_content  = isset( $form->{'text_content'} ) ? $form->{'text_content'} : '';
			$send_mail->html_content  = isset( $form->{'htmlcontent'} ) ? $form->{'htmlcontent'} : '';
			$test_targets             = isset( $form->{'test_targets'} ) ? $form->{'test_targets'} : '';

			$errors = BLFPST_Send_Mails_Controller::test_send( $send_mail, $test_targets );
		}

		$error_messages = array();
		foreach ( $errors->get_error_messages() as $message ) {
			array_push( $error_messages, $message );
		}

		$return_object = array(
			'errors' => $error_messages,
		);

		echo json_encode( $return_object );
		exit;
	}
}
