<?php

/**
 * Plugin Name: Bluff Post
 * Plugin URI: http://www.bluff-lab.com/
 * Description: Publish the e-mail magazine plugin for WordPress.
 * Author: Hideaki Oguchi
 * Author URI: http://www.bluff-lab.com/
 * Text Domain: bluff-post
 * Domain Path: /languages/
 * Version: 1.1.1
 */

define( 'BLFPST_VERSION', '1.1.1' );
define( 'BLFPST_PLUGIN', __FILE__ );
define( 'BLFPST_PLUGIN_BASENAME', plugin_basename( BLFPST_PLUGIN ) );
define( 'BLFPST_PLUGIN_NAME', trim( dirname( BLFPST_PLUGIN_BASENAME ), '/' ) );
define( 'BLFPST_PLUGIN_DIR', untrailingslashit( dirname( BLFPST_PLUGIN ) ) );
define( 'BLFPST_PLUGIN_URL', untrailingslashit( plugins_url( '', BLFPST_PLUGIN ) ) );
define( 'BLFPST_USE_DB_LOCK', true );

require_once 'ajax/class-blfpst-ajax.php';
require_once 'modules/class-blfpst-database-manager.php';
require_once 'modules/class-blfpst-user-manager.php';
require_once 'modules/class-blfpst-custom-post-manager.php';
require_once 'includes/class-blfpst-dashboard.php';

require_once 'modules/class-blfpst-template-loader.php';
require_once 'modules/class-blfpst-send-mail.php';
require_once 'modules/utility.php';

require_once 'widgets/class-blfpst-subscribe-widgets.php';
require_once 'shortcode/blfpst-class-unsubscribe-shortcode.php';
require_once 'data-sources/class-blfpst-data-source.php';

spl_autoload_register( 'blfpst_auto_loader' );

/*
 * Custom auto loader for the application
 *
 * @param  string Class name
 * @return -
 */

function blfpst_auto_loader( $class_name ) {

	$class_components = explode( '_', $class_name );

	if ( isset( $class_components[0] ) && 'BLFPST' == $class_components[0] &&
	     isset( $class_components[1] )
	) {

		$class_directory = $class_components[1];

		unset( $class_components[0], $class_components[1] );

		$file_name = implode( '-', $class_components );

		switch ( $class_directory ) {
			case 'Model':

				$file_path = BLFPST::plugin_dir( 'models/class-blfpst-model-' . strtolower( $file_name ) . '.php' );

				if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
					/** @noinspection PhpIncludeInspection */
					include $file_path;
				}

				break;

			case 'Data':

				if ( isset( $class_components[2] ) && ( 'Source' === $class_components[2] ) ) {
					$file_path = BLFPST::plugin_dir( 'data-sources/class-blfpst-data-' . strtolower( $file_name ) . '.php' );

					if ( file_exists( $file_path ) && is_readable( $file_path ) ) {
						/** @noinspection PhpIncludeInspection */
						include $file_path;
					}
				}

				break;

		}
	}
}

class BLFPST {

	public static $blfpst_plugin_version = BLFPST_VERSION;

	public function initialize_controllers() {
		require_once 'controllers/class-blfpst-activation-controller.php';
		require_once 'controllers/class-blfpst-script-controller.php';
		require_once 'controllers/class-blfpst-admin-menu-controller.php';
		require_once 'controllers/class-blfpst-send-mails-controller.php';
		require_once 'controllers/class-blfpst-send-mails-view-controller.php';
		require_once 'controllers/class-blfpst-mail-templates-controller.php';
		require_once 'controllers/class-blfpst-mail-templates-view-controller.php';
		require_once 'controllers/class-blfpst-targets-controller.php';
		require_once 'controllers/class-blfpst-targets-view-controller.php';
		require_once 'controllers/class-blfpst-logs-controller.php';
		require_once 'controllers/class-blfpst-subscribe-controller.php';
		require_once 'controllers/class-blfpst-cron.php';

		$activation_controller = new BLFPST_Activation_Controller();
		$activation_controller->initialize_activation_hooks( __FILE__ );

		$script_controller = new BLFPST_Script_Controller();
		$script_controller->enque_scripts();

		$admin_menu_controller = new BLFPST_Admin_Menu_Controller();
		$admin_menu_controller->initialize_admin_menu();

		$send_mail_controller = new BLFPST_Send_Mails_Controller();
		$send_mail_controller->initialize();

		$send_mail_view_controller = new BLFPST_Send_Mails_View_Controller();
		$send_mail_view_controller->initialize();

		$mail_template_controller = new BLFPST_Mail_Templates_Controller();
		$mail_template_controller->initialize();

		$mail_template_view_controller = new BLFPST_Mail_Templates_View_Controller();
		$mail_template_view_controller->initialize();

		$mail_target_controller = new BLFPST_Targets_Controller();
		$mail_target_controller->initialize();

		$mail_target_view_controller = new BLFPST_Target_View_Controller();
		$mail_target_view_controller->initialize();

		$log_controller = new BLFPST_Logs_Controller();
		$log_controller->initialize();

		$cron_controller = new BLFPST_Cron_Controller();
		$cron_controller->initialize();
	}

	public function initialize_app_controllers() {
		$dashboard = new BLFPST_Dashboard();
		$dashboard->initialize();

		$custom_post_manager = new BLFPST_Custom_Post_Manager();
		$custom_post_manager->initialize();

		$ajax = new BLFPST_Ajax();
		$ajax->initialize();
	}

	/**
	 * @param string $key
	 * @param bool $default
	 *
	 * @return bool | string
	 */
	public static function get_option( $key, $default = false ) {
		$options = get_option( 'blfpst_options' );

		if ( false === $options ) {
			return $default;
		}

		if ( isset( $options[ $key ] ) ) {
			return $options[ $key ];
		} else {
			return $default;
		}
	}

	/**
	 * @param string $key
	 * @param string | int | bool $value
	 */
	public static function update_option( $key, $value ) {
		$options = get_option( 'blfpst_options' );
		$options = ( false === $options ) ? array() : (array) $options;
		$options = array_merge( $options, array( $key => $value ) );

		update_option( 'blfpst_options', $options );
	}

	public static function plugin_dir( $path = '' ) {
		return path_join( BLFPST_PLUGIN_DIR, trim( $path, '/' ) );
	}

	public static function plugin_url( $path = '' ) {
		$url = plugins_url( $path, BLFPST_PLUGIN );

		if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) ) {
			$url = 'https:' . substr( $url, 5 );
		}

		return $url;
	}

	public static function data_sources_dir() {
		return self::plugin_dir( '/data-sources' );
	}
}

$blfpst_web_app = new BLFPST();
$blfpst_web_app->initialize_controllers();
$blfpst_web_app->initialize_app_controllers();
