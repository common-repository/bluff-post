<?php

/**
 * WordPress dash bord.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Dashboard {
	/*
	 * Include necessary actions and filters to initialize the plugin.
	 *
	 * @param  -
	 * @return -
	 */

	public function __construct() {
	}

	public function initialize() {
		add_action( 'wp_before_admin_bar_render', array( $this, 'customize_admin_toolbar' ) );
	}

	/*
	 * Enable or disable front end admin toolbar
	 *
	 * @param  boolean $status Display status of admin toolbar
	 * @return -
	 */
	public function set_frontend_toolbar( $status ) {
		show_admin_bar( $status );
	}

	/*
	 * Customize existing menu items and adding new menu items
	 *
	 * @param  -
	 * @return -
	 */
	public function customize_admin_toolbar() {

		/** @global WP_Admin_Bar $wp_admin_bar */
		global $wp_admin_bar;

		if ( current_user_can( 'blfpst_edit_mail' ) ) {

			$wp_admin_bar->add_menu( array(
				'id'     => 'blfpst-send-mail',
				'title'  => __( 'Calendar', 'bluff-post' ),
				'href'   => admin_url( 'admin.php?page=blfpst-send-mail' ),
				'parent' => 'site-name',
			) );

			$wp_admin_bar->add_menu( array(
				'id'     => 'blfpst-send-mail-crate',
				'title'  => __( 'Create e-mail', 'bluff-post' ),
				'href'   => admin_url( 'admin.php?page=blfpst-send-mail-crate' ),
				'parent' => 'site-name',
			) );

			$wp_admin_bar->add_menu( array(
				'id'     => 'blfpst-send-mail-reserves',
				'title'  => __( 'Reservation list', 'bluff-post' ),
				'href'   => admin_url( 'admin.php?page=blfpst-send-mail-reserves' ),
				'parent' => 'site-name',
			) );

			$wp_admin_bar->add_menu( array(
				'id'     => 'blfpst-send-mail-histories',
				'title'  => __( 'Outbox', 'bluff-post' ),
				'href'   => admin_url( 'admin.php?page=blfpst-send-mail-histories' ),
				'parent' => 'site-name',
			) );
		}
	}

	/*
	 * Removes the dashboard menu item
	 *
	 * @param  -
	 * @return -
	 */
	public function customize_main_navigation() {
	}
}

