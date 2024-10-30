<?php

/**
 * administrator menu controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Admin_Menu_Controller {
	public function initialize_admin_menu() {
		add_action( 'admin_menu', array( $this, 'execute_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
	}

	public function execute_admin_menu() {
		// Send Mail
		$send_mail_view_controller = new BLFPST_Send_Mails_View_Controller();

		$send_mail_base_name = 'blfpst-send-mail';
		$page_hook = add_menu_page( esc_html__( 'Mail Box', 'bluff-post' ), esc_html__( 'Mail Box', 'bluff-post' ), 'blfpst_edit_mail', $send_mail_base_name, array(
			&$send_mail_view_controller,
			'index_routing',
		), 'dashicons-email-alt' );

		add_action( 'admin_print_scripts-' . $page_hook, array(
			&$send_mail_view_controller,
			'print_scripts_create_view',
		) );

		$page_hook = add_submenu_page( $send_mail_base_name, esc_html__( 'Calendar', 'bluff-post' ), esc_html__( 'Calendar', 'bluff-post' ), 'blfpst_edit_mail', $send_mail_base_name, array(
			$send_mail_view_controller,
			'index_routing',
		) );

		add_action( 'admin_print_scripts-' . $page_hook, array(
			&$send_mail_view_controller,
			'print_scripts_create_view',
		) );

		$page_hook = add_submenu_page( $send_mail_base_name, esc_html__( 'Create e-mail', 'bluff-post' ), esc_html__( 'Create e-mail', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-send-mail-crate', array(
			$send_mail_view_controller,
			'create_routing',
		) );

		add_action( 'admin_print_scripts-' . $page_hook, array(
			&$send_mail_view_controller,
			'print_scripts_create_view',
		) );

		add_submenu_page( $send_mail_base_name, esc_html__( 'Reservation list', 'bluff-post' ), esc_html__( 'Reservation list', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-send-mail-reserves', array(
			$send_mail_view_controller,
			'reserves_routing',
		) );

		add_submenu_page( $send_mail_base_name, esc_html__( 'Sending e-mail', 'bluff-post' ), esc_html__( 'Sending e-mail', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-send-mail-sending', array(
			$send_mail_view_controller,
			'sending_routing',
		) );

		add_submenu_page( $send_mail_base_name, esc_html__( 'Outbox', 'bluff-post' ), esc_html__( 'Outbox', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-send-mail-histories', array(
			$send_mail_view_controller,
			'histories_routing',
		) );

		add_submenu_page( $send_mail_base_name, esc_html__( 'Drafts', 'bluff-post' ), esc_html__( 'Drafts', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-send-mail-drafts', array(
			$send_mail_view_controller,
			'drafts_routing',
		) );

		add_submenu_page( $send_mail_base_name, esc_html__( 'Failure', 'bluff-post' ), esc_html__( 'Failure', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-send-mail-failures', array(
			$send_mail_view_controller,
			'failures_routing',
		) );

		add_submenu_page( $send_mail_base_name, esc_html__( 'Trash', 'bluff-post' ), esc_html__( 'Trash', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-send-mail-trashes', array(
			$send_mail_view_controller,
			'trashes_routing',
		) );

		// Log
		$log_controller = new BLFPST_Logs_Controller();
		add_submenu_page( $send_mail_base_name, esc_html__( 'Logs', 'bluff-post' ), esc_html__( 'Logs', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-logs', array(
			$log_controller,
			'index',
		) );

		// Mail Template
		$mail_template_base_name = 'blfpst-mail-template';
		$mail_template_view_controller = new BLFPST_Mail_Templates_View_Controller();

		$page_hook = add_menu_page( esc_html__( 'Mail templates', 'bluff-post' ), esc_html__( 'Mail templates', 'bluff-post' ), 'blfpst_edit_mail', $mail_template_base_name, array(
			&$mail_template_view_controller,
			'index_routing',
		), 'dashicons-email-alt' );

		add_action( 'admin_print_scripts-' . $page_hook, array(
			&$mail_template_view_controller,
			'print_scripts_create_view',
		) );

		$page_hook = add_submenu_page( $mail_template_base_name, esc_html__( 'Registration template', 'bluff-post' ), esc_html__( 'Registration template', 'bluff-post' ), 'blfpst_edit_mail', 'blfpst-mail-template-create', array(
			$mail_template_view_controller,
			'create_routing',
		) );

		add_action( 'admin_print_scripts-' . $page_hook, array(
			&$mail_template_view_controller,
			'print_scripts_create_view',
		) );

		// Recipients
		$targets_base_name = 'blfpst-targets';
		$mail_target_view_controller = new BLFPST_Target_View_Controller();

		add_menu_page( esc_html__( 'Recipients', 'bluff-post' ), esc_html__( 'Recipients', 'bluff-post' ), 'blfpst_edit_target', $targets_base_name, array(
			&$mail_target_view_controller,
			'index_routing',
		), 'dashicons-email-alt' );

		$page_hook = add_submenu_page( $targets_base_name, esc_html__( 'Registration recipient', 'bluff-post' ), esc_html__( 'Registration recipient', 'bluff-post' ), 'blfpst_edit_target', 'blfpst-target-register', array(
			$mail_target_view_controller,
			'register_routing',
		) );

		add_action( 'admin_print_scripts-' . $page_hook, array(
			&$mail_target_view_controller,
			'print_scripts_create_view',
		) );

		add_submenu_page( $targets_base_name, esc_html__( 'Exclusion receivers list', 'bluff-post' ), esc_html__( 'Exclusion recipients', 'bluff-post' ), 'blfpst_edit_target', 'blfpst-target-exclude-recipient', array(
			$mail_target_view_controller,
			'exclude_recipient_routing',
		) );

		// Options
		add_options_page( esc_html__( 'Bluff Post', 'bluff-post' ), esc_html__( 'Bluff Post', 'bluff-post' ), 'manage_options', 'blfpst-target-option', array(
			$mail_target_view_controller,
			'option_routing',
		) );

	}

	public function admin_init() {
		if ( isset( $_POST['blfpst_target_option'] ) && $_POST['blfpst_target_option'] ) {

			if ( check_admin_referer( 'blfpst-target-option', 'blfpst_target_option' ) ) {

				$admin_action = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';

				if ( 'preview_sql' !== $admin_action ) {
					BLFPST_Targets_Controller::target_register();
				}
			}
		}

		if ( isset( $_POST['blfpst_target_option_edit'] ) && $_POST['blfpst_target_option_edit'] ) {

			if ( ! check_admin_referer( 'blfpst-target-option-edit', 'blfpst_target_option_edit' ) ) {
				wp_safe_redirect( home_url() );
			}
		}

		// To send confirm view
		if ( isset( $_POST['blfpst_send_mail_conf'] ) && $_POST['blfpst_send_mail_conf'] ) {

			// Reservation
			if ( ! check_admin_referer( 'blfpst-send-mail-conf', 'blfpst_send_mail_conf' ) ) {
				wp_safe_redirect( home_url() );
				exit;
			}
		}

		// To start send view
		if ( isset( $_POST['blfpst_send_mail_register'] ) && $_POST['blfpst_send_mail_register'] ) {

			// Reservation
			if ( check_admin_referer( 'blfpst-send-mail-register', 'blfpst_send_mail_register' ) ) {
				$send_mail_view_controller = new BLFPST_Send_Mails_View_Controller();
				$send_mail_view_controller->register_view();
			} else {
				wp_safe_redirect( home_url() );
				exit;
			}
		}

		// Copy
		if ( isset( $_POST['blfpst_send_mail_duplicate'] ) && $_POST['blfpst_send_mail_duplicate'] ) {

			if ( ! check_admin_referer( 'blfpst-send-mail-duplicate', 'blfpst_send_mail_duplicate' ) ) {
				wp_safe_redirect( home_url() );
				exit;
			}
		}

		// Recycle
		if ( isset( $_POST['blfpst_send_mail_recycle'] ) && $_POST['blfpst_send_mail_recycle'] ) {

			if ( ! check_admin_referer( 'blfpst-send-mail-recycle', 'blfpst_send_mail_recycle' ) ) {
				wp_safe_redirect( home_url() );
				exit;
			}
		}

		// To creating template finish view.
		if ( isset( $_POST['blfpst_mail_template_register'] ) && $_POST['blfpst_mail_template_register'] ) {

			if ( check_admin_referer( 'blfpst-mail-template-register', 'blfpst_mail_template_register' ) ) {
				$mail_template_controller = new BLFPST_Mail_Templates_View_Controller();
				$mail_template_controller->register_view();
			} else {
				wp_safe_redirect( home_url() );
				exit;
			}
		}

		// Options
		if ( isset( $_POST['blfpst_target_option'] ) && $_POST['blfpst_target_option'] ) {

			if ( check_admin_referer( 'blfpst-target-option', 'blfpst_target_option' ) ) {
				BLFPST_Targets_Controller::update_option();
			}
		}
	}
}
