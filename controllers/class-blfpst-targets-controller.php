<?php

/**
 * send target controller.
 * PHP Version 5.4.0
 * Version 1.1.1
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Targets_Controller {

	/**
	 * initialize
	 *
	 * @return void
	 */
	public function initialize() {
	}

	/**
	 *  Execute SQL Query
	 *
	 * @param integer $target_id target identifier
	 * @param integer $page_num
	 * @param integer $limit
	 * @param string &$sql
	 *
	 * @return array|false
	 */
	public static function execute_query_recipient( $target_id, $page_num = - 1, $limit = 0, &$sql ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$target = self::load_target_info( $target_id );
		$sql    = '';

		if ( ! empty( $target ) ) {
			$data_source = self::data_source_object( $target );
			$sql         = $data_source->recipient_sql( $target );

			if ( $page_num >= 0 ) {
				$sql .= $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
			};

			if ( ! empty( $sql ) ) {
				$results = $wpdb->get_results( $sql );
			} else {
				error_log( 'MySQL Error: ' . $wpdb->last_error );

				// error
				$results = false;
			}
		} else {
			$results = array();
		}

		return $results;
	}

	/**
	 *  Execute SQL Query
	 *
	 * @return integer|null Database query result (as string), or null on failure
	 */
	public static function execute_query_targets_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Target::table_name();
		$sql        = "SELECT count(*) FROM ${table_name} ORDER BY id";

		$results = $wpdb->get_var( $sql );

		return $results;
	}

	/**
	 *  Execute SQL Query
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return int|false
	 */
	public static function execute_query_recipients_count( $target_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$target = self::load_target_info( $target_id );

		if ( ! empty( $target ) ) {
			$data_source = self::data_source_object( $target );
			$sql         = $data_source->recipient_count_sql( $target );

			if ( ! empty( $sql ) ) {
				$results = $wpdb->get_var( $sql );
			} else {
				// error
				$results = false;
			}
		} else {
			$results = 0;
		}

		return $results;
	}

	/**
	 * register target data
	 *
	 * @return void
	 */
	public static function target_register() {
		if ( ! current_user_can( 'blfpst_edit_target' ) ) {
			wp_die( esc_html__( 'Invalid access.', 'bluff-post' ) );
		}

		/**
		 * @var BLFPST_Model_Target $target
		 * @var WP_Error $errors
		 */
		$result = self::targets_from_post();
		$target = $result['target'];
		$errors = $result['errors'];

		if ( count( $errors->errors ) == 0 ) {
			self::register_target_to_db( $target, get_current_user_id(), true );
		}

		$_REQUEST['targets'] = json_encode( $target ); // for redirect
		$_REQUEST['errors']  = json_encode( $errors ); // for redirect
	}

	/**
	 * register target data
	 *
	 * @param BLFPST_Model_Target $target
	 * @param integer $user_id
	 * @param boolean $enable_redirect
	 *
	 * @return void
	 */
	public static function register_target_to_db( $target, $user_id, $enable_redirect = true ) {

		$errors    = new WP_Error();
		$target_id = (int) $target->id;

		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Target::table_name();

		if ( 0 == $target_id ) {
			$result = $wpdb->insert(
				$table_name,
				array(
					'user_id'     => $user_id,
					'target_type' => $target->target_type,
					'class_name'  => $target->class_name,
					'title'       => $target->title,
					'type'        => 'builder',
					'updated_at'  => current_time( 'mysql', 0 ),
					'created_at'  => current_time( 'mysql', 0 ),
				),
				array(
					'%d',
					'%s',
					'%s',
					'%s',
                    '%s',
                    '%s',
					'%s',
				)
			);

			$target_id = $wpdb->insert_id;

		} else {
			// Delete group,condition
			self::delete_target_conditional( $target_id );

			// Update
			$result = $wpdb->update(
				$table_name,
				array(
					'user_id'    => $user_id,
					'title'      => $target->title,
					'updated_at' => current_time( 'mysql', 0 ),
				),
				array( 'ID' => $target_id ),
				array(
					'%d', // user id
					'%s', // title
					'%s', // updated_at
				),
				array( '%d' )
			);
		}

		if ( $result && ( $target_id > 0 ) ) {
			$target_parent_conditionals = $target->target_conditionals;

			for ( $i = 0; $i < count( $target_parent_conditionals ); $i ++ ) {

				/** @var BLFPST_Model_Target_Conditional $parent_conditional */
				$parent_conditional = $target_parent_conditionals[ $i ];
				$table_name         = BLFPST_Model_Target_Conditional::table_name();

				$result = $wpdb->insert(
					$table_name,
					array(
						'target_id'                    => $target_id,
						'parent_target_conditional_id' => 0,
						'class_name'                   => $target->class_name,
						'order_index'                  => $i,
						'and_or'                       => $parent_conditional->and_or,
						'updated_at'                   => current_time( 'mysql', 0 ),
						'created_at'                   => current_time( 'mysql', 0 ),
					),
					array(
						'%d',
						'%d',
						'%s',
						'%d',
						'%s',
						'%s',
						'%s',
					)
				);

				if ( $result ) {

					$parent_target_conditional_id = $wpdb->insert_id;
					$target_conditionals          = $parent_conditional->target_conditionals;

					for ( $j = 0; $j < count( $target_conditionals ); $j ++ ) {

						/** @var BLFPST_Model_Target_Conditional $target_conditional */
						$target_conditional = $target_conditionals[ $j ];

						$table_name = BLFPST_Model_Target_Conditional::table_name();

						$result = $wpdb->insert(
							$table_name,
							array(
								'target_id'                    => $target_id,
								'parent_target_conditional_id' => $parent_target_conditional_id,
								'class_name'                   => $target->class_name,
								'order_index'                  => $j,
								'and_or'                       => $target_conditional->and_or,
								'table_name'                   => $target_conditional->table_name,
								'column_name'                  => $target_conditional->column_name,
								'compare'                      => $target_conditional->compare,
								'column_value'                 => $target_conditional->column_value,
								'updated_at'                   => current_time( 'mysql', 0 ),
								'created_at'                   => current_time( 'mysql', 0 ),
							),
							array(
								'%d',
								'%d',
								'%s',
								'%d',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
							)
						);

						if ( ! $result ) {
							$errors->add( 'Error', esc_html__( 'An error has occurred in the registration of parameters.', 'bluff-post' ) );
							break;
						}
					}

					if ( count( $errors->errors ) > 0 ) {
						break;
					}
				} else {
					$errors->add( 'Error', esc_html__( 'An error has occurred in the registration of group.', 'bluff-post' ) );
					break;
				}
			}

			if ( count( $errors->errors ) == 0 ) {
				if ( $enable_redirect ) {
					wp_safe_redirect( admin_url( 'admin.php?page=blfpst-targets' ) );
					exit;
				}
			}
		} else {
			$errors->add( 'Error', esc_html__( 'An error has occurred in the registration of recipients.', 'bluff-post' ) );
		}
	}

	/**
	 * register target data
	 *
	 * @return array
	 */
	public static function targets_from_post() {
		$errors = new WP_Error();

		$target_title = isset( $_POST['title'] ) ? stripslashes( $_POST['title'] ) : '';

		if ( '' === $target_title ) {
			$errors->add( 'Error', esc_html__( 'Please enter a recipients name.', 'bluff-post' ) );
		}

		if ( mb_strlen( $target_title ) > 255 ) {
			$errors->add( 'Error', esc_html__( 'Please enter a recipients name 255 or less characters.', 'bluff-post' ) );
		}

		// view parameter
		$target             = new BLFPST_Model_Target();
		$target->id         = ! empty( $_POST['target_id'] ) ? $_POST['target_id'] : 0;
		$target->title      = isset( $_POST['title'] ) ? stripslashes( $_POST['title'] ) : '';
		$target->class_name = ! empty( $_POST['class_name'] ) ? $_POST['class_name'] : BLFPST::get_option( 'target_database_name', '' );

		$parent_count = isset( $_POST['conditional_count'] ) ? $_POST['conditional_count'] : '';

		// Parent
		for ( $i = 0; $i < $parent_count; $i ++ ) {

			$child_count = isset( $_POST[ 'conditional_count' . $i ] ) ? $_POST[ 'conditional_count' . $i ] : 0;

			$parent_conditional = new BLFPST_Model_Target_Conditional();

			// Conditional
			for ( $j = 0; $j < $child_count; $j ++ ) {

				$key = $i . '-' . $j;

				$table_name   = isset( $_POST[ 'table_name' . $key ] ) ? $_POST[ 'table_name' . $key ] : '';
				$compare      = isset( $_POST[ 'compare' . $key ] ) ? $_POST[ 'compare' . $key ] : '';
				$column_name  = isset( $_POST[ 'column_name' . $key ] ) ? $_POST[ 'column_name' . $key ] : '';
				$column_value = isset( $_POST[ 'column_value' . $key ] ) ? stripslashes( $_POST[ 'column_value' . $key ] ) : '';
				$and_or       = isset( $_POST[ 'and_or' . $key ] ) ? $_POST[ 'and_or' . $key ] : '';

				if ( '' === $table_name ) {
					$errors->add( 'Error', esc_html__( 'Please select a table.', 'bluff-post' ) );
				}

				if ( '' === $column_name ) {
					$errors->add( 'Error', esc_html__( 'Please enter a column.', 'bluff-post' ) );
				}

				if ( '' === $compare ) {
					$errors->add( 'Error', esc_html__( 'Please enter a compare.', 'bluff-post' ) );
				}

				if ( ( 'ISNULL' !== $compare ) && ( 'ISNOTNULL' !== $compare ) ) {
					if ( '' === $column_value ) {
						$errors->add( 'Error', esc_html__( 'Please enter a parameter value.', 'bluff-post' ) );
					}
				} else {
					$column_value = '';
				}

				if ( mb_strlen( $column_value ) > 255 ) {
					$errors->add( 'Error', esc_html__( 'Please enter a recipients parameter value 255 or less characters.', 'bluff-post' ) );
				}

				$target_conditional               = new BLFPST_Model_Target_Conditional();
				$target_conditional->and_or       = $and_or;
				$target_conditional->compare      = $compare;
				$target_conditional->table_name   = $table_name;
				$target_conditional->column_name  = $column_name;
				$target_conditional->column_value = $column_value;
				array_push( $parent_conditional->target_conditionals, $target_conditional );
			}

			$parent_conditional->and_or = isset( $_POST[ 'and_or' . $i ] ) ? $_POST[ 'and_or' . $i ] : '';
			array_push( $target->target_conditionals, $parent_conditional );
		}

		return array(
			'errors' => $errors,
			'target' => $target,
		);
	}

	/**
	 * request target data
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return array|false
	 */
	private static function request_target( $target_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		// テーブル
		$targets_table             = BLFPST_Model_Target::table_name();
		$target_conditionals_table = BLFPST_Model_Target_Conditional::table_name();

		// SQL
		$sql = $wpdb->prepare( "SELECT * FROM ${targets_table}, ${target_conditionals_table} WHERE ($targets_table.id=%d) AND ($targets_table.id=$target_conditionals_table.target_id) ORDER BY $target_conditionals_table.order_index", $target_id );

		// Request
		$targets_results = $wpdb->get_results( $sql );

		if ( null === $targets_results ) {
			error_log( 'DB Error, could not select blfpst_targets' );
			error_log( 'MySQL Error: ' . $wpdb->last_error );

			return false;
		}

		return $targets_results;
	}

    /**
     * request target data
     *
     * @param integer $target_id target identifier
     *
     * @return array|false
     */
    private static function request_target_without_conditionals( $target_id ) {
        /** @var wpdb $wpdb */
        global $wpdb;

        // テーブル
        $targets_table = BLFPST_Model_Target::table_name();

        // SQL
        $sql = $wpdb->prepare( "SELECT * FROM ${targets_table} WHERE ($targets_table.id=%d)", $target_id );

        // Request
        $targets_results = $wpdb->get_results( $sql );

        if ( null === $targets_results ) {
            error_log( 'DB Error, could not select blfpst_targets' );
            error_log( 'MySQL Error: ' . $wpdb->last_error );

            return false;
        }

        return $targets_results;
    }

	/**
	 * load target data from DB
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return BLFPST_Model_Target|false
	 */
	public static function load_target_info( $target_id ) {
        $target = new BLFPST_Model_Target();

        $targets_results = self::request_target( $target_id );
        if ( false === $targets_results ) {
            return false;
        }

        # no conditional
        if ( count( $targets_results ) == 0 ) {
            $targets_results = self::request_target_without_conditionals( $target_id );
            if ( empty( $targets_results ) ) {
                return false;
            }
        }

        if ( count( $targets_results ) > 0 ) {
            $target->set_result( $targets_results );
        }

		return $target;
	}

	/**
	 * load source table parameter from DB
	 *
	 * @param BLFPST_Model_Target $target
	 *
	 * @return array
	 */
	public static function table_data( $target ) {
		$class = 'BLFPST_Data_Source_' . $target->class_name;

		/** @var BLFPST_Abstract_Data_Source $data_source */
		$data_source  = new $class();
		$table_params = $data_source->table_params();

		return $table_params;
	}

	/**
	 * default load source table parameter from DB
	 *
	 * @return array
	 */
	public static function default_table_data() {
		$target_database_name = BLFPST::get_option( 'target_database_name', '' );

		$table_params = array();

		if ( ! empty( $target_database_name ) ) {
			$class = 'BLFPST_Data_Source_' . $target_database_name;

			/** @var BLFPST_Abstract_Data_Source $data_source */
			$data_source  = new $class();
			$table_params = $data_source->table_params();
		}

		return $table_params;
	}

	/**
	 * load blfpst_targets from DB
	 *
	 * @param int $page_num
	 * @param int $limit
	 * @param boolean $desc
	 *
	 * @return array
	 */
	public static function load_targets( $page_num = - 1, $limit = 0, $desc = false ) {
		$targets = array();

		if ( current_user_can( 'blfpst_edit_mail' ) ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$table_name = BLFPST_Model_Target::table_name();
			$sql        = "SELECT * FROM ${table_name} ORDER BY id";

			if ( $desc ) {
				$sql .= ' DESC';
			}

			if ( $page_num >= 0 ) {
				$sql .= $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
			}

			$targets_results = $wpdb->get_results( $sql );
			if ( null === $targets_results ) {
				error_log( 'DB Error, could not select blfpst_targets' );
				error_log( 'MySQL Error: ' . $wpdb->last_error );
			} else {
				foreach ( $targets_results as $targets_result ) {
					$target        = new BLFPST_Model_Target();
					$target->id    = $targets_result->id;
					$target->title = $targets_result->title;
					array_push( $targets, $target );
				}
			}
		}

		return $targets;
	}

	/**
	 * load blfpst_targets and recipient count from DB
	 *
	 * @return array
	 */
	public static function load_targets_with_recipient_count() {
		$targets = array();

		if ( current_user_can( 'blfpst_edit_mail' ) ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$table_name = BLFPST_Model_Target::table_name();
			$sql        = "SELECT * FROM ${table_name}";

			$targets_results = $wpdb->get_results( $sql );

			if ( null === $targets_results ) {
				error_log( 'DB Error, could not select blfpst_targets' );
				error_log( 'MySQL Error: ' . $wpdb->last_error );
			} else {
				foreach ( $targets_results as $targets_result ) {

					// 宛先件数
					$count = self::execute_query_recipients_count( $targets_result->id );
					$count = ( false === $count ) ? 0 : $count;

					$target        = new BLFPST_Model_Target();
					$target->id    = $targets_result->id;
					$target->title = $targets_result->title;
					$target->count = (int) $count;

					array_push( $targets, $target );
				}
			}
		}

		return $targets;
	}

	/**
	 * load blfpst_targets from DB
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return BLFPST_Model_Target
	 */
	public static function load_target( $target_id ) {
		$target = array();

		if ( current_user_can( 'blfpst_edit_mail' ) ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$table_name = BLFPST_Model_Target::table_name();
			$sql        = $wpdb->prepare( "SELECT * FROM ${table_name} WHERE (id='%d')", $target_id );

			$targets_results = $wpdb->get_results( $sql );
			if ( null === $targets_results ) {
				error_log( 'DB Error, could not select blfpst_targets' );
				error_log( 'MySQL Error: ' . $wpdb->last_error );
			} else {
				if ( count( $targets_results ) > 0 ) {
					$targets_result = $targets_results[0];

					$target        = new BLFPST_Model_Target();
					$target->id    = $targets_result->id;
					$target->title = $targets_result->title;
				}
			}
		}

		return $target;
	}

	/**
	 * get send_mails data count
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return integer
	 */
	public static function load_send_mail_count_with_target_id( $target_id ) {
		$count = 0;

		if ( current_user_can( 'blfpst_edit_mail' ) ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$table_name = BLFPST_Model_Send_Mail::table_name();
			$sql        = $wpdb->prepare( "SELECT count(*) FROM ${table_name} WHERE (target_id='%d') AND (send_result='wait') AND ((status='reserved') OR (status='send')) AND (send_request_end_at IS NULL)", $target_id );
			$count      = $wpdb->get_var( $sql );

			if ( null === $count ) {
				error_log( 'DB Error, could not select blfpst_targets' );
				error_log( 'MySQL Error: ' . $wpdb->last_error );
			} else {
				$count = (int) $count;
			}
		}

		return $count;
	}

	/**
	 * Set default options
	 *
	 * @return void
	 */
	public static function set_default_option() {
		// Data source
		$target_database_name = BLFPST::get_option( 'target_database_name' );
		if ( ! $target_database_name ) {
			BLFPST::update_option( 'target_database_name', 'WordPress' );
		}
	}

	/**
	 * Update options
	 *
	 * @return void
	 */
	public static function update_option() {
		$errors = new WP_Error();

		// Data source
		if ( isset( $_POST['target_database_name'] ) && $_POST['target_database_name'] ) {

			$target_database_name = isset( $_POST['target_database_name'] ) ? stripslashes( $_POST['target_database_name'] ) : '';

			if ( ! empty( $target_database_name ) ) {
				BLFPST::update_option( 'target_database_name', $target_database_name );
			} else {
				$errors->add( 'Error', esc_html__( 'Please select a data source.', 'bluff-post' ) );
			}
		}

		// Bounce e-mail address
		if ( isset( $_POST['error_address'] ) && $_POST['error_address'] ) {

			if ( is_email( $_POST['error_address'] ) ) {
				$error_address = stripslashes( $_POST['error_address'] );
				BLFPST::update_option( 'error_address', $error_address );
			} else {
				$errors->add( 'Error', esc_html__( 'The format of the bounce e-mail address is invalid.', 'bluff-post' ) );
			}
		} else {
			BLFPST::update_option( 'error_address', '' );
		}

		// Send type
		$mailer_type = '';
		if ( isset( $_POST['mailer_type'] ) && $_POST['mailer_type'] ) {

			$mailer_type = $_POST['mailer_type'];

			if ( ( 'mail' !== $mailer_type ) && ( 'sendmail' !== $mailer_type ) && ( 'smtp' !== $mailer_type ) ) {
				$errors->add( 'Error', esc_html__( 'Please select a send request type.', 'bluff-post' ) );
			} else {
				BLFPST::update_option( 'mailer_type', $mailer_type );
			}
		}

        if ( 'sendmail' === $mailer_type ) {
            if ( isset( $_POST['sendmail'] ) && $_POST['sendmail'] ) {
                $sendmail_path = stripslashes( $_POST['sendmail'] );

                if ( empty( $sendmail_path ) ) {
                    $errors->add( 'Error', esc_html__( 'Please enter a sendmail command path.', 'bluff-post' ) );
                } else {
                    BLFPST::update_option( 'sendmail_path', $sendmail_path );
                }
            } else {
                $errors->add( 'Error', esc_html__( 'Please enter a sendmail command path.', 'bluff-post' ) );
            }
        }

		if ( 'smtp' === $mailer_type ) {
			// SMTP host
			if ( isset( $_POST['smtp_host'] ) && $_POST['smtp_host'] ) {
				$smtp_host = stripslashes( $_POST['smtp_host'] );

				if ( empty( $smtp_host ) ) {
					$errors->add( 'Error', esc_html__( 'Please enter a SMTP host.', 'bluff-post' ) );
				} else {
					BLFPST::update_option( 'smtp_host', $smtp_host );
				}
			} else {
				$errors->add( 'Error', esc_html__( 'Please enter a SMTP host.', 'bluff-post' ) );
			}

			// SMTP port number
			if ( isset( $_POST['smtp_port'] ) && $_POST['smtp_port'] ) {

				$smtp_port = stripslashes( $_POST['smtp_port'] );

				if ( empty( $smtp_port ) ) {
					$errors->add( 'Error', esc_html__( 'Please enter a SMTP port.', 'bluff-post' ) );
				} else {
					if ( filter_var( $smtp_port, FILTER_VALIDATE_INT ) !== false ) {
						BLFPST::update_option( 'smtp_port', $smtp_port );
					} else {
						$errors->add( 'Error', esc_html__( 'Please enter a numeric port number.', 'bluff-post' ) );
					}
				}
			} else {
				$errors->add( 'Error', esc_html__( 'Please enter a SMTP port.', 'bluff-post' ) );
			}

            // SMTP SMTPAuth
            $smtp_auth = isset( $_POST['smtp_auth'] ) ? stripslashes( $_POST['smtp_auth'] ) : '';
            BLFPST::update_option( 'smtp_auth', $smtp_auth === '1' ? 'true' : 'false' );
            $is_smtp_auth = $smtp_auth === '1';

            if ($is_smtp_auth) {
                // SMTP SMTPSecure
                if ( isset( $_POST['smtp_secure'] ) && $_POST['smtp_secure'] ) {
                    $smtp_secure = stripslashes( $_POST['smtp_secure'] );

                    if ( empty( $smtp_secure ) || ( ( $smtp_secure !== 'tls' ) && ( $smtp_secure !== 'ssl' ) ) ) {
                        $errors->add( 'Error', esc_html__( 'Please select SMTP secure mode.', 'bluff-post' ) );
                    } else {
                        BLFPST::update_option( 'smtp_secure', $smtp_secure );
                    }
                } else {
                    $errors->add( 'Error', esc_html__( 'Please select a SMTP secure mode.', 'bluff-post' ) );
                }

                // SMTP User Name
                if ( isset( $_POST['smtp_user_name'] ) && $_POST['smtp_user_name'] ) {
                    $smtp_user_name = stripslashes( $_POST['smtp_user_name'] );

                    if ( empty( $smtp_user_name ) ) {
                        $errors->add( 'Error', esc_html__( 'Please enter a SMTP user name.', 'bluff-post' ) );
                    } else {
                        BLFPST::update_option( 'smtp_user_name', $smtp_user_name );
                    }
                } else {
                    $errors->add( 'Error', esc_html__( 'Please enter a SMTP user name.', 'bluff-post' ) );
                }

                // SMTP Password
                if ( isset( $_POST['smtp_password'] ) && $_POST['smtp_password'] ) {
                    $smtp_password = stripslashes( $_POST['smtp_password'] );

                    if ( empty( $smtp_password ) ) {
                        $errors->add( 'Error', esc_html__( 'Please enter a SMTP password.', 'bluff-post' ) );
                    } else {
                        BLFPST::update_option( 'smtp_password', $smtp_password );
                    }
                } else {
                    $errors->add( 'Error', esc_html__( 'Please enter a SMTP password.', 'bluff-post' ) );
                }
            }
        }

		// E-mail charset
		if ( isset( $_POST['mail_content_charset'] ) && $_POST['mail_content_charset'] ) {

			$mail_content_charset = stripslashes( $_POST['mail_content_charset'] );
			BLFPST::update_option( 'mail_content_charset', $mail_content_charset );
		} else {
			$errors->add( 'Error', esc_html__( 'Please select a mail charset.', 'bluff-post' ) );
		}

		// Transmission speed limits
		if ( isset( $_POST['transmission_speed_limit_count'] ) && $_POST['transmission_speed_limit_count'] ) {

			$transmission_speed_limit_count = stripslashes( $_POST['transmission_speed_limit_count'] );

			if ( filter_var( $transmission_speed_limit_count, FILTER_VALIDATE_INT ) !== false ) {
				BLFPST::update_option( 'transmission_speed_limit_count', $transmission_speed_limit_count );
			} else {
				$errors->add( 'Error', esc_html__( 'Please enter a numeric continuous transmission count.', 'bluff-post' ) );
			}
		}

		if ( isset( $_POST['transmission_speed_limit_time'] ) && $_POST['transmission_speed_limit_time'] ) {

			$transmission_speed_limit_time = stripslashes( $_POST['transmission_speed_limit_time'] );

			if ( filter_var( $transmission_speed_limit_time, FILTER_VALIDATE_INT ) !== false ) {
				BLFPST::update_option( 'transmission_speed_limit_time', $transmission_speed_limit_time );
			} else {
				$errors->add( 'Error', esc_html__( 'Please enter a numeric transmission interval.', 'bluff-post' ) );
			}
		}

		// Theme
		if ( isset( $_POST['theme_name'] ) && $_POST['theme_name'] ) {

			$theme_name = stripslashes( $_POST['theme_name'] );
			BLFPST::update_option( 'theme_name', $theme_name );
		} else {
			$errors->add( 'Error', esc_html__( 'Please select a theme.', 'bluff-post' ) );
		}

		if ( 0 == count( $errors->errors ) ) {
			$_POST['message'] = esc_html__( 'Options have been saved.', 'bluff-post' );
		}

		$_POST['errors'] = json_encode( $errors );
	}

	/**
	 * delete blfpst_targets
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return void
	 */
	public static function delete_target( $target_id ) {
		// 先にtarget_groupを削除
		self::delete_target_conditional( $target_id );

		/** @var wpdb $wpdb */
		global $wpdb;

		$table = BLFPST_Model_Target::table_name();
		$wpdb->delete( $table, array( 'id' => $target_id ), array( '%d' ) );
	}

	/**
	 * delete blfpst_target_groups
	 *
	 * @param integer $target_id target identifier
	 *
	 * @return void
	 */
	private static function delete_target_conditional( $target_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table = BLFPST_Model_Target_Conditional::table_name();

		// first:target_parameterを削除
		$sql = $wpdb->prepare( "SELECT * FROM ${table} WHERE (target_id='%d')", $target_id );

		$results = $wpdb->get_results( $sql );
		if ( null === $results ) {
			error_log( 'DB Error, could not select blfpst_targets' );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			foreach ( $results as $result ) {
				self::delete_child_target_conditional( $result->target_conditional_id );
			}
		}

		$wpdb->delete( $table, array( 'target_id' => $target_id ), array( '%d' ) );
	}

	/**
	 * delete blfpst_target_conditions
	 *
	 * @param integer $parent_target_conditional_id parent conditional identifier
	 *
	 * @return void
	 */
	private static function delete_child_target_conditional( $parent_target_conditional_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table = BLFPST_Model_Target_Conditional::table_name();

		$wpdb->delete( $table, array( 'parent_target_conditional_id' => $parent_target_conditional_id ), array( '%d' ) );
	}

	/**
	 * get data sources
	 *
	 * @return array
	 */
	public static function data_sources() {

		$data_sources = array();

		$directory_path = BLFPST::data_sources_dir();

		if ( $handle = opendir( $directory_path ) ) {
			while ( false !== ( $file_name = readdir( $handle ) ) ) {
				$class_components = explode( '.php', $file_name );
				if ( count( $class_components ) > 0 ) {
					$file_name = $class_components[0];
				}

				$class_components = explode( '-', $file_name );

				if ( ( count( $class_components ) > 4 ) &&
				     isset( $class_components[0] ) && ( 'class' == $class_components[0] )
				     && isset( $class_components[1] ) && ( 'blfpst' == $class_components[1] )
				     && isset( $class_components[2] ) && ( 'data' == $class_components[2] )
				     && isset( $class_components[3] ) && ( 'source' == $class_components[3] )
				) {
					$class = 'BLFPST_Data_Source_' . $class_components[4];

					/** @var BLFPST_Abstract_Data_Source $query */
					$data_source = new $class();
					array_push( $data_sources, $data_source );
				}
			}

			closedir( $handle );
		}

		return $data_sources;
	}

	/**
	 * get data source
	 *
	 * @param BLFPST_Model_Target $target target data
	 *
	 * @return BLFPST_Abstract_Data_Source
	 */
	public static function data_source_object( $target ) {
		if ( empty( $target ) || empty( $target->class_name ) ) {
			$class_name = BLFPST::get_option( 'target_database_name', '' );
		} else {
			$class_name = $target->class_name;
		}
		$class_name = 'BLFPST_Data_Source_' . $class_name;

		/** @var BLFPST_Abstract_Data_Source $query */
		$data_source = new $class_name();

		return $data_source;
	}

	/**
	 * set up preset target data
	 *
	 * @return void
	 */
	public static function setup_preset_targets() {
		$target_count = BLFPST_Targets_Controller::execute_query_targets_count();

		if ( 0 == $target_count ) {

			$targets = self::load_all_targets_from_json_file();

			if ( ! empty( $targets ) ) {

				/** @var BLFPST_Model_Target $target */
				foreach ( $targets as $target ) {
					self::register_target_to_db( $target, 0, false );
				}
			}
		}
	}

	/**
	 * load all target data from file
	 *
	 * @return array
	 */
	public static function load_all_targets_from_json_file() {

		$targets = array();
		$dir     = BLFPST::plugin_dir( 'presets/targets' );

		if ( ! file_exists( $dir ) ) {
			return $targets;
		}

		if ( is_dir( $dir ) && ( $handle = opendir( $dir ) ) ) {
			while ( ( $file = readdir( $handle ) ) !== false ) {
				if ( 'file' === filetype( $path = path_join( $dir, $file ) ) ) {
					if ( ! preg_match( '/^\./', $path ) && preg_match( '/.json\z/', $path ) ) {
						$target = self::load_target_from_json_file( $path, BLFPST_Model_Target::$target_type_preset );

						if ( false !== $target ) {
							array_push( $targets, $target );
						}
					}
				}
			}
		}

		return $targets;
	}

	/**
	 * load target data from file
	 *
	 * @param string $file_name
	 * @param integer $target_type
	 *
	 * @return boolean | BLFPST_Model_Target
	 */
	public static function load_target_from_json_file( $file_name, $target_type = 0 ) {

		if ( ! isset( $file_name ) || ( '' === $file_name ) ) {
			return false;
		}

		if ( ! file_exists( $file_name ) ) {
			return false;
		}

		$locale                  = get_locale();
		$json_target             = file_get_contents( $file_name, true );
		$target                  = json_decode( $json_target );
		$new_target              = new BLFPST_Model_Target();
		$new_target->id          = 0;
		$new_target->user_id     = 0;
		$new_target->title       = isset( $target->title->{$locale} ) ? $target->title->{$locale} : $target->title->{'ja'};
		$new_target->type        = $target->type;
		$new_target->file_name   = basename( $file_name );
		$new_target->target_type = $target_type;
		$new_target->description = isset( $target->description->{$locale} ) ? $target->description->{$locale} : $target->description->{'ja'};
		$new_target->class_name  = $target->class_name;

		foreach ( $target->conditionals as $target_group ) {
			$new_target_group         = new BLFPST_Model_Target_Conditional();
			$new_target_group->and_or = $target_group->and_or;

			foreach ( $target_group->conditionals as $target_conditional ) {
				$new_conditional               = new BLFPST_Model_Target_Conditional();
				$new_conditional->and_or       = $target_conditional->and_or;
				$new_conditional->table_name   = $target_conditional->table_name;
				$new_conditional->column_name  = $target_conditional->column_name;
				$new_conditional->compare      = $target_conditional->compare;
				$new_conditional->column_value = $target_conditional->column_value;

				array_push( $new_target_group->target_conditionals, $new_conditional );
			}

			array_push( $new_target->target_conditionals, $new_target_group );
		}

		return $new_target;
	}


	/**
	 * load exclude targets from DB
	 *
	 * @param int $page_num
	 * @param int $limit
	 * @param boolean $desc
	 *
	 * @return array
	 */
	public static function load_exclude_targets( $page_num = - 1, $limit = 0, $desc = false ) {
		$targets = array();

		if ( current_user_can( 'blfpst_edit_mail' ) ) {

			/** @var wpdb $wpdb */
			global $wpdb;

			$table_name = BLFPST_Model_Target::table_name();
			$sql        = "SELECT * FROM ${table_name} ORDER BY id";

			if ( $desc ) {
				$sql .= ' DESC';
			}

			if ( $page_num >= 0 ) {
				$sql .= $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
			}

			$targets_results = $wpdb->get_results( $sql );
			if ( null === $targets_results ) {
				error_log( 'DB Error, could not select blfpst_targets' );
				error_log( 'MySQL Error: ' . $wpdb->last_error );
			} else {
				foreach ( $targets_results as $targets_result ) {
					$target        = new BLFPST_Model_Target();
					$target->id    = $targets_result->id;
					$target->title = $targets_result->title;
					array_push( $targets, $target );
				}
			}
		}

		return $targets;
	}

	/**
	 *  Execute SQL Query
	 *
	 * @param integer $page_num
	 * @param integer $limit
	 *
	 * @return array
	 */
	public static function execute_query_exclude_recipients( $page_num = - 1, $limit = 0 ) {
		$exclude_recipients = array();

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Exclude_Recipient::table_name();
		$sql        = "SELECT * FROM ${table_name} ORDER BY updated_at, id DESC";

		if ( $page_num > -1 ) {
			$sql .= $wpdb->prepare( ' LIMIT %d, %d', $page_num * $limit, $limit );
		};

		$results = $wpdb->get_results( $sql );
		if ( null === $results ) {
			error_log( 'DB Error, could not select ' . $table_name );
			error_log( 'MySQL Error: ' . $wpdb->last_error );
		} else {
			foreach ( $results as $result ) {
				$exclude_recipient = array(
					'mail_address' => $result->mail_address,
					'id'            => $result->id,
				);
				array_push( $exclude_recipients, $exclude_recipient );
			}
		}

		return $exclude_recipients;
	}

	/**
	 *  Execute SQL Query
	 *
	 * @return integer
	 */
	public static function execute_query_exclude_recipients_count() {
		/** @var wpdb $wpdb */
		global $wpdb;

		$table_name = BLFPST_Model_Exclude_Recipient::table_name();
		$sql        = "SELECT count(*) FROM ${table_name}";
		$count      = $wpdb->get_var( $sql );
		$count      = ( false === $count ) ? 0 : $count;

		return $count;
	}

	/**
	 * add exclude recipient address to DB
	 *
	 * @param string $mail_address
	 *
	 * @return boolean
	 */
	private static function is_exists_exclude_recipient( $mail_address ) {

		$mail_address = mb_strtolower( $mail_address );

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Exclude_Recipient::table_name();
		$sql        = $wpdb->prepare( "SELECT count(*) FROM ${table_name} WHERE (mail_address='%s') ",
			$mail_address
		);

		$count = $wpdb->get_var( $sql );
		$count = ( false === $count ) ? 0 : $count;

		return ( $count > 0 );
	}

	/**
	 * add exclude recipient address to DB
	 *
	 * @param string $mail_address
	 * @param integer $user_id
	 *
	 * @return array
	 */
	public static function register_exclude_recipients( $mail_address, $user_id = 0 ) {

		if ( self::is_exists_exclude_recipient( $mail_address ) ) {
			return;
		}

		$mail_address = mb_strtolower( $mail_address );

		/** @var wpdb $wpdb */
		global $wpdb;
		$table_name = BLFPST_Model_Exclude_Recipient::table_name();

		$values = array(
			'user_id'      => $user_id,
			'mail_address' => $mail_address,

			'updated_at' => current_time( 'mysql', 0 ),
			'created_at' => current_time( 'mysql', 0 ),
		);

		$format = array(
			'%d',
			'%s',

			'%s',
			'%s',
		);

		$wpdb->insert( $table_name, $values, $format );
	}

	/**
	 * delete exclude recipient address at DB
	 *
	 * @param integer $exclude_recipient_id
	 *
	 * @return array
	 */
	public static function delete_exclude_recipients_with_id( $exclude_recipient_id ) {
		/** @var wpdb $wpdb */
		global $wpdb;
		$table = BLFPST_Model_Exclude_Recipient::table_name();
		$wpdb->delete( $table, array( 'id' => $exclude_recipient_id ), array( '%d' ) );
	}
}
