<?php

/**
 * PHP Version 5.4.0
 * Version 1.0.0
 * Date: 2016/09/29
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 * */
class BLFPST_Subscribe_Controller {
	/**
	 * request from ajax
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	public static function request_subscribe( $email ) {

		$errors = self::request_subscribe_mail_magazine( $email );

		if ( 0 == count( $errors->get_error_messages() ) ) {
			$result  = 'success';
			$message = '';
		} else {
			$result   = 'error';
			$messages = $errors->get_error_messages();
			$message  = $messages[0];
		}

		$return_object = array( 'result' => $result, 'message' => $message );

		return $return_object;
	}

	/**
	 * request from ajax
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	public static function request_unsubscribe( $email ) {
		$errors = self::request_unsubscribe_mail_magazine( $email );

		if ( 0 == count( $errors->get_error_messages() ) ) {
			$result  = 'success';
			$message = '';
		} else {
			$result   = 'error';
			$messages = $errors->get_error_messages();
			$message  = $messages[0];
		}

		$return_object = array( 'result' => $result, 'message' => $message );

		return $return_object;
	}

	/**
	 * subscribe mail magazine
	 *
	 * @param string $email
	 *
	 * @return WP_Error
	 */
	private static function request_subscribe_mail_magazine( $email ) {

		$errors = new WP_Error();

		if ( self::validation( $errors, $_POST ) ) {
			self::add_mail_address( $email );
		}

		return $errors;
	}

	/**
	 * unsubscribe mail magazine
	 *
	 * @param string $email
	 *
	 * @return WP_Error
	 */
	private static function request_unsubscribe_mail_magazine( $email ) {

		$errors = new WP_Error();

		if ( self::validation( $errors, $_POST ) ) {
			self::delete_mail_address( $email );
		}

		return $errors;
	}

	/**
	 * add mail address
	 *
	 * @param string $mail_address
	 *
	 * @return boolean
	 */
	private static function add_mail_address( $mail_address ) {

		$result = false;

		switch ( self::target_type() ) {
			case 'wordpress':
				$result = self::add_mail_address_for_wordpress( $mail_address );
				break;
			case 'bluffmail':
				$result = self::add_mail_address_for_bluffmail( $mail_address );
				break;
		}

		return $result;
	}

	/**
	 * add mail address for WordPress
	 *
	 * @param string $mail_address
	 *
	 * @return boolean
	 */
	private static function add_mail_address_for_wordpress( $mail_address ) {

		$sanitized_user_login = sanitize_user( $mail_address );

		// 登録済みチェック
		$user_id = username_exists( $sanitized_user_login );

		if ( $user_id ) {
			update_user_meta( $user_id, '_blfpst_subscribe_mailmagazine', 'subscribe' );

			return true;
		}

		$user_pass = wp_generate_password();

		$user_id = wp_insert_user( array(
				'user_login' => $sanitized_user_login,
				'user_email' => $mail_address,
				'role'       => 'subscriber',
				'user_pass'  => $user_pass,
			)
		);

		if ( $user_id instanceof WP_Error ) {
			return false;
		}

		// option subscribe mailmagazine
		// 外部Systemから本プラグインで登録されたユーザーであるかをチェックするためのメタ情報
		add_user_meta( $user_id, '_blfpst_subscribe_mailmagazine', 'subscribe', true );
		add_user_meta( $user_id, '_blfpst_text_only', 0, true );

		return true;
	}

	/**
	 * add mail address for Bluff Mail
	 *
	 * @param string $mail_address
	 *
	 * @return boolean
	 */
	private static function add_mail_address_for_bluffmail( $mail_address ) {

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Recipient::table_name();

		$sql           = $wpdb->prepare( "SELECT ${table_name}.id FROM ${table_name} WHERE (${table_name}.mail_address='%s') ", $mail_address );
		$recipients_id = $wpdb->get_var( $sql );

		if ( false === $recipients_id ) {
			return false;
		}

		if ( empty( $recipients_id ) ) {
			$wpdb->insert(
				$table_name,
				array(
					'mail_address' => $mail_address,
					'first_name'   => '',
					'last_name'    => '',
					'status'       => 'subscribe',
					'updated_at'   => current_time( 'mysql', 0 ),
					'created_at'   => current_time( 'mysql', 0 ),
				),
				array(
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);
		} else {
			$wpdb->update(
				$table_name,
				array(
					'status'     => 'subscribe',
					'updated_at' => current_time( 'mysql', 0 ),
				),
				array( 'ID' => $recipients_id ),
				array(
					'%s', // status
					'%s', // updated_at
				),
				array( '%d' )
			);
		}

		return true;
	}

	/**
	 * delete mail address
	 *
	 * @param string $mail_address
	 *
	 * @return void
	 */
	private static function delete_mail_address( $mail_address ) {

		switch ( self::target_type() ) {
			case 'wordpress':
				self::delete_mail_address_for_wordpress( $mail_address );
				break;
			case 'bluffmail':
				self::delete_mail_address_for_bluffmail( $mail_address );
				break;
		}
	}

	/**
	 * delete mail address for WordPress
	 *
	 * @param string $mail_address
	 *
	 * @return void
	 */
	private static function delete_mail_address_for_wordpress( $mail_address ) {

		$sanitized_user_login = sanitize_user( $mail_address );
		$user_id              = username_exists( $sanitized_user_login );

		// 外部システムが使用中のwp_usersデータの可能性があるので、メタ情報の更新のみ行う
		if ( $user_id ) {
			update_user_meta( $user_id, '_blfpst_subscribe_mailmagazine', 'unsubscribe' );
		}
	}

	/**
	 * delete mail address for Bluff Mail
	 *
	 * @param string $mail_address
	 *
	 * @return boolean
	 */
	private static function delete_mail_address_for_bluffmail( $mail_address ) {

		$result = false;

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Recipient::table_name();

		$sql           = $wpdb->prepare( "SELECT ${table_name}.id FROM ${table_name} WHERE (${table_name}.mail_address='%s') ", $mail_address );
		$recipients_id = $wpdb->get_var( $sql );

		if ( ! empty( $recipients_id ) ) {
			$wpdb->update(
				$table_name,
				array(
					'status'     => 'unsubscribe',
					'updated_at' => current_time( 'mysql', 0 ),
				),
				array( 'ID' => $recipients_id ),
				array(
					'%s', // status
					'%s', // updated_at
				),
				array( '%d' )
			);

			$result = true;
		}

		return $result;
	}

	/**
	 * validate mail parameter
	 *
	 * @param WP_Error $errors
	 * @param array $post
	 *
	 * @return boolean
	 */
	private static function validation( $errors, $post ) {

		$email = isset( $post['email'] ) ? $post['email'] : '';
		$email = trim( $email );

		if ( '' === $email ) {
			$errors->add( 'Error', esc_html__( 'Please enter a e-mail address.', 'bluff-post' ) );
		}

		if ( ( '' !== $email ) && ( ! is_email( $email ) ) ) {
			$errors->add( 'Error', esc_html__( 'The e-mail address you entered is incorrect.', 'bluff-post' ) );
		}

		return ( 0 == count( $errors->get_error_messages() ) );
	}

	/**
	 * get target type
	 *
	 * @return string
	 */
	private static function target_type() {

		$dummy    = new BLFPST_Subscribe_Widget();
		$settings = $dummy->get_settings();

		$type = 'wordpress';

		foreach ( $settings as $setting ) {
			if ( isset( $setting['type'] ) ) {
				$type = $setting['type'];
				break;
			}
		}

		return $type;
	}
}
