<?php

/**
 * mail template controller.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Mail_Templates_Controller {

	public function initialize() {
	}

	/**
	 * mail template count
	 *
	 * @return int
	 */
	public static function load_mail_template_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Template::table_name();
		$sql        = "SELECT count(*) FROM ${table_name}";
		$count      = $wpdb->get_var( $sql );

		return (int) $count;
	}

	/**
	 * @param int $page_num
	 * @param int $limit
	 *
	 * @return array
	 */
	public static function load_mail_templates( $page_num = - 1, $limit = 0 ) {
		$mail_templates = array();

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Template::table_name();
		$sql        = "SELECT * FROM ${table_name} ORDER BY updated_at DESC";

		if ( $page_num >= 0 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
		};

		$results = $wpdb->get_results( $sql );
		if ( null === $results ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			foreach ( $results as $result ) {
				$template = new BLFPST_Model_Template();
				$template->set_result( $result );
				array_push( $mail_templates, $template );
			}
		}

		return $mail_templates;
	}

	public static function load_mail_template( $mail_template_id ) {
		$mail_template = null;

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Template::table_name();
		$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (id='%d')", $mail_template_id );

		$results = $wpdb->get_results( $sql );
		if ( null === $results ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			if ( count( $results ) > 0 ) {
				$result        = $results[0];
				$mail_template = new BLFPST_Model_Template();
				$mail_template->set_result( $result );
			}
		}

		return $mail_template;
	}


	/**
	 * set up preset template data
	 *
	 * @return void
	 */
	public static function setup_preset_templates() {

		$template_count = BLFPST_Mail_Templates_Controller::load_mail_template_count();
		if ( 0 == $template_count ) {

			$templates = self::load_all_templates_from_file();
			if ( ! empty( $templates ) ) {

				/** @var BLFPST_Model_Template $template */
				foreach ( $templates as $template ) {

					/** @var wpdb $wpdb */
					global $wpdb;
					$table_name = BLFPST_Model_Template::table_name();

					// DBへの新規追加
					$wpdb->insert(
						$table_name,
						array(
							'user_id'       => $template->user_id,
							'template_type' => $template->template_type,
							'content_type'  => $template->content_type,
							'file_name'     => $template->file_name,
							'title'         => $template->title,
							'from_name'     => '',
							'from_address'  => '',
							'reply_name'    => '',
							'reply_address' => '',
							'subject'       => '',
							'text_content'  => $template->text_content,
							'html_content'  => $template->html_content,
							'author'        => $template->author,
							'description'   => $template->description,

							'updated_at' => current_time( 'mysql', 0 ),
							'created_at' => current_time( 'mysql', 0 ),
						),
						array(
							'%d', // user_id
							'%d', // template_type
							'%s', // content_type
							'%s', // file_name
							'%s', // title
							'%s', // from_name
							'%s', // from_address
							'%s', // reply_name
							'%s', // reply_address
							'%s', // subject
							'%s', // text_content
							'%s', // html_content
							'%s', // author
							'%s', // description

							'%s', // updated_at
							'%s', // created_at
						)
					);
				}
			}
		}
	}

	/**
	 * load all template data from file
	 *
	 * @return array
	 */
	public static function load_all_templates_from_file() {

		$locale = get_locale();
		$locale = ( 'ja' === $locale ) ? $locale : 'en_US';

		$templates    = array();
		$template_dir = BLFPST::plugin_dir( 'presets/mail-templates/' . $locale );

		if ( ! file_exists( $template_dir ) ) {
			return $templates;
		}

		if ( is_dir( $template_dir ) && ( $handle = opendir( $template_dir ) ) ) {
			while ( ( $file = readdir( $handle ) ) !== false ) {
				if ( 'file' === filetype( $path = path_join( $template_dir, $file ) ) ) {
					if ( ! preg_match( '/^\./', $path ) && preg_match( '/.tmpl.html\z/', $path ) ) {
						$template = self::load_template_from_file( $path, BLFPST_Model_Template::$template_type_preset, 0 );

						if ( false !== $template ) {
							array_push( $templates, $template );
						}
					}
				}
			}
		}

		return $templates;
	}

	/**
	 * load template data from file
	 *
	 * @param string $template_file
	 * @param int $template_type
	 * @param int $user_id
	 *
	 * @return boolean | BLFPST_Model_Template
	 */
	public static function load_template_from_file( $template_file, $template_type = 0, $user_id = 0 ) {

		if ( ! isset( $template_file ) || ( '' === $template_file ) ) {
			return false;
		}

		if ( ! file_exists( $template_file ) ) {
			return false;
		}

		$load_content = file_get_contents( $template_file, true );

		$lines      = explode( "\n", $load_content ); // とりあえず行に分割

		if ( count( $lines ) < 3 ) {
			return false;
		}

		preg_match( '/<meta name="template_name" content="(.*)"\/>/i', $load_content, $titles );
		preg_match( '/<meta name="template_type" content="(.*)"\/>/i', $load_content, $types );
		preg_match( '/<meta name="template_author" content="(.*)"\/>/i', $load_content, $authors );
		preg_match( '/<meta name="template_description" content="(.*)"\/>/i', $load_content, $descriptions );

		$title = '';
		if ( 1 < count( $titles ) ) {
			$title = trim( $titles[1] );
		}

		$author = '';
		if ( 1 < count( $authors ) ) {
			$author = trim( $authors[1] );
		}

		$description = '';
		if ( 1 < count( $descriptions ) ) {
			$description = trim( $descriptions[1] );
		}

		$template                = new BLFPST_Model_Template();
		$template->title         = ( '' !== $title ) ? $title : '';
		$type                    = ( count( $types ) > 1 ) ? $types[1] : '';
		$pos                     = strpos( $load_content, $lines[2] );
		$content                 = substr( $load_content, $pos );
		$template->user_id       = $user_id;
		$template->content_type  = $type;
		$template->template_type = $template_type;
		$template->author        = $author;
		$template->description   = $description;
		$template->file_name     = basename( $template_file );

		if ( 'html' === $type ) {
			$template->html_content = $content;
		} elseif ( 'text' === $type ) {
			$template->text_content = $content;
		}

		return $template;
	}
}
