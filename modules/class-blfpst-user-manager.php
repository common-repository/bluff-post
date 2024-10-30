<?php

/**
 * user manager.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_User_Manager {
	/*
	 * Add new user roles to application
	 *
	 * @param  -
	 * @return void
	*/
	public static function add_application_user_roles() {
		add_role( 'blfpst_mail_manager', __( 'Mail magazine editor', 'bluff-post' ), array( 'read' => true ) );
	}

	/*
	 * Add capabilities to user roles
	 *
	 * @param  -
	 * @return void
	*/
	public static function add_application_user_capabilities() {

		$role                          = get_role( 'blfpst_mail_manager' );
		$custom_developer_capabilities = array(
			'blfpst_edit_mail',
			'blfpst_edit_target',
		);

		foreach ( $custom_developer_capabilities as $capability ) {
			$role->add_cap( $capability );
		}

		$role                      = get_role( 'administrator' );
		$custom_admin_capabilities = array(
			'blfpst_edit_mail',
			'blfpst_edit_target',
		);

		foreach ( $custom_admin_capabilities as $capability ) {
			$role->add_cap( $capability );
		}
	}
}

