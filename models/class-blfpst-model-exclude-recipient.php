<?php
/**
 * Log model.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Model_Exclude_Recipient {

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
	public $class_name = '';

	/**
	 * @var string
	 */
	public $mail_address = '';

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
		$this->user_id      = $result->user_id;
		$this->class_name   = stripslashes( $result->class_name );
		$this->mail_address = stripslashes( $result->mail_address );

		$this->updated_at = $result->updated_at;
		$this->created_at = $result->created_at;
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_exclude_recipients';
	}
}
