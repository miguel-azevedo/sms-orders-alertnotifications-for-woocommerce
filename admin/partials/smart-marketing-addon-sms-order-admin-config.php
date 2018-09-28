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
        $this->helper->admin_notice__success();
    } else {
	    $this->helper->admin_notice__error();
    }
}

$sender_option = json_decode(get_option('egoi_sms_order_sender'), true);
$recipients = json_decode(get_option('egoi_sms_order_recipients'), true);
$texts = json_decode(get_option('egoi_sms_order_texts'), true);

$senders = $this->helper->get_senders();

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
		<?php _e('Senders/Recipients', 'addon-sms-order'); ?>
    </a>

    <a class="nav-tab nav-tab-addon" id="nav-tab-sms-texts">
        <?php _e('SMS Texts', 'addon-sms-order'); ?>
    </a>
</h2>

<!-- wrap SMS Senders/Recipients -->
<div class="wrap tab wrap-addon" id="tab-sms-senders">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">
            <div class="main-content col col-12" style="margin:0 0 20px;">

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-senders">
                    <input name="form_id" type="hidden" value="form-sms-order-senders" />

                    <p class="label_text"><?php _e('E-goi Sender', 'addon-sms-order');?></p>

                    <select class="e-goi-option-select-admin-forms" style="width: 400px;" name="sender_hash" id="sender_hash" required>
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

                    <p class="label_text"><?php _e('Admin Cellphone', 'addon-sms-order');?></p>

                    <select name="admin_prefix" class="e-goi-option-select-admin-forms" style="width: 175px; float: left;" required >
		                <?php
		                foreach (unserialize(COUNTRY_CODES) as $key => $value) {
			                $string = ucwords(strtolower($value['country_pt']))." (+".$value['prefix'].")";
			                ?>
                            <option value="<?=$value['prefix']?>" <?php selected($value['prefix'], $sender_option['admin_prefix']);?> ><?=$string?></option>
			                <?php
		                }
		                ?>
                    </select>
                    <input type="text" id="admin_phone" name="admin_phone" class="regular-text" style="width: 222px; height: 38px;"
                           value="<?php echo isset($sender_option['admin_phone']) ? $sender_option['admin_phone'] : null; ?>"
                    />


                    <p class="label_text"><?php _e('Select the SMS type that you want to sent', 'addon-sms-order');?></p>

                    <table border='0' class="widefat striped" style="max-width: 800px;">
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
                                    <input class="input-checkbox admin-order-status" type="checkbox" name="egoi_sms_order_admin_<?=$cod?>" value="1"
							            <?php checked($recipients['egoi_sms_order_admin_'.$cod], 1);?>
                                    />
                                </td>
                            </tr>
			            <?php } ?>
                        </tbody>
                    </table>

                    <p class="label_text">
                        <input class="input-checkbox" type="checkbox" name="notification_option" value="1" id="notification_option"
                            <?php checked($recipients['notification_option'], 1);?>
                        />
                        <label for="notification_option"><?php _e('Enable option on order checkout to receive SMS', 'addon-sms-order');?></label>
                    </p>

		            <?php submit_button(); ?>
                </form>

                <hr>

                <div id="test-sms" >
                    <form action="#" method="post">
	                    <?php
	                    if ($sender_option['sender_hash']) {
		                    $disabled = null;
	                    } else {
		                    $disabled = array('disabled' => 1);
	                    }
	                    ?>
                        <input name="form_id" type="hidden" value="form-sms-order-tests" />

                        <p class="label_text"><?php _e('Send a TEST SMS', 'addon-sms-order');?></p>

                        <select name="recipient_prefix" class="e-goi-option-select-admin-forms" style="width: 175px; float: left;" required <?php echo $disabled ? 'disabled' : null;?> >
		                    <?php
		                    foreach (unserialize(COUNTRY_CODES) as $key => $value) {
			                    $string = ucwords(strtolower($value['country_pt']))." (+".$value['prefix'].")";
			                    ?>
                                <option value="<?=$value['prefix']?>" ><?=$string?></option>
			                    <?php
		                    }
		                    ?>
                        </select>
                        <input type="text" name="recipient_phone" class="regular-text" style="width: 222px; height: 38px;" required <?php echo $disabled ? 'disabled' : null;?> />

                        <br>

                        <textarea name="message" style="width: 400px;" rows="5" required <?php echo $disabled ? 'disabled' : null;?>></textarea>

	                    <?php submit_button('Send SMS', 'secondary', 'submit', true, $disabled); ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- wrap SMS Texts -->
<div class="wrap tab wrap-addon" id="tab-sms-texts">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">
            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_text"><?php _e('Select the language', 'addon-sms-order');?></p>

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-texts">
                    <input name="form_id" type="hidden" value="form-sms-order-texts" />
                    <div id="sms_texts_select_lang">
                        <select class="e-goi-option-select-admin-forms" style="width: 400px;" name="sms_text_language" id="sms_text_language">
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
                            <table border="0" class="widefat striped" style="max-width: 900px;">
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
                                            <textarea name="egoi_sms_order_text_customer_<?=$cod?>" cols="40" rows="4" id="egoi_sms_order_text_customer_<?=$cod?>"><?=$texts[$lang]["egoi_sms_order_text_customer_".$cod]?></textarea>
                                        </td>
                                        <td>
                                            <textarea name="egoi_sms_order_text_admin_<?=$cod?>" cols="40" rows="4" id="egoi_sms_order_text_admin_<?=$cod?>"><?=$texts[$lang]["egoi_sms_order_text_admin_".$cod]?></textarea>
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