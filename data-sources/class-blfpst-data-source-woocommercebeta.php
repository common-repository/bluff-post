<?php

/**
 * data source of WooCommerce Beta.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2021 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Data_Source_Woocommercebeta extends BLFPST_Abstract_Data_Source {
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
        return __( 'Woocommercebeta', 'bluff-post' );
    }

    /**
     * Application display name
     *
     * @return string
     */
    public function display_name() {
        return __( 'Woocommerce beta', 'bluff-post' );
    }

    /**
     * description
     *
     * @return string
     */
    public function description() {
        return __( 'Woocommerce DB(beta)', 'bluff-post' );
    }

    /**
     * id field name form user table
     *
     * @return string
     */
    public function id_field_name() {
        return 'email_id';
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
        return 'email';
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
            $wpdb->prefix . 'posts',
            $wpdb->prefix . 'postmeta',
            $wpdb->prefix . 'woocommerce_order_items',
            $wpdb->prefix . 'woocommerce_order_itemmeta',
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
        /** wpdb $wpdb */
        global $wpdb;

        // WordPress
        $posts_table = $wpdb->prefix . 'posts';
        $postmeta_table = $wpdb->prefix . 'postmeta';

        $target_groups = $target->target_conditionals;

        if ( $is_count ) {
            // count
            $sql = "SELECT count(T1.email) FROM ( SELECT lower(trim(EMAIL_TABLE.meta_value)) as email FROM ${posts_table} AS POST_TABLE, ${postmeta_table} AS EMAIL_TABLE JOIN ( wp_postmeta as FIRST_NAME_TABLE) ON FIRST_NAME_TABLE.post_id = EMAIL_TABLE.post_id AND FIRST_NAME_TABLE.meta_key = '_billing_first_name' JOIN ( wp_postmeta as LAST_NAME_TABLE) ON LAST_NAME_TABLE.post_id = EMAIL_TABLE.post_id AND LAST_NAME_TABLE.meta_key = '_billing_last_name' WHERE POST_TABLE.ID = EMAIL_TABLE.post_id AND POST_TABLE.post_type = 'shop_order' AND EMAIL_TABLE.meta_key = '_billing_email'";
        } else {
            // 副問い合わせで複数行を抱えることがあるので IN を使う
            $sql = "SELECT lower(trim(EMAIL_TABLE.meta_value)) as email, EMAIL_TABLE.meta_id as email_id, FIRST_NAME_TABLE.meta_value as first_name, LAST_NAME_TABLE.meta_value as last_name FROM ${posts_table} AS POST_TABLE, ${postmeta_table} AS EMAIL_TABLE JOIN ( wp_postmeta as FIRST_NAME_TABLE) ON FIRST_NAME_TABLE.post_id = EMAIL_TABLE.post_id AND FIRST_NAME_TABLE.meta_key = '_billing_first_name' JOIN ( wp_postmeta as LAST_NAME_TABLE) ON LAST_NAME_TABLE.post_id = EMAIL_TABLE.post_id AND LAST_NAME_TABLE.meta_key = '_billing_last_name' WHERE POST_TABLE.ID = EMAIL_TABLE.post_id AND POST_TABLE.post_type = 'shop_order' AND EMAIL_TABLE.meta_key = '_billing_email'";
        }

        $sql .= ' AND ';

        $where_sql = $this->create_where_recipient_sql( $target_groups, '', '' );

        if ( false === $where_sql ) {
            $sql = '';
        } else {
            $sql .= $where_sql;

            // Exclude Recipient
            $and_or = ( 0 < count( $target_groups ) ) ? ' AND' : '';
            $exclude_recipients = BLFPST_Model_Exclude_Recipient::table_name();
            $sql .= "${and_or} (EMAIL_TABLE.meta_key = '_billing_email' AND EMAIL_TABLE.meta_value NOT IN";
            $sql .= " (select distinct(${exclude_recipients}.mail_address) FROM ${exclude_recipients})";

            // WHERE - IN end
            $sql .= ') ';
            $sql .= 'GROUP BY (lower(trim(EMAIL_TABLE.meta_value)))';

            if ( $is_count ) {
                $sql .= ') as T1';
            }
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
        //FIXME:
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

        /** wpdb $wpdb */
        global $wpdb;

        // WordPress
        $posts_table    = $wpdb->prefix . 'posts';
        $postmeta_table = $wpdb->prefix . 'postmeta';

        // Woo Commerce
        $woocommerce_order_items_table    = $wpdb->prefix . 'woocommerce_order_items';
        $woocommerce_order_itemmeta_table = $wpdb->prefix . 'woocommerce_order_itemmeta';

        // posts.ID relationship
        $post_ids = array( $postmeta_table => 'post_id', $woocommerce_order_items_table => 'order_id' );

        // woocommerce_order_items.order_item_id relationship
        $woocommerce_order_items_order_item_ids = array( $woocommerce_order_itemmeta_table => 'order_item_id' );

        // require tables
        $tables = array();

        // Parent
        for ( $parent_index = 0; $parent_index < count( $target_groups ); $parent_index ++ ) {

            /** @var BLFPST_Model_Target_Conditional $parent_conditional */
            $parent_conditional = $target_groups[ $parent_index ];
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
                    } // woocommerce_order_itemmeta
                    else if ( $table === $woocommerce_order_itemmeta_table ) {
                        if ( ! in_array( $woocommerce_order_items_table, $tables ) ) {
                            array_push( $tables, $woocommerce_order_items_table );
                        }
                    } // woocommerce_order_items
                    else if ( $table === $woocommerce_order_items_table ) {
                        if ( ! in_array( $posts_table, $tables ) ) {
                            array_push( $tables, $posts_table );
                        }
                    }
                }
            }
        }

        // User WHERE
        $use_where_parenthesis = ( 0 < count( $target_groups ) );

        if ( $use_where_parenthesis ) {
            $sql .= "POST_TABLE.ID IN (SELECT DISTINCT ${woocommerce_order_items_table}.order_id FROM ";

            if ( !in_array( $woocommerce_order_items_table, $tables ) ) {
                array_push( $tables, $woocommerce_order_items_table );
            }
        }

        for ( $i = 0; $i < count( $tables ); $i ++ ) {
            $table = $tables[ $i ];

            if ( 0 < $i ) {
                $sql .= ', ';
            }
            $sql .= $table;
        }

        $sql .= $use_where_parenthesis ? ' WHERE ' : '';

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

        // woocommerce_order_items.order_item_id
        if ( in_array( $woocommerce_order_items_table, $tables ) ) {

            for ( $i = 0; $i < count( $tables ); $i ++ ) {

                $table = $tables[ $i ];
                if ( isset( $woocommerce_order_items_order_item_ids[ $table ] ) ) {

                    if ( '' !== $woocommerce_order_items_order_item_ids[ $table ] ) {
                        $order_item_ids = $woocommerce_order_items_order_item_ids[ $table ];
                        $sql .= "(${woocommerce_order_items_table}.order_item_id=$table.${order_item_ids}) AND ";
                    }
                }
            }
        }

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
        $sql .= $use_where_parenthesis ? '))' : '';


        if ( $error ) {
            $sql = false;
        }

        return $sql;
    }
}
