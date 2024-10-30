<?php

/**
 * send target group model.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Model_Target_Conditional {

	/**
	 * get_resultで他のテーブルのidと混在するため使用しないこと
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var int
	 */
	public $target_id = 0;

	/**
	 * @var int
	 */
	public $parent_target_conditional_id = 0;

	/**
	 * @var int
	 */
	public $order_index = 0;

	/**
	 * @var string
	 */
	public $class_name = '';

	/**
	 * @var string
	 */
	public $and_or = '';

	/**
	 * @var string
	 */
	public $table_name = '';

	/**
	 * @var string
	 */
	public $column_name = '';

	/**
	 * @var string
	 */
	public $compare = '';

	/**
	 * @var string
	 */
	public $column_value = '';


	/**
	 * @var string
	 */
	public $updated_at = '';

	/**
	 * @var string
	 */
	public $created_at = '';

	/**
	 * @var array
	 */
	public $target_conditionals = array();

	/**
	 * set parameter from data
	 *
	 * @param OBJECT $result
	 */
	public function set_result( $result ) {
		$this->id                           = $result->target_conditional_id;
		$this->target_id                    = $result->id;
		$this->parent_target_conditional_id = $result->parent_target_conditional_id;
		$this->class_name                   = $result->class_name;
		$this->order_index                  = $result->order_index;
		$this->and_or                       = $result->and_or;
		$this->table_name                   = $result->table_name;
		$this->column_name                  = $result->column_name;
		$this->compare                      = $result->compare;
		$this->column_value                 = stripslashes( $result->column_value );
		$this->updated_at                   = $result->updated_at;
		$this->created_at                   = $result->created_at;
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_target_conditionals';
	}
}
