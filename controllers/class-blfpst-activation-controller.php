<?php

/**
 * activation controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Activation_Controller {

	public function __construct() {
	}

	public function initialize_activation_hooks( $base_path ) {
		register_activation_hook( $base_path, array( $this, 'execute_activation_hooks' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_shortcode( 'blfpst-unsubscribe-page', 'BLFPST_UnSubscribePageShortCode::blfpst_unsubscribe_page' );
		add_action( 'widgets_init', function () {
			register_widget( 'BLFPST_Subscribe_Widget' );
		} );
	}

	public function execute_activation_hooks() {
		// crate DB tables
		$database_manager = new BLFPST_Database_Manager();
		$database_manager->create_custom_tables();

		// Default options
		BLFPST_Send_Mails_Controller::set_default_option();
		BLFPST_Targets_Controller::set_default_option();

		// Role
		BLFPST_User_Manager::add_application_user_roles();
		BLFPST_User_Manager::add_application_user_capabilities();

		// Load preset settings
		BLFPST_Mail_Templates_Controller::setup_preset_templates();
		BLFPST_Targets_Controller::setup_preset_targets();

		// Version
		/** @var string $blfpst_plugin_version */
		update_option( '_blfpst_plugin_version', BLFPST::$blfpst_plugin_version );
	}

	function plugins_loaded() {
		// $languages_path is not ABSPATH
		$languages_path = dirname( plugin_basename( __FILE__ ) ) . '/../languages/';
		load_plugin_textdomain(
			'bluff-post',
			false,          // abs path
			$languages_path // rel path
		);
	}
}
