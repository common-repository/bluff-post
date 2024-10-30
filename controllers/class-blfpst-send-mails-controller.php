<?php

/**
 * mail send controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Send_Mails_Controller {

	public function initialize() {
	}

	/**
	 * validate parameter
	 *
	 * @param array $request
	 * @param WP_Error $errors
	 * @param boolean $check_reserved_at
	 *
	 * @return void
	 */
	public static function validate_mail_data( $request, $errors, $check_reserved_at = true ) {

		$send_type     = isset( $request['send_type'] ) ? $request['send_type'] : '';
		$content_type  = isset( $_POST['content_type'] ) ? $_POST['content_type'] : 'content_type_html';
		$reserved_at   = isset( $request['reserved_at'] ) ? $request['reserved_at'] : '';
		$from_name     = isset( $request['from_name'] ) ? stripslashes( $request['from_name'] ) : '';
		$from_address  = isset( $request['from_address'] ) ? stripslashes( $request['from_address'] ) : '';
		$reply_address = isset( $request['reply_address'] ) ? stripslashes( $request['reply_address'] ) : '';
		$subject       = isset( $request['subject'] ) ? stripslashes( $request['subject'] ) : '';
		$text_content  = isset( $request['text_content'] ) ? stripslashes( $request['text_content'] ) : '';
		$html_content  = isset( $request['htmlcontent'] ) ? stripslashes( $request['htmlcontent'] ) : '';
		$time_zone     = blfpst_get_wp_timezone();

		if ( '____/__/__ __:__' === $reserved_at ) {
			$reserved_at = '';
		}

		// Reservation/Immediately
		if ( ( '' !== $send_type ) && ( 'reserved' !== $send_type ) ) {
			$errors->add( 'Error', esc_html__( 'Incorrect specification of reservation.', 'bluff-post' ) );
		}

		// Reservation Datetime
		if ( 'reserved' !== $send_type ) {
			$reserved_at = '';
		}

		if ( $check_reserved_at ) {

			if ( '' !== $reserved_at ) {
				$reserved_datetime = DateTime::createFromFormat( 'Y/m/d H:i', $reserved_at );
				if ( ! $reserved_datetime ) {
					$errors->add( 'Error', esc_html__( 'The format of the reservation date is invalid.', 'bluff-post' ) );
				} else {
					$today = new DateTime( 'now', $time_zone );

					if ( $today > $reserved_datetime ) {
						$errors->add( 'Error', esc_html__( 'Past has been specified in the transmission reservation date.', 'bluff-post' ) );
					}
				}
			} else if ( 'reserved' === $send_type ) {
				$errors->add( 'Error', esc_html__( 'Please select a reservation date.', 'bluff-post' ) );
			}
		}

		// From name
		if ( '' === $from_name ) {
			$errors->add( 'Error', esc_html__( 'Please enter a from name.', 'bluff-post' ) );
		}

		if ( 255 < mb_strlen( $from_name ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a from name 255 or less characters.', 'bluff-post' ) );
		}

		// From e-mail address
		if ( '' === $from_address ) {
			$errors->add( 'Error', esc_html__( 'Please enter a from address.', 'bluff-post' ) );
		} else if ( ! is_email( $from_address ) ) {
			$errors->add( 'Error', esc_html__( 'The format of a from address is invalid.', 'bluff-post' ) );
		}

		if ( 255 < mb_strlen( $from_address ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a from address 255 or less characters.', 'bluff-post' ) );
		}

		// Reply e-mail address
		if ( ( '' !== $reply_address ) && ( ! is_email( $reply_address ) ) ) {
			$errors->add( 'Error', esc_html__( 'The format of a reply address is invalid.', 'bluff-post' ) );
		}

		if ( 255 < mb_strlen( $reply_address ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a replay address 255 or less characters.', 'bluff-post' ) );
		}

		// Subject
		if ( '' === $subject ) {
			$errors->add( 'Error', esc_html__( 'Please enter a subject.', 'bluff-post' ) );
		}

		if ( 255 < mb_strlen( $subject ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a subject 255 or less characters.', 'bluff-post' ) );
		}

		// Content
		if ( ( 'content_type_text' === $content_type ) && ( '' === $text_content ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a content.', 'bluff-post' ) );
		} elseif ( ( 'content_type_html' === $content_type ) && ( '' === $html_content ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a content.', 'bluff-post' ) );
		} elseif ( ( '' === $text_content ) && ( '' === $html_content ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a content.', 'bluff-post' ) );
		}

		if ( 5000 < mb_strlen( $text_content ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a text content 5,000 or less characters.', 'bluff-post' ) );
		}

		if ( BLFPST_Model_Send_Mail::$html_content_size_max < mb_strlen( $html_content ) ) {
			$errors->add( 'Error', esc_html__( 'HTML text Please enter no more than 10MB.' . mb_strlen( $html_content ), 'bluff-post' ) );
		}
	}

	/**
	 * send mail
	 *
	 * @param array $targets
	 * @param int $target_id
	 * @param int $send_mail_id
	 * @param string $subject
	 * @param string $text_content
	 * @param string $html_content
	 * @param string $from_name
	 * @param string $from_address
	 * @param string $reply_address
	 * @param int $post_id
	 *
	 * @return array
	 */
	public static function send_mail( $targets, $target_id, $send_mail_id, $subject, $text_content, $html_content, $from_name, $from_address, $reply_address, $post_id ) {
		$send_mail = new BLFPST_Send_Mail();

		$results = $send_mail->send_mail_sync( $targets, $target_id, $send_mail_id, $subject, $text_content, $html_content,
			array(
				'from_name'     => $from_name,
				'from_address'  => $from_address,
				'reply_address' => $reply_address,
				'post_id'       => $post_id,
			)
		);

		return $results;
	}

	public static function is_exists_mail_from( $from_name, $from_address, $reply_address ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Mail_From::table_name();
		$sql        = $wpdb->prepare( "SELECT count(*) FROM ${table_name} WHERE (from_name='%s') AND (from_address='%s') AND (reply_address='%s') ",
			$from_name,
			$from_address,
			$reply_address
		);

		$count = $wpdb->get_var( $sql );
		$count = ( false === $count ) ? 0 : $count;

		return ( $count > 0 );
	}

	public static function register_mail_from( $from_name, $from_address, $reply_address, $user_id ) {

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Mail_From::table_name();
		$result     = $wpdb->insert(
			$table_name,
			array(
				'user_id'       => $user_id,
				'from_name'     => $from_name,
				'from_address'  => $from_address,
				'reply_address' => $reply_address,
				'updated_at'    => current_time( 'mysql', 0 ),
				'created_at'    => current_time( 'mysql', 0 ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		return $result;
	}

	/**
	 * Request calendar data.
	 *
	 * @param Datetime $in_datetime current date.
	 * @param integer $week_count number of showing week.
	 *
	 * @return array
	 */
	public static function request_calendar_data( $in_datetime, $week_count ) {

		$time_zone      = blfpst_get_wp_timezone();
		$start_datetime = clone $in_datetime;
		$first_week     = $start_datetime->format( 'w' );
		date_sub( $start_datetime, DateInterval::createFromDateString( $first_week . ' days' ) );

		// Preview month
		$prev_month_datetime = clone $in_datetime;
		date_sub( $prev_month_datetime, DateInterval::createFromDateString( '1 months' ) );
		$prev_year  = $prev_month_datetime->format( 'Y' );
		$prev_month = $prev_month_datetime->format( 'm' );

		// Next month
		$next_month_datetime = clone $in_datetime;
		date_add( $next_month_datetime, DateInterval::createFromDateString( '1 months' ) );
		$next_year  = $next_month_datetime->format( 'Y' );
		$next_month = $next_month_datetime->format( 'm' );

		$format               = esc_html__( 'm.Y', 'bluff-post' );
		$current_month_string = $in_datetime->format( $format );
		$prev_month_url       = esc_url( admin_url( 'admin.php?page=blfpst-send-mail&year=' . $prev_year . '&month=' . $prev_month ) );
		$next_month_url       = esc_url( admin_url( 'admin.php?page=blfpst-send-mail&year=' . $next_year . '&month=' . $next_month ) );

		$current_month  = $in_datetime->format( 'm' );
		$today_datetime = new DateTime( 'now', $time_zone );
		$a_datetime     = clone $start_datetime;
		$is_future      = false;

		$calendar_data = array();

		for ( $i = 0; $i < $week_count; $i ++ ) {

			$day_data = array();

			for ( $j = 0; $j < 7; $j ++ ) {
				$is_current_month = false;
				$is_current_day   = false;

				// Specified month
				if ( $a_datetime->format( 'm' ) == $current_month ) {
					$is_current_month = true;
				}

				// Specified day
				if ( ( $a_datetime->format( 'm' ) == $today_datetime->format( 'm' ) ) && ( $a_datetime->format( 'd' ) == $today_datetime->format( 'd' ) ) ) {
					$is_current_day = true;
				}

				if ( $is_current_month ) {
					if ( $is_current_day ) {
						$background_color = 'calendar_today_cell';
					} else {
						$background_color = 'calendar_current_month_cell';
					}
				} else {
					$background_color = 'calendar_old_cell';
				}

				if ( strtotime( $today_datetime->format( 'Y-m-d' ) ) <= strtotime( $a_datetime->format( 'Y-m-d' ) ) ) {
					$is_future = true;
				}

				$mails = self::load_mails_at_date( $a_datetime );

				$current_date_string = $a_datetime->format( 'j' );
				$date_param          = esc_html( $a_datetime->format( 'Y/m/d H:00' ) ); // == Javascript format
				$register_url        = esc_url( admin_url( 'admin.php?page=blfpst-send-mail-crate&reserved_at=' . $date_param ) );
				$subject_max_length  = 20;

				$day_send_list = array();

				/**
				 * @var BLFPST_Model_Send_Mail $mail
				 */
				foreach ( $mails as $mail ) {

					$send_data = array();

					$send_mail_id  = $mail->id;
					$subject       = esc_html( blfpst_shortcut_string( $mail->subject, $subject_max_length ) );
					$reserved_time = '';
					$total_count   = 0;

					// Reservation
					if ( empty( $mail->send_request_start_at ) ) {
						$reserved_at   = $mail->reserved_at;
						$reserved_date = new DateTime( $reserved_at, $time_zone );
						$reserved_time = ( $reserved_date ) ? esc_html( $reserved_date->format( 'H:i' ) ) : '';
						$page          = 'blfpst-send-mail-reserves';

						// Sending
					} else if ( empty( $mail->send_request_end_at ) ) {
						$total_count = esc_html( number_format( $mail->success_count + $mail->failure_count ) );
						$page        = 'blfpst-send-mail-histories';

						// History
					} else {
						$total_count = esc_html( number_format( $mail->success_count + $mail->failure_count ) );
						$page        = 'blfpst-send-mail-histories';
					}

					$url = esc_url( admin_url( "admin.php?page=${page}&admin_action=info&send_mail_id=${send_mail_id}" ) );

					$send_data['target_count']  = $total_count;
					$send_data['subject']       = $subject;
					$send_data['url']           = $url;
					$send_data['reserved_time'] = $reserved_time;

					array_push( $day_send_list, $send_data );
				}

				date_add( $a_datetime, DateInterval::createFromDateString( '1 days' ) );

				$day_data['date']             = $current_date_string;
				$day_data['background_color'] = $background_color;
				$day_data['sendData']         = $day_send_list;
				$day_data['is_future']        = $is_future ? 'true' : 'false';
				$day_data['register_url']     = $register_url;

				array_push( $calendar_data, $day_data );
			}
		}

		$response                         = array();
		$response['current_month_string'] = $current_month_string;
		$response['prev_month_url']       = $prev_month_url;
		$response['next_month_url']       = $next_month_url;
		$response['calendar_data']        = $calendar_data;

		return $response;
	}

	/**
	 * delete history
	 *
	 * @param int $send_mail_id
	 *
	 * @return int | false
	 */
	public static function history_delete( $send_mail_id ) {

		$result = self::move_to_trash( $send_mail_id );

		return $result;
	}

	/**
	 * delete history
	 *
	 * @param int $send_mail_id
	 *
	 * @return WP_Error
	 */
	public static function move_to_draft( $send_mail_id ) {
		$result = true;

		if ( $send_mail_id > 0 ) {

			// To draft
			$status = 'draft';

			$values = array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql', 0 ),
			);

			$format = array(
				'%s',
				'%s',
			);

			/** @var wpdb $wpdb */
			global $wpdb;
			$table_name = BLFPST_Model_Send_Mail::table_name();

			$result = $wpdb->update( $table_name, $values, array( 'id' => $send_mail_id ), $format, array( '%d' ) );
		}

		return $result;
	}

	/**
	 * delete history
	 *
	 * @param int $send_mail_id
	 *
	 * @return int | false
	 */
	public static function move_to_trash( $send_mail_id ) {
		$result = 0;

		if ( $send_mail_id > 0 ) {

			// To trash
			$status = 'deleted';

			$values = array(
				'status'     => $status,
				'deleted_at' => current_time( 'mysql', 0 ),
				'updated_at' => current_time( 'mysql', 0 ),
			);

			$format = array(
				'%s',
				'%s',
				'%s',
			);

			/** @var wpdb $wpdb */
			global $wpdb;
			$table_name = BLFPST_Model_Send_Mail::table_name();

			$result = $wpdb->update( $table_name, $values, array( 'id' => $send_mail_id ), $format, array( '%d' ) );
		}

		return $result;
	}

	/**
	 * Default options setting
	 *
	 * @return void
	 */
	public static function set_default_option() {
		// Bounce e-mail address
		$error_address = BLFPST::get_option( 'error_address' );
		if ( ! $error_address ) {
			BLFPST::update_option( 'error_address', '' );
		}

		// Send type
		$mailer_type = BLFPST::get_option( 'mailer_type' );
		if ( ! $mailer_type ) {
			BLFPST::update_option( 'mailer_type', 'mail' );
		}

		// sendmail command path
		$sendmail_path = BLFPST::get_option( 'sendmail_path' );
		if ( ! $sendmail_path ) {
			BLFPST::update_option( 'sendmail_path', '/usr/sbin/sendmail' );
		}

		// SMTP host
		$smtp_host = BLFPST::get_option( 'smtp_host' );
		if ( ! $smtp_host ) {
			BLFPST::update_option( 'smtp_host', '' );
		}

		// SMTP port number
		$smtp_port = BLFPST::get_option( 'smtp_port' );
		if ( ! $smtp_port ) {
			BLFPST::update_option( 'smtp_port', '25' );
		}

        // SMTP SMTPSecure
        $smtp_secure = BLFPST::get_option( 'smtp_secure' );
        if ( ! $smtp_secure ) {
            BLFPST::update_option( 'smtp_secure', '' );
        }

        // SMTP SMTPAuth
        $smtp_auth = BLFPST::get_option( 'smtp_auth' );
        if ( ! $smtp_auth ) {
            BLFPST::update_option( 'smtp_auth', 'false' );
        }

        // SMTP User Name
        $smtp_user_name = BLFPST::get_option( 'smtp_user_name' );
        if ( ! $smtp_user_name ) {
            BLFPST::update_option( 'smtp_user_name', '' );
        }

        // SMTP Password
        $smtp_password = BLFPST::get_option( 'smtp_password' );
        if ( ! $smtp_password ) {
            BLFPST::update_option( 'smtp_password', '' );
        }

		// E-mail charset
		$blog_charset         = get_bloginfo( 'charset' );
		$mail_content_charset = BLFPST::get_option( 'mail_content_charset' );
		if ( ! $mail_content_charset ) {
			BLFPST::update_option( 'mail_content_charset', $blog_charset );
		}

		// Transmission speed limits
		$transmission_speed_limit_count = BLFPST::get_option( 'transmission_speed_limit_count' );
		if ( ! $transmission_speed_limit_count ) {
			BLFPST::update_option( 'transmission_speed_limit_count', '0' );
		}

		$transmission_speed_limit_time = BLFPST::get_option( 'transmission_speed_limit_time' );
		if ( ! $transmission_speed_limit_time ) {
			BLFPST::update_option( 'transmission_speed_limit_time', '0' );
		}

		$db_lock = get_option( '_blfpst_db_lock' );
		if ( ! $db_lock ) {
			update_option( '_blfpst_db_lock', '0' );
		}
	}

	/**
	 *
	 * load mail from information
	 *
	 * @return array
	 */
	public static function load_mail_froms() {
		$mail_froms = array();
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Mail_From::table_name();
		$sql        = "SELECT * FROM ${table_name} ORDER BY updated_at DESC";

		$results = $wpdb->get_results( $sql );
		if ( null === $results ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			foreach ( $results as $result ) {
				$mail_from = new BLFPST_Model_Mail_From();
				$mail_from->set_result( $result );
				array_push( $mail_froms, $mail_from );
			}
		}

		return $mail_froms;
	}

	/**
	 * draft mail count
	 *
	 * @return int
	 */
	public static function load_draft_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT count(*) FROM ${table_name} WHERE (status='draft') AND (send_request_start_at IS NULL)";
		$count      = $wpdb->get_var( $sql );

		return (int) $count;
	}

	/**
	 * load draft mails
	 *
	 * @param int $page_num
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function load_drafts( $page_num = - 1, $limit = 0 ) {
		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT * FROM ${table_name} WHERE (status='draft') AND (send_request_start_at IS NULL) ORDER BY updated_at DESC";
		$mails      = self::load_send_mails_with_sql( $sql, $table_name, $page_num, $limit );

		return $mails;
	}

	/**
	 * load draft mail
	 *
	 * @param int $send_mail_id
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_draft( $send_mail_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (status='draft') AND (send_request_start_at IS NULL) AND (id='%d') ", $send_mail_id );
		$mails      = self::load_send_mail_with_sql( $sql, $table_name );

		return $mails;
	}

	/**
	 * load reserved mail count
	 *
	 * @return int
	 */
	public static function load_reserves_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT count(*) FROM ${table_name} WHERE (status='reserved') AND (reserved_at IS NOT NULL) AND (send_request_start_at IS NULL)";
		$count      = (int) $wpdb->get_var( $sql );

		return $count;
	}

	/**
	 * load reserved mails
	 *
	 * @param int $page_num
	 * @param int $limit
	 * @param boolean $desc
	 *
	 * @return array
	 */
	public static function load_reserves( $page_num = - 1, $limit = 0, $desc = true ) {
		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT * FROM ${table_name} WHERE (status='reserved') AND (reserved_at IS NOT NULL) AND (send_request_start_at IS NULL) ORDER BY reserved_at";

		if ( $desc ) {
			$sql .= ' DESC';
		}

		$mails = self::load_send_mails_with_sql( $sql, $table_name, $page_num, $limit );

		return $mails;
	}

	/**
	 * load reserved mail
	 *
	 * @param int $send_mail_id
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_reserve( $send_mail_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (status='reserved') AND (reserved_at IS NOT NULL) AND (send_request_start_at IS NULL) AND (id='%d')", $send_mail_id );
		$mails      = self::load_send_mail_with_sql( $sql, $table_name );

		return $mails;
	}

	/**
	 * load reserved mail
	 *
	 * @param int $send_mail_id
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_sending_reserve( $send_mail_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (status='reserved') AND (reserved_at IS NOT NULL) AND (send_request_start_at IS NOT NULL) AND (send_request_end_at IS NULL) AND (id='%d')", $send_mail_id );
		$mails      = self::load_send_mail_with_sql( $sql, $table_name );

		return $mails;
	}

	/**
	 * load sending mail count
	 *
	 * @return int
	 */
	public static function load_sending_mails_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT count(*) FROM ${table_name} WHERE (send_result='wait') AND (send_request_start_at IS NOT NULL) AND (send_request_end_at IS NULL)";
		$count      = (int) $wpdb->get_var( $sql );

		return $count;
	}

	/**
	 * load sending mails
	 *
	 * @param int $page_num
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function load_sending_mails( $page_num = - 1, $limit = 0 ) {
		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT * FROM ${table_name} WHERE (send_result='wait') AND (send_request_start_at IS NOT NULL) AND (send_request_end_at IS NULL) ORDER BY reserved_at DESC";
		$mails      = self::load_send_mails_with_sql( $sql, $table_name, $page_num, $limit );

		return $mails;
	}

	/**
	 * load sending mail
	 *
	 * @param int $send_mail_id
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_sending_mail( $send_mail_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (send_result='wait') AND (send_request_start_at IS NOT NULL) AND (send_request_end_at IS NULL) AND (id='%d')", $send_mail_id );
		$mails      = self::load_send_mail_with_sql( $sql, $table_name );

		return $mails;
	}

	/**
	 * load reserved mail
	 *
	 * @param int $send_mail_id
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_mail( $send_mail_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE ((status='send') OR (status='reserved')) AND (deleted_at IS NULL) AND (id='%d')", $send_mail_id );
		$mails      = self::load_send_mail_with_sql( $sql, $table_name );

		return $mails;
	}

	/**
	 * load reserved mails
	 *
	 * @param Datetime $datetime date.
	 *
	 * @return array
	 */
	public static function load_mails_at_date( $datetime ) {
		$time_zone    = blfpst_get_wp_timezone();
		$end_datetime = new DateTime( $datetime->format( 'Y-m-d' ), $time_zone );

		$start_param = $datetime->format( 'Y-m-d' );
		date_add( $end_datetime, DateInterval::createFromDateString( '1 days' ) );

		$end_param = $end_datetime->format( 'Y-m-d' );

		/** @var wpdb $wpdb */
		global $wpdb;

		// Exclude trash, draft
		// Reservation mail is reserved date. other mail is starting request date.
		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (deleted_at IS NULL) AND (status<>'draft') AND (((status='reserved') AND (reserved_at>='%s') AND (reserved_at<'%s')) OR ((status<>'reserved') AND (send_request_start_at>='%s') AND (send_request_start_at<'%s')))", $start_param, $end_param, $start_param, $end_param );
		$mails      = self::load_send_mails_with_sql( $sql, $table_name );

		return $mails;
	}

	/**
	 * load deleted mail count
	 *
	 * @return int
	 */
	public static function load_deleted_mails_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT count(*) FROM ${table_name} WHERE (status='deleted') AND (deleted_at IS NOT NULL)";
		$count      = (int) $wpdb->get_var( $sql );

		return $count;
	}

	/**
	 * load deleted mails
	 *
	 * @param int $page_num
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function load_deleted_mails( $page_num = - 1, $limit = 0 ) {
		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT * FROM ${table_name} WHERE (status='deleted') AND (deleted_at IS NOT NULL) ORDER BY deleted_at DESC";
		$mails      = self::load_send_mails_with_sql( $sql, $table_name, $page_num, $limit );

		return $mails;
	}

	/**
	 * load send mail history count
	 *
	 * @return int
	 */
	public static function load_history_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT count(*) FROM ${table_name} WHERE (status='send') AND (send_request_start_at IS NOT NULL)";
		$count      = (int) $wpdb->get_var( $sql );

		return $count;
	}

	/**
	 * load send mail history
	 *
	 * @param int $page_num
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function load_histories( $page_num = - 1, $limit = 0 ) {
		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT * FROM ${table_name} WHERE (status='send') AND (send_request_start_at IS NOT NULL) ORDER BY send_request_start_at DESC";
		$mails      = self::load_send_mails_with_sql( $sql, $table_name, $page_num, $limit );

		return $mails;
	}

	/**
	 * load send mail history
	 *
	 * @param int $send_mail_id
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_history( $send_mail_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (status='send') AND (send_request_start_at IS NOT NULL) AND (id='%d')", $send_mail_id );

		$mail = self::load_send_mail_with_sql( $sql, $table_name );

		return $mail;
	}

	/**
	 * load trash mail
	 *
	 * @param int $send_mail_id
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_trash( $send_mail_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (status='deleted') AND (deleted_at IS NOT NULL) AND (id='%d')", $send_mail_id );

		$mails = self::load_send_mail_with_sql( $sql, $table_name );

		return $mails;
	}

	/**
	 * load failure mail count
	 *
	 * @return int
	 */
	public static function load_failures_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT count(*) FROM ${table_name} WHERE (send_result='failure') AND (deleted_at IS NULL)";
		$count      = (int) $wpdb->get_var( $sql );

		return $count;
	}

	/**
	 * load failure mails
	 *
	 * @param int $page_num
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function load_failures( $page_num = - 1, $limit = 0 ) {
		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "SELECT * FROM ${table_name} WHERE (send_result='failure') AND (deleted_at IS NULL) ORDER BY updated_at DESC";
		$mails      = self::load_send_mails_with_sql( $sql, $table_name, $page_num, $limit );

		return $mails;
	}

	/**
	 * load send mail count with target_id
	 *
	 * @param integer $target_id
	 *
	 * @return array
	 */
	public static function load_send_mails_with_target_id( $target_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (target_id='%d') AND ((status='reserved') OR (status='send')) AND (send_request_end_at IS NULL)", $target_id );

		$mails = self::load_send_mails_with_sql( $sql, $table_name, - 1, 0 );

		return $mails;
	}

	/**
	 * load send mail data
	 *
	 * @param string $sql
	 * @param string $table_name
	 *
	 * @return BLFPST_Model_Send_Mail
	 */
	public static function load_send_mail_with_sql( $sql, $table_name ) {
		$mail = new BLFPST_Model_Send_Mail();

		/** @var wpdb $wpdb */
		global $wpdb;

		$results = $wpdb->get_results( $sql );
		if ( null === $results ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			if ( count( $results ) > 0 ) {
				$result = $results[0];
				$mail->set_result( $result );
			}
		}

		return $mail;
	}

	/**
	 * load send mails data
	 *
	 * @param string $sql
	 * @param string $table_name
	 * @param int $page_num
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function load_send_mails_with_sql( $sql, $table_name, $page_num = - 1, $limit = 0 ) {
		$mails = array();

		/** @var wpdb $wpdb */
		global $wpdb;

		if ( $page_num < 0 ) {
			$query = $sql;
		} else {
			$query = $sql . $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
		};

		$results = $wpdb->get_results( $query );
		if ( null === $results ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			foreach ( $results as $result ) {

				$mail = new BLFPST_Model_Send_Mail();
				$mail->set_result( $result );
				array_push( $mails, $mail );
			}
		}

		return $mails;
	}

	/**
	 * save send mails data
	 *
	 * @param BLFPST_Model_Send_Mail $send_mail
	 *
	 * @return int|false
	 */
	public static function save( &$send_mail ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Send_Mail::table_name();

		$is_reserved_mail = ! empty( $send_mail->reserved_at );

		if ( 0 == $send_mail->id ) {
			$values = array(
				'user_id'     => get_current_user_id(),
				'post_id'     => $send_mail->post_id,
				'target_id'   => $send_mail->target_id,
				'target_name' => $send_mail->target_name,
				'status'      => $send_mail->status,

				'subject'       => $send_mail->subject,
				'text_content'  => $send_mail->text_content,
				'html_content'  => $send_mail->html_content,
				'from_name'     => $send_mail->from_name,
				'from_address'  => $send_mail->from_address,
				'reply_address' => $send_mail->reply_address,

				'create_code'     => $send_mail->create_code,
				'send_result'     => 'wait',
				'recipient_count' => 0,
				'success_count'   => 0,
				'failure_count'   => 0,
				'updated_at'      => current_time( 'mysql', 0 ),
				'created_at'      => current_time( 'mysql', 0 ),
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
				'%s',
				'%s',
				'%s',

				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
				'%s',
			);

			if ( $is_reserved_mail ) {
				$values = array_merge( $values, array( 'reserved_at' => $send_mail->reserved_at ) );
				array_push( $format, '%s' );
			}

			$result = $wpdb->insert( $table_name, $values, $format );

			if ( $result ) {
				$send_mail->id = $wpdb->insert_id;
			}
		} else {

			$values = array(
				'user_id'     => get_current_user_id(),
				'post_id'     => $send_mail->post_id,
				'target_id'   => $send_mail->target_id,
				'target_name' => $send_mail->target_name,
				'status'      => $send_mail->status,

				'subject'       => $send_mail->subject,
				'text_content'  => $send_mail->text_content,
				'html_content'  => $send_mail->html_content,
				'from_name'     => $send_mail->from_name,
				'from_address'  => $send_mail->from_address,
				'reply_address' => $send_mail->reply_address,

				'create_code'     => $send_mail->create_code,
				'send_result'     => 'wait',
				'recipient_count' => 0,
				'success_count'   => 0,
				'failure_count'   => 0,
				'updated_at'      => current_time( 'mysql', 0 ),
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
				'%s',
				'%s',
				'%s',

				'%s',
				'%s',
				'%d',
				'%d',
				'%d',
				'%s',
			);

			if ( $is_reserved_mail ) {
				$values = array_merge( $values, array( 'reserved_at' => $send_mail->reserved_at ) );
				array_push( $format, '%s' );
			}

			$result = $wpdb->update( $table_name, $values, array( 'id' => $send_mail->id ), $format, array( '%d' ) );
		}

		if ( ! $result ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		}

		return $result;
	}

	/**
	 * save send mails data
	 *
	 * @return string
	 */
	public static function load_test_targets() {

		$return_mail_address = '';
		$json_test_targets   = BLFPST::get_option( 'test_targets', '' );
		$test_targets        = json_decode( $json_test_targets );

		if ( is_array( $test_targets ) ) {
			foreach ( $test_targets as $test_target ) {
				$mail_address = isset( $test_target->mail_address ) ? esc_textarea( $test_target->mail_address ) . '&#13' : '';
				$return_mail_address .= $mail_address;
			}

			$return_mail_address = rtrim( $return_mail_address, '&#13' );
		}

		return $return_mail_address;
	}

	/**
	 * save send mails data
	 *
	 * @param string $in_mail_address
	 *
	 * @return void
	 */
	public static function save_test_targets( $in_mail_address ) {

		$mail_addresses = preg_split( '/\r\n|\r|\n/', $in_mail_address, - 1, PREG_SPLIT_NO_EMPTY );
		$mail_addresses = array_map( 'trim', $mail_addresses );
		$mail_addresses = array_filter( $mail_addresses, 'strlen' ); // empty line

		$save_mail_addresses = array();

		foreach ( $mail_addresses as $mail_address ) {
			$mail_address = trim( $mail_address );
			$data         = array(
				'mail_address'    => $mail_address,
				'user_name'       => '',
				'user_last_name'  => '',
				'user_first_name' => '',
			);
			array_push( $save_mail_addresses, $data );
		}

		$json_save_mail_addresses = json_encode( $save_mail_addresses );
		BLFPST::update_option( 'test_targets', $json_save_mail_addresses );
	}

	/**
	 * send test mail
	 *
	 * @param BLFPST_Model_Send_Mail $send_mail
	 * @param string $test_targets
	 *
	 * @return WP_Error
	 */
	public static function test_send( $send_mail, $test_targets ) {
		$errors = new WP_Error();

		$target_id     = isset( $send_mail->target_id ) ? $send_mail->target_id : 0;
		$from_name     = isset( $send_mail->from_name ) ? $send_mail->from_name : '';
		$from_address  = isset( $send_mail->from_address ) ? $send_mail->from_address : '';
		$reply_address = isset( $send_mail->reply_address ) ? $send_mail->reply_address : '';
		$subject       = isset( $send_mail->subject ) ? $send_mail->subject : '';
		$text_content  = isset( $send_mail->text_content ) ? $send_mail->text_content : '';
		$html_content  = isset( $send_mail->htmlcontent ) ? $send_mail->htmlcontent : '';
		$test_targets  = isset( $test_targets ) ? $test_targets : array();

		BLFPST_Send_Mails_Controller::validate_mail_data( $_POST, $errors, false );

		if ( empty( $test_targets ) ) {
			$errors->add( 'Error', esc_html__( 'Please enter a test mail receiver.', 'bluff-post' ) );
		}

		if ( 0 == count( $errors->errors ) ) {

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

		return $errors;
	}

	/**
	 * create create-code
	 *
	 * @return string
	 */
	public static function create_create_code() {
		$now             = new DateTime();
		$timestamp       = $now->getTimestamp();
		$current_user_id = get_current_user_id();
		$seed            = sprintf( '%s.%d', $timestamp, $current_user_id );
		$create_code     = md5( $seed );

		return $create_code;
	}

	/**
	 * create-code exist check
	 *
	 * @param  string $create_code
	 *
	 * @return boolean
	 */
	public static function is_exist_create_code( $create_code ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (create_code='%s') AND (`updated_at` > ( NOW( ) - INTERVAL 1 DAY ))", $create_code );

		$count = $wpdb->get_var( $sql );
		$count = ( false === $count ) ? 0 : $count;

		return ( 0 < $count );
	}

	public static function execute_post_mail() {

		if ( BLFPST_USE_DB_LOCK ) {
			if ( self::blfpst_db_lock() ) {
				return;
			}
		}

		$time_zone = blfpst_get_wp_timezone();
		$now       = new DateTime( 'now', $time_zone );

		$mails = self::load_reserved_mails( $now );

		if ( ! empty( $mails ) ) {
			//error_log( 'BLFPST_Send_Mails_Controller::execute_post_mail start ' );

			/** @var BLFPST_Model_Send_Mail $mail */
			foreach ( $mails as $mail ) {
				BLFPST_Send_Mail::start_send( $mail, $mail->id, 0 );
			}

			//error_log( 'BLFPST_Send_Mails_Controller::execute_post_mail end ' );
		}

		if ( BLFPST_USE_DB_LOCK ) {
			self::blfpst_db_unlock();
		}
	}

	/**
	 * load reserved mails
	 *
	 * @param DateTime $now
	 *
	 * @return array
	 */
	private static function load_reserved_mails( $now ) {
		$mails = array();

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Send_Mail::table_name();

		$sql   = "SELECT * FROM ${table_name} WHERE (status='reserved') AND (reserved_at IS NOT NULL) AND (send_request_start_at IS NULL) AND (reserved_at<='%s') ORDER BY reserved_at";
		$query = $wpdb->prepare( $sql, $now->format( 'Y-m-d H:i:s' ) );

		$results = $wpdb->get_results( $query );

		if ( null === $results ) {

			error_log( 'Post_Mail::load_reserved_mails: DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
			BLFPST_Logs_Controller::error_log( esc_html__( 'Reservations mail transmission request error', 'bluff-post' ), esc_html__( 'An error occurred in the DB access at the time of booking read.', 'bluff-post' ) . $wpdb->last_error, 'Post_Mail::load_reserved_mails', 0, 0 );

		} else {

			foreach ( $results as $result ) {
				$mail = new BLFPST_Model_Send_Mail();
				$mail->set_result( $result );
				array_push( $mails, $mail );
			}
		}

		return $mails;
	}

	private static function blfpst_db_lock() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$table = $wpdb->prefix . 'options';
		$wpdb->query( 'begin;' );

		$results = $wpdb->get_results(
			"SELECT option_value FROM ${table} WHERE (option_name='_blfpst_db_lock') FOR UPDATE;"
		);

		$is_locked = false;

		if ( 0 == count( $results ) ) {
			// Insert default
			$wpdb->insert(
				$table,
				array(
					'option_name'  => '_blfpst_db_lock',
					'option_value' => '0',
					'autoload'     => 'no',
				),
				array(
					'%s',
					'%s',
					'%s',
				)
			);
		} else {
			$result       = $results[0];
			$option_value = $result->option_value;

			if ( 1 == $option_value ) {
				$is_locked = 1;
			}
		}

		if ( ! $is_locked ) {
			$wpdb->update(
				$table,
				array(
					'option_value' => '1',
				),
				array( 'option_name' => '_blfpst_db_lock' ),
				array(
					'%s',
				),
				array( '%s' )
			);
		}

		$wpdb->query( 'commit' );

		return $is_locked;
	}

	private static function blfpst_db_unlock() {
		/** @var wpdb $wpdb */
		global $wpdb;
		$table = $wpdb->prefix . 'options';
		$wpdb->query( 'begin;' );

		$results = $wpdb->get_results(
			"SELECT option_id FROM ${table} WHERE (option_name='_blfpst_db_lock') AND (option_value='1') FOR UPDATE;"
		);

		if ( 0 < count( $results ) ) {
			$wpdb->update(
				$table,
				array(
					'option_value' => '0',
				),
				array( 'option_name' => '_blfpst_db_lock' ),
				array(
					'%s',
				),
				array( '%s' )
			);
		}

		$wpdb->query( 'commit' );
	}

	//private static function sem_lock() {
	//	$enable_sem = function_exists( 'ftok' ) && function_exists( 'sem_get' ) && function_exists( 'sem_acquire' ) && function_exists( 'sem_release' );
	//
	//	$sem = sem_get( ftok( __FILE__, 'p' ), 1 );
	//
	//	if ( false == $sem ) {
	//		error_log( 'sem_get failed' );
	//		exit;
	//	}
	//
	//	if ( ! sem_acquire( $sem ) ) {
	//		error_log( 'sem_acquire failed' );
	//		exit;
	//	}
	//
	//	return $sem;
	//}
	//
	//private static function sem_unlock( $sem ) {
	//	if ( ! sem_release( $sem ) ) {
	//		error_log( 'sem_release failed' );
	//	}
	//}
}
