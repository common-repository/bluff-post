<?php

/**
 * include script controller.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Script_Controller {

	public function enque_scripts() {
		add_action( 'wp_enqueue_scripts', array( $this, 'include_scripts_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'include_admin_scripts_styles' ) );
		add_action( 'login_enqueue_scripts', array( $this, 'include_login_scripts' ) );
	}

	public function include_scripts_styles() {
	}

	public function include_admin_scripts_styles( $hook_suffix ) {

		$page                  = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		$admin_action          = isset( $_REQUEST['admin_action'] ) ? $_REQUEST['admin_action'] : '';
		$is_mail_edit          = false;
		$is_mail_template_edit = false;
		$use_datetime_picker   = false;
		$use_parsley           = false;
		$use_stringify         = false;
		$color_theme           = BLFPST::get_option( 'theme_name', '' );

		// BootStrap
		if ( preg_match( '/blfpst-/', $hook_suffix ) ) {
			wp_enqueue_script( 'jquery' );

            wp_register_style( 'blfpst_bootstrap_styles',  'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css' );
			wp_enqueue_style( 'blfpst_bootstrap_styles' );

            wp_register_style( 'blfpst_bootstrap_icons',  'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css' );
            wp_enqueue_style( 'blfpst_bootstrap_icons' );

            wp_register_script( 'blfpst_bootstrap_js', 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js' );
			wp_enqueue_script( 'blfpst_bootstrap_js' );
		}

		// CSS
		if ( preg_match( '/blfpst-/', $hook_suffix ) ) {
			wp_register_style( 'user_styles', plugins_url( 'css/blfpst-style.css', dirname( __FILE__ ) ) );
			wp_enqueue_style( 'user_styles' );
		}

		if ( 'blfpst_standard' === $color_theme ) {
			if ( ( preg_match( '/blfpst-send-mail/', $hook_suffix ) ) || ( preg_match( '/blfpst-logs/', $hook_suffix ) ) ) {
				wp_register_style( 'user_theme_send_mail_styles', plugins_url( 'css/blfpst-theme-standard-send-mail.css', dirname( __FILE__ ) ) );
				wp_enqueue_style( 'user_theme_send_mail_styles' );
			}

			if ( preg_match( '/blfpst-mail-template/', $hook_suffix ) ) {
				wp_register_style( 'user_theme_mail_template_styles', plugins_url( 'css/blfpst-theme-standard-mail-template.css', dirname( __FILE__ ) ) );
				wp_enqueue_style( 'user_theme_mail_template_styles' );
			}

			if ( preg_match( '/blfpst-target/', $hook_suffix ) && ! preg_match( '/blfpst-target-option/', $hook_suffix ) ) {
				wp_register_style( 'user_theme_targets_styles', plugins_url( 'css/blfpst-theme-standard-targets.css', dirname( __FILE__ ) ) );
				wp_enqueue_style( 'user_theme_targets_styles' );
			}
		}

		// JavaScript
		if ( preg_match( '/blfpst-send-mail$/', $hook_suffix ) && ( 'blfpst-send-mail' === $page ) && ( '' === $admin_action ) ) {
			// Calendar
			wp_enqueue_script(
				'blfpst_send_mail_js',
				plugins_url( '/js/blfpst-send-mail.js', dirname( __FILE__ ) ),
				array( 'jquery' )
			);
		}

		if ( preg_match( '/blfpst-send-mail-histories$/', $hook_suffix ) && ( 'blfpst-send-mail-histories' === $page ) && ( 'info' === $admin_action ) ) {
			// History
			wp_enqueue_script(
				'blfpst_send_mail_info_js',
				plugins_url( '/js/blfpst-send-mail-info.js', dirname( __FILE__ ) ),
				array( 'jquery' )
			);
		}

		if ( preg_match( '/blfpst-target-option$/', $hook_suffix ) ) {
			wp_register_script( 'blfpst_option_js', plugins_url( 'js/blfpst-option.js', dirname( __FILE__ ) ), array( 'jquery' ) );
			wp_enqueue_script( 'blfpst_option_js' );
		}

		if ( ( preg_match( '/blfpst-send-mail$/', $hook_suffix ) ) ||
		     ( preg_match( '/blfpst-send-mail-crate$/', $hook_suffix ) ) ||
		     ( preg_match( '/blfpst-send-mail-drafts$/', $hook_suffix ) )
		) {
			$use_datetime_picker  = true;
		}

		// send mail create
		if ( preg_match( '/blfpst-send-mail$/', $hook_suffix ) ||
		     preg_match( '/blfpst-send-mail-crate$/', $hook_suffix ) ||
		     preg_match( '/blfpst-mail-template$/', $hook_suffix ) ||
		     preg_match( '/blfpst-mail-template-create$/', $hook_suffix ) ||
		     preg_match( '/blfpst-target-option$/', $hook_suffix ) ||
		     preg_match( '/blfpst-target-exclude-recipient$/', $hook_suffix ) ||
		     preg_match( '/blfpst-target-register$/', $hook_suffix )
		) {
			$use_parsley  = true;
		}

		if ( preg_match( '/blfpst-target-register$/', $hook_suffix ) ) {
			$use_stringify = true;
		}

		if ( preg_match( '/blfpst-send-mail$/', $hook_suffix )
		     && ( 'blfpst-send-mail' === $page )
		     && ( ( 'edit_reserved' === $admin_action ) || ( 'duplicate' === $admin_action ) || ( 'edit_draft' === $admin_action ) || ( 'save' === $admin_action ) || ( 'test' === $admin_action ))
		) {
			$is_mail_edit = true;

		} else if ( preg_match( '/blfpst-send-mail-crate$/', $hook_suffix )
		            && ( 'blfpst-send-mail-crate' === $page )
		            && ( ( '' === $admin_action ) || ( 'save' === $admin_action ) || ( 'test' === $admin_action ) )
		) {
			// Mail Create/Edit/Duplicate
			$is_mail_edit = true;
		}

		if ( preg_match( '/blfpst-mail-template$/', $hook_suffix )
		     && ( 'blfpst-mail-template' === $page )
		     && ( ( 'edit' === $admin_action ) || ( 'edit_from_send_mail' === $admin_action ) )
		) {
			$is_mail_template_edit = true;

		} else if ( preg_match( '/blfpst-mail-template-create$/', $hook_suffix )
		            && ( 'blfpst-mail-template-create' === $page )
		            && ( '' === $admin_action )
		) {
			$is_mail_template_edit = true;
		}

		if ( $is_mail_edit ) {
			// Media uploader
			wp_enqueue_media();

			wp_enqueue_script(
				'blfpst_send_mail_create_js',
				plugins_url( '/js/blfpst-send-mail-create.js', dirname( __FILE__ ) ),
				array( 'jquery' )
			);
			//wp_register_script( 'blfpst_stringify_js', plugins_url( '/js/jquery.stringify.js', dirname( __FILE__ ) ) );
			//wp_enqueue_script( 'blfpst_stringify_js' );
		}

		if ( $is_mail_template_edit ) {
			// Media uploader
			wp_enqueue_media();

			wp_enqueue_script(
				'blfpst_mail_template_create_js',
				plugins_url( '/js/blfpst-mail-template-create.js', dirname( __FILE__ ) ),
				array( 'jquery' )
			);
		}

		if ( $use_parsley ) {
			wp_register_script( 'blfpst_Parsley_js', plugins_url( 'vendor/Parsley/dist/parsley.min.js', dirname( __FILE__ ) ) );
			wp_register_script( 'blfpst_Parsley_ja_js', plugins_url( 'vendor/Parsley/dist/i18n/ja.js', dirname( __FILE__ ) ) );
			wp_enqueue_script( 'blfpst_Parsley_js' );
			wp_enqueue_script( 'blfpst_Parsley_ja_js' );
		}

		if ( $use_stringify ) {
			wp_register_script( 'blfpst_stringify_js', plugins_url( '/js/jquery.stringify.js', dirname( __FILE__ ) ) );
			wp_enqueue_script( 'blfpst_stringify_js' );
		}

		if ( $use_datetime_picker ) {
			wp_register_style( 'blfpst_wp_datetime_picker_css', plugins_url( 'vendor/datetimepicker/jquery.datetimepicker.css', dirname( __FILE__ ) ), false, '1.0.0' );
			wp_enqueue_style( 'blfpst_wp_datetime_picker_css' );
			wp_enqueue_script( 'blfpst_datetime_picker', plugins_url( 'vendor/datetimepicker/jquery.datetimepicker.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0', false );
		}
	}

	public function include_login_scripts() {
	}
}
