<?php

/**
 * WordPress custom post manager.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Custom_Post_Manager {

	public function initialize() {
		add_action( 'init', array( $this, 'create_mail_post_type' ) );
		add_action( 'init', array( $this, 'create_mails_custom_taxonomies' ) );
	}

	/*
	 * Register custom post type for articles
	 *
	 * @return void
	 * */
	public function create_mail_post_type() {

		$labels = array(
			'name'               => __( 'Mails', 'blfpst' ),
			'singular_name'      => __( 'Mail', 'blfpst' ),
			'add_new'            => __( 'Add New', 'blfpst' ),
			'add_new_item'       => __( 'Add New Article', 'blfpst' ),
			'edit_item'          => __( 'Edit Mail', 'blfpst' ),
			'new_item'           => __( 'New Mail', 'blfpst' ),
			'all_items'          => __( 'All Mails', 'blfpst' ),
			'view_item'          => __( 'View Mail', 'blfpst' ),
			'search_items'       => __( 'Search Mails', 'blfpst' ),
			'not_found'          => __( 'No Mails found', 'blfpst' ),
			'not_found_in_trash' => __( 'No Mails found in the Trash', 'blfpst' ),
			'parent_item_colon'  => '',
			'menu_name'          => __( 'Mail', 'blfpst' ),
		);

		$args = array(
			'labels'              => $labels,
			'hierarchical'        => true,
			'description'         => 'Bluff Post Mail',
			'supports'            => array( 'title', 'editor' ),
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'has_archive'         => true,
			'query_var'           => true,
			'can_export'          => true,
			'rewrite'             => true,
			'capability_type'     => 'post',
			'menu_icon'   => 'dashicons-email-alt',
		);

		register_post_type( 'blfpst_mail', $args );
	}

	/*
	 * Register custom taxonomies for the articles screen
	 *
	 * @return void
	*/
	public function create_mails_custom_taxonomies() {

		register_taxonomy(
			'blfpst_mail_category',
			'blfpst_mail',
			array(
				'labels'       => array(
					'name'              => __( 'Mail Category', 'blfpst' ),
					'singular_name'     => __( 'Mail Category', 'blfpst' ),
					'search_items'      => __( 'Search Mail Category', 'blfpst' ),
					'all_items'         => __( 'All Mail Category', 'blfpst' ),
					'parent_item'       => __( 'Parent Mail Category', 'blfpst' ),
					'parent_item_colon' => __( 'Parent Mail Category:', 'blfpst' ),
					'edit_item'         => __( 'Edit Mail Category', 'blfpst' ),
					'update_item'       => __( 'Update Mail Category', 'blfpst' ),
					'add_new_item'      => __( 'Add New Mail Category', 'blfpst' ),
					'new_item_name'     => __( 'New Mail Category Name', 'blfpst' ),
					'menu_name'         => __( 'Mail Category', 'blfpst' ),
				),
				'hierarchical' => true,
			)
		);
	}

	/**
	 * @param integer $user_id
	 * @param integer $post_id
	 * @param string $status
	 * @param string $title
	 * @param string $content
	 *
	 * @return integer
	 */
	public static function add_mail_page( $user_id, $post_id, $status, $title, $content ) {

		// Issue #148 replacing Insertion keyword.
		// insertion keys
		$insertions = array(
			'%%user_id%%',
			'%%user_name%%',
			'%%user_last_name%%',
			'%%user_first_name%%',
			'%%user_mail_address%%',
			'%%mail_id%%',
			'%%mail_page_url%%',
			'%%random_id%%',
		);

		$edited_title        = $title;
		$edited_text_content = $content;

		foreach ( $insertions as $insertion ) {
			$edited_title        = str_replace( $insertion, '', $edited_title );
			$edited_text_content = str_replace( $insertion, '', $edited_text_content );
		}

		// Issue #148 Replace URL


		$post_details = array(
			'post_author'  => $user_id,
			'post_type'    => 'blfpst_mail',
			'post_status'  => esc_html( $status ), // 'draft' | 'publish' | 'pending'| 'future' | 'private'
			'post_title'   => esc_html( $edited_title ),
			'post_content' => $edited_text_content,
		);

		if ( empty( $post_id ) ) {
			//remove_filterを行うとHTMLが崩れることがある'Smython'メルマガで確認
			// http://kihon-no-ki.com/wordpress-wp-insert-post-allow-saving-html-tags
			//remove_filter( 'content_save_pre', 'wp_filter_post_kses' );
			$new_post_id = wp_insert_post( $post_details );
			//add_filter( 'content_save_pre', 'wp_filter_post_kses' );

			if ( is_wp_error( $new_post_id ) ) {
				$errors = $new_post_id->get_error_messages();
				foreach ( $errors as $error ) {
					error_log( 'wp_insert_post() error. ' . $error );
				}
			} else {
				if ( $new_post_id ) {
					wp_set_object_terms( $new_post_id, 'Mail Magazine', 'blfpst_mail_category' );
				}
			}
		} else {
			$post = array(
				'ID'           => $post_id,
				'post_author'  => $user_id,
				'post_title'   => esc_html( $edited_title ),
				'post_content' => $edited_text_content,
			);
			$new_post_id = wp_update_post( $post, true );

			if ( is_wp_error( $new_post_id ) ) {
				$errors = $new_post_id->get_error_messages();
				foreach ( $errors as $error ) {
					error_log( 'wp_update_post() error. ' . $error );
				}
			}
		}

		return $new_post_id;
	}

	public static function update_page_name( $post_id, $send_mail_id ) {
		$my_post = array(
			'ID'        => $post_id,
			'post_name' => 'mail' . $send_mail_id,
		);
		wp_update_post( $my_post );
	}

	public static function update_page_meta( $post_id, $send_mail_id, $is_html_mail ) {
		// Issue #146
		update_post_meta( $post_id, '_blfpst_content_type_html', $is_html_mail );
		update_post_meta( $post_id, '_blfpst_send_mail_id', $send_mail_id );
	}
}
