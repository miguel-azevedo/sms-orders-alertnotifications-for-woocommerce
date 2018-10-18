<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/public
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function smsonw_enqueue_styles() {

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smart-marketing-addon-sms-order-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function smsonw_enqueue_scripts() {

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smart-marketing-addon-sms-order-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Add field to order checkout form
	 *
	 * @param $checkout
	 */
	function smsonw_notification_checkout_field($checkout) {
		$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
		if (isset($recipients['notification_option']) && $recipients['notification_option']) {
			woocommerce_form_field('egoi_notification_option', array(
				'type'          => 'checkbox',
				'class'         => array('my-field-class form-row-wide'),
				'label'         => __('I want to be notified by SMS (Order Status)', 'smart-marketing-addon-sms-order'),
			), $checkout->get_value( 'egoi_notification_option'));
		}
	}

	/**
	 * Save notification field from order checkout
	 *
	 * @param $order_id
	 */
	function smsonw_notification_checkout_field_update_order_meta($order_id) {
	    $option = filter_var($_POST['egoi_notification_option'], FILTER_SANITIZE_NUMBER_INT);
		if (isset($option) && filter_var($option, FILTER_VALIDATE_BOOLEAN)) {
			update_post_meta($order_id, 'egoi_notification_option', 1);
		} else {
			update_post_meta($order_id, 'egoi_notification_option', 0);
		}
	}

}
