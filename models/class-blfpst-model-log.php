<?php
/**
 * Log model.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Model_Log {
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
	public $send_mail_id = 0;

	/**
	 * @var int
	 */
	public $level = 0;

	/**
	 * @var string
	 */
	public $identifier = '';

	/**
	 * @var string
	 */
	public $summary = '';

	/**
	 * @var string
	 */
	public $detail = '';

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
		$this->send_mail_id = $result->send_mail_id;
		$this->level        = $result->level;
		$this->identifier   = stripslashes( $result->identifier );
		$this->summary      = stripslashes( $result->summary );
		$this->detail       = stripslashes( $result->detail );

		$this->updated_at = $result->updated_at;
		$this->created_at = $result->created_at;
	}

	/**
	 * get level name
	 * @return string
	 */
	public function get_level_name() {
		$names = [ 'debug', 'information', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency' ];

		if ( ( $this->level < 0 ) || ( $this->level >= count( $names ) ) ) {
			return '';
		}

		return $names[ $this->level ];
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_logs';
	}
}
