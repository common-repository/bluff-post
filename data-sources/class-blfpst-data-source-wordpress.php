<?php

/**
 * data source of WordPress.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Data_Source_Wordpress extends BLFPST_Abstract_Data_Source {
	public function __construct() {
	}

	public function create_custom_tables() {

	}

	/**
	 * name
	 *
	 * @return string
	 */
	public function name() {
		return __( 'WordPress', 'bluff-post' );
	}

	/**
	 * Application display name
	 *
	 * @return string
	 */
	public function display_name() {
		return __( 'WordPress', 'bluff-post' );
	}

	/**
	 * description
	 *
	 * @return string
	 */
	public function description() {
		return __( 'WordPress DB', 'bluff-post' );
	}

	/**
	 * id field name form user table
	 *
	 * @return string
	 */
	public function id_field_name() {
		return 'ID';
	}

	/**
	 * user first name field name form user table
	 *
	 * @return string
	 */
	public function user_first_name_field_name() {
		return 'display_name';
	}

	/**
	 * user last name field name form user table
	 *
	 * @return string
	 */
	public function user_last_name_field_name() {
		return '';
	}

	/**
	 * mail address field name form user table
	 *
	 * @return string
	 */
	public function email_field_name() {
		return 'user_email';
	}

	/**
	 * DB table names
	 *
	 * @return array
	 */
	public function table_params() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_names = array(
			$wpdb->prefix . 'users',
			$wpdb->prefix . 'usermeta',
			$wpdb->prefix . 'posts',
			$wpdb->prefix . 'postmeta',
			$wpdb->prefix . 'comments',
			$wpdb->prefix . 'commentmeta',
		);

		$table_params = array();
		foreach ( $table_names as $table_name ) {

			$fields         = array();
			$fields_results = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table_name );
			foreach ( $fields_results as $fields_result ) {
				array_push( $fields, $fields_result->{'Field'} );
			}

			array_push( $table_params, array( 'name' => $table_name, 'fields' => $fields ) );
		}

		return $table_params;
	}

	/**
	 * recipient count SQL
	 *
	 * @param BLFPST_Model_Target $target
	 * @param bool|false $is_count
	 *
	 * @return string
	 */
	public function recipient_sql( $target, $is_count = false ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$users_table    = $wpdb->prefix . 'users';
		$usermeta_table = $wpdb->prefix . 'usermeta';

		$posts_table    = $wpdb->prefix . 'posts';
		$postmeta_table = $wpdb->prefix . 'postmeta';

		$comments_table    = $wpdb->prefix . 'comments';
		$commentmeta_table = $wpdb->prefix . 'commentmeta';

		// users.ID
		$user_ids = array( $usermeta_table => 'user_id', $posts_table => 'post_author' );

		// posts.ID
		$post_ids = array( $postmeta_table => 'post_id', $comments_table => 'comment_post_ID' );

		// comment.ID
		$comment_ids = array( $commentmeta_table => 'comment_id' );

		$target_groups = $target->target_conditionals;

		// require 'users' table
		$tables = array( $users_table );

		// Parent
		for ( $parent_index = 0; $parent_index < count( $target_groups ); $parent_index ++ ) {

			/** @var BLFPST_Model_Target_Conditional $parent_conditional */
			$parent_conditional = $target->target_conditionals[ $parent_index ];
			$child_conditionals = $parent_conditional->target_conditionals;

			// Children
			for ( $i = 0; $i < count( $child_conditionals ); $i ++ ) {

				/** @var BLFPST_Model_Target_Conditional $child_conditional */
				$child_conditional = $child_conditionals[ $i ];
				$table             = $child_conditional->table_name;

				if ( ! in_array( $table, $tables ) ) {
					array_push( $tables, $table );

					// postmeta
					if ( $table === $postmeta_table ) {
						if ( ! in_array( $posts_table, $tables ) ) {
							array_push( $tables, $posts_table );
						}
					} // commentmeta
					else if ( $table === $commentmeta_table ) {
						if ( ! in_array( $comments_table, $tables ) ) {
							array_push( $tables, $comments_table );
						}
						if ( ! in_array( $posts_table, $tables ) ) {
							array_push( $tables, $posts_table );
						}
					} // comments
					else if ( $table === $comments_table ) {
						if ( ! in_array( $posts_table, $tables ) ) {
							array_push( $tables, $posts_table );
						}
					}
				}
			}
		}

		if ( $is_count ) {
			// count
			$sql = "SELECT count(*) FROM ${users_table} WHERE ${users_table}.ID IN (" . "SELECT distinct(${users_table}.ID) FROM ";
		} else {
			// 副問い合わせで複数の業抱えることがあるので IN を使う
			$sql = "SELECT * FROM ${users_table} WHERE ${users_table}.ID IN (" . "SELECT distinct(${users_table}.ID) FROM ";
		}

		// sub query
		for ( $i = 0; $i < count( $tables ); $i ++ ) {
			$table = $tables[ $i ];

			if ( 0 < $i ) {
				$sql .= ', ';
			}
			$sql .= $table;
		}

		$sql .= ' WHERE ';

		// users.ID
		for ( $i = 0; $i < count( $tables ); $i ++ ) {

			$table = $tables[ $i ];
			if ( isset( $user_ids[ $table ] ) ) {

				if ( 0 < $i ) {
					if ( '' !== $user_ids[ $table ] ) {
						$user_id = $user_ids[ $table ];
						$sql .= "(${users_table}.ID=${table}.${user_id}) AND ";
					}
				}
			}
		}

		// posts.ID
		if ( in_array( $posts_table, $tables ) ) {

			for ( $i = 0; $i < count( $tables ); $i ++ ) {

				$table = $tables[ $i ];
				if ( isset( $post_ids[ $table ] ) ) {

					if ( '' !== $post_ids[ $table ] ) {
						$post_id = $post_ids[ $table ];
						$sql .= "(${posts_table}.ID=$table.${post_id}) AND ";
					}
				}
			}
		}

		// comments.ID
		if ( in_array( $comments_table, $tables ) ) {

			for ( $i = 0; $i < count( $tables ); $i ++ ) {

				$table = $tables[ $i ];
				if ( isset( $comment_ids[ $table ] ) ) {

					if ( '' !== $comment_ids[ $table ] ) {
						$comment_id = $comment_ids[ $table ];
						$sql .= "(${comments_table}.comment_ID=${table}.${comment_id}) AND ";
					}
				}
			}
		}

		$where_sql = $this->create_where_recipient_sql( $target_groups, $users_table, 'user_email' );

		if ( false === $where_sql ) {
			$sql = '';
		} else {
			$sql .= $where_sql;

			// WHERE - IN end
			$sql .= ')';
		}

		return $sql;
	}

	/**
	 * recipient count SQL
	 *
	 * @param BLFPST_Model_Target $target
	 *
	 * @return string
	 */
	public function recipient_count_sql( $target ) {
		return $this->recipient_sql( $target, true );
	}

	/**
	 * mail content insertion description
	 *
	 * @return string
	 */
	public function insertion_description() {
		$description = '<table class="table"><thead>' .
		               '<tr><th>' . esc_html__( 'Replace key', 'bluff-post' ) . '</td><th>' . esc_html__( 'Replace value', 'bluff-post' ) . '</td></tr>' .
		               '</thead><tbody>' .
		               '<tr><td>%%user_name%%</td><td>' . esc_html__( 'Receiver name', 'bluff-post' ) . '(display_name)</td></tr>' .
		               '<tr><td>%%user_mail_address%%</td><td>' . esc_html__( 'Receiver e-mail address', 'bluff-post' ) . '(user_email)</td></tr>' .
		               '<tr><td>%%user_id%%</td><td>' . esc_html__( 'Receiver ID', 'bluff-post' ) . '</td></tr>' .
		               '<tr><td>%%mail_id%%</td><td>' . esc_html__( 'Mail ID', 'bluff-post' ) . '</td></tr>' .
		               '<tr><td>%%mail_page_url%%</td><td>' . esc_html__( 'Mail page URL', 'bluff-post' ) . '</td></tr>' .
		               '<tr><td>%%random_id%%</td><td>' . esc_html__( 'Random ID for Google Measurement Protocol (&cid)', 'bluff-post' ) . '</td></tr>' .
		               '</tbody></table>';

		return $description;
	}

	/**
	 * mail tracking description
	 *
	 * @return string
	 */
	public function mail_tracking_description() {
		$description = 'Mail Tracking ....';

		return $description;
	}
}
