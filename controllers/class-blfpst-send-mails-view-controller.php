<?php

/**
 * mail send view controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Send_Mails_View_Controller {
	private $items_per_page = 20;

	public function initialize() {
	}

	/**
	 *
	 * index
	 *
	 * @return void
	 */
	public function index_routing() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : 0;

		switch ( $admin_action ) {
			case 'conf':
				$this->conf_view();
				return;

			case 'save':
				$this->save_view();
				return;

			case 'test':
				$this->test_send_view();
				return;

			case 'start_send':
				$this->start_send_view();
				return;

			case 'registered':
				$this->registered_view(); // for Reservation
				return;

			case 'edit_draft':
				if ( ! empty( $send_mail_id ) ) {
					$this->edit_draft_view( $send_mail_id );
					return;
				}
				break;

			case 'duplicate':
				if ( ! empty( $send_mail_id ) ) {
					$this->duplicate_view( $send_mail_id );
					return;
				}
				break;

			case 'edit_reserved':
				if ( ! empty( $send_mail_id ) ) {
					$this->edit_reserved_view( $send_mail_id );
					return;
				}
				break;

			case 'recycle':
				if ( ! empty( $send_mail_id ) ) {
					$this->recycle_trash_view( $send_mail_id );
					return;
				}
				break;
		}

		$this->calendar_view();
	}

	/**
	 *
	 * route
	 *
	 * @return void
	 */
	public function create_routing() {
		$errors = new WP_Error();

		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : 0;
		$create_code  = isset( $_REQUEST['create_code'] ) ? $_REQUEST['create_code'] : 0;
		$create_page  = isset( $_REQUEST['create_page'] ) ? $_REQUEST['create_page'] : 1;

		switch ( $admin_action ) {
			case 'conf':
				$this->conf_view();
				return;

			case 'save':
				$this->save_view();
				return;

			case 'test':
				$this->test_send_view();
				return;

			case 'registered':
				$this->registered_view();
				return;

			case 'start_send':
				$this->start_send_view();
				return;

			case 'edit_draft':
				if ( ! empty( $send_mail_id ) ) {
					$this->edit_draft_view( $send_mail_id );
					return;
				}
				break;

			case 'duplicate':
				if ( ! empty( $send_mail_id ) ) {
					$this->duplicate_view( $send_mail_id );
					return;
				}
				break;

			case 'edit_reserved':
				if ( ! empty( $send_mail_id ) ) {
					$this->edit_reserved_view( $send_mail_id );
					return;
				}
				break;

			case 'recycle':
				if ( ! empty( $send_mail_id ) ) {
					$this->recycle_trash_view( $send_mail_id );
					return;
				}
				break;
		}

		// error page
		$post_errors = isset( $_POST['errors'] ) ? $_POST['errors'] : '';
		/** @var WP_Error $post_errors */

		if ( ! empty( $post_errors ) && ( count( $post_errors->errors ) > 0 ) ) {

			$error_codes = $post_errors->get_error_codes();
			foreach ( $error_codes as $code ) {
				$message = $post_errors->get_error_message( $code );
				$errors->add( $code, $message );
			}

			BLFPST_Template_Loader::render( 'common/error', array(
				'errors' => $errors,
			) );
		} else {
			$this->render_create_view( 0, $errors, null, $create_code, $create_page );
		}
	}

	/**
	 * render mail create page
	 *
	 * @param int $send_mail_id
	 * @param WP_Error $errors
	 * @param BLFPST_Model_Send_Mail $send_mail
	 * @param string $create_code
	 * @param integer $create_page
	 *
	 * @return void
	 */
	private function render_create_view( $send_mail_id, $errors, $send_mail = null, $create_code = '0', $create_page = 1 ) {
		if ( empty( $create_code ) ) {
			$create_code = BLFPST_Send_Mails_Controller::create_create_code();
		}

		// Load templates
		$mail_templates = BLFPST_Mail_Templates_Controller::load_mail_templates();

		$new_mail_templates = array();

		// only title & id
		foreach ( $mail_templates as &$mail_template ) {
			$new_mail_template        = new BLFPST_Model_Template();
			$new_mail_template->id    = $mail_template->id;
			$new_mail_template->title = $mail_template->title;
			array_push( $new_mail_templates, $new_mail_template );
		}

		$targets    = BLFPST_Targets_Controller::load_targets_with_recipient_count();
		$mail_froms = BLFPST_Send_Mails_Controller::load_mail_froms();

		if ( empty( $target_id ) ) {
			$target = null;
		} else {
			$target = BLFPST_Targets_Controller::load_target_info( $target_id );
		}
		$data_source  = BLFPST_Targets_Controller::data_source_object( $target );
		$test_targets = BLFPST_Send_Mails_Controller::load_test_targets();

		BLFPST_Template_Loader::render( 'send/create', array(
			'create_code'               => $create_code,
			'send_mail_id'              => $send_mail_id,
			'send_mail'                 => $send_mail,
			'mail_templates'            => $new_mail_templates,
			'targets'                   => $targets,
			'mail_froms'                => $mail_froms,
			'test_targets'              => $test_targets,
			'insertion_description'     => $data_source->insertion_description(),
			'mail_tracking_description' => $data_source->mail_tracking_description(),
			'create_page'               => $create_page,
			'errors'                    => $errors,
		) );
	}

	/**
	 * confirm page
	 *
	 * @return void
	 */
	private function conf_view() {
		$errors = new WP_Error();

		if ( empty( $_POST ) || ! isset( $_POST['blfpst_send_mail_conf'] ) || ! wp_verify_nonce( $_POST['blfpst_send_mail_conf'], 'blfpst-send-mail-conf' ) ) {
			$this->render_create_view( 0, $errors );
			return;
		}

		$send_mail_id  = isset( $_POST['send_mail_id'] ) ? $_POST['send_mail_id'] : 0;
		$target_id     = isset( $_POST['target_id'] ) ? $_POST['target_id'] : '';
		$send_type     = isset( $_POST['send_type'] ) ? $_POST['send_type'] : '';
		$content_type  = isset( $_POST['content_type'] ) ? $_POST['content_type'] : 'content_type_html';
		$reserved_at   = isset( $_POST['reserved_at'] ) ? $_POST['reserved_at'] : '';
		$from_name     = isset( $_POST['from_name'] ) ? stripslashes( $_POST['from_name'] ) : '';
		$from_address  = isset( $_POST['from_address'] ) ? stripslashes( $_POST['from_address'] ) : '';
		$reply_address = isset( $_POST['reply_address'] ) ? stripslashes( $_POST['reply_address'] ) : '';
		$subject       = isset( $_POST['subject'] ) ? stripslashes( $_POST['subject'] ) : '';
		$text_content  = isset( $_POST['text_content'] ) ? stripslashes( $_POST['text_content'] ) : '';
		$html_content  = isset( $_POST['htmlcontent'] ) ? stripslashes( $_POST['htmlcontent'] ) : '';
		$create_page   = isset( $_POST['create_page'] ) ? $_POST['create_page'] : 1;
		$create_code   = isset( $_POST['create_code'] ) ? $_POST['create_code'] : '';

		if ( empty( $create_code ) ) {
			$errors->add( 'Error', esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		if ( $this->check_exist_create_code( $create_code, false ) ) {
			return;
		}

		// target
		if ( empty( $target_id ) ) {
			$errors->add( 'Error', esc_html__( 'Please select a recipients.', 'bluff-post' ) );
		}

		// content type
		if ( 'content_type_text' === $content_type ) {
			$html_content = '';
		}

		// validation
		BLFPST_Send_Mails_Controller::validate_mail_data( $_POST, $errors );

		if ( '____/__/__ __:__' === $reserved_at ) {
			$reserved_at = '';
		}

		// reservation
		if ( 'reserved' !== $send_type ) {
			$reserved_at = '';
		}

		$preview_content = str_replace( '<', '&lt;', $html_content );
		$preview_content = str_replace( '>', '&gt;', $preview_content );
		$preview_content = str_replace( '"', '&quot;', $preview_content );
		$preview_content = str_replace( '\'', '&#39;', $preview_content );

		if ( count( $errors->errors ) == 0 ) {

			// load target
			$target = BLFPST_Targets_Controller::load_target( $target_id );

			BLFPST_Template_Loader::render( 'send/conf', array(
				'create_code'     => $create_code,
				'send_mail_id'    => $send_mail_id,
				'target_id'       => $target_id,
				'target'          => $target,
				'reserved_at'     => $reserved_at,
				'from_name'       => $from_name,
				'from_address'    => $from_address,
				'reply_address'   => $reply_address,
				'subject'         => $subject,
				'content_type'    => $content_type,
				'text_content'    => $text_content,
				'html_content'    => $html_content,
				'preview_content' => $preview_content,
				'create_page'     => $create_page,
				'errors'          => $errors,
			) );

			return;
		}

		$send_mail                = new BLFPST_Model_Send_Mail();
		$send_mail->id            = $send_mail_id;
		$send_mail->target_id     = $target_id;
		$send_mail->send_type     = $send_type;
		$send_mail->content_type  = $content_type;
		$send_mail->reserved_at   = $reserved_at;
		$send_mail->from_name     = $from_name;
		$send_mail->from_address  = $from_address;
		$send_mail->reply_address = $reply_address;
		$send_mail->subject       = $subject;
		$send_mail->text_content  = $text_content;
		$send_mail->html_content  = $html_content;
		$send_mail->create_code   = $create_code;

		$this->render_create_view( $send_mail_id, $errors, $send_mail, $create_code, $create_page );
	}


	/**
	 * register page
	 *
	 * @return void
	 */
	public function register_view() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		if ( empty( $_POST ) || ! isset( $_POST['blfpst_send_mail_register'] ) || ! wp_verify_nonce( $_POST['blfpst_send_mail_register'], 'blfpst-send-mail-register' ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		$errors = new WP_Error();

		$send_mail_id  = isset( $_POST['send_mail_id'] ) ? $_POST['send_mail_id'] : 0;
		$target_id     = isset( $_POST['target_id'] ) ? $_POST['target_id'] : '';
		$reserved_at   = isset( $_POST['reserved_at'] ) ? $_POST['reserved_at'] : '';
		$from_name     = isset( $_POST['from_name'] ) ? stripslashes( $_POST['from_name'] ) : '';
		$from_address  = isset( $_POST['from_address'] ) ? stripslashes( $_POST['from_address'] ) : '';
		$reply_address = isset( $_POST['reply_address'] ) ? stripslashes( $_POST['reply_address'] ) : '';
		$subject       = isset( $_POST['subject'] ) ? stripslashes( $_POST['subject'] ) : '';
		$text_content  = isset( $_POST['text_content'] ) ? stripslashes( $_POST['text_content'] ) : '';
		$html_content  = isset( $_POST['htmlcontent'] ) ? stripslashes( $_POST['htmlcontent'] ) : '';
		$create_page   = isset( $_POST['create_page'] ) ? $_POST['create_page'] : 1;
		$create_code   = isset( $_POST['create_code'] ) ? $_POST['create_code'] : '';

		if ( empty( $create_code ) ) {
			wp_safe_redirect( home_url() );
			exit;
		}

		if ( $this->check_exist_create_code( $create_code, true ) ) {
			$mail = BLFPST_Send_Mails_Controller::load_mail( $send_mail_id );

			if ( $mail->is_sending() ) {
				wp_safe_redirect( admin_url( 'admin.php?page=blfpst-send-mail-sending&admin_action=info&send_mail_id=' . $send_mail_id ) );
			} else {
				wp_safe_redirect( admin_url( 'admin.php?page=blfpst-send-mail-histories&admin_action=info&send_mail_id=' . $send_mail_id ) );
			}
			exit;
		}

		// validation
		if ( empty( $target_id ) ) {
			$errors->add( 'Error', esc_html__( 'Invalid access.', 'bluff-post' ) );
			goto ERROR;
		}

		BLFPST_Send_Mails_Controller::validate_mail_data( $_POST, $errors );

		if ( count( $errors->errors ) > 0 ) {
			goto ERROR;
		}

		$status = empty( $reserved_at ) ? 'send' : 'reserved';

		$target      = BLFPST_Targets_Controller::load_target( $target_id );
		$target_name = ( ! empty( $target ) && ( '' !== $target->title ) ) ? $target->title : '';

		// Save mail from
		if ( ! BLFPST_Send_Mails_Controller::is_exists_mail_from( $from_name, $from_address, $reply_address ) ) {
			BLFPST_Send_Mails_Controller::register_mail_from( $from_name, $from_address, $reply_address, get_current_user_id() );
		}

		// add WordPress custom post
		$post_id = 0;
		$content = empty( $html_content ) ? $text_content : $html_content;

		if ( 0 < $create_page ) {
			$create_page_status = 'draft';
			if ( 2 == $create_page ) {
				$create_page_status = 'publish';
			}

			$post_id = BLFPST_Custom_Post_Manager::add_mail_page( get_current_user_id(), 0, $create_page_status, $subject, $content );
			$post_id = is_wp_error( $post_id ) ? 0 : $post_id;
		}

		$send_mail                  = new BLFPST_Model_Send_Mail();
		$send_mail->id              = $send_mail_id;
		$send_mail->post_id         = $post_id;
		$send_mail->user_id         = get_current_user_id();
		$send_mail->target_id       = $target_id;
		$send_mail->target_name     = $target_name;
		$send_mail->status          = $status;
		$send_mail->subject         = $subject;
		$send_mail->text_content    = $text_content;
		$send_mail->html_content    = $html_content;
		$send_mail->from_name       = $from_name;
		$send_mail->from_address    = $from_address;
		$send_mail->reply_address   = $reply_address;
		$send_mail->create_code     = $create_code;
		$send_mail->send_result     = 'wait';
		$send_mail->recipient_count = 0;
		$send_mail->success_count   = 0;
		$send_mail->failure_count   = 0;
		$send_mail->reserved_at     = $reserved_at;

		$result = BLFPST_Send_Mails_Controller::save( $send_mail );

		$send_mail_id = $send_mail->id;
		$is_html_mail = ! $send_mail->is_text_content_only();

		BLFPST_Custom_Post_Manager::update_page_name( $post_id, $send_mail_id );
		BLFPST_Custom_Post_Manager::update_page_meta( $post_id, $send_mail_id, $is_html_mail );

		if ( ! $result ) {
			$errors->add( 'Error', esc_html__( 'Error occurs in DB access. Send e-mail does not start.', 'bluff-post' ) );
		} else {
			if ( $send_mail->is_reserved() ) {
				wp_safe_redirect( admin_url( 'admin.php?page=blfpst-send-mail&admin_action=registered&reserved_at=' . $reserved_at ) );
			} else {
				$nonce = wp_create_nonce( 'blfpst-send-mail-start-send' );
				wp_safe_redirect( admin_url( 'admin.php?page=blfpst-send-mail&admin_action=start_send&send_mail_id=' . $send_mail->id . '&blfpst_send_mail_start_send=' . $nonce ) );
			}
			exit;
		}

		ERROR: // require PHP5.3
		$_POST['errors'] = $errors;
	}

	/**
	 * finish register page
	 *
	 * @return void
	 */
	private function registered_view() {
		$reserved_at = isset( $_GET['reserved_at'] ) ? $_GET['reserved_at'] : '';

		if ( empty( $reserved_at ) ) {
			$title = esc_html__( 'E-Mail delivery completion.', 'bluff-post' );
			$message = esc_html__( 'We have received a transmission processing of mail.', 'bluff-post' );

		} else {
			$title = esc_html__( 'Reservation completion.', 'bluff-post' );
			$message = esc_html__( 'Was the e-mail of the reservation.', 'bluff-post' );
		}

		BLFPST_Template_Loader::render( 'send/register', array(
			'title' => $title,
			'message' => $message,
		) );
	}

	/**
	 * start mail send process
	 *
	 * @return void
	 */
	private function start_send_view() {
		$send_mail_id = isset( $_GET['send_mail_id'] ) ? $_GET['send_mail_id'] : '';

		if ( empty( $_GET ) || ! isset( $_GET['blfpst_send_mail_start_send'] ) || ! wp_verify_nonce( $_GET['blfpst_send_mail_start_send'], 'blfpst-send-mail-start-send' ) ) {
			wp_redirect( home_url() );
			exit;
		}

		if ( empty( $send_mail_id ) ) {
			wp_redirect( home_url() );
			exit;
		}

		// ignore session disconnect.
		ignore_user_abort( true );
		set_time_limit( 0 );

		// load send_mail
		$mail = BLFPST_Send_Mails_Controller::load_mail( $send_mail_id );

		// load target count
		$recipient_count = BLFPST_Targets_Controller::execute_query_recipients_count( $mail->target_id );
		$recipient_count = ( false === $recipient_count ) ? 0 : $recipient_count;

		BLFPST_Template_Loader::render( 'send/send-start', array(
			'mail'            => $mail,
			'recipient_count' => $recipient_count,
		) );

		BLFPST_Send_Mail::start_send( null, $send_mail_id, get_current_user_id() );
	}

	/**
	 * save draft
	 *
	 * 重複チェックのためにcreate_codeはここで保存しないこと
	 * 「保存」実行時はcreate_codeを保存せず register()のupdate時にcreate_codeを保存すること
	 *
	 * @return void
	 */
	private function save_view() {
		$errors = new WP_Error();

		$send_mail_id  = isset( $_POST['send_mail_id'] ) ? $_POST['send_mail_id'] : 0;
		$target_id     = isset( $_POST['target_id'] ) ? $_POST['target_id'] : '';
		$send_type     = isset( $_POST['send_type'] ) ? $_POST['send_type'] : '';
		$content_type  = isset( $_POST['content_type'] ) ? $_POST['content_type'] : 'content_type_html';
		$reserved_at   = isset( $_POST['reserved_at'] ) ? $_POST['reserved_at'] : '';
		$from_name     = isset( $_POST['from_name'] ) ? stripslashes( $_POST['from_name'] ) : '';
		$from_address  = isset( $_POST['from_address'] ) ? stripslashes( $_POST['from_address'] ) : '';
		$reply_address = isset( $_POST['reply_address'] ) ? stripslashes( $_POST['reply_address'] ) : '';
		$subject       = isset( $_POST['subject'] ) ? stripslashes( $_POST['subject'] ) : '';
		$text_content  = isset( $_POST['text_content'] ) ? stripslashes( $_POST['text_content'] ) : '';
		$html_content  = isset( $_POST['htmlcontent'] ) ? stripslashes( $_POST['htmlcontent'] ) : '';
		$create_page   = isset( $_POST['create_page'] ) ? $_POST['create_page'] : 1;
		$create_code   = isset( $_REQUEST['create_code'] ) ? stripslashes( $_REQUEST['create_code'] ) : 0;
		$time_zone     = blfpst_get_wp_timezone();

		// content type
		if ( 'content_type_text' === $content_type ) {
			$html_content         = '';
		}

		if ( mb_strlen( $from_name ) > 255 ) {
			$from_name = '';
		}

		if ( mb_strlen( $from_address ) > 255 ) {
			$from_address = '';
		}

		if ( mb_strlen( $reply_address ) > 255 ) {
			$reply_address = '';
		}

		if ( mb_strlen( $subject ) > 255 ) {
			$subject = '';
		}

		if ( 'reserved' === $send_type ) {

			if ( '' !== $reserved_at ) {
				$reserved_datetime = DateTime::createFromFormat( 'Y/m/d H:i', $reserved_at );
				if ( ! $reserved_datetime ) {
					$reserved_at = '';
				} else {
					$today = new DateTime( 'now', $time_zone );
					if ( $today > $reserved_datetime ) {
						$errors->add( 'Error', esc_html__( 'Past has been specified in the transmission reservation date.', 'bluff-post' ) );
					}
				}
			}
		} else {
			$reserved_at = '';
		}

		// target title
		if ( 0 < $target_id ) {
			$target      = BLFPST_Targets_Controller::load_target( $target_id );
			$target_name = ( ! empty( $target ) && ( '' !== $target->title ) ) ? $target->title : '';
		} else {
			$target_name = '';
		}

		// draft
		$status = 'draft';

		$send_mail                = new BLFPST_Model_Send_Mail();
		$send_mail->id            = $send_mail_id;
		$send_mail->user_id       = get_current_user_id();
		$send_mail->send_type     = $send_type;
		$send_mail->content_type  = $content_type;
		$send_mail->reserved_at   = $reserved_at;
		$send_mail->target_id     = $target_id;
		$send_mail->target_name   = $target_name;
		$send_mail->status        = $status;
		$send_mail->subject       = $subject;
		$send_mail->text_content  = $text_content;
		$send_mail->html_content  = $html_content;
		$send_mail->from_name     = $from_name;
		$send_mail->from_address  = $from_address;
		$send_mail->reply_address = $reply_address;
		$send_mail->updated_at    = current_time( 'mysql', 0 );
		$send_mail->created_at    = current_time( 'mysql', 0 );

		$result = BLFPST_Send_Mails_Controller::save( $send_mail );

		if ( ! $result ) {
			$errors->add( 'Error', esc_html__( 'An error occurred in the DB storage process.', 'bluff-post' ) );
		}

		$send_mail_id = $send_mail->id;

		$this->render_create_view( $send_mail_id, $errors, $send_mail, $create_code, $create_page );
	}

	/**
	 * send test mail
	 *
	 * @return WP_Error
	 */
	private function test_send_view() {
		$errors = new WP_Error();

		$send_mail_id  = isset( $_POST['send_mail_id'] ) ? $_POST['send_mail_id'] : 0;
		$target_id     = isset( $_POST['target_id'] ) ? $_POST['target_id'] : 0;
		$send_type     = isset( $_POST['send_type'] ) ? $_POST['send_type'] : '';
		$content_type  = isset( $_POST['content_type'] ) ? $_POST['content_type'] : 'content_type_html';
		$reserved_at   = isset( $_POST['reserved_at'] ) ? $_POST['reserved_at'] : '';
		$from_name     = isset( $_POST['from_name'] ) ? stripslashes( $_POST['from_name'] ) : '';
		$from_address  = isset( $_POST['from_address'] ) ? stripslashes( $_POST['from_address'] ) : '';
		$reply_address = isset( $_POST['reply_address'] ) ? stripslashes( $_POST['reply_address'] ) : '';
		$subject       = isset( $_POST['subject'] ) ? stripslashes( $_POST['subject'] ) : '';
		$text_content  = isset( $_POST['text_content'] ) ? stripslashes( $_POST['text_content'] ) : '';
		$html_content  = isset( $_POST['htmlcontent'] ) ? stripslashes( $_POST['htmlcontent'] ) : '';
		$test_targets  = isset( $_POST['test_targets'] ) ? $_POST['test_targets'] : array();
		$create_page   = isset( $_POST['create_page'] ) ? $_POST['create_page'] : 1;
		$create_code   = isset( $_REQUEST['create_code'] ) ? $_REQUEST['create_code'] : 0;

		BLFPST_Send_Mails_Controller::validate_mail_data( $_POST, $errors, false );

		$send_mail                = new BLFPST_Model_Send_Mail();
		$send_mail->id            = $send_mail_id;
		$send_mail->send_type     = $send_type;
		$send_mail->content_type  = $content_type;
		$send_mail->reserved_at   = $reserved_at;
		$send_mail->target_id     = $target_id;
		$send_mail->subject       = $subject;
		$send_mail->text_content  = $text_content;
		$send_mail->html_content  = $html_content;
		$send_mail->from_name     = $from_name;
		$send_mail->from_address  = $from_address;
		$send_mail->reply_address = $reply_address;

		if ( empty( $test_targets ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a test mail receiver.', 'bluff-post' ) );
		}

		if ( 0 == count( $errors->errors ) ) {

			// content type
			if ( 'content_type_text' === $content_type ) {
				$html_content         = '';
			}

			if ( empty( $target_id ) ) {
				$target = null;
			} else {
				$target = BLFPST_Targets_Controller::load_target_info( $target_id );
			}
			$data_source = BLFPST_Targets_Controller::data_source_object( $target );

			$test_targets_array = preg_split( '/\r\n|\r|\n/', $test_targets, - 1, PREG_SPLIT_NO_EMPTY );
			$test_targets_array = array_map( 'trim', $test_targets_array );
			$test_targets_array = array_filter( $test_targets_array, 'strlen' ); // empty line

			$recipients = $new_array = array();
			foreach ( $test_targets_array as $test_target ) {
				if ( ! empty( $test_target ) ) {

					$params = explode( ';', $test_target );

					if ( 0 == count( $params ) ) {
						continue;
					}

					$mail_address    = $params[0];
					$user_id         = 0;
					$user_name       = '';
					$user_last_name  = '';
					$user_first_name = '';

					for ( $i = 1; $i < count( $params ); $i ++ ) {
						$insertion = explode( ':', $params[ $i ] );
						if ( 1 < count( $insertion ) ) {
							$key   = trim( $insertion[0] );
							$value = trim( $insertion[1] );

							if ( '%%user_id%%' === $key ) {
								$user_id = trim( $value );
							} elseif ( '%%user_name%%' === $key ) {
								$user_name = trim( $value );
							} elseif ( '%%user_last_name%%' === $key ) {
								$user_last_name = trim( $value );
							} elseif ( '%%user_first_name%%' === $key ) {
								$user_first_name = trim( $value );
							}
						}
					}

					$recipient                                     = (object) array();
					$recipient->{$data_source->id_field_name()}    = $user_id;
					$recipient->{$data_source->email_field_name()} = $mail_address;

					// set insertion data
					$recipient->{'user_name'} = $user_name;

					if ( $data_source->user_last_name_field_name() !== '' ) {
						$recipient->{$data_source->user_last_name_field_name()} = $user_last_name;
					}

					if ( $data_source->user_first_name_field_name() !== '' ) {
						$recipient->{$data_source->user_first_name_field_name()} = $user_first_name;
					}

					array_push( $recipients, $recipient );
				}
			}

			$results = BLFPST_Send_Mails_Controller::send_mail( $recipients, $target_id, 0, $subject, $text_content, $html_content, $from_name, $from_address, $reply_address, 0 );

			if ( 'failure' === $results['result'] ) {
				$errors->add( 'Error', esc_html__( 'Failed to send processing of test mail.', 'bluff-post' ) );
			}

			BLFPST_Send_Mails_Controller::save_test_targets( $test_targets );
		}

		$this->render_create_view( $send_mail_id, $errors, $send_mail, $create_code, $create_page );
	}

	/**
	 * calendar page
	 *
	 * @return void
	 */
	private function calendar_view() {
		$errors = new WP_Error();

		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';

		switch ( $admin_action ) {
			case 'info':
				return;
		}

		// localize
		$time_zone    = blfpst_get_wp_timezone();
		$current_date = new DateTime( 'now', $time_zone );

		$year  = empty( $_REQUEST['year'] ) ? $current_date->format( 'Y' ) : esc_html( $_REQUEST['year'] );
		$month = empty( $_REQUEST['month'] ) ? $current_date->format( 'm' ) : esc_html( $_REQUEST['month'] );
		$day   = 1;

		// HTML
		$time_zone        = blfpst_get_wp_timezone();
		$current_datetime = new DateTime( "${year}-${month}-1", $time_zone );

		if ( $current_datetime ) {
			BLFPST_Template_Loader::render( 'send/calendar', array(
				'current_year'  => $current_datetime->format( 'Y' ),
				'current_month' => $current_datetime->format( 'm' ),
				'current_day'   => $current_datetime->format( 'd' ),
				'errors'        => $errors,
			) );
		} else {
			BLFPST_Template_Loader::render( 'send/calendar', array(
				'current_year'  => $year,
				'current_month' => $month,
				'current_day'   => $day,
				'errors'        => $errors,
			) );
		}
	}

	/**
	 * reserved mail list page
	 *
	 * @return void
	 */
	public function reserves_routing() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$errors   = new WP_Error();
		$messages = array();

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : '';
		$page_num     = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		switch ( $admin_action ) {

			case 'info':
				if ( ! empty( $send_mail_id ) ) {
					$this->reserve_info_view( $send_mail_id );
					return;
				}
				break;

			case 'sending_info':
				if ( ! empty( $send_mail_id ) ) {
					$this->sending_reserve_info_view( $send_mail_id );
					return;
				}
				break;

			case 'delete':

				if ( ! empty( $_POST ) && isset( $_POST['blfpst_send_mail_delete'] ) && wp_verify_nonce( $_POST['blfpst_send_mail_delete'], 'blfpst-send-mail-delete' ) ) {

					if ( ! empty( $send_mail_id ) ) {

						if ( BLFPST_Send_Mails_Controller::move_to_trash( $send_mail_id ) === false ) {
							$errors->add( 'Error', esc_html__( 'There was an error in the cancellation of the reservation.', 'bluff-post' ) );
						} else {
							$messages = array( esc_html__( 'Reservation canceled.', 'bluff-post' ) );
						}
					}
				} else {
					wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
				}
				break;
		}

		$this->render_list_view( 'reserves', $page_num, $messages, $errors );
	}

	/**
	 * reserved mail page
	 *
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private function reserve_info_view( $send_mail_id ) {
		$errors = new WP_Error();

		if ( empty( $send_mail_id ) ) {
			return;
		}

		$mail            = BLFPST_Send_Mails_Controller::load_reserve( $send_mail_id );

		// not found
		if ( 0 === $mail->id ) {
			$mail = BLFPST_Send_Mails_Controller::load_mail( $send_mail_id );
		}

		$recipient_count = BLFPST_Targets_Controller::execute_query_recipients_count( $mail->target_id );
		$recipient_count = ( false === $recipient_count ) ? 0 : $recipient_count;

		BLFPST_Template_Loader::render( 'send/info', array(
			'mail'            => $mail,
			'recipient_count' => $recipient_count,
			'errors'          => $errors,
		) );
	}

	/**
	 * sending reserved mail page
	 *
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private function sending_reserve_info_view( $send_mail_id ) {
		$errors = new WP_Error();

		if ( empty( $send_mail_id ) ) {
			return;
		}

		$mail            = BLFPST_Send_Mails_Controller::load_sending_reserve( $send_mail_id );
		$recipient_count = BLFPST_Targets_Controller::execute_query_recipients_count( $mail->target_id );
		$recipient_count = ( false === $recipient_count ) ? 0 : $recipient_count;

		BLFPST_Template_Loader::render( 'send/info', array(
			'mail'            => $mail,
			'recipient_count' => $recipient_count,
			'errors'          => $errors,
		) );
	}

	/**
	 * sending mail list page
	 *
	 * @return void
	 */
	public function sending_routing() {

		$errors   = new WP_Error();
		$messages = array();

		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : '';
		$page_num     = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		switch ( $admin_action ) {

			case 'info':
				if ( ! empty( $send_mail_id ) ) {
					$this->sending_mail_info_view( $send_mail_id );
					return;
				}
				break;
		}

		$this->render_list_view( 'sending-mails', $page_num, $messages, $errors );
	}

	/**
	 * sending mail page
	 *
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private function sending_mail_info_view( $send_mail_id ) {
		$errors = new WP_Error();

		if ( empty( $send_mail_id ) ) {
			return;
		}

		$mail            = BLFPST_Send_Mails_Controller::load_sending_mail( $send_mail_id );
		$recipient_count = BLFPST_Targets_Controller::execute_query_recipients_count( $mail->target_id );
		$recipient_count = ( false === $recipient_count ) ? 0 : $recipient_count;

		BLFPST_Template_Loader::render( 'send/info', array(
			'mail'            => $mail,
			'recipient_count' => $recipient_count,
			'errors'          => $errors,
		) );
	}

	/**
	 * draft mail list page
	 *
	 * @return void
	 */
	public function drafts_routing() {
		$errors   = new WP_Error();
		$messages = array();

		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : '';
		$page_num     = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		switch ( $admin_action ) {

			case 'delete':
				if ( ! empty( $send_mail_id ) ) {

					if ( BLFPST_Send_Mails_Controller::move_to_trash( $send_mail_id ) === false ) {
						$errors->add( 'Error', esc_html__( 'An error occurred in the deletion of the draft.', 'bluff-post' ) );
					} else {
						$messages = array( esc_html__( 'Successfully deleted the draft.', 'bluff-post' ) );
					}
				}
		}

		$this->render_list_view( 'drafts', $page_num, $messages, $errors );
	}

	/**
	 * draft mail editing page
	 *
	 * @param $send_mail_id
	 *
	 * @return void
	 */
	private function edit_draft_view( $send_mail_id ) {
		$errors = new WP_Error();
		$mail   = BLFPST_Send_Mails_Controller::load_draft( $send_mail_id );

		$this->render_create_view( $send_mail_id, $errors, $mail );
	}

	/**
	 * trash page
	 *
	 * @return void
	 */
	public function trashes_routing() {
		$errors   = new WP_Error();
		$messages = array();

		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$page_num     = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		switch ( $admin_action ) {
			case 'info':
				break;

			case 'clear':
				/** @var wpdb $wpdb */
				global $wpdb;
				$table_name = BLFPST_Model_Send_Mail::table_name();

				if ( $wpdb->delete( $table_name, array( 'status' => 'deleted' ) ) === false ) {
					$errors->add( 'Error', esc_html__( 'An error occurred in the deletion of the trash.', 'bluff-post' ) );
				} else {
					$messages = array( esc_html__( 'I was emptying the trash.', 'bluff-post' ) );
				}

				break;

			case 'recycle':
				break;

		}

		$this->render_list_view( 'trashes', $page_num, $messages, $errors );
	}

	/**
	 * trash to draft
	 *
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private function recycle_trash_view( $send_mail_id ) {

		if ( ! empty( $send_mail_id ) ) {

			$mail = BLFPST_Send_Mails_Controller::load_trash( $send_mail_id );

			if ( ! empty( $mail ) ) {
				$errors = new WP_Error();
				BLFPST_Send_Mails_Controller::move_to_draft( $send_mail_id );
				$this->render_create_view( $send_mail_id, $errors, $mail );
			}
		}
	}

	/**
	 * send mail history list page
	 *
	 * @return void
	 */
	public function histories_routing() {

		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$errors   = new WP_Error();
		$messages = array();

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : '';
		$page_num     = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		switch ( $admin_action ) {
			case 'info':
				if ( ! empty( $send_mail_id ) ) {
					$this->history_info_view( $send_mail_id );
					return;
				}
				break;

			case 'delete':
				if ( ! empty( $send_mail_id ) ) {

					// to deleted
					if ( BLFPST_Send_Mails_Controller::history_delete( $send_mail_id ) === false ) {
						$errors->add( 'Error', esc_html__( 'An error occurred in the deletion of the sent data.', 'bluff-post' ) );
					} else {
						$messages = array( esc_html__( 'Successfully deleted the send data.', 'bluff-post' ) );
					}
				}
				break;
		}

		$this->render_list_view( 'histories', $page_num, $messages, $errors );
	}

	/**
	 * send mail history info page
	 *
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private function history_info_view( $send_mail_id ) {
		$errors = new WP_Error();

		if ( empty( $send_mail_id ) ) {
			return;
		}

		$mail = BLFPST_Send_Mails_Controller::load_history( $send_mail_id );

		// not found
		if ( 0 === $mail->id ) {
			$mail = BLFPST_Send_Mails_Controller::load_mail( $send_mail_id );
		}

		$recipient_count = $mail->success_count + $mail->failure_count;

		BLFPST_Template_Loader::render( 'send/info', array(
			'mail'            => $mail,
			'recipient_count' => (int) $recipient_count,
			'errors'          => $errors,
		) );
	}

	/**
	 * duplicate mail view
	 *
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private function duplicate_view( $send_mail_id ) {
		$mail = BLFPST_Send_Mails_Controller::load_history( $send_mail_id );

		if ( ! empty( $mail ) ) {
			$errors   = new WP_Error();
			$mail->id = 0;
			$mail->send_type  = 'reserved';
			$mail->reserved_at  = '';

			$this->render_create_view( 0, $errors, $mail );
		}
	}

	/**
	 * failure mail list page
	 *
	 * @return void
	 */
	public function failures_routing() {
		$errors   = new WP_Error();
		$messages = array();

		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : '';
		$page_num     = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		switch ( $admin_action ) {
			case 'info':
				if ( ! empty( $send_mail_id ) ) {
					$this->history_info_view( $send_mail_id );
					return;
				}
				break;

			case 'clear':
				/** @var wpdb $wpdb */
				global $wpdb;
				$table_name = BLFPST_Model_Send_Mail::table_name();

				if ( $wpdb->delete( $table_name, array( 'status' => 'deleted' ) ) === false ) {
					$errors->add( 'Error', esc_html__( 'An error occurred in the deletion of the trash.', 'bluff-post' ) );
				} else {
					$messages = array( esc_html__( 'I was emptying the trash.', 'bluff-post' ) );
				}

				break;
		}

		$this->render_list_view( 'failures', $page_num, $messages, $errors );
	}

	/**
	 * edit reserved data view
	 *
	 * @param int $send_mail_id
	 *
	 * @return void
	 */
	private function edit_reserved_view( $send_mail_id ) {
		if ( empty( $send_mail_id ) ) {
			return;
		}

		/** @var BLFPST_Model_Send_Mail $mail */
		$mail = BLFPST_Send_Mails_Controller::load_reserve( $send_mail_id );

		if ( ! empty( $mail ) ) {
			$errors = new WP_Error();

			$this->render_create_view( $send_mail_id, $errors, $mail );
		}
	}

	/**
	 * edit reserved data view
	 *
	 * @param string $name
	 * @param int $page_num
	 * @param string $messages
	 * @param WP_Error $errors
	 *
	 * @return void
	 */
	private function render_list_view( $name, $page_num, $messages = null, $errors = null ) {

		if ( null === $messages ) {
			$messages = array();
		}

		if ( null === $errors ) {
			$errors = new WP_Error();
		}

		$mails       = array();
		$total_count = 0;

		switch ( $name ) {
			case 'reserves':
				$mails       = BLFPST_Send_Mails_Controller::load_reserves( $page_num, $this->items_per_page, false );
				$total_count = BLFPST_Send_Mails_Controller::load_reserves_count();
				break;

			case 'sending-mails':
				$mails       = BLFPST_Send_Mails_Controller::load_sending_mails( $page_num, $this->items_per_page );
				$total_count = BLFPST_Send_Mails_Controller::load_sending_mails_count();
				break;

			case 'drafts':
				$mails       = BLFPST_Send_Mails_Controller::load_drafts( $page_num, $this->items_per_page );
				$total_count = BLFPST_Send_Mails_Controller::load_draft_count();
				break;

			case 'trashes':
				$mails       = BLFPST_Send_Mails_Controller::load_deleted_mails( $page_num, $this->items_per_page );
				$total_count = BLFPST_Send_Mails_Controller::load_deleted_mails_count();
				break;

			case 'histories':
				$mails       = BLFPST_Send_Mails_Controller::load_histories( $page_num, $this->items_per_page );
				$total_count = BLFPST_Send_Mails_Controller::load_history_count();
				break;

			case 'failures':
				$mails       = BLFPST_Send_Mails_Controller::load_failures( $page_num, $this->items_per_page );
				$total_count = BLFPST_Send_Mails_Controller::load_failures_count();
				break;
		}

		$total_page = empty( $this->items_per_page ) ? 0 : ceil( $total_count / $this->items_per_page );

		BLFPST_Template_Loader::render( 'send/' . $name, array(
			'mails'      => $mails,
			'page_num'   => $page_num,
			'total_page' => $total_page,
			'messages'   => $messages,
			'errors'     => $errors,
		) );
	}

	/**
	 * create-code exist check
	 *
	 * @param  string $create_code
	 * @param  boolean $use_redirect
	 *
	 * @return boolean
	 */
	private function check_exist_create_code( $create_code, $use_redirect = false ) {

		if ( BLFPST_Send_Mails_Controller::is_exist_create_code( $create_code ) ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$table_name = BLFPST_Model_Send_Mail::table_name();
			$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (create_code='%s') AND (updated_at > ( NOW( ) - INTERVAL 1 DAY ))", $create_code );
			$mail       = BLFPST_Send_Mails_Controller::load_send_mail_with_sql( $sql, $table_name );

			if ( $use_redirect ) {

				if ( 0 < $mail->id ) {
					if ( 'reserved' === $mail->status ) {
						wp_safe_redirect( admin_url( 'admin.php?page=blfpst-send-mail-reserves&admin_action=info&send_mail_id=' . $mail->id ) );
					} else {
						wp_safe_redirect( admin_url( 'admin.php?page=blfpst-send-mail-histories&admin_action=info&send_mail_id=' . $mail->id ) );
					}
				} else {
					wp_safe_redirect( admin_url( 'admin.php?page=blfpst-send-mail' ) );
				}

				return true;
			} else {

				if ( 0 < $mail->id ) {
					if ( 'reserved' === $mail->status ) {
						$this->reserve_info_view( $mail->id );
					} else {
						$this->history_info_view( $mail->id );
					}
				} else {
					$this->calendar_view();
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * print javascript at header
	 *
	 * @return void
	 */
	public function print_scripts_create_view() {

		$page         = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$is_mail_edit = false;

		if ( ( 'blfpst-send-mail' === $page )
		     && ( ( 'edit_reserved' === $admin_action ) || ( 'duplicate' === $admin_action ) || ( 'edit_draft' === $admin_action ) )
		) {
			$is_mail_edit = true;

		} else if ( ( 'blfpst-send-mail-crate' === $page ) && ( ( '' === $admin_action ) || ( 'save' === $admin_action ) || ( 'test' === $admin_action ) )
		) {
			$is_mail_edit = true;
		}

		if ( $is_mail_edit ) {
			// Create/Edit
			$targets         = BLFPST_Targets_Controller::load_targets_with_recipient_count();
			$json_targets    = json_encode( $targets );
			$mail_froms      = BLFPST_Send_Mails_Controller::load_mail_froms();
			$json_mail_froms = json_encode( $mail_froms, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT );
			?>
			<script type="text/javascript">
				/* <![CDATA[ */
				var invalid_email_address_string = '<?php esc_html_e( 'The e-mail address you entered is incorrect.', 'bluff-post' ) ?>';
				var enter_email_address_string = '<?php esc_html_e( 'Please enter a e-mail address.', 'bluff-post' ) ?>';
                var target_list_url = '<?php echo admin_url( 'admin.php' ) . '?page=blfpst-targets&admin_action=recipients&target_id=' ?>';
				var targets = JSON.parse('<?php echo $json_targets; ?>');
				var mail_froms = JSON.parse('<?php echo $json_mail_froms; ?>');
				var content_string = '<?php esc_html_e( 'Content', 'bluff-post' ) ?>';
				var alt_text_string = '<?php esc_html_e( 'Alternate text content', 'bluff-post' ) ?>';
				var choose_image_string = '<?php esc_html_e( 'Choose Image', 'bluff-post' ) ?>';
				/* ]]> */
			</script>
			<?php
		}
	}
}
