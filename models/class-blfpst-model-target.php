<?php

/**
 * send target model.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Model_Target {

	/**
	 * @var int
	 */
	public static $target_type_user = 0;

	/**
	 * @var int
	 */
	public static $target_type_preset = 1;

	/**
	 * get_resultで他のテーブルのidと混在するため、テーブル単体での使用以外は使用しないこと
	 * @var int
	 */
	public $id = 0;

	/**
	 * @var int
	 */
	public $user_id = 0;

	/**
	 * @var int
	 * 0:ユーザー 1:プリセット
	 */
	public $target_type = 0;

	/**
	 * @var string
	 */
	public $class_name = '';

	/**
	 * @var string
	 */
	public $file_name = '';

	/**
	 * @var string
	 */
	public $title = '';

	/**
	 * @var string
	 */
	public $type = '';

	/**
	 * @var int
	 */
	public $count = 0;

	/**
	 * @var string
	 */
	public $description = '';

	/**
	 * @var array
	 */
	public $target_conditionals = array();

	/**
	 * @var string
	 */
	public $updated_at = '';

	/**
	 * @var string
	 */
	public $created_at = '';

	/**
	 * set parameter from data
	 *
	 * @param array $conditional_results
	 */
	public function set_result( $conditional_results ) {

		if ( empty( $conditional_results ) ) {
			return;
		}

		/** @var object $targets_result */
		$first_conditional = $conditional_results[0];
		$this->id          = $first_conditional->id;
		$this->user_id     = $first_conditional->user_id;
		$this->target_type = $first_conditional->target_type;
		$this->class_name  = stripslashes( $first_conditional->class_name );
		$this->file_name   = $first_conditional->file_name;
		$this->title       = stripslashes( $first_conditional->title );
		$this->type        = $first_conditional->type;
		$this->count       = 0;
		$this->description = stripslashes( $first_conditional->description );
		$this->updated_at  = $first_conditional->updated_at;
		$this->created_at  = $first_conditional->created_at;

        if ( isset( $first_conditional->parent_target_conditional_id ) ) {
            foreach ( $conditional_results as $conditional ) {

                // 本来は再帰処理/v1.0では２階層

                // parent
                if ( 0 == $conditional->parent_target_conditional_id ) {
                    $new_parent_conditional = new BLFPST_Model_Target_Conditional();
                    $new_parent_conditional->set_result( $conditional );

                    $this->set_child_conditional_with_results( $conditional_results, $new_parent_conditional );

                    array_push( $this->target_conditionals, $new_parent_conditional );
                }
            }
        }
    }

	private function set_child_conditional_with_results( $conditional_results, $parent_conditional ) {
		foreach ( $conditional_results as $child_conditional ) {

			// child
			if ( $child_conditional->parent_target_conditional_id == $parent_conditional->id ) {
				$new_child_conditional                               = new BLFPST_Model_Target_Conditional();
				$new_child_conditional->set_result( $child_conditional );

				array_push( $parent_conditional->target_conditionals, $new_child_conditional );
			}
		}
	}

	/**
	 * @return string
	 */
	public static function table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_targets';
	}

	/**
	 * @return string
	 */
	public static function meta_table_name() {
		global $wpdb;

		return $wpdb->prefix . 'blfpst_target_meta';
	}
}
