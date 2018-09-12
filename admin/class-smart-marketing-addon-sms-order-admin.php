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
	 * List of order status WooCommerce hooks
	 *
	 * @var array
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
	 * List of sms languages
	 *
	 * @var array
	 */
    protected $languages = array( "en", "es", "pt", "pt_BR");

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

	    if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-senders') {

		    update_option('egoi_sms_order_sender', json_encode($post));

	    } else if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-recipients') {

		    update_option('egoi_sms_order_recipients', json_encode($post));

	    } else if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-texts') {

		    $texts = json_decode(get_option('egoi_sms_order_texts'), true);
		    $texts[$post['sms_text_language']] = $post;
		    update_option('egoi_sms_order_texts', json_encode($texts));

	    } else if (isset($post['form_id']) && $post['form_id'] == 'form-sms-order-tests') {

	    	$sender = json_decode(get_option('egoi_sms_order_sender'), true);

	    	$response = $this->send_sms(array(
			    "apikey" => $this->apikey,
			    "sender_hash" => $sender['sender_hash'],
			    "message" => $post['message'],
			    "recipient" => $post['recipient'],
			    "type" => 'test',
			    "order_id" => 0
		    ));

	    	$response = json_decode($response);
	    	if (isset($response->errorCode)) {
				return false;
		    }
	    }
	    return true;
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
    public function send_sms($sms_params) {
        $url = 'http://www.smart-marketing-addon-sms-order-middleware.local/sms';
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
	    global $wpdb;

	    $table_name = $wpdb->prefix. 'egoi_sms_order_reminders';

        $sql = " INSERT INTO $table_name (time, order_id) VALUES ('". date('Y-m-d H:i:s') ."', '2') ";

        $wpdb->query($sql);
    }

    public function my_add_every_minute($schedules) {
	    // add a every minute schedule to the existing set
	    $schedules['every_minute'] = array(
		    'interval' => 60,
		    'display' => __('Every Minute')
	    );
	    return $schedules;
    }
}
