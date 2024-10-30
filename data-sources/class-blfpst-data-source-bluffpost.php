<?php

/**
 * data source of Bluff Post.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Data_Source_Bluffpost extends BLFPST_Abstract_Data_Source {
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
		return __( 'Bluffpost', 'bluff-post' );
	}

	/**
	 * Application display name
	 *
	 * @return string
	 */
	public function display_name() {
		return __( 'Bluff Post', 'bluff-post' );
	}

	/**
	 * description
	 *
	 * @return string
	 */
	public function description() {
		return __( 'Bluff Post DB', 'bluff-post' );
	}

	/**
	 * id field name form user table
	 *
	 * @return string
	 */
	public function id_field_name() {
		return 'id';
	}

	/**
	 * user first name field name form user table
	 *
	 * @return string
	 */
	public function user_first_name_field_name() {
		return 'first_name';
	}

	/**
	 * user last name field name form user table
	 *
	 * @return string
	 */
	public function user_last_name_field_name() {
		return 'last_name';
	}

	/**
	 * mail address field name form user table
	 *
	 * @return string
	 */
	public function email_field_name() {
		return 'mail_address';
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
			BLFPST_Model_Recipient::table_name(),
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
		$users_table    = BLFPST_Model_Recipient::table_name();
		$usermeta_table = BLFPST_Model_Recipient::meta_table_name();

		$user_ids = array( $usermeta_table => 'recipient_id' );

		$target_groups = $target->target_conditionals;

		// require table
		$tables = array( $users_table );

		// Parent
		for ( $parent_index = 0; $parent_index < count( $target_groups ); $parent_index ++ ) {

			/** @var BLFPST_Model_Target_Conditional $parent_conditional */
			$parent_conditional = $target->target_conditionals[ $parent_index ];
			$child_conditionals = $parent_conditional->target_conditionals;

			for ( $i = 0; $i < count( $child_conditionals ); $i ++ ) {

				/** @var BLFPST_Model_Target_Conditional $child_conditional */
				$child_conditional = $child_conditionals[ $i ];
				$table             = $child_conditional->table_name;

				if ( ! in_array( $table, $tables ) ) {
					array_push( $tables, $table );
				}
			}
		}

		// 副問い合わせで複数の行を返すことがあるので IN を使う
		if ( $is_count ) {
			$sql = "SELECT count(*) FROM ${users_table} WHERE (${users_table}.status='subscribe') AND ${users_table}.id IN (" . "SELECT distinct(${users_table}.id) FROM ";
		} else {
			$sql = "SELECT * FROM ${users_table} WHERE (${users_table}.status='subscribe') AND ${users_table}.id IN (" . "SELECT distinct(${users_table}.id) FROM ";
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

		for ( $i = 0; $i < count( $tables ); $i ++ ) {

			$table = $tables[ $i ];
			if ( isset( $user_ids[ $table ] ) ) {

				if ( $i > 0 ) {
					if ( '' !== $user_ids[ $table ] ) {
						$user_id = $user_ids[ $table ];
						$sql .= "(${users_table}.ID=${table}.${user_id}) AND ";
					}
				}
			}
		}

		$where_sql = $this->create_where_recipient_sql( $target_groups, $users_table, 'mail_address' );

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
		               '<tr><td>%%user_name%%</td><td>' . esc_html__( 'Receiver name', 'bluff-post' ) . '</td></tr>' .
		               '<tr><td>%%user_last_name%%</td><td>' . esc_html__( 'Receiver last name', 'bluff-post' ) . '</td></tr>' .
		               '<tr><td>%%user_first_name%%</td><td>' . esc_html__( 'Receiver first name', 'bluff-post' ) . '</td></tr>' .
		               '<tr><td>%%user_mail_address%%</td><td>' . esc_html__( 'Receiver e-mail address', 'bluff-post' ) . '</td></tr>' .
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
