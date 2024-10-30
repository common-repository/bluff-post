<?php

/**
 * subscribe widget .
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */

class BLFPST_Subscribe_Widget extends WP_Widget {

	public $ajax_actions;

	function __construct() {
		$widgets_options = array(
			'classname'                   => 'BLFPST_Subscribe_Widget',
			'description'                 => __( 'Subscribe widget', 'bluff-post' ),
			'customize_selective_refresh' => true,
		);
		$control_options = array();

		parent::__construct(
			'bluff_subscribe_widget',
			'Bluff Subscribe Widget',
			$widgets_options,
			$control_options
		);

		$this->configure_actions();
	}

	/**
	 *
	 * @param array $args before_title, after_title, before_widget, after_widget
	 * @param array $instance 設定項目
	 */
	public function widget( $args, $instance ) {
		wp_enqueue_script( 'jquery' );

		//wp_register_script( 'blfpst_ajax', BLFPST::plugin_url() . '/js/blfpst-ajax.js', array( 'jquery' ) );
		//wp_enqueue_script( 'blfpst_ajax' );

		wp_register_script( 'blfpst_subscribe_script_ajax', BLFPST::plugin_url() . '/js/blfpst-ajax-subscribe-widgets.js', array( 'jquery' ) );
		wp_enqueue_script( 'blfpst_subscribe_script_ajax' );

		$nonce = wp_create_nonce( 'blfpst-ajax' );

		$config_array = array(
			'ajaxURL'     => admin_url( 'admin-ajax.php' ),
			'ajaxActions' => $this->ajax_actions,
			'ajaxNonce'   => $nonce,
		);

		wp_localize_script( 'blfpst_subscribe_script_ajax', 'blfpst_conf', $config_array );

		$title = isset( $instance['title'] ) ? $instance['title'] : '';

		echo $args['before_widget'];

		if ( $title ) {
			echo $args['before_title'];
			echo "<label for='title'>${title}</label>";
			echo $args['after_title'];
		}

		$this->get_subscribe_form( $instance );

		echo $args['after_widget'];
	}

	/**
	 *
	 * @param array $instance
	 *
	 * @return void
	 */
	public function form( $instance ) {
		$type                             = isset( $instance['type'] ) ? $instance['type'] : '';
		$type_name                        = $this->get_field_name( 'type' );
		$type_id                          = $this->get_field_id( 'type' );
		$title                            = isset( $instance['title'] ) ? $instance['title'] : '';
		$title_name                       = $this->get_field_name( 'title' );
		$title_id                         = $this->get_field_id( 'title' );
		$description                      = isset( $instance['description'] ) ? $instance['description'] : '';
		$description_name                 = $this->get_field_name( 'description' );
		$description_id                   = $this->get_field_id( 'description' );
		$subscribe_success_message        = isset( $instance['subscribe_success_message'] ) ? $instance['subscribe_success_message'] : '';
		$subscribe_success_message_name   = $this->get_field_name( 'subscribe_success_message' );
		$subscribe_success_message_id     = $this->get_field_id( 'subscribe_success_message' );
		$unsubscribe_success_message      = isset( $instance['unsubscribe_success_message'] ) ? $instance['unsubscribe_success_message'] : '';
		$unsubscribe_success_message_name = $this->get_field_name( 'unsubscribe_success_message' );
		$unsubscribe_success_message_id   = $this->get_field_id( 'unsubscribe_success_message' );
		$subscribe_button_message         = isset( $instance['subscribe_button_message'] ) ? $instance['subscribe_button_message'] : '';
		$subscribe_button_message_name    = $this->get_field_name( 'subscribe_button_message' );
		$subscribe_button_message_id      = $this->get_field_id( 'subscribe_button_message' );
		$unsubscribe_button_message       = isset( $instance['unsubscribe_button_message'] ) ? $instance['unsubscribe_button_message'] : '';
		$unsubscribe_button_message_name  = $this->get_field_name( 'unsubscribe_button_message' );
		$unsubscribe_button_message_id    = $this->get_field_id( 'unsubscribe_button_message' );
		?>
		<p>
			<label for="<?php echo $type_id ?>"><?php esc_html_e( 'Data source:', 'bluff-post' ); ?>
				<select id="<?php echo $type_id ?>" name="<?php echo $type_name ?>">
					<option value="wordpress" <?php echo ( 'wordpress' === $type ) ? 'selected' : '' ?>>WordPress
					</option>
					<option value="bluffmail" <?php echo ( 'bluffmail' === $type ) ? 'selected' : '' ?>>Bluff Mail
					</option>
				</select> </label>
		</p>
		<p>
			<label for="<?php echo $title_id ?>"><?php esc_html_e( 'Heading:', 'bluff-post' ); ?>
				<input class="widefat" id="<?php echo $title_id ?>" name="<?php echo $title_name ?>" type="text" value="<?php echo esc_attr( $title ); ?>"/>
			</label> <label for="<?php echo $description_id ?>"><?php esc_html_e( 'Description:', 'bluff-post' ); ?>
				<input class="widefat" id="<?php echo $description_id ?>" name="<?php echo $description_name ?>" type="text" value="<?php echo esc_attr( $description ); ?>"/>
			</label>
			<label for="<?php echo $subscribe_button_message_id ?>"><?php esc_html_e( 'Subscribe button title:', 'bluff-post' ); ?>
				<input class="widefat" id="<?php echo $subscribe_button_message_id ?>" name="<?php echo $subscribe_button_message_name ?>" type="text" value="<?php echo esc_attr( $subscribe_button_message ); ?>"/>
			</label>
			<label for="<?php echo $unsubscribe_button_message_id ?>"><?php esc_html_e( 'Unsubscribe button title:', 'bluff-post' ); ?>
				<input class="widefat" id="<?php echo $unsubscribe_button_message_id ?>" name="<?php echo $unsubscribe_button_message_name ?>" type="text" value="<?php echo esc_attr( $unsubscribe_button_message ); ?>"/>
			</label>
			<label for="<?php echo $subscribe_success_message_id ?>"><?php esc_html_e( 'Subscribe completion message:', 'bluff-post' ); ?>
				<input class="widefat" id="<?php echo $subscribe_success_message_id ?>" name="<?php echo $subscribe_success_message_name ?>" type="text" value="<?php echo esc_attr( $subscribe_success_message ); ?>"/>
			</label>
			<label for="<?php echo $unsubscribe_success_message_id ?>"><?php esc_html_e( 'Unsubscribe completion message:', 'bluff-post' ); ?>
				<input class="widefat" id="<?php echo $unsubscribe_success_message_id ?>" name="<?php echo $unsubscribe_success_message_name ?>" type="text" value="<?php echo esc_attr( $unsubscribe_success_message ); ?>"/>
			</label>
		</p>
		<?php
	}

	/**
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {

		$instance                                = $old_instance;
		$new_instance                            = wp_parse_args( (array) $new_instance, array(
			'title'                       => '',
			'description'                 => '',
			'subscribe_button_message'    => '',
			'unsubscribe_button_message'  => '',
			'subscribe_success_message'   => '',
			'unsubscribe_success_message' => '',
		) );
		$instance['title']                       = sanitize_text_field( $new_instance['title'] );
		$instance['description']                 = sanitize_text_field( $new_instance['description'] );
		$instance['subscribe_button_message']    = sanitize_text_field( $new_instance['subscribe_button_message'] );
		$instance['unsubscribe_button_message']  = sanitize_text_field( $new_instance['unsubscribe_button_message'] );
		$instance['subscribe_success_message']   = sanitize_text_field( $new_instance['subscribe_success_message'] );
		$instance['unsubscribe_success_message'] = sanitize_text_field( $new_instance['unsubscribe_success_message'] );

		return $new_instance;
	}

	/**
	 *
	 * @param array $instance Widgetの設定項目
	 *
	 * @return void
	 */
	private function get_subscribe_form( $instance ) {

		$format     = current_theme_supports( 'html5', 'search-form' ) ? 'html5' : 'xhtml';
		$action_url = admin_url( 'admin.php?page=blfpst-targets&admin_action=subscribe' );

		$title           = isset( $instance['title'] ) ? $instance['title'] : '';
		$description     = isset( $instance['description'] ) ? $instance['description'] : '';
		$button_message  = ( isset( $instance['subscribe_button_message'] ) && ( '' !== $instance['subscribe_button_message'] ) ) ? $instance['subscribe_button_message'] : esc_html__( 'Subscribe', 'bluff-post' );
		$success_message = isset( $instance['subscribe_success_message'] ) ? $instance['subscribe_success_message'] : esc_html__( 'Unsubscribe', 'bluff-post' );

		echo '<form role="subscribe" method="post" class="blfpst-subscribe-form" action="' . esc_url( $action_url ) . '">';

		if ( 'html5' == $format ) {
			if ( '' !== $description ) {
				?>
				<div>
					<small><?php echo esc_html( $description ) ?></small>
				</div>
				<?php
			}

			?>
			<label><span class="screen-reader-text"><?php echo esc_html( $title ) ?></span>
				<input type="email" placeholder="<?php echo esc_attr__( 'e-mail address', 'bluff-post' ) ?>" value="" name="subscribe-email"/>
			</label>
			<div id="blfpst_subscribe_success_message" style="display:none"><?php echo esc_html( $success_message ) ?></div>
			<div id="blfpst_subscribe_error_message" style="display:none"></div>
			<input type="hidden" name="action" value="subscribe">
			<input type="hidden" name="page" value="blfpst-targets">
			<input type="hidden" name="subscribe_action" value="subscribe">
			<button type="button" id="blfpst-subscribe-submit"><?php echo esc_html( $button_message ) ?></button>
			<?php
		} else {
			?>
			<div>
				<?php
				if ( '' !== $description ) {
					?>
					<div>
						<small><?php echo esc_html( $description ) ?></small>
					</div>
					<?php
				}
				?>
				<div>
					<label class="screen-reader-text" for="blfpst-subscribe-email"><?php echo esc_html( $title ) ?></label>
					<input type="email" value="" name="subscribe-email" id="blfpst-subscribe-email"/>
					<input type="hidden" name="subscribe_action" value="subscribe">
					<button type="button" id="blfpst-subscribe-submit"><?php echo esc_html( $button_message ) ?></button>
					<div id="blfpst_subscribe_success_message" style="display:none"><?php echo $success_message ?></div>
					<div id="blfpst_subscribe_error_message" style="display:none"></div>
				</div>
			</div>
			<?php
		}

		wp_nonce_field( 'blfpst-request-subscribe', 'blfpst_request_subscribe' );
		echo '</form>';
	}

	public function request_subscribe() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		$email = isset( $_POST['email'] ) ? $_POST['email'] : '';
		$email = trim( $email );

		$return_object = BLFPST_Subscribe_Controller::request_subscribe( $email );

		echo json_encode( $return_object );
		exit;
	}

	public function request_unsubscribe() {

		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';

		if ( ! wp_verify_nonce( $nonce, 'blfpst-ajax' ) ) {
			die( 'Unauthorized request!' );
		}

		$email = isset( $_POST['email'] ) ? $_POST['email'] : '';
		$email = trim( $email );

		$return_object = BLFPST_Subscribe_Controller::request_unsubscribe( $email );

		echo json_encode( $return_object );
		exit;
	}

	public function configure_actions() {
		$this->ajax_actions = array(
			'request_subscribe'   => array(
				'action'   => 'blfpst_request_subscribe_action',
				'function' => 'request_subscribe',
			),
			'request_unsubscribe' => array(
				'action'   => 'blfpst_request_unsubscribe_action',
				'function' => 'request_unsubscribe',
			),
		);

		/*
		 * Add the AJAX actions into WordPress
		 */
		foreach ( $this->ajax_actions as $custom_key => $custom_action ) {

			if ( isset( $custom_action['logged'] ) && $custom_action['logged'] ) {

				// Actions for users who are logged in
				add_action( 'wp_ajax_' . $custom_action['action'], array( $this, $custom_action['function'] ) );

			} else if ( isset( $custom_action['logged'] ) && ! $custom_action['logged'] ) {
				// Actions for users who are not logged in
				add_action( 'wp_ajax_nopriv_' . $custom_action['action'], array( $this, $custom_action['function'] ) );

			} else {
				// Actions for users who are logged in and not logged in
				add_action( 'wp_ajax_nopriv_' . $custom_action['action'], array( $this, $custom_action['function'] ) );
				add_action( 'wp_ajax_' . $custom_action['action'], array( $this, $custom_action['function'] ) );
			}
		}
	}
}
