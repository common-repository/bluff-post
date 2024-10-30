<?php
/**
 * PHP Version 5.4.0
 * Version 1.0.0
 * Date: 2016/09/30
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */

class BLFPST_Cron_Controller {

	public function initialize() {
		add_action( 'admin_post_nopriv_blfpst_cron', array( $this, 'action_admin_post_nopriv_blfpst_cron' ) );
	}

	function action_admin_post_nopriv_blfpst_cron() {
		BLFPST_Send_Mails_Controller::execute_post_mail();
	}
}
