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

    protected $helper;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

	    $this->helper = new Smart_Marketing_Addon_Sms_Order_Helper();

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
	public function smsonw_enqueue_styles() {

        wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/smart-marketing-addon-sms-order-admin.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function smsonw_enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/smart-marketing-addon-sms-order-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'smsonw-meta-box-ajax-script', plugin_dir_url( __FILE__ ) . 'js/smsonw_order_action_sms_meta_box.min.js', array('jquery') );
		wp_localize_script( 'smsonw-meta-box-ajax-script', 'smsonw_meta_box_ajax_object', array(
		        'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce('egoi_send_order_sms'),
        ) );

	}

    /**
     * Add an options page to smart marketing menu
     *
     * @since  1.0.0
     */
    public function smsonw_add_options_page() {
        $this->plugin_screen_hook_suffix = add_submenu_page(
            $this->parent_plugin_name,
            __( 'SMS Notifications', 'smart-marketing-addon-sms-order' ),
            __( 'SMS Notifications', 'smart-marketing-addon-sms-order' ),
            'manage_options',
            'smart-marketing-addon-sms-order-config',
            array( $this, 'smsonw_display_plugin_sms_order_config' )
        );
    }

    /**
     * Render the options page for plugin
     *
     * @since  1.0.0
     */
    public function smsonw_display_plugin_sms_order_config() {
        include_once 'partials/smart-marketing-addon-sms-order-admin-config.php';
    }

	/**
	 * Save sms order configs in wordpress options
	 *
	 * @param $post
	 *
	 * @return bool
	 */
    public function smsonw_process_config_form($post) {

        try {
            $form_id = sanitize_text_field($post['form_id']);
            check_admin_referer($form_id);

            if (isset($form_id) && $form_id == 'form-sms-order-senders') {

                $sender = array (
                    'sender_hash' => sanitize_text_field($post['sender_hash']),
                    'admin_prefix' => filter_var($post['admin_prefix'], FILTER_SANITIZE_NUMBER_INT),
                    'admin_phone' => sanitize_text_field($post['admin_phone'])
                );

                foreach ($this->helper->smsonw_get_order_statuses() as $status => $name) {
                    $recipients = array(
                        'egoi_sms_order_customer_'.$status => $this->helper->smsonw_sanitize_boolean_field('egoi_sms_order_customer_'.$status),
                        'egoi_sms_order_admin_'.$status => $this->helper->smsonw_sanitize_boolean_field('egoi_sms_order_admin_'.$status)
                    );
                }

                $recipients = array_merge($recipients, array(
                    'notification_option' => $this->helper->smsonw_sanitize_boolean_field('notification_option'),
                    'egoi_payment_info' => $this->helper->smsonw_sanitize_boolean_field('egoi_payment_info'),
                    'egoi_reminders' => $this->helper->smsonw_sanitize_boolean_field('egoi_reminders'),
                    'egoi_payment_info_billet' => $this->helper->smsonw_sanitize_boolean_field('egoi_payment_info_billet'),
                    'egoi_reminders_billet' => $this->helper->smsonw_sanitize_boolean_field('egoi_reminders_billet')
                ));

                update_option('egoi_sms_order_sender', json_encode($sender));
                update_option('egoi_sms_order_recipients', json_encode($recipients));

            } else if (isset($form_id) && $form_id == 'form-sms-order-texts') {

                $texts = json_decode(get_option('egoi_sms_order_texts'), true);
                $lang = sanitize_text_field($post['sms_text_language']);

                foreach ($this->helper->smsonw_get_order_statuses() as $status => $name) {
                    if (trim($post['egoi_sms_order_text_customer_'.$status]) != '') {
                        $messages['egoi_sms_order_text_customer_' . $status] = sanitize_textarea_field($post['egoi_sms_order_text_customer_' . $status]);
                        $messages['egoi_sms_order_text_admin_' . $status] = sanitize_textarea_field($post['egoi_sms_order_text_admin_' . $status]);
                    }
                }

                $texts[$lang] = $messages;

                update_option('egoi_sms_order_texts', json_encode($texts));

            } else if (isset($form_id) && $form_id == 'form-sms-order-payment-texts') {

                $texts = json_decode(get_option('egoi_sms_order_payment_texts'), true);
                $method = sanitize_text_field($post['sms_payment_method']);

                foreach ($this->helper->smsonw_get_languages() as $code => $lang) {
                    if (trim($post['egoi_sms_order_payment_text_'.$code]) != '') {
                        $messages['egoi_sms_order_payment_text_' . $code] = sanitize_textarea_field($post['egoi_sms_order_payment_text_' . $code]);
                    }
                    if (trim($post['egoi_sms_order_reminder_text_'.$code]) != '') {
                        $messages['egoi_sms_order_reminder_text_' . $code] = sanitize_textarea_field($post['egoi_sms_order_reminder_text_' . $code]);
                    }
                }

                $texts[$method] = $messages;

                update_option('egoi_sms_order_payment_texts', json_encode($texts));

            } else if (isset($form_id) && $form_id == 'form-sms-order-tests') {

                $prefix = filter_var($post['recipient_prefix'], FILTER_SANITIZE_NUMBER_INT);
                $phone = sanitize_text_field($post['recipient_phone']);
                $message = sanitize_textarea_field($post['message']);

	            $recipient = $this->helper->smsonw_get_valid_recipient($phone, null, $prefix);
                $response = $this->helper->smsonw_send_sms($recipient, $message, 'test', 0);

                if (isset($response->errorCode)) {
                    return false;
                }
            }
	        return true;
        } catch (Exception $e) {
            $this->helper->smsonw_save_logs('process_config_form: ' . $e->getMessage());
        }

    }

	/**
	 * Process SMS reminders (CRON every fifteen minutes)
	 */
    public function smsonw_sms_order_reminder() {
        try {

            if (date('G') >= 10 && date('G') <= 22) {

                global $wpdb;

                $table_name = $wpdb->prefix . 'egoi_sms_order_reminders';

                $sql = " SELECT DISTINCT order_id FROM $table_name ";
                $order_ids = $wpdb->get_col($sql);

                $orders = $this->helper->smsonw_get_not_paid_orders();

                if (isset($orders)) {

                    $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);
                    $count = 0;

                    foreach ($orders as $order) {

                        if ($count >= 20) {
                            break;
                        }

                        $order_data = $order->get_data();

                        if ($recipient_options['notification_option']) {
                            $sms_notification = (bool)get_post_meta($order->get_id(), 'egoi_notification_option')[0];
                        } else {
                            $sms_notification = 1;
                        }

                        $lang = $this->helper->smsonw_get_lang($order_data['billing']['country']);

                        $payment_method = $this->helper->smsonw_get_option_payment_method($order['payment_method']);

                        if (!$order->is_paid() && !in_array($order->get_id(), $order_ids) && $sms_notification &&
                            $recipient_options['egoi_reminders'] && array_key_exists($order_data['payment_method'], $this->helper->payment_map)) {

                            $message = $this->helper->smsonw_get_tags_content($order_data, $this->helper->sms_payment_info['reminder'][$lang]);
                            $send_message = $message ? true : false;

                        } else if (!$order->is_paid() && !in_array($order->get_id(), $order_ids) && $sms_notification &&
                            $recipient_options['egoi_reminders_billet'] && $order_data['payment_method'] == 'pagseguro') {

                            $message = $this->helper->smsonw_get_tags_content($order_data, $this->helper->sms_payment_info['reminder'][$lang]);
                            $send_message = $message ? true : false;
                        }

                        if ($send_message) {
                            $recipient = $this->helper->smsonw_get_valid_recipient($order->billing_phone, $order->billing_country);
                            $this->helper->smsonw_send_sms($recipient, $message, $order->get_status(), $order->get_id());
                            $count++;

                            $wpdb->insert($table_name, array(
                                "time" => current_time('mysql'),
                                "order_id" => $order->get_id()
                            ));
                        }
                    }
                }
            }

        } catch (Exception $e) {
	        $this->helper->smsonw_save_logs('sms_order_reminder: ' . $e->getMessage());
        }
    }

	/**
     * Send SMS when order status changed
     *
	 * @param $order_id
	 */
	public function smsonw_order_send_sms_new_status($order_id) {

		$recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);

		if ($recipient_options['notification_option']) {
			$sms_notification = (bool) get_post_meta($order_id, 'egoi_notification_option')[0];
		} else {
			$sms_notification = 1;
		}


        $sender = json_decode(get_option('egoi_sms_order_sender'), true);
        $order = wc_get_order($order_id)->get_data();
        $types = array('admin' => $sender['admin_prefix'].'-'.$sender['admin_phone']);
        if ($sms_notification) {
            $types['customer'] = $order['billing']['phone'];
        }

        foreach ($types as $type => $phone) {
            $message = $this->helper->smsonw_get_sms_order_message($type, $order);
            if ($message !== false) {
                $recipient = $type == 'customer' ? $this->helper->smsonw_get_valid_recipient($phone, $order['billing']['country']) : $phone;
                $this->helper->smsonw_send_sms($recipient, $message, $order['status'], $order['id']);
            }
        }
	}

	/**
     * Send SMS with payment instructions when order is closed
     *
	 * @param $order_id
	 */
    public function smsonw_order_send_sms_payment_data($order_id) {

	    $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);

	    $order = wc_get_order($order_id)->get_data();

	    if ($recipient_options['notification_option']) {
		    $sms_notification = (bool) get_post_meta($order_id, 'egoi_notification_option')[0];
	    } else {
		    $sms_notification = 1;
	    }

        $lang = $this->helper->smsonw_get_lang($order['billing']['country']);
        $messages = json_decode(get_option('egoi_sms_order_payment_texts'), true);
        $payment_method = $this->helper->smsonw_get_option_payment_method($order['payment_method']);

        if ($sms_notification && $recipient_options['egoi_payment_info'] && array_key_exists($order['payment_method'], $this->helper->payment_map)) {

            $message = $this->helper->smsonw_get_tags_content($order, $messages[$payment_method]['egoi_sms_order_payment_text_'.$lang]);
            $send_message = $message ? true : false;

        } else if ($sms_notification && $recipient_options['egoi_payment_info_billet'] && $payment_method == 'billet') {

            $code = $this->smsonw_save_billet($order_id);
            if ($code) {
                $message = $this->helper->smsonw_get_tags_content($order, $messages[$payment_method]['egoi_sms_order_payment_text_'.$lang], $code);
                $send_message = $message ? true : false;
            }
        }

        if ($send_message) {
            $recipient = $this->helper->smsonw_get_valid_recipient($order['billing']['phone'], $order['billing']['country']);
            $this->helper->smsonw_send_sms('351-917936217', $message,'order', $order_id); // TODO - Put $recipient in recipient param
        }
    }

	/**
	 * Add SMS meta box to order admin page
	 */
	public function smsonw_order_add_sms_meta_box() {
		add_meta_box(
			'woocommerce-order-my-custom',
			__('Send SMS to buyer', 'smart-marketing-addon-sms-order'),
			array( $this, 'smsonw_order_display_sms_meta_box' ),
			'shop_order',
			'side',
			'core'
		);
	}

	/**
	 * The meta box content
	 *
	 * @param $post
	 */
	public function smsonw_order_display_sms_meta_box($post) {

		$recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);
		$order = wc_get_order($post->ID)->get_data();

		if ($recipient_options['notification_option']) {
			$sms_notification = (bool) get_post_meta($post->ID, 'egoi_notification_option')[0];
		} else {
			$sms_notification = 1;
		}

		if ($sms_notification) {
			$recipient = $order['billing']['phone'];
			?>
            <div id="egoi_send_order_sms">
                <input type="hidden" name="egoi_sms_order_id" id="egoi_send_order_sms_order_id"
                       value="<?php echo $order['id']; ?>"/>
                <input type="hidden" name="egoi_sms_order_country" id="egoi_send_order_sms_order_country"
                       value="<?php echo $order['billing']['country']; ?>"/>
                <input type="hidden" name="egoi_sms_recipient" id="egoi_send_order_sms_recipient"
                       value="<?= $recipient ?>"/>
                <p>
                    <label for="egoi_send_order_sms_message"><?php _e('Message', 'smart-marketing-addon-sms-order');?></label><br>
                    <textarea name="egoi_sms_message" id="egoi_send_order_sms_message" style="width: 100%;"></textarea>
                </p>
                <p>
                    <button type="button" class="button" id="egoi_send_order_sms_button"><?php _e('Send', 'smart-marketing-addon-sms-order'); ?></button>
                    <span id="egoi_send_order_sms_error" style="display: none; color: red;"><?php _e('You can\'t send a empty SMS', 'smart-marketing-addon-sms-order');?></span>
                    <span id="egoi_send_order_sms_notice" style="display: none;"><?php _e('Sending... Wait please', 'smart-marketing-addon-sms-order');?></span>
                </p>
            </div>
			<?php
		} else {
		    _e('The customer doesn\'t want to receive sms', 'smart-marketing-addon-sms-order');
        }
	}

	/**
	 * Send SMS and add note to admin order page
	 */
	public function smsonw_order_action_sms_meta_box() {
        check_ajax_referer( 'egoi_send_order_sms', 'security' );

        $cellphone = sanitize_text_field($_POST['recipient']);
        $country = sanitize_text_field($_POST['country']);
        $message = sanitize_textarea_field($_POST['message']);
        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_NUMBER_INT);

		$recipient = $this->helper->smsonw_get_valid_recipient($cellphone, $country);

		$result = $this->helper->smsonw_send_sms($recipient, $message, 'order', $order_id);

		if (!isset($result->errorCode)) {
			$order = wc_get_order($order_id);
			$order->add_order_note('SMS: '.$message);

			$note = array(
				"message" => 'SMS: '.$message,
				"date" => __('added on', 'smart-marketing-addon-sms-order').' '.current_time(get_option('date_format').' '.get_option('time_format'))
			);
			echo json_encode($note);
		} else {
			echo json_encode($result);
		}
		wp_die();
	}

	/**
	 * Add new interval to wordpress cron schedules
	 * @param $schedules
	 *
	 * @return mixed
	 */
	public function smsonw_my_add_every_fifteen_minutes($schedules) {
		$schedules['every_fifteen_minutes'] = array(
			'interval' => 60 * 15,
			'display' => __('Every Fifteen Minutes')
		);
		return $schedules;
	}

    /**
     * Show a error notice if don't have WooCommerce
     */
    public function smsonw_woocommerce_dependency_notice() {
        if ( !class_exists( 'WooCommerce' ) ) {
            ?>
            <div class="notice notice-error is-dismissible">
            <p><?php _e('To use this plugin, you first need to install WooCommerce', 'smart-marketing-addon-sms-order'); ?></p>
            </div>
            <?php
        }
    }



    function smsonw_save_billet($order_id) {
        $order = wc_get_order( $order_id );
        $data = $order->get_meta( '_wc_pagseguro_payment_data' );
        if (isset($data['link'])) {
            global $wpdb;

            $code = uniqid();

            $wpdb->insert("{$wpdb->prefix}egoi_sms_order_billets", array(
                    'time' => current_time('mysql'),
                    'order_id' => $order_id,
                    'link' => $data['link'],
                    'code' => $code
            ));

            return $code;
        }
        return false;
    }

    function smsonw_billet_endpoint() {
        register_rest_route( 'smsonw/v1', '/billet', array(
            'methods' => 'GET',
            'callback' => array( $this, 'smsonw_billet_redirect'),
            'args' => array(
                'c' => array(
                    'sanitize_callback'  => 'sanitize_text_field'
                ),
            ),
        ) );
    }

    function smsonw_billet_redirect( WP_REST_Request $request ) {
        $params = $request->get_query_params('c');

        global $wpdb;

        $link = $wpdb->get_var("SELECT link FROM {$wpdb->prefix}egoi_sms_order_billets WHERE code = '$params[c]'");

        if ($link) {
            wp_redirect($link);
            exit;
        }
        return 'Not Found';
    }

}
