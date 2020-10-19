<div class="wrap tab wrap-addon" id="tab-sms-abandoned-cart">
    <div class="wrap egoi4wp-settings" id="tab-forms">
        <div class="row">

            <div id="abandoned_cart_message">
                <?php
                if (isset($_POST['form_id']) && $_POST['form_id'] == 'form-sms-order-abandoned-cart') {
                    if ($result) {
                        $this->helper->smsonw_admin_notice_success();
                    } else {
                        $this->helper->smsonw_admin_notice_error();
                    }
                }
                ?>
            </div>

            <div class="main-content col col-12" style="margin:0 0 20px;">

                <p class="label_text"><?php _e('Use this to add a lost cart sms trigger.', 'smart-marketing-addon-sms-order');?></p>

                <form action="#" method="post" class="form-sms-order-config" id="form-sms-order-abandoned-cart">
                    <?php wp_nonce_field( 'form-sms-order-abandoned-cart' ); ?>
                    <input name="form_id" type="hidden" value="form-sms-order-abandoned-cart" />
                    <div id="sms_abandoned_cart">
                        <table border="0" class="widefat striped" style="max-width: 900px;">
                            <thead>
                            <tr>
                                <th><?php _e('Configurations', 'smart-marketing-addon-sms-order');?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td><span><?php _e('Message', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <textarea name="message" id="message" style="min-width: 400px;width: 100%;"><?php
                                        echo (isset($abandoned_cart_obj["message"]) && trim($abandoned_cart_obj["message"]) != '') ? $abandoned_cart_obj["message"] : '';
                                        ?></textarea>
                                    <p>
                                        <?php _e('Use %link% to choose the position of the link otherwise the link will be placed at the end','smart-marketing-addon-sms-order');?>
                                    </p>
                                </td>
                            </tr>


                            <tr>
                                <td><span><?php _e('Title Pop', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="text" id="title_pop" name="title_pop"
                                               value="<?php
                                               echo (isset($abandoned_cart_obj["title_pop"]) ) ? $abandoned_cart_obj["title_pop"] : ''; ?>"
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Text on button', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="text" id="button_name" name="button_name"
                                               value="<?php
                                               echo (isset($abandoned_cart_obj["button_name"]) ) ? $abandoned_cart_obj["button_name"] : ''; ?>"
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Enabled', 'smart-marketing-addon-sms-order');?></span></td>
                                <td>
                                    <div>
                                        <input type="checkbox" id="enable" name="enable"
                                            <?php
                                            echo (isset($abandoned_cart_obj["enable"]) && $abandoned_cart_obj["enable"] == "on") ? 'checked' : ''; ?>
                                        >
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td><span><?php _e('Shortener', 'egoi_sms_follow_price_enable_title');?></span></td>
                                <td>
                                    <div>
                                        <input type="checkbox" id="shortener" name="shortener"
                                            <?php
                                            echo (isset($abandoned_cart_obj["shortener"]) && $abandoned_cart_obj["shortener"] == "on") ? 'checked' : ''; ?>
                                        >
                                    </div>
                                </td>
                            </tr>



                            </tbody>
                        </table>
                    </div>
                    <div id="sms_order_abandoned_cart">
                        <?php submit_button(); ?>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>