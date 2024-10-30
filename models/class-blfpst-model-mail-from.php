<?php
/**
 * mail from model.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Model_Mail_From {

	/**
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var int
	 */
	public $user_id = 0;

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

		$this->id            = $result->id;
		$this->user_id       = $result->user_id; // wp_users.ID
		$this->from_name     = stripslashes( $result->from_name );
		$this->from_address  = stripslashes( $result->from_address );
		$this->reply_name    = stripslashes( $result->reply_name );
		$this->reply_address = stripslashes( $result->reply_address );

		$this->updated_at = $result->updated_at;
		$this->created_at = $result->created_at;
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_mail_froms';
	}

	/**
	 * @return string
	 */
	public static function meta_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_mail_from_meta';
	}
}
