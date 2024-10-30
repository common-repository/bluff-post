<?php
/**
 * Log model.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Model_Recipient {

	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var string
	 */
	public $mail_address = '';

	/**
	 * @var string
	 */
	public $first_name = '';

	/**
	 * @var string
	 */
	public $last_name = '';

	/**
	 * @var boolean
	 */
	public $text_only = false;

	/**
	 * @var string
	 */
	public $status = '';

	/**
	 * @var string
	 */
	public $error_count = '';

	/**
	 * @var string
	 */
	public $delete_at = '';

	/**
	 * @var string
	 */
	public $updated_at = '';

	/**
	 * @var string
	 */
	public $created_at = '';

	/**
	 * set parameter from data
	 *
	 * @param OBJECT $result
	 */
	public function set_result( $result ) {

		$this->id           = $result->id;
		$this->mail_address = stripslashes( $result->mail_address );
		$this->first_name   = stripslashes( $result->first_name );
		$this->last_name    = stripslashes( $result->last_name );
		$this->text_only    = $result->text_only;
		$this->status       = stripslashes( $result->status );
		$this->error_count  = $result->error_count;

		$this->delete_at  = $result->delete_at;
		$this->updated_at = $result->updated_at;
		$this->created_at = $result->created_at;
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_recipients';
	}

	/**
	 * @return string
	 */
	public static function meta_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_recipient_meta';
	}
}
