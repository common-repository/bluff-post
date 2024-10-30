<?php

/**
 * data source.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
abstract class BLFPST_Abstract_Data_Source {
	abstract public function name();

	abstract public function display_name();

	abstract public function description();

	abstract public function table_params();

	abstract public function id_field_name();

	abstract public function user_first_name_field_name();

	abstract public function user_last_name_field_name();

	abstract public function email_field_name();

	abstract function recipient_sql( $target, $is_count = false );

	abstract public function recipient_count_sql( $target );

	abstract public function insertion_description();

	abstract public function mail_tracking_description();

	static private $compares = array(
		'='         => '=',
		'<>'        => '<>',
		'<'         => '<',
		'<='        => '<=',
		'>'         => '>',
		'>='        => '>=',
		'LIKE'      => 'LIKE',
		'NOTLIKE'   => 'NOT LIKE',
		'ISNULL'    => 'IS NULL',
		'ISNOTNULL' => 'IS NOT NULL',
	);

	/**
	 * @param array $target_groups
	 * @param string $users_table
	 * @param string $mail_address
	 *
	 * @return string | bool
	 */
	public function create_where_recipient_sql( $target_groups, $users_table, $mail_address ) {

		$error = false;
		$sql   = '';

		// User WHERE
		$use_where_parenthesis = ( 0 < count( $target_groups ) );
		$sql .= $use_where_parenthesis ? '(' : '';

		// Parent
		for ( $parent_index = 0; $parent_index < count( $target_groups ); $parent_index ++ ) {

			/** @var BLFPST_Model_Target_Conditional $parent_conditional */
			$parent_conditional    = $target_groups[ $parent_index ];
			$child_conditionals    = $parent_conditional->target_conditionals;
			$and_or                = $parent_conditional->and_or;
			$use_group_parenthesis = ( 1 < count( $target_groups ) );

			$sql .= ( 0 < $parent_index ) ? " $and_or " : '';
			$sql .= $use_group_parenthesis ? '(' : '';

			for ( $i = 0; $i < count( $child_conditionals ); $i ++ ) {

				/** @var BLFPST_Model_Target_Conditional $child_conditional */
				$child_conditional     = $child_conditionals[ $i ];
				$use_child_parenthesis = ( 1 < count( $child_conditionals ) );

				$table   = $child_conditional->table_name;
				$field   = $child_conditional->column_name;
				$value   = $child_conditional->column_value;
				$compare = $child_conditional->compare;
				$and_or  = $child_conditional->and_or;

				$compare_val = self::$compares[ $compare ];
				if ( empty( $compare_val ) ) {
					$error = true;
					break;
				}

				$sql .= ( 0 < $i ) ? " $and_or " : '';
				$sql .= $use_child_parenthesis ? '(' : '';

				if ( ( 'LIKE' == $compare ) || ( 'NOTLIKE' == $compare ) ) {
					$sql .= "${table}.${field} ${compare_val} '%" . esc_sql( $value ) . "%'";
				} else if ( ( 'ISNULL' == $compare ) || ( 'ISNOTNULL' == $compare ) ) {
					$sql .= "${table}.${field} ${compare_val}";
				} else {
					$sql .= "${table}.${field}${compare_val}'" . esc_sql( $value ) . "'";
				}

				$sql .= $use_child_parenthesis ? ')' : '';
			}

			$sql .= $use_group_parenthesis ? ')' : '';
		}

		$sql .= $use_where_parenthesis ? ')' : '';

		// exclude recipients
		$and_or             = ( 0 < count( $target_groups ) ) ? ' AND' : '';
		$exclude_recipients = BLFPST_Model_Exclude_Recipient::table_name();
		$sql .= "${and_or} (lower(${users_table}.${mail_address}) NOT IN";
		$sql .= " (select distinct(lower(${exclude_recipients}.mail_address)) FROM ${exclude_recipients})";
		$sql .= ')';

		if ( $error ) {
			$sql = false;
		}

		return $sql;
	}
}
