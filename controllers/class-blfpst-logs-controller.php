<?php

/**
 * log controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Logs_Controller {
	private static $logs_per_page = 50;

	public static $debug = 0;
	public static $info = 1;
	public static $notice = 2;
	public static $warn = 3;
	public static $err = 4;
	public static $crit = 5;
	public static $alert = 6;
	public static $emerg = 7;

	public function initialize() {
	}

	public static function log( $level, $summary, $detail, $identifier = '', $user_id = 0, $send_mail_id = 0 ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Log::table_name();

		$values = array(
			'user_id'      => $user_id,
			'send_mail_id' => $send_mail_id,
			'level'        => $level,
			'identifier'   => $identifier,
			'summary'      => $summary,
			'detail'       => $detail,

			'updated_at' => current_time( 'mysql', 0 ),
			'created_at' => current_time( 'mysql', 0 ),
		);

		$format = array(
			'%d',
			'%d',
			'%d',
			'%s',
			'%s',
			'%s',

			'%s',
			'%s',
		);

		$wpdb->insert( $table_name, $values, $format );
	}

	public static function error_log( $summary, $detail, $identifier = '', $user_id = 0, $send_mail_id = 0 ) {
		self::log( self::$err, $summary, $detail, $identifier, $user_id, $send_mail_id );
	}

	public static function notice_log( $summary, $detail, $identifier = '', $user_id = 0, $send_mail_id = 0 ) {
		self::log( self::$notice, $summary, $detail, $identifier, $user_id, $send_mail_id );
	}

	/**
	 * delete blfpst_logs
	 *
	 * @param integer $log_id log identifier
	 *
	 * @return void
	 */
	public static function delete_log( $log_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table = BLFPST_Model_Log::table_name();
		$wpdb->delete( $table, array( 'id' => $log_id ), array( '%d' ) );
	}

	/**
	 * routing index
	 *
	 * @return void
	 */
	public function index() {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';

		switch ( $admin_action ) {
			case 'delete': {
				$this->delete();
			}
				break;

			default:
				$this->log_list();
		}
	}

	public function log_list( $message = '' ) {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$page_num = isset( $_REQUEST['page_num'] ) ? (int) $_REQUEST['page_num'] : 0;

		$level       = self::$info;
		$logs        = self::load_logs( $level, $page_num, self::$logs_per_page );
		$total_count = self::load_log_count();
		$total_page  = empty( self::$logs_per_page ) ? 0 : ceil( $total_count / self::$logs_per_page );

		BLFPST_Template_Loader::render( 'logs/list', array(
			'logs'       => $logs,
			'page_num'   => $page_num,
			'total_page' => $total_page,
			'message'    => $message,
		) );
	}

	/**
	 * @param int $level
	 * @param int $page_num
	 * @param int $limit
	 * @param int $send_mail_id
	 * @param int $user_id
	 * @param boolean $desc
	 *
	 * @return array
	 */
	public static function load_logs( $level = 0, $page_num = - 1, $limit = 0, $send_mail_id = 0, $user_id = - 1, $desc = true ) {
		$logs = array();

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Log::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (level >= %d)", $level );

		if ( $send_mail_id > 0 ) {
			$sql .= $wpdb->prepare( ' AND (send_mail_id = %d)', $send_mail_id );
		};

		if ( $user_id > - 1 ) {
			$sql .= $wpdb->prepare( ' AND (user_id = %d)', $user_id );
		};

		$sql .= ' ORDER BY id';

		if ( $desc ) {
			$sql .= ' DESC';
		}

		if ( $page_num >= 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
		};

		$results = $wpdb->get_results( $sql );
		if ( null === $results ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			foreach ( $results as $result ) {
				$log = new BLFPST_Model_Log();
				$log->set_result( $result );
				array_push( $logs, $log );
			}
		}

		return $logs;
	}

	/**
	 * @param int $page_num
	 * @param int $limit
	 * @param int $send_mail_id
	 * @param int $user_id
	 * @param boolean $desc
	 *
	 * @return array
	 */
	public static function load_error_logs( $page_num = - 1, $limit = 0, $send_mail_id = 0, $user_id = - 1, $desc = true ) {
		return self::load_logs( self::$err, $page_num, $limit, $send_mail_id, $user_id, $desc );
	}

	/**
	 * log count
	 *
	 * @return int
	 */
	private static function load_log_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Log::table_name();
		$sql        = 'SELECT count(*) FROM ' . $table_name;
		$count      = $wpdb->get_var( $sql );

		return (int) $count;
	}

	public function delete( $message = '' ) {
		if ( ! current_user_can( 'blfpst_edit_mail' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		$log_id = isset( $_REQUEST['log_id'] ) ? (int) $_REQUEST['log_id'] : 0;

		if ( ! empty( $log_id ) ) {
			self::delete_log( $log_id );
		}

		$this->log_list( $message );
	}
}
