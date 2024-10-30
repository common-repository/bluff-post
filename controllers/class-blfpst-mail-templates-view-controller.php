<?php

/**
 * mail template view controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Mail_Templates_View_Controller {
	private $items_per_page = 20;

	public function initialize() {
	}

	public function index_routing() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';

		switch ( $admin_action ) {

			case 'registered':
				$this->registered_view();
				return;

			case 'info': {
				$mail_template_id = isset( $_REQUEST['mail_template_id'] ) ? $_REQUEST['mail_template_id'] : '';
				if ( ! empty( $mail_template_id ) ) {
					$this->template_info_view( $mail_template_id );
					return;
				}
			}
				break;

			case 'edit': {
				$mail_template_id = isset( $_REQUEST['mail_template_id'] ) ? $_REQUEST['mail_template_id'] : '';
				if ( ! empty( $mail_template_id ) ) {
					$this->template_edit_view( $mail_template_id );
					return;
				}
			}
				break;

			case 'delete':
				$mail_template_id = isset( $_REQUEST['mail_template_id'] ) ? $_REQUEST['mail_template_id'] : '';
				if ( ! empty( $mail_template_id ) ) {
					$this->template_delete_view( $mail_template_id );
					return;
				}
				break;

			case 'register':
				// 入力エラー後の表示ルーティング
				$this->register_view();
				return;

			case 'edit_from_send_mail':
				$send_mail_id = isset( $_REQUEST['send_mail_id'] ) ? $_REQUEST['send_mail_id'] : 0;
				if ( ! empty( $send_mail_id ) ) {
					$this->edit_from_send_mail_view( $send_mail_id );
					return;
				}
				return;
		}

		$this->template_list_view();
	}

	/**
	 * target information
	 *
	 * @param string $message messages
	 * @param WP_Error $errors error
	 *
	 * @return void
	 */
	private function template_list_view( $message = '', $errors = null ) {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$page_num = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		$mail_templates = BLFPST_Mail_Templates_Controller::load_mail_templates( $page_num, $this->items_per_page );
		$total_count    = BLFPST_Mail_Templates_Controller::load_mail_template_count();
		$total_page     = empty( $this->items_per_page ) ? 0 : ceil( $total_count / $this->items_per_page );

		BLFPST_Template_Loader::render( 'template/templates', array(
			'mail_templates' => $mail_templates,
			'page_num'       => $page_num,
			'total_page'     => $total_page,
			'message'        => $message,
			'errors'         => $errors,
		) );
	}

	public function create_routing() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$errors       = new WP_Error();
		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';

		switch ( $admin_action ) {

			case 'registered':
				$this->registered_view();
				return;

			case 'delete':
				$mail_template_id = isset( $_REQUEST['mail_template_id'] ) ? $_REQUEST['mail_template_id'] : '';
				if ( ! empty( $mail_template_id ) ) {
					$this->template_delete_view( $mail_template_id );
					return;
				}
				break;
		}

		$this->render_create_view( $errors );
	}

	/**
	 * @param $errors WP_Error
	 * @param $mail_template BLFPST_Model_Template
	 *
	 * @return void
	 */
	private function render_create_view( $errors, $mail_template = null ) {

		$data_source = BLFPST_Targets_Controller::data_source_object( null );

		BLFPST_Template_Loader::render( 'template/create', array(
			'insertion_description' => $data_source->insertion_description(),
			'mail_template'         => $mail_template,
			'errors'                => $errors,
		) );
	}

	public function register_view() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$errors = isset( $_POST['errors'] ) ? json_decode( $_POST['errors'] ) : new WP_Error();

		// validate error(register_view() -> index())
		if ( count( $errors->errors ) > 0 ) {
			$this->render_create_view( $errors );
			return;
		}

		$errors = new WP_Error();

		$mail_template_id = isset( $_REQUEST['mail_template_id'] ) ? $_REQUEST['mail_template_id'] : 0;
		$title            = isset( $_REQUEST['title'] ) ? stripslashes( $_REQUEST['title'] ) : '';
		$from_name        = isset( $_REQUEST['from_name'] ) ? stripslashes( $_REQUEST['from_name'] ) : '';
		$from_address     = isset( $_REQUEST['from_address'] ) ? stripslashes( $_REQUEST['from_address'] ) : '';
		$reply_address    = isset( $_REQUEST['reply_address'] ) ? stripslashes( $_REQUEST['reply_address'] ) : '';
		$subject          = isset( $_REQUEST['subject'] ) ? stripslashes( $_REQUEST['subject'] ) : '';
		$text_content     = isset( $_REQUEST['text_content'] ) ? stripslashes( $_REQUEST['text_content'] ) : '';
		$html_content     = isset( $_REQUEST['htmlcontent'] ) ? stripslashes( $_REQUEST['htmlcontent'] ) : '';
		$content_type     = isset( $_REQUEST['content_type'] ) ? stripslashes( $_REQUEST['content_type'] ) : 'content_type_html';

		// content type
		if ( 'content_type_text' === $content_type ) {
			$html_content = '';
		}

		// Validation
		if ( '' === $title ) {
			$errors->add( 'Error', esc_html__( 'Please enter a template name.', 'bluff-post' ) );
		}

		if ( ( '' === $from_name ) && ( '' === $from_address ) && ( '' === $reply_address ) && ( '' === $subject ) && ( '' === $text_content ) && ( '' === $html_content ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a template content.', 'bluff-post' ) );
		}

		if ( mb_strlen( $title ) > 255 ) {
			$errors->add( 'Error', esc_html__( 'Please enter a template name 255 or less characters.', 'bluff-post' ) );
		}

		if ( mb_strlen( $from_name ) > 255 ) {
			$errors->add( 'Error', esc_html__( 'Please enter a from name 255 or less characters.', 'bluff-post' ) );
		}

		if ( mb_strlen( $from_address ) > 255 ) {
			$errors->add( 'Error', esc_html__( 'Please enter a from address 255 or less characters.', 'bluff-post' ) );
		}

		if ( mb_strlen( $reply_address ) > 255 ) {
			$errors->add( 'Error', esc_html__( 'Please enter a replay address 255 or less characters.', 'bluff-post' ) );
		}

		if ( mb_strlen( $subject ) > 255 ) {
			$errors->add( 'Error', esc_html__( 'Please enter a subject 255 or less characters.', 'bluff-post' ) );
		}

		if ( mb_strlen( $text_content ) > 5000 ) {
			$errors->add( 'Error', esc_html__( 'Please enter a text content 5,000 or less characters.', 'bluff-post' ) );
		}

		if ( mb_strlen( $html_content ) > BLFPST_Model_Send_Mail::$html_content_size_max ) {
			$errors->add( 'Error', esc_html__( 'HTML text Please enter no more than 10MB.', 'bluff-post' ) );
		}

		if ( count( $errors->errors ) == 0 ) {

			/** @var wpdb $wpdb */
			global $wpdb;
			$table_name             = BLFPST_Model_Template::table_name();
			$mail_template_modified = 0;

			// 新規作成
			if ( empty( $mail_template_id ) ) {

				$result           = $wpdb->insert(
					$table_name,
					array(
						'user_id'       => get_current_user_id(),
						'title'         => $title,
						'from_name'     => $from_name,
						'from_address'  => $from_address,
						'reply_address' => $reply_address,
						'subject'       => $subject,
						'text_content'  => $text_content,
						'html_content'  => $html_content,

						'updated_at' => current_time( 'mysql', 0 ),
						'created_at' => current_time( 'mysql', 0 ),
					),
					array(
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',

						'%s',
						'%s',
					)
				);
				$mail_template_id = $wpdb->insert_id;
			} else {

				// 更新
				$result = $wpdb->update(
					$table_name,
					array(
						'user_id'       => get_current_user_id(),
						'title'         => $title,
						'from_name'     => $from_name,
						'from_address'  => $from_address,
						'reply_address' => $reply_address,
						'subject'       => $subject,
						'text_content'  => $text_content,
						'html_content'  => $html_content,

						'updated_at' => current_time( 'mysql', 0 ),
					),
					array( 'ID' => $mail_template_id ),
					array(
						'%d', // user id
						'%s', // title
						'%s', // from_name
						'%s', // from_address
						'%s', // reply_address
						'%s', // subject
						'%s', // text_content
						'%s', // html_content

						'%s', // updated_at,
					),
					array( '%d' )
				);

				$mail_template_modified = 1;
			}

			if ( ! $result ) {
				error_log( 'DB Error, could not write database.' );
				error_log( 'MySQL Error: ' . $wpdb->last_error );
				$errors->add( 'Error', esc_html__( 'An error has occurred in writing to the DB.', 'bluff-post' ) );
			}

			if ( count( $errors->errors ) == 0 ) {
				// リロードによる多重投稿防止のリダイレクト
				wp_safe_redirect( admin_url( 'admin.php?page=blfpst-mail-template-create&admin_action=registered&mail_template_id=' . $mail_template_id . '&mail_template_modified=' . $mail_template_modified ) );
				exit;
			}
		}

		$_POST['mail_template_id'] = $mail_template_id;
		$_POST['errors']           = json_encode( $errors );
	}

	/**
	 * finish register page
	 *
	 * @return void
	 */
	private function registered_view() {
		$mail_template_id       = isset( $_REQUEST['mail_template_id'] ) ? $_REQUEST['mail_template_id'] : 0;
		$mail_template_modified = isset( $_REQUEST['mail_template_modified'] ) ? $_REQUEST['mail_template_modified'] : 0;

		if ( empty( $mail_template_modified ) ) {
			$message = esc_html__( 'Template successfully created.', 'bluff-post' );

		} else {
			$message = esc_html__( 'Template successfully updated.', 'bluff-post' );
		}

		$this->template_info_view( $mail_template_id, $message );
	}

	private function template_info_view( $mail_template_id, $message = '' ) {

		$errors = new WP_Error();

		$mail_template = BLFPST_Mail_Templates_Controller::load_mail_template( $mail_template_id );
		if ( empty( $mail_template ) ) {
			$errors->add( 'Error', esc_html__( 'An error has occurred in reading to the DB.', 'bluff-post' ) );
		}

		$preview_content = str_replace( '<', '&lt;', $mail_template->html_content );
		$preview_content = str_replace( '>', '&gt;', $preview_content );
		$preview_content = str_replace( '"', '&quot;', $preview_content );
		$preview_content = str_replace( '\'', '&#39;', $preview_content );

		BLFPST_Template_Loader::render( 'template/info', array(
			'mail_template'   => $mail_template,
			'preview_content' => $preview_content,
			'message'         => $message,
			'errors'          => array(),
		) );
	}

	private function template_edit_view( $mail_template_id ) {
		$errors = new WP_Error();

		$mail_template = BLFPST_Mail_Templates_Controller::load_mail_template( $mail_template_id );
		if ( empty( $mail_template ) ) {
			$errors->add( 'Error', esc_html__( 'An error has occurred in reading to the DB.', 'bluff-post' ) );
		}

		$this->render_create_view( $errors, $mail_template );
	}

	private function edit_from_send_mail_view( $mail_template_id ) {
		$errors = new WP_Error();

		$send_mail = BLFPST_Send_Mails_Controller::load_mail( $mail_template_id );
		if ( empty( $send_mail ) ) {
			$errors->add( 'Error', esc_html__( 'An error occurred while reading the send data.', 'bluff-post' ) );
		}

		$mail_template                = new BLFPST_Model_Template();
		$mail_template->id            = 0;
		$mail_template->title         = $send_mail->subject;
		$mail_template->subject       = $send_mail->subject;
		$mail_template->html_content  = $send_mail->html_content;
		$mail_template->text_content  = $send_mail->text_content;
		$mail_template->from_name     = $send_mail->from_name;
		$mail_template->from_address  = $send_mail->from_address;
		$mail_template->reply_address = $send_mail->reply_address;

		$this->render_create_view( $errors, $mail_template );
	}

	private function template_delete_view( $mail_template_id ) {

		if ( empty( $_POST ) || ! isset( $_POST['blfpst_mail_template_delete'] ) || ! wp_verify_nonce( $_POST['blfpst_mail_template_delete'], 'blfpst-mail-template-delete' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$errors = new WP_Error();

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Template::table_name();
		$result     = $wpdb->delete( $table_name, array( 'ID' => $mail_template_id ), array( '%d' ) );
		if ( ! $result ) {
			$message = '';
			$errors->add( 'Error', esc_html__( 'An error occurred while deleting the send data.', 'bluff-post' ) );
		} else {
			$message = esc_html__( 'The template has been deleted successfully.', 'bluff-post' );
		}

		$this->template_list_view( $message, $errors );
	}

	/**
	 * print javascript at header
	 *
	 * @return void
	 */
	public function print_scripts_create_view() {
		?>
		<script type="text/javascript">
			/* <![CDATA[ */
			var content_string = '<?php esc_html_e( 'Content', 'bluff-post' ) ?>';
			var alt_text_string = '<?php esc_html_e( 'Alternate text content', 'bluff-post' ) ?>';
			var choose_image_string = '<?php esc_html_e( 'Choose Image', 'bluff-post' ) ?>';
			/* ]]> */
		</script>
		<?php
	}
}
