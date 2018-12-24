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
 * This class defines all generic attributes and methods.
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
	public $payment_map = array(
		'eupago_multibanco' => array(
			'ent' => '_eupago_multibanco_entidade',
			'ref' => '_eupago_multibanco_referencia',
			'val' => '_order_total'
		),
		'eupago_payshop' => array(
			'ref' => '_eupago_payshop_referencia',
			'val' => '_order_total'
		),
		/*
		'eupago_mbway' => array(
			'ref' => '_eupago_mbway_referencia',
			'val' => '_order_total'
		),
		*/
		'multibanco_ifthen_for_woocommerce' => array(
			'ent' => '_multibanco_ifthen_for_woocommerce_ent',
			'ref' => '_multibanco_ifthen_for_woocommerce_ref',
			'val' => '_multibanco_ifthen_for_woocommerce_val'
		),
		/*
		// TODO - confirm fields for ifThenPay MBWay
		'mbway_ifthen_for_woocommerce' => array(
			'ref' => '_mbway_ifthen_for_woocommerce_ref',
			'val' => '_mbway_ifthen_for_woocommerce_val'
		)
		*/
	);

    /**
     * @var array
     */
	public $sms_payment_info = array(
	    'first' => array(
	        'en' => 'Hello, your order at %shop_name% is waiting for MB payment. Use Ent. %ent% Ref. %ref% Value %total%%currency% Thank you',
            'es' => 'Hola, su pedido en %shop_name% está esperando el pago MB - Ent. %ent% Ref. %ref% Valor %total%%currency% Gracias',
            'pt' => 'Olá, a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado',
            'pt_BR' => 'Olá, a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado'
        ),
        'reminder' => array(
            'en' => 'Hello, we remind you that your order at %shop_name% is waiting for MB. Use Ent. %ent% Ref. %ref% Value %total%%currency% Thank you',
            'es' => 'Hola, recordamos que su pedido en %shop_name% está esperando el pago MB - Ent. %ent% Ref. %ref% Valor %total%%currency% Gracias',
            'pt' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado',
            'pt_BR' => 'Olá, lembramos que a sua encomenda em %shop_name% está aguardar pagamento MB use Ent. %ent% Ref. %ref% Valor %total%%currency% Obrigado'
        ),
    );

    /**
     * @var array
     */
    public $currency = array(
        'EUR' => '€',
        'USD' => '$',
        'GBP' => '£',
        'BRL' => 'R$'
    );

    /**
     * @var array List of SMS text tags
     */
    public $sms_text_tags = array(
        "order_id" => '%order_id%',
        "order_status" => '%order_status%',
        "total" => '%total%',
        "currency" => '%currency%',
        "payment_method" => '%payment_method%',
        "reference" => '%ref%',
        "entity" => '%ent%',
        "shop_name" => '%shop_name%',
        "billing_name" => '%billing_name%',
        "billet_URL" => '%billet_url%'
    );

    /**
     * @var SoapClient
     */
    protected $egoi_api_client;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
        $this->egoi_api_client = new SoapClient('http://api.e-goi.com/v2/soap.php?wsdl');

		$apikey = get_option('egoi_api_key');
		$this->apikey = $apikey['api_key'];
	}

    /**
     * @return array
     */
    public function smsonw_get_order_statuses() {
        return array(
            'pending' => __('Pending payment', 'smart-marketing-addon-sms-order'),
            'processing' => __('Processing', 'smart-marketing-addon-sms-order'),
            'on-hold' => __('On Hold', 'smart-marketing-addon-sms-order'),
            'completed' => __('Completed', 'smart-marketing-addon-sms-order'),
            'cancelled' => __('Cancelled', 'smart-marketing-addon-sms-order'),
            'refunded' => __('Refunded', 'smart-marketing-addon-sms-order'),
            'failed' => __('Failed', 'smart-marketing-addon-sms-order'),
        );
    }

    /**
     * @return array
     */
    public function smsonw_get_languages() {
        return array(
            'en' =>  __('English', 'smart-marketing-addon-sms-order'),
            'es' =>  __('Spanish', 'smart-marketing-addon-sms-order'),
            'pt' =>  __('Portuguese', 'smart-marketing-addon-sms-order'),
            'pt_BR' =>  __('Brazilian Portuguese', 'smart-marketing-addon-sms-order'),
        );
    }

    /**
     * @return array
     */
    public function smsonw_get_payment_methods() {
        return array(
            'multibanco' =>  __('Multibanco (EuPago, IfthenPay)', 'smart-marketing-addon-sms-order'),
            'payshop' =>  __('Payshop (EuPago)', 'smart-marketing-addon-sms-order'),
            'billet' =>  __('Billet (PagSeguro)', 'smart-marketing-addon-sms-order'),
        );
    }

	/**
	 * Function to get cellphone senders from E-goi account
	 *
	 * @return array with senders
	 */
	public function smsonw_get_senders() {
		$result = $this->egoi_api_client->getSenders(array(
            'apikey' 		=> $this->apikey,
            'channel' 		=> 'telemovel'
        ));
		return $result;
	}

    /**
     * @return string
     */
	public function smsonw_get_balance() {
        $credits = explode(' ',$this->egoi_api_client->getClientData(array('apikey' => $this->apikey))['CREDITS']);
        return $credits[1].$this->currency[$credits[0]];
    }

	/**
	 * Get not paid orders over 48 hours
	 *
	 * @return mixed
	 */
	public function smsonw_get_not_paid_orders() {
		$two_days_in_sec = 2 * 24 * 60 * 60;
		$args = array(
			"status" => array(
				"pending",
				"on-hold"
			),
			"date_created" => '<' . (time() - $two_days_in_sec), // TODO - change time
			'limit' => -1
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
	public function smsonw_get_sms_order_message($recipient_type, $order) {
		$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
		$texts = json_decode(get_option('egoi_sms_order_texts'), true);
		$lang = $this->smsonw_get_lang($order['billing']['country']);

		if (isset($texts[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']])
            && isset($recipients['egoi_sms_order_' . $recipient_type . '_' . $order['status']])
            && $recipients['egoi_sms_order_' . $recipient_type . '_' . $order['status']] == 1
        ) {
            return $this->smsonw_get_tags_content($order, $texts[$lang]['egoi_sms_order_text_' . $recipient_type . '_' . $order['status']]);
		}
		return false;
	}

	/**
	 * @param $country
	 *
	 * @return bool|string
	 */
	public function smsonw_get_lang($country) {
		$country_codes = unserialize(COUNTRY_CODES);
		$lang = $country_codes[$country]['language'];
		$lang_allowed = array('en', 'pt', 'es');
		if ($lang == 'pt-BR') {
			return 'pt_BR';
		} else if (in_array(substr($lang, 0, 2), $lang_allowed)) {
			return substr($lang, 0, 2);
		}
		return 'en';
    }

	/**
	 * Get order payment instructions
	 *
	 * @param $order
	 * @param $field
	 *
	 * @return bool
	 */
	public function smsonw_get_payment_data($order, $field) {
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
	public function smsonw_get_valid_recipient($phone, $country, $prefix = null) {
		$prefix = preg_replace('/[^0-9]/', '', $prefix);
		$recipient = preg_replace('/[^0-9]/', '', $phone);

		if ($prefix) {
			return $prefix.'-'.$recipient;
		} else if ($country) {

			$prefixes = unserialize( COUNTRY_CODES );
			$len = strlen($prefixes[$country]['prefix']);
		    if ($prefixes[$country]['prefix'] != substr($recipient, 0, $len)) {
			    return $prefixes[ $country ]['prefix'] . '-' . $recipient;
		    } else {
		        return substr($recipient, 0, $len) . '-' . substr($recipient, $len);
            }

		}

		return $phone;
	}

    /**
     * replace tags with order data
     *
     * @param $order
     * @param $message
     * @return string
     */
    public function smsonw_get_tags_content($order, $message, $billet_code = false)
    {
        $tags = array(
            '%order_id%' => $order['id'],
            '%order_status%' => $order['status'],
            '%total%' => $order['total'],
            '%currency%' => $order['currency'],
            '%payment_method%' => $order['payment_method'],
            '%ref%' => $this->smsonw_get_payment_data($order, 'ref'),
            '%ent%' => $this->smsonw_get_payment_data($order, 'ent'),
            '%shop_name%' => get_bloginfo('name'),
            '%billing_name%' => $order['billing']['first_name'] . ' ' . $order['billing']['last_name']
        );

        if ($billet_code) {
            $tags['%billet_url%'] = get_site_url(null, '/wp-json/smsonw/v1/billet?c=' . $billet_code);
        }

        foreach ($tags as $tag => $content) {
            if ($tag == '%ref%' && $this->smsonw_get_payment_data($order, 'ref') == false) {
                $message = str_replace('Ref. %ref%', '', $message);
                continue;
            }
            if ($tag == '%ent%' && $this->smsonw_get_payment_data($order, 'ent') == false) {
                $message = str_replace('Ent. %ent%', '', $message);
                continue;
            }
            $message = str_replace($tag, $content, $message);
        }

        return $message;
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
	public function smsonw_send_sms($recipient, $message, $type, $order_id, $gsm = true, $max_count = 3) {
		$url = 'http://dev-web-agency.e-team.biz/smaddonsms/sms';

		$sender = json_decode(get_option('egoi_sms_order_sender'), true);

		$sms_params = array(
			"apikey" => $this->apikey,
			"sender_hash" => $sender['sender_hash'],
			"message" => $message,
			"recipient" => $recipient,
			"type" => $type,
			"order_id" => $order_id,
			"gsm" => $gsm,
            "max_count" => $max_count
		);

		$response = wp_remote_post($url, array(
            'timeout' => 60,
            'body' => $sms_params
        ));

		$result = json_encode($response['body']);

		if (!isset($result->errorCode)) {
            $sms_counter = get_option('egoi_sms_counter');
            $counter = $sms_counter ? $sms_counter+1 : 1;
            update_option('egoi_sms_counter', $counter);
        }

		return $result;
	}

	/**
	 * Save logs in /logs/smart-marketing-addon-sms-order.log
	 *
	 * @param $log
	 */
	public function smsonw_save_logs($log) {
		$path = dirname(__FILE__).'/logs/';

		$file = fopen($path.'smart-marketing-addon-sms-order.log', 'a+');
		fwrite($file, $log."\xA");
		fclose($file);
	}

	/**
	 * Div to success notices
	 */
	public function smsonw_admin_notice_success() {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php _e( 'Changes saved successfully', 'smart-marketing-addon-sms-order' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Div to error notices
	 */
	public function smsonw_admin_notice_error() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php _e( 'Irks! An error has occurred.', 'smart-marketing-addon-sms-order' ); ?></p>
		</div>
		<?php
	}

	public function smsonw_sanitize_boolean_field($field) {
        if (isset($_POST[$field]) && filter_var($_POST[$field], FILTER_VALIDATE_BOOLEAN)) {
            return filter_var($_POST[$field], FILTER_SANITIZE_NUMBER_INT);
        } else {
            return 0;
        }
    }

    /**
     * @param $order_id
     * @return bool|int
     */
    public function smsonw_check_notification_option($order_id) {
        $recipient_options = json_decode(get_option('egoi_sms_order_recipients'), true);

        if ($recipient_options['notification_option']) {
            return (bool) get_post_meta($order_id, 'egoi_notification_option')[0];
        } else {
            return 1;
        }
    }

}