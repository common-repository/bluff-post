<?php

/**
 * _blfpst_db_version for version_compare()
 */
global $blfpst_db_version;
$blfpst_db_version = '1.0';

/**
 * database manager.
 * PHP Version 5.4.0
 * Version 1.0.0
 * @author Hideaki Oguchi (bluff-lab.com) <oguchi@bluff-lab.com>
 * @copyright 2016 Yamate Kenkyujo - Bluff Laboratory
 */
class BLFPST_Database_Manager {

	public function __construct() {
	}

	public function create_custom_tables() {

		global $blfpst_db_version;

		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		$table_name = BLFPST_Model_Target::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    target_type TINYINT unsigned NOT NULL DEFAULT '0',
                    class_name varchar(32) NOT NULL DEFAULT '',
                    file_name text DEFAULT '',
                    title varchar(255) NOT NULL DEFAULT '',
                    type varchar(16) NOT NULL DEFAULT 'builder',
                    description text DEFAULT '',
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY id (id)
                    ) ${charset_collate};";
		dbDelta( $sql );

		//$table_name = BLFPST_Model_Target::meta_table_name();
		//$sql        = "CREATE TABLE ${table_name} (
		//           id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		//           target_id bigint(20) unsigned NOT NULL DEFAULT '0',
		//           meta_key varchar(255) DEFAULT NULL,
		//           meta_value longtext,
		//           updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           KEY idx_target_id (target_id),
		//           KEY idx_meta_key (meta_key(191))
		//             ) ${charset_collate};";
		//dbDelta( $sql );

		$table_name = BLFPST_Model_Target_Conditional::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    target_conditional_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    target_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    parent_target_conditional_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    class_name varchar(32) NOT NULL DEFAULT '',
                    order_index int(11) unsigned NOT NULL DEFAULT '0',
                    and_or varchar(8) NOT NULL DEFAULT '',
                    table_name varchar(255) DEFAULT '',
                    column_name varchar(255) DEFAULT '',
                    compare varchar(9) DEFAULT '',
                    column_value varchar(255) DEFAULT '',
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY target_conditional_id (target_conditional_id)
                    ) ${charset_collate};";
		dbDelta( $sql );

		$table_name = BLFPST_Model_Mail_From::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    from_name varchar(255) DEFAULT '',
                    from_address varchar(255) NOT NULL DEFAULT '',
                    reply_name varchar(255) DEFAULT '',
                    reply_address varchar(255) DEFAULT '',
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY id (id)
                    ) ${charset_collate};";
		dbDelta( $sql );

		//$table_name = BLFPST_Model_Mail_From::meta_table_name();
		//$sql        = "CREATE TABLE ${table_name} (
		//           id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		//           mail_from_id bigint(20) unsigned NOT NULL DEFAULT '0',
		//           meta_key varchar(255) DEFAULT NULL,
		//           meta_value longtext,
		//           updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           KEY idx_mail_from_id (mail_from_id),
		//           KEY idx_meta_key (meta_key(191))
		//             ) ${charset_collate};";
		//dbDelta( $sql );

		$table_name = BLFPST_Model_Template::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    template_type TINYINT unsigned NOT NULL DEFAULT '0',
                    content_type varchar(8) DEFAULT '',
                    file_name text DEFAULT '',
                    title varchar(255) DEFAULT '',
                    subject varchar(255) DEFAULT '',
                    text_content text DEFAULT '',
                    html_content longtext DEFAULT '',
                    from_name varchar(255) DEFAULT '',
                    from_address varchar(255) NOT NULL DEFAULT '',
                    reply_name varchar(255) DEFAULT '',
                    reply_address varchar(255) DEFAULT '',
                    author varchar(255) DEFAULT '',
                    description text DEFAULT '',
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY id (id)
                    ) ${charset_collate};";
		dbDelta( $sql );

		//$table_name = BLFPST_Model_Template::meta_table_name();
		//$sql        = "CREATE TABLE ${table_name} (
		//           id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		//           mail_from_id bigint(20) unsigned NOT NULL DEFAULT '0',
		//           meta_key varchar(255) DEFAULT NULL,
		//           meta_value longtext,
		//           updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           KEY idx_mail_from_id (mail_from_id),
		//           KEY idx_meta_key (meta_key(191))
		//             ) ${charset_collate};";
		//dbDelta( $sql );

		$table_name = BLFPST_Model_Send_Mail::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    post_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    target_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    target_name varchar(255) DEFAULT '',
                    repeat_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    status varchar(12) NOT NULL DEFAULT 'draft',
                    charset varchar(32) DEFAULT '',
                    reserved_at datetime DEFAULT NULL,
                    subject varchar(255) DEFAULT '',
                    text_content text DEFAULT '',
                    html_content longtext DEFAULT '',
                    from_name varchar(255) DEFAULT '',
                    from_address varchar(255) NOT NULL DEFAULT '',
                    reply_name varchar(255) DEFAULT '',
                    reply_address varchar(255) DEFAULT '',
                    create_code varchar(32) DEFAULT '',
                    send_result varchar(12) NOT NULL DEFAULT 'wait',
                    send_request_start_at datetime DEFAULT NULL,
                    send_request_end_at datetime DEFAULT NULL,
                    deleted_at datetime DEFAULT NULL,
                    recipient_count int NOT NULL DEFAULT '0',
                    success_count int NOT NULL DEFAULT '0',
                    failure_count int NOT NULL DEFAULT '0',
                    target_sql text DEFAULT '',
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY id (id)
                    ) ${charset_collate};";
		dbDelta( $sql );

		//$table_name = BLFPST_Model_Send_Mail::meta_table_name();
		//$sql        = "CREATE TABLE ${table_name} (
		//           id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		//           send_mail_id bigint(20) unsigned NOT NULL DEFAULT '0',
		//           meta_key varchar(255) DEFAULT NULL,
		//           meta_value longtext,
		//           updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           KEY idx_send_mail_id (send_mail_id),
		//           KEY idx_meta_key (meta_key(191))
		//             ) ${charset_collate};";
		//dbDelta( $sql );

		$table_name = BLFPST_Model_Log::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    user_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    send_mail_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    level int(11) unsigned NOT NULL DEFAULT '0',
                    identifier varchar(64) DEFAULT '',
                    summary text DEFAULT '',
                    detail text DEFAULT '',
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY id (id)
                    ) ${charset_collate};";
		dbDelta( $sql );

		$table_name = BLFPST_Model_Exclude_Recipient::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    user_id bigint(20) unsigned NOT NULL DEFAULT '0',
                    class_name varchar(32) NOT NULL DEFAULT '',
                    mail_address varchar(255) NOT NULL,
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    KEY idx_mail_addres (mail_address)
                    ) ${charset_collate};";
		dbDelta( $sql );

		$table_name = BLFPST_Model_Recipient::table_name();
		$sql        = "CREATE TABLE ${table_name} (
                    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                    mail_address varchar(256) NOT NULL DEFAULT '',
                    first_name varchar(64) DEFAULT '',
                    last_name varchar(64) DEFAULT '',
                    text_only boolean NOT NULL DEFAULT false,
                    status varchar(16) NOT NULL DEFAULT 'subscribe',
                    error_count int unsigned NOT NULL DEFAULT '0',
                    delete_at datetime DEFAULT NULL,
                    updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
                    UNIQUE KEY id (id)
                    ) ${charset_collate};";
		dbDelta( $sql );

		//$table_name = BLFPST_Model_Recipient::meta_table_name();
		//$sql        = "CREATE TABLE ${table_name} (
		//           id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		//           recipient_id bigint(20) unsigned NOT NULL DEFAULT '0',
		//           meta_key varchar(255) DEFAULT NULL,
		//           meta_value longtext,
		//           updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		//           KEY idx_recipient_id (recipient_id),
		//           KEY idx_meta_key (meta_key(191))
		//             ) ${charset_collate};";
		//dbDelta( $sql );

		update_option( '_blfpst_db_version', $blfpst_db_version );
	}
}
