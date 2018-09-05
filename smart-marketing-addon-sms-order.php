<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.e-goi.com
 * @since             1.0.0
 * @package           Smart_Marketing_Addon_Sms_Order
 *
 * @wordpress-plugin
 * Plugin Name:       Smart Marketing Addon SMS Order
 * Plugin URI:        https://wordpress.org/plugins/smart-marketing-addon-sms-order/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            E-goi
 * Author URI:        https://www.e-goi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       smart-marketing-addon-sms-order
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

add_action( 'admin_init', 'child_plugin_has_parent_plugin' );
function child_plugin_has_parent_plugin() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'smart-marketing-for-wp/egoi-for-wp.php' ) ) {
        add_action( 'admin_notices', 'child_plugin_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) );

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}

function child_plugin_notice(){
    ?><div class="notice notice-error is-dismissible"><p>Sorry, but Child Plugin requires the Parent plugin to be installed and active.</p></div><?php
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PLUGIN_NAME_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-smart-marketing-addon-sms-order-activator.php
 */
function activate_smart_marketing_addon_sms_order() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smart-marketing-addon-sms-order-activator.php';
	Smart_Marketing_Addon_Sms_Order_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-smart-marketing-addon-sms-order-deactivator.php
 */
function deactivate_smart_marketing_addon_sms_order() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-smart-marketing-addon-sms-order-deactivator.php';
	Smart_Marketing_Addon_Sms_Order_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_smart_marketing_addon_sms_order' );
register_deactivation_hook( __FILE__, 'deactivate_smart_marketing_addon_sms_order' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-smart-marketing-addon-sms-order.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_smart_marketing_addon_sms_order() {

	$plugin = new Smart_Marketing_Addon_Sms_Order();
	$plugin->run();

}
run_smart_marketing_addon_sms_order();
