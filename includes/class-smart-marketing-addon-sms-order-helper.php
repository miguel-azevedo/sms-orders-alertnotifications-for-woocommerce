<?php

/**
 * Helper class
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 */

/**
 * This class defines all generic methods.
 *
 * @since      1.0.0
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/includes
 * @author     E-goi <egoi@egoi.com>
 */
class Smart_Marketing_Addon_Sms_Order_Helper {

	private $apikey;

	/**
	 * @var array
	 */
	protected $payment_map = array(
		'eupago_multibanco' => array(
			'ent' => '_eupago_multibanco_entidade',
			'ref' => '_eupago_multibanco_referencia',
			'val' => '_order_total'
		),
		'eupago_payshop' => array(
			'ref' => '_eupago_payshop_referencia',
			'val' => '_order_total'
		),
		'eupago_mbway' => array(
			'ref' => '_eupago_mbway_referencia',
			'val' => '_order_total'
		),
		'multibanco_ifthen_for_woocommerce' => array(
			'ent' => '_multibanco_ifthen_for_woocommerce_ent',
			'ref' => '_multibanco_ifthen_for_woocommerce_ref',
			'val' => '_multibanco_ifthen_for_woocommerce_val'
		),
		// TODO - confirm fields for ifThenPay MBWay
		'mbway_ifthen_for_woocommerce' => array(
			'ref' => '_mbway_ifthen_for_woocommerce_ref',
			'val' => '_mbway_ifthen_for_woocommerce_val'
		)
	);

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$apikey = get_option('egoi_api_key');
		$this->apikey = $apikey['api_key'];
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
	 * Get not paid orders over 48 hours
	 *
	 * @return mixed
	 */
	public function get_not_paid_orders() {
		$two_days_in_sec = 2 * 24 * 60 * 60;
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

	/**
	 * Get SMS text from configs
	 *
	 * @param $recipient_type
	 * @param $order
	 *
	 * @return bool|mixed
	 */
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
				"%ref%" => $this->get_payment_data($order, 'ref'),
				"%ent%" => $this->get_payment_data($order, 'ent'),
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

	/**
	 * Get order payment instructions
	 *
	 * @param $order
	 * @param $field
	 *
	 * @return bool
	 */
	public function get_payment_data($order, $field) {
		$order_meta = get_post_meta($order['id']);

		if (isset($this->payment_map[$order['payment_method']][$field])) {
			$payment_field = $this->payment_map[$order['payment_method']][ $field ];
			return $order_meta[$payment_field][0];
		}
		return false;
	}

	/**
	 * Prepare recipient to E-goi
	 */
	public function get_valid_recipient($phone, $country) {
		$recipient = preg_replace('/[^0-9]/', '', $phone);
		if (strlen($recipient) > 9) {
			$recipient = substr($recipient, 0, -9).'-'.substr($recipient, -9);
		} else {
			$prefixes = unserialize(COUNTRY_CODES);
			$recipient = $prefixes[$country]['code'].'-'.$recipient;
		}
		return $recipient;
	}

	/**
	 * Method to send SMS
	 *
	 * @param $recipient
	 * @param $message
	 * @param $type
	 * @param $order_id
	 * @param bool $gsm
	 *
	 * @return mixed
	 */
	public function send_sms($recipient, $message, $type, $order_id, $gsm = false) {
		$url = 'http://dev-web-agency.e-team.biz/smaddonsms/sms';

		$sender = json_decode(get_option('egoi_sms_order_sender'), true);

		$sms_params = array(
			"apikey" => $this->apikey,
			"sender_hash" => $sender['sender_hash'],
			"message" => $message,
			"recipient" => $recipient,
			"type" => $type,
			"order_id" => $order_id,
			"gsm" => $gsm
		);

		return $this->curl($url, $sms_params);
	}

	/**
	 * Save logs in /logs/smart-marketing-addon-sms-order.log
	 *
	 * @param $log
	 */
	public function save_logs($log) {
		$path = dirname(__FILE__).'/logs/';

		$file = fopen($path.'smart-marketing-addon-sms-order.log', 'a+');
		fwrite($file, $log."\xA");
		fclose($file);
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

	/**
	 * Div to success notices
	 */
	public function admin_notice__success() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Done!', 'addon-sms-order' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Div to error notices
	 */
	public function admin_notice__error() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'Irks! An error has occurred.', 'addon-sms-order' ); ?></p>
		</div>
		<?php
	}

}