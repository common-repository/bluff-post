<?php

/**
 * data source of Welcart.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Data_Source_Welcart extends BLFPST_Abstract_Data_Source {
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
		return __( 'Welcart', 'bluff-post' );
	}

	/**
	 * Application display name
	 *
	 * @return string
	 */
	public function display_name() {
		return __( 'Welcart', 'bluff-post' );
	}

	/**
	 * description
	 *
	 * @return string
	 */
	public function description() {
		return __( 'Welcart DB', 'bluff-post' );
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
		return 'mem_name2';
	}

	/**
	 * user last name field name form user table
	 *
	 * @return string
	 */
	public function user_last_name_field_name() {
		return 'mem_name1';
	}

	/**
	 * mail address field name form user table
	 *
	 * @return string
	 */
	public function email_field_name() {
		return 'mem_email';
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
			$wpdb->prefix . 'usces_member',
			$wpdb->prefix . 'usces_member_meta',
			$wpdb->prefix . 'usces_order',
			$wpdb->prefix . 'usces_order_meta',
			$wpdb->prefix . 'usces_ordercart',
			$wpdb->prefix . 'usces_ordercart_meta',
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

		$usces_member_table         = $wpdb->prefix . 'usces_member';
		$usces_order_table          = $wpdb->prefix . 'usces_order';
		$usces_ordercart_table      = $wpdb->prefix . 'usces_ordercart';
		$usces_member_meta_table    = $wpdb->prefix . 'usces_member_meta';
		$usces_order_meta_table     = $wpdb->prefix . 'usces_order_meta';
		$usces_ordercart_meta_table = $wpdb->prefix . 'usces_ordercart_meta';

		// usces_member.ID
		$usces_member_ids = array( $usces_member_meta_table => 'member_id', $usces_order_table => 'mem_id' );

		// usces_order.ID
		$usces_order_ids = array( $usces_order_meta_table => 'order_id', $usces_ordercart_table => 'order_id' );

		// usces_ordercart.ID
		$usces_ordercart_ids = array( $usces_ordercart_meta_table => 'cart_id' );

		$target_groups = $target->target_conditionals;

		// require 'usces_member' table
		$tables = array( $usces_member_table );

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

					// usces_order_meta
					if ( $table === $usces_order_meta_table ) {
						if ( ! in_array( $usces_order_table, $tables ) ) {
							array_push( $tables, $usces_order_table );
						}
					} // usces_ordercart_meta
					else if ( $table === $usces_ordercart_meta_table ) {
						if ( ! in_array( $usces_order_table, $tables ) ) {
							array_push( $tables, $usces_order_table );
						}
						if ( ! in_array( $usces_ordercart_table, $tables ) ) {
							array_push( $tables, $usces_ordercart_table );
						}
					} // usces_ordercart
					else if ( $table === $usces_ordercart_table ) {
						if ( ! in_array( $usces_order_table, $tables ) ) {
							array_push( $tables, $usces_order_table );
						}
					}
				}
			}
		}

		if ( $is_count ) {
			// count
			$sql = "SELECT count(*) FROM ${usces_member_table} WHERE ${usces_member_table}.ID IN (" . "SELECT distinct(${usces_member_table}.ID) FROM ";
		} else {
			// 副問い合わせで複数の行を返すことがあるので IN を使う
			$sql = "SELECT * FROM ${usces_member_table} WHERE ${usces_member_table}.ID IN (" . "SELECT distinct(${usces_member_table}.ID) FROM ";
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

		// USER ID
		for ( $i = 0; $i < count( $tables ); $i ++ ) {

			$table = $tables[ $i ];
			if ( isset( $usces_member_ids[ $table ] ) ) {

				if ( 0 < $i ) {
					if ( '' !== $usces_member_ids[ $table ] ) {
						$usces_member_id = $usces_member_ids[ $table ];
						$sql .= "(${usces_member_table}.ID=${table}.${usces_member_id}) AND ";
					}
				}
			}
		}

		// usces_order.ID
		if ( in_array( $usces_order_table, $tables ) ) {

			for ( $i = 0; $i < count( $tables ); $i ++ ) {

				$table = $tables[ $i ];
				if ( isset( $usces_order_ids[ $table ] ) ) {

					if ( '' !== $usces_order_ids[ $table ] ) {
						$usces_order_id = $usces_order_ids[ $table ];
						$sql .= "(${usces_order_table}.ID=$table.${usces_order_id}) AND ";
					}
				}
			}
		}

		// usces_ordercart.ID
		if ( in_array( $usces_ordercart_table, $tables ) ) {

			for ( $i = 0; $i < count( $tables ); $i ++ ) {

				$table = $tables[ $i ];
				if ( isset( $usces_ordercart_ids[ $table ] ) ) {

					if ( '' !== $usces_ordercart_ids[ $table ] ) {
						$usces_ordercart_id = $usces_ordercart_ids[ $table ];
						$sql .= "(${usces_ordercart_table}.cart_id=${table}.${usces_ordercart_id}) AND ";
					}
				}
			}
		}

		$where_sql = $this->create_where_recipient_sql( $target_groups, $usces_member_table, 'mem_email' );

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
		               '<tr><td>%%user_name%%</td><td>' . esc_html__( 'Receiver name', 'bluff-post' ) . '(mem_name1+mem_name2)</td></tr>' .
		               '<tr><td>%%user_last_name%%</td><td>' . esc_html__( 'Receiver last name', 'bluff-post' ) . '(mem_name1)</td></tr>' .
		               '<tr><td>%%user_first_name%%</td><td>' . esc_html__( 'Receiver first name', 'bluff-post' ) . '(mem_name2)</td></tr>' .
		               '<tr><td>%%user_mail_address%%</td><td>' . esc_html__( 'Receiver e-mail address', 'bluff-post' ) . '(mem_email)</td></tr>' .
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
		$description = '<div>' .
		               '<p>Mail Tracking by Google Measurement Protocol. 本システムではGoogle Measurement Protocolにより送信したメールの効果測定を行うことができます。</p>' .
		               '<p><a href="#">see this page. 詳しくはこちらのページの解説を御覧ください。</a></p>' .
		               '</div>';

		return $description;
	}
}
