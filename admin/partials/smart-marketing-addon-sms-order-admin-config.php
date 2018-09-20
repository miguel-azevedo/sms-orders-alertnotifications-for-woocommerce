<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.e-goi.com
 * @since      1.0.0
 *
 * @package    Smart_Marketing_Addon_Sms_Order
 * @subpackage Smart_Marketing_Addon_Sms_Order/admin/partials
 */

if (isset($_POST['form_id'])) {
    $result = $this->process_config_form($_POST);
    if ($result) {
        $this->admin_notice__success();
    } else {
	    $this->admin_notice__error();
    }
}

$sender_option = json_decode(get_option('egoi_sms_order_sender'), true);
$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
$texts = json_decode(get_option('egoi_sms_order_texts'), true);

$senders = $this->get_senders();

?>
<span id="form_info" data-form-id="<?=$_POST['form_id']?>" data-form-lang="<?=$_POST['sms_text_language']?>"></span>
<!-- head -->
<h1 class="logo">Smart Marketing - <?php _e( 'SMS Order Config', 'addon-sms-order' ); ?></h1>
<p class="breadcrumbs">
    <span class="prefix"><?php _e( 'You are here: ', 'addon-sms-order' ); ?></span>
    <strong>Smart Marketing</a> &rsaquo;
        <span class="current-crumb"><?php _e( 'SMS Order Config', 'addon-sms-order' ); ?></strong></span>
</p>
<hr/>

<h2 class="nav-tab-wrapper">
    <a class="nav-tab nav-tab-addon nav-tab-active" id="nav-tab-sms-senders">
		<?php _e('Senders', 'addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-recipients">
        <?php _e('Recipients', 'addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-texts">
        <?php _e('SMS Texts', 'addon-sms-order'); ?>
    </a>
</h2>

<!-- wrap SMS Senders -->
<div class="wrap tab wrap-addon" id="tab-sms-senders">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">
            <div class="main-content col col-4" style="margin:0 0 20px;">

                <div style="font-size:14px; margin:10px 0;">
		            <?php _e('Senders', 'addon-sms-order');?>
                </div>

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-senders">
                    <input name="form_id" type="hidden" value="form-sms-order-senders" />
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e('E-goi Sender', 'addon-sms-order');?></label>
                            </th>
                            <td>
                                <select name="sender_hash" id="sender_hash">
                                    <option value="" disabled selected>
                                        <?php _e('Selected the sender', 'addon-sms-order');?>
                                    </option>
                                    <?php
                                        if (isset($senders) && count($senders) > 0) {
                                            foreach ($senders as $sender) {
                                                ?>
                                                <option value="<?=$sender['FROMID']?>" <?php selected($sender['FROMID'], $sender_option['sender_hash']);?> >
                                                    <?=$sender['SENDER']?>
                                                </option>
                                                <?php
                                            }
                                        }
                                    ?>
                                </select>
                            </td>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e('Admin Cellphone', 'addon-sms-order');?></label>
                            </th>
                            <td>
                                <select name="admin_prefix" style="width:10em; float: left;" required >
		                            <?php
		                            foreach (unserialize(COUNTRY_CODES) as $key => $value) {
			                            $string = ucwords(strtolower($value['name']))." (+".$value['code'].")";
			                            ?>
                                        <option value="<?=$value['code']?>" <?php selected($value['code'], $sender_option['admin_prefix']);?> ><?=$string?></option>
			                            <?php
		                            }
		                            ?>
                                </select>
                                <input type="text" name="admin_phone" class="regular-text" maxlength="9"
                                       value="<?php echo isset($sender_option['admin_phone']) ? $sender_option['admin_phone'] : null; ?>"
                                />
                            </td>
                        </tr>
                    </table>
                    <table border="0">
                        <tr>
                            <td><?php submit_button(); ?></td>
                            <td><a id="button-test-sms">Send a test SMS</a></td>
                        </tr>
                    </table>
                </form>

                <div id="test-sms" style="display: none;">
                    <form action="#" method="post">
                        <input name="form_id" type="hidden" value="form-sms-order-tests" />
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label><?php _e('Recipient', 'addon-sms-order');?></label>
                                </th>
                                <td>
                                    <select name="recipient_prefix" style="width:10em; float: left;" required >
		                                <?php
		                                foreach (unserialize(COUNTRY_CODES) as $key => $value) {
			                                $string = ucwords(strtolower($value['name']))." (+".$value['code'].")";
			                                ?>
                                            <option value="<?=$value['code']?>" ><?=$string?></option>
			                                <?php
		                                }
		                                ?>
                                    </select>
                                    <input type="text" name="recipient_phone" class="regular-text" maxlength="9" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label><?php _e('Message', 'addon-sms-order');?></label>
                                </th>
                                <td>
                                    <textarea name="message" style="width: 35em;"></textarea>
                                </td>
                            </tr>
                        </table>
	                    <?php submit_button('Send SMS', 'secondary'); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- wrap SMS Recipients -->
<div class="wrap tab wrap-addon" id="tab-sms-recipients">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">
            <div class="main-content col col-4" style="margin:0 0 20px;">
                <div style="font-size:14px; margin:10px 0;">
                    <?php _e('Recipients', 'addon-sms-order');?>
                </div>
                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-recipients">
                    <input name="form_id" type="hidden" value="form-sms-order-recipients" />

                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e('Notification option', 'addon-sms-order');?></label>
                            </th>
                            <td>
                                <input class="input-checkbox" type="checkbox" name="notification_option" value="1"
                                    <?php checked($recipients['notification_option'], 1);?>
                                />
                            </td>
                        </tr>
                    </table>

                    <table border='0' class="widefat striped">
                        <thead>
                        <tr>
                            <th><?php _e('Order Status', 'addon-sms-order');?></th>
                            <th><?php _e('Customer', 'addon-sms-order');?></th>
                            <th><?php _e('Admin', 'addon-sms-order');?></th>
                        </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($this->order_statuses as $cod => $name) { ?>
                            <tr>
                                <td>
                                    <?php _e($name, 'addon-sms-order');?>
                                </td>
                                <td>
                                    <input class="input-checkbox" type="checkbox" name="egoi_sms_order_customer_<?=$cod?>" value="1"
                                        <?php checked($recipients['egoi_sms_order_customer_'.$cod], 1);?>
                                    />
                                </td>
                                <td>
                                    <input class="input-checkbox" type="checkbox" name="egoi_sms_order_admin_<?=$cod?>" value="1"
	                                    <?php checked($recipients['egoi_sms_order_admin_'.$cod], 1);?>
                                    />
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- wrap SMS Texts -->
<div class="wrap tab wrap-addon" id="tab-sms-texts">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">
            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_span"><?php _e('Select the language', 'addon-sms-order');?></p>

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-texts">
                    <input name="form_id" type="hidden" value="form-sms-order-texts" />
                    <div id="sms_texts_select_lang">
                        <select class="e-goi-option-select-admin-forms" name="sms_text_language" id="sms_text_language">
                            <option value="" disabled selected>
                                <?php _e('Selected the language', 'addon-sms-order');?>
                            </option>
                            <option value="pt_BR" <?php selected($_GET['sms_text_language'], 'pt_BR');?>>
                                <?php _e('Brazilian Portuguese', 'addon-sms-order');?>
                            </option>
                            <option value="en" <?php selected($_GET['sms_text_language'], 'en');?> >
                                <?php _e('English', 'addon-sms-order');?>
                            </option>
                            <option value="pt" <?php selected($_GET['sms_text_language'], 'pt');?>>
                                <?php _e('Portuguese', 'addon-sms-order');?>
                            </option>
                            <option value="es" <?php selected($_GET['sms_text_language'], 'es');?>>
                                <?php _e('Spanish', 'addon-sms-order');?>
                            </option>
                        </select>
                    </div>

                    <div id="sms_texts_tags">
                        <?php foreach ($this->sms_text_tags as $tag_name => $tag_cod) { ?>
                            <button type="button" class="button button-default sms_texts_tags_button" data-text-cod="<?=$tag_cod?>">
                                <?php echo ucwords(str_replace('_', ' ', $tag_name)); ?>
                            </button>
                        <?php } ?>
                    </div>

                    <?php foreach ($this->languages as $lang) { ?>
                        <div id="sms_order_texts_<?=$lang?>">
                            <table border="0" class="widefat striped">
                                <thead>
                                    <tr>
                                        <th><?php _e('Order Status', 'addon-sms-order');?></th>
                                        <th><?php _e('Customer', 'addon-sms-order');?></th>
                                        <th><?php _e('Admin', 'addon-sms-order');?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($this->order_statuses as $cod => $name) { ?>
                                    <tr>
                                        <td><?php _e($name, 'addon-sms-order');?></td>
                                        <td>
                                            <textarea name="egoi_sms_order_text_customer_<?=$cod?>" id="egoi_sms_order_text_customer_<?=$cod?>"><?=$texts[$lang]["egoi_sms_order_text_customer_".$cod]?></textarea>
                                        </td>
                                        <td>
                                            <textarea name="egoi_sms_order_text_admin_<?=$cod?>" id="egoi_sms_order_text_admin_<?=$cod?>"><?=$texts[$lang]["egoi_sms_order_text_admin_".$cod]?></textarea>
                                        </td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <?php submit_button(); ?>
                        </div>
                    <?php } ?>
                </form>
            </div>
        </div>
    </div>
</div>