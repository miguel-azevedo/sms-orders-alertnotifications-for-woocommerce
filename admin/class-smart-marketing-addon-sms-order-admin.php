<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/admin
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Admin {

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
     * The ID of parent of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $parent_plugin_name    The ID of parent of this plugin.
     */
    private $parent_plugin_name = 'egoi-for-wp';

    private $apikey;

	/**
	 * @var array List of order status WooCommerce hooks
	 */
    protected $order_statuses = array(
	    "pending" => "Pending payment",
	    "failed" => "Failed",
	    "on-hold" => "On Hold",
	    "processing" => "Processing",
	    "completed" => "Completed",
	    "refunded" => "Refunded",
	    "cancelled" => "Failed",
    );

	/**
	 * @var array List of sms languages
	 */
    protected $languages = array( "en", "es", "pt", "pt_BR");

	/**
	 * @var array List of SMS text tags
	 */
    protected $sms_text_tags = array(
        "order_id" => '%order_id%',
        "order_status" => '%order_status%',
        "total" => '%total%',
        "currency" => '%currency%',
        "payment_method" => '%payment_method%',
        "reference" => '%ref%',
        "entity" => '%ent%',
        "shop_name" => '%shop_name%',
        "billing_name" => '%billing_name%'
    );

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$apikey = get_option('egoi_api_key');
		$this->apikey = $apikey['api_key'];
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smart-marketing-addon-sms-order-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smart-marketing-addon-sms-order-admin.js', array( 'jquery' ), $this->version, false );

	}

    /**
     * Add an options page to smart marketing menu
     *
     * @since  1.0.0
     */
    public function add_options_page() {

        $this->plugin_screen_hook_suffix = add_submenu_page(
            $this->parent_plugin_name,
            __( 'SMS Order Config', 'addon-sms-order' ),
            __( 'SMS Order Config', 'addon-sms-order' ),
            'manage_options',
            'smart-marketing-addon-sms-order-config',
            array( $this, 'display_plugin_sms_order_config' )
        );

    }

    /**
     * Render the options page for plugin
     *
     * @since  1.0.0
     */
    public function display_plugin_sms_order_config() {
        include_once 'partials/smart-marketing-addon-sms-order-admin-config.php';
    }

	/**
	 * Save sms order configs in wordpress options
	 *
	 * @param $post
	 *
	 * @return bool
	 */
    public function process_config_form($post) {

        try {
            if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-senders') {

                update_option('egoi_sms_order_sender', json_encode($post));

            } else if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-recipients') {

                update_option('egoi_sms_order_recipients', json_encode($post));

            } else if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-texts') {

                $texts = json_decode(get_option('egoi_sms_order_texts'), true);
                $texts[$post['sms_text_language']] = $post;
                update_option('egoi_sms_order_texts', json_encode($texts));

            } else if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-tests') {

                $response = $this->send_sms($post['recipient'], $post['message'], 'test', 0);

                $response = json_decode($response);
                if (isset($response->errorCode)) {
                    return false;
                }
            }
	        return true;
        } catch (Exception $e) {
            $this->save_logs('process_config_form: ' . $e->getMessage());
        }

    }

	/**
	 * Function to get cellphone senders from E-goi account
	 *
	 * @return array with senders
	 */
    public function get_senders() {
	    $params = array(
		    'apikey' 		=> $this->apikey,
		    'channel' 		=> 'telemovel'
	    );

	    $client = new SoapClient('http://api.e-goi.com/v2/soap.php?wsdl');
	    $result = $client->getSenders($params);

	    return $result;
    }

	/**
     * Method to send SMS
	 * @param $sms_params
	 *
	 * @return mixed
	 */
    public function send_sms($recipient, $message, $type, $order_id) {
        $url = 'http://www.smart-marketing-addon-sms-order-middleware.local/sms';

	    $sender = json_decode(get_option('egoi_sms_order_sender'), true);

	    $sms_params = array(
		    "apikey" => $this->apikey,
		    "sender_hash" => $sender['sender_hash'],
		    "message" => $message,
		    "recipient" => $recipient,
		    "type" => $type,
		    "order_id" => $order_id
	    );
        return $this->curl($url, $sms_params);
    }

	/**
	 * cURL helper
	 * @param $url
	 * @param $post
	 *
	 * @return mixed
	 */
    public function curl($url, $post) {
	    $ch = curl_init($url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

	    $response = curl_exec($ch);

	    curl_close($ch);

	    return $response;
    }

	public function admin_notice__success() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Done!', 'addon-sms-order' ); ?></p>
		</div>
		<?php
	}

	public function admin_notice__error() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'Irks! An error has occurred.', 'addon-sms-order' ); ?></p>
		</div>
		<?php
	}

    public function sms_order_reminder() {
        try {

            global $wpdb;

	        $sender = json_decode(get_option('egoi_sms_order_sender'), true);
            $table_name = $wpdb->prefix. 'egoi_sms_order_reminders';

            $sql = " SELECT DISTINCT order_id FROM $table_name ";
            $order_ids = $wpdb->get_col($sql);

            $orders = $this->get_not_paid_orders();

            if (isset($orders)) {
                foreach ($orders as $order) {
                    if (!$order->is_paid() && !in_array($order->get_id(), $order_ids)) {

                        $customer_message = $this->get_sms_order_message('customer', $order->get_data());
	                    $admin_message = $this->get_sms_order_message('admin', $order->get_data());

                        if ($customer_message !== false) {
                            $this->send_sms('351-'.$order->billing_phone, $customer_message, $order->get_status(), $order->get_id());
                        }

	                    if ($admin_message !== false) {
		                    $this->send_sms($sender['admin_cellphone'], $admin_message, $order->get_status(), $order->get_id());
	                    }

                        $wpdb->insert($table_name, array(
                            "time" => date('Y-m-d H:i:s'),
                            "order_id" => $order->get_id()
                        ));

                    }
                }
            }
        } catch (Exception $e) {
	        $this->save_logs('sms_order_reminder: ' . $e->getMessage());
        }
    }

    public function get_not_paid_orders() {
	    // TODO -> $two_days_in_sec = 2 * 24 * 60 * 60;
	    $two_days_in_sec = 60;
	    $args = array(
		    "status" => array(
			    "pending",
			    "failed",
			    "on-hold"
		    ),
		    "date_created" => '<' . (time() - $two_days_in_sec)
	    );
	    return wc_get_orders($args);
    }

    public function get_sms_order_message($recipient_type, $order) {

	    $recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
	    // TODO - get language with order billing country
        $lang = strtolower($order['billing']['country']);
	    $texts = json_decode(get_option('egoi_sms_order_texts'), true);

	    if (isset($texts[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']]) && isset($recipients['egoi_sms_order_' . $recipient_type . '_' . $order['status']])) {

		    $tags = array(
			    "%order_id%" => $order['id'],
			    "%order_status%" => $order['status'], // TODO - Translate status too
			    "%total%" => $order['total'],
			    "%currency%" => $order['currency'],
			    "%payment_method%" => $order['payment_method'],
			    "%ref%" => '%ref%', // TODO - what is ref?
			    "%ent%" => '%ent%', // TODO - what is ent?
			    "%shop_name%" => get_bloginfo('name'),
			    "%billing_name%" => $order['billing']['first_name'].' '.$order['billing']['last_name']
		    );

	        $message = $texts[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']];
	        foreach ($tags as $tag => $content) {
	            $message = str_replace($tag, $content, $message);
            }

	        return $message;
	    }
	    return false;
    }

    public function my_add_every_minute($schedules) {
	    $schedules['every_minute'] = array(
		    'interval' => 60,
		    'display' => __('Every Minute')
	    );
	    return $schedules;
    }

    public function save_logs($log) {
	    $path = dirname(__FILE__).'/logs/';

	    $file = fopen($path.'smart-marketing-addon-sms-order.log', 'a+');
	    fwrite($file, $log."\xA");
	    fclose($file);
    }
}
