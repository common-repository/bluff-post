<?php

/**
 * send mail model.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Model_Send_Mail {

	public static $html_content_size_max = 10485760; // 10MB

	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var int
	 */
	public $user_id = 0;

	/**
	 * @var int
	 */
	public $post_id = 0;

	/**
	 * @var int
	 */
	public $target_id = 0;

	/**
	 * @var int
	 */
	public $repeat_id = 0;

	/**
	 * @var string
	 */
	public $status = '';

	/**
	 * @var string
	 */
	public $charset = '';

	/**
	 * @var string
	 */
	public $target_name = '';

	/**
	 * @var string
	 */
	public $reserved_at = '';

	/**
	 * @var string
	 */
	public $subject = '';

	/**
	 * @var string
	 */
	public $text_content = '';

	/**
	 * @var string
	 */
	public $html_content = '';

	/**
	 * message type
	 */
	const MESSAGE_TYPE_TEXT = 0;
	const MESSAGE_TYPE_HTML = 1;
	const MESSAGE_TYPE_TEXT_HTML = 2;

	/**
	 * text only, html only, text and html
	 * @var int
	 */
	public $message_type = self::MESSAGE_TYPE_TEXT;

	/**
	 * @var string
	 */
	public $from_name = '';

	/**
	 * @var string
	 */
	public $from_address = '';

	/**
	 * @var string
	 */
	public $reply_name = '';

	/**
	 * @var string
	 */
	public $reply_address = '';

	/**
	 * @var string
	 */
	public $create_code = '';

	/**
	 * @var string
	 */
	public $send_result = '';

	/**
	 * @var string
	 */
	public $send_request_start_at = '';

	/**
	 * @var string
	 */
	public $send_request_end_at = '';

	/**
	 * @var int
	 */
	public $recipient_count = 0;

	/**
	 * @var int
	 */
	public $success_count = 0;

	/**
	 * @var int
	 */
	public $failure_count = 0;

	/**
	 * @var string
	 */
	public $target_sql = '';

	/**
	 * @var string
	 */
	public $deleted_at = '';

	/**
	 * @var string
	 */
	public $updated_at = '';

	/**
	 * @var string
	 */
	public $created_at = '';

	/**
	 * @var string
	 */
	public $send_type  = 'reserved';

	/**
	 * @var string
	 */
	public $content_type  = 'content_type_html';

	/**
	 * set parameter from data
	 *
	 * @param OBJECT $result
	 */
	public function set_result( $result ) {

		$this->id          = $result->id;
		$this->user_id     = $result->user_id;
		$this->post_id     = $result->post_id;
		$this->target_id   = $result->target_id;
		$this->target_name = $result->target_name;
		$this->repeat_id   = $result->repeat_id;

		$this->status      = $result->status;
		$this->charset     = $result->charset;
		$this->reserved_at = $result->reserved_at;

		$this->subject       = stripslashes( $result->subject );
		$this->text_content  = stripslashes( $result->text_content );
		$this->html_content  = stripslashes( $result->html_content );
		$this->from_name     = stripslashes( $result->from_name );
		$this->from_address  = stripslashes( $result->from_address );
		$this->reply_name    = stripslashes( $result->reply_name );
		$this->reply_address = stripslashes( $result->reply_address );

		$this->create_code           = $result->create_code;
		$this->send_result           = $result->send_result;
		$this->send_request_start_at = $result->send_request_start_at;
		$this->send_request_end_at   = $result->send_request_end_at;
		$this->recipient_count       = $result->recipient_count;
		$this->success_count         = $result->success_count;
		$this->failure_count         = $result->failure_count;
		$this->target_sql            = $result->target_sql;

		$this->deleted_at = $result->deleted_at;
		$this->updated_at = $result->updated_at;
		$this->created_at = $result->created_at;

		$this->content_type = ( ! empty( $result->html_content ) ) ? 'content_type_html' : 'content_type_text';
		$this->send_type    = ( ! empty( $result->reserved_at ) ) ? 'reserved' : '';
	}

	/**
	 * @return boolean
	 */
	public function is_reserved() {
		return ( ( 'reserved' === $this->status ) && ( '' !== $this->reserved_at ) );
	}

	/**
	 * @return boolean
	 */
	public function is_history() {
		return ( ( 'wait' !== $this->send_result ) && ! empty( $this->send_request_start_at ) && ! empty( $this->send_request_end_at ) );
	}

	/**
	 * @return boolean
	 */
	public function is_waiting() {
		return ( ( 'wait' === $this->send_result ) && empty( $this->send_request_start_at ) );
	}

	/**
	 * @return boolean
	 */
	public function is_sending() {
		return ( ( 'wait' === $this->send_result ) && ! empty( $this->send_request_start_at ) && empty( $this->send_request_end_at ) );
	}

	/**
	 * @return boolean
	 */
	public function is_text_content_only() {
		return ( 0 == mb_strlen( $this->html_content ) );
	}

	/**
	 * @return boolean
	 */
	public function is_html_content_only() {
		return ( 0 == mb_strlen( $this->text_content ) );
	}

	/**
	 * @return boolean
	 */
	public function is_html_mail() {
		return ( ! $this->is_text_content_only() && ! $this->is_text_content_only() );
	}

	/**
	 * @return boolean
	 */
	public function is_send_result_failure() {
		return ( 'failure' == $this->send_result );
	}

	/**
	 * @param integer $send_mail_id
	 * @param integer $recipient_count
	 * @param string $charset
	 * @param string $sql
	 *
	 * @return void
	 */
	public function update_start_send( $send_mail_id, $recipient_count, $charset, $sql ) {

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Send_Mail::table_name();

		$wpdb->update(
			$table_name,
			array(
				'status'                => 'send',
				'send_request_start_at' => current_time( 'mysql', 0 ),
				'recipient_count'       => $recipient_count,
				'charset'               => $charset,
				'target_sql'            => $sql,
				'updated_at'            => current_time( 'mysql', 0 ),
			),
			array( 'ID' => $send_mail_id ),
			array(
				'%s',    // status
				'%s',    // send_request_start_at
				'%d',    // recipient_count
				'%s',    // target_sql
				'%s',    // update
			),
			array( '%d' )
		);
	}

	/**
	 * @param integer $send_mail_id
	 * @param string $result
	 * @param integer $success_count
	 * @param integer $failure_count
	 *
	 * @return void
	 */
	public function update_finish_send( $send_mail_id, $result, $success_count, $failure_count ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Send_Mail::table_name();

		$wpdb->update(
			$table_name,
			array(
				'send_request_end_at' => current_time( 'mysql', 0 ),
				'send_result'         => $result,
				'success_count'       => $success_count,
				'failure_count'       => $failure_count,
				'updated_at'          => current_time( 'mysql', 0 ),
			),
			array( 'ID' => $send_mail_id ),
			array(
				'%s',    // send_request_start_at
				'%s',    // send_result
				'%d',    // success_count
				'%d',    // failure_count
				'%s',    // update
			),
			array( '%d' )
		);
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_send_mails';
	}

	/**
	 * @return string
	 */
	public static function meta_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_send_mail_meta';
	}
}
