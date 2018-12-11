<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Activator {

	/**
	 * Register hooks on plugin activation
	 *
	 * Create a schedule event to process SMS order reminder
	 * Create table egoi_sms_order_reminders
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if (! wp_next_scheduled ( 'egoi_sms_order_event' )) {
			wp_schedule_event(time(), 'every_fifteen_minutes', 'egoi_sms_order_event');
		}

		self::create_sms_order_reminders_table();

        self::checkApiKey();
	}

	public static function create_sms_order_reminders_table() {
		global $wpdb;

		$table_name = $wpdb->prefix. 'egoi_sms_order_reminders';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  order_id int NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function checkApiKey() {
        $apikey = get_option('egoi_api_key');
        $params = [
            'plugin_key' => '2f711c62b1eda65bfed5665fbd2cdfc9',
            'apikey' 		=> $apikey['api_key']
        ];
        $client = new SoapClient('http://api.e-goi.com/v2/soap.php?wsdl');
        $client->checklogin($params);
    }
}
