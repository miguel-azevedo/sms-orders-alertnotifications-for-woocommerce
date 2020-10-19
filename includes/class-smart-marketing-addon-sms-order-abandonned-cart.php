<?php

class Smart_Marketing_Addon_Sms_Order_Abandoned_Cart{

    const SESSION_TAG_UID = 'egoi_tracking_uid';

    protected $helper;

    public function start(){
        if( WC()->cart->is_empty() ){ return false; }
        $this->helper = new Smart_Marketing_Addon_Sms_Order_Helper();
        //$url = self::getCartUrl($this->getCartSession());//url recuperação carrinho
        if($this->isKnowNumber() === false){
            require_once plugin_dir_path( dirname( __FILE__ ) ).'includes/class-smart-marketing-addon-sms-order-cellphone-popup.php';
            $popUp = new Smart_Marketing_Addon_Sms_Order_Cellphone_Popup();
            $popUp->printPopup();
        }
    }

    public function convertCart(){
        if(empty($_SESSION['sid_eg'])){//didnt came from sms url
            return false;
        }

        try{
            global $wpdb;
            $query = $wpdb->update($wpdb->prefix.'egoi_sms_abandoned_carts', ['status' => 'sold'], ['php_session_key' => $_SESSION['sid_eg']]);
        }catch (Exception $e){

        }
    }

    public static function getCartUrl($wc_session){
        $cartArray = self::getCartBySessionId($wc_session);
        return wc_get_checkout_url().self::cartArrayToUrlParam($cartArray,$wc_session);
    }

    public static function cartArrayToUrlParam($cartArray,$wc_session){
        global $wpdb;
        $query = sprintf(
            "SELECT %s FROM %s%s WHERE %s = '%s' AND %s = '%s'",
            'php_session_key',
            $wpdb->prefix,
            'egoi_sms_abandoned_carts',
            'woo_session_key',
            $wc_session,
            'status',
            'waiting'
        );

        $result = $wpdb->get_var($query);

        $output='?create-cart=';
        foreach ($cartArray as $product_id => $quantity){
            $output .= "$product_id:$quantity,";
        }
        return $output.(!empty($result))?'&sid_eg='.$result:'';
    }

    private function isKnowNumber(){
        $cellphone = $this->getCellphoneLogged();
        //not logged
        if($cellphone === false){

            if(empty($_SESSION[self::SESSION_TAG_UID])){
                return false;
            }
            return false;
        }else if ($cellphone === ''){//logged but no phone
            return false;
        }
        return $cellphone;
    }

    public function saveAbandonedCart(){

    }

    private function getCartSession(){
        $separator = '%7C%7C';
        $needle = 'wp_woocommerce_session_';
        if(!is_array($_COOKIE)){return false;}

        foreach ($_COOKIE as $key => $value) {
            if(strpos($key,'woocommerce_session_') !== false){
                return explode('||',$value)[0];
            }
        }

        return 0;
    }

    private function isSavedCart(){

    }

    private static function getCartBySessionId($wc_session){

        global $wpdb;
        $query = sprintf(
            "SELECT %s FROM %s%s WHERE %s = '%s'",
            'session_value',
            $wpdb->prefix,
            'woocommerce_sessions',
            'session_key',
            $wc_session
        );


        $result = $wpdb->get_var($query);
        if(empty($result)){return false;}
        $cart = unserialize(unserialize($result)['cart']);

        $output = [];
        foreach ($cart as $item){
            if(!empty($item['variation_id'])){
                $output[$item['variation_id']] = $item['quantity'];
                continue;
            }
            $output[$item['product_id']] = $item['quantity'];
        }
        return $output;
    }


    /**
     * Return false if not logged
     * empty string if cellphone is not found
     *
     * @return bool | string
     */
    private function getCellphoneLogged(){
        $current_user = wp_get_current_user();
        if(!$current_user->exists()){
            return false;
        }
        $billing_phone = get_user_meta( $current_user->ID, 'billing_phone', true );
        $billing_country = get_user_meta( $current_user->ID, 'billing_country', true );
        if(empty($billing_phone)){return '';}
        return $this->helper->smsonw_get_valid_recipient($billing_phone, $billing_country);
    }
}