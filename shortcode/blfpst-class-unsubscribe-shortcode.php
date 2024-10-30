<?php
/**
 * unsubscribe shortcode .
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_UnSubscribePageShortCode {

	public static function blfpst_unsubscribe_page() {

		$action_url                 = BLFPST::plugin_url( 'subscribe.php' );
		$nonce                      = wp_create_nonce( 'blfpst-request-subscribe' );
		$title                      = '';
		$unsubscribe_button_message = '';
		$success_message            = '';

		$dummy = new BLFPST_Subscribe_Widget();
		$settings = $dummy->get_settings();

		foreach ( $settings as $setting ) {
			if ( isset( $setting['title'] ) ) {
				$title = $setting['title'];
			}
			if ( isset( $setting['unsubscribe_button_message'] ) ) {
				$unsubscribe_button_message = $setting['unsubscribe_button_message'];
			}
			if ( isset( $setting['unsubscribe_success_message'] ) ) {
				$success_message = $setting['unsubscribe_success_message'];
			}
		}

		$unsubscribe_button_message = ( '' !== $unsubscribe_button_message ) ? $unsubscribe_button_message : esc_html__( 'Unsubscribe', 'bluff-post' );

		$html = '
		<form method="post" class="blfpst-unsubscribe-form" action="' . esc_url( $action_url ) . '">
		<label><span class="screen-reader-text">' . esc_html__( 'e-mail address', 'bluff-post' ) . '</span>
		<input type="email" class="email-field" placeholder="' . esc_html( $title ) . '" value="" name="unsubscribe-email"/>
		</label>
		<div id="blfpst_unsubscribe_success_message" style="display:none">' . esc_html( $success_message ) . '</div>
		<div id="blfpst_unsubscribe_error_message" style="display:none">' . esc_html( $success_message ) . '</div>
		<input type="hidden" name="subscribe_action" value="unsubscribe">
		<input type="hidden" id="blfpst_request_subscribe" name="blfpst_request_subscribe"  value="' . $nonce . '">
		<input type="submit" class="blfpst-subscribe-submit" value="' . esc_html( $unsubscribe_button_message ) . '"/>';

		return $html;
	}
}
