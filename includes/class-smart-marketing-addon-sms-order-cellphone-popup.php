<?php

class Smart_Marketing_Addon_Sms_Order_Cellphone_Popup{

    const TRIGGERS = ['on_leave', 'on_click'];

    const DEFAULT_CONFIG = [
        'type' => 'abandoned_cart',
        'trigger' => 'on_leave'
    ];

    public function __construct($config = self::DEFAULT_CONFIG)
    {

    }


    //todo popup for contact check
    public function printPopup(){
        ?>


        <style>
            .egoi-public-modal-cellphone{
                position: absolute;
                z-index: 1000;
                top: 39%;
                left: 39%;
                background: black;
                padding: 3em;
                border: 2px solid #ffffff00;
                border-radius: 20px;
                filter: drop-shadow(9px 9px 11px grey);
            }
        </style>


        <div id="modal-cellphone" class="egoi-public-modal-cellphone" style="display:none">
            <form method="POST" action="#">
                <?php wp_nonce_field('egoi-public-modal-cellphone'); ?>
                <input type="hidden" name="action" value="saveCellphone" />
                <p> Please enter your mobile phone </p>
                <p>  + <input name="prefixMphone" placeholder="351" style="width: 35px;" /> <input name="mphone" placeholder="917789988" /> </p>
                <p> <input type="submit" value="OK" /> </p>
            </form>
        </div>

        <script type="text/javascript">
            (function( $ ) {


                $( document ).ready(function() {
                    function addEvent(obj, evt, fn) {
                        if (obj.addEventListener) {
                            obj.addEventListener(evt, fn, false);
                        } else if (obj.attachEvent) {
                            obj.attachEvent("on" + evt, fn);
                        }
                    }

                    addEvent(window, "load", function (e) {
                        addEvent(document, "mouseout", function (e) {
                            e = e ? e : window.event;
                            var from = e.relatedTarget || e.toElement;
                            if (!from || from.nodeName == "HTML") {
                                showPopUP();
                            }
                        });
                    });

                    function showPopUP() {
                        $('#modal-cellphone').show();
                    }
                });

            })(jQuery);
        </script>

        <?php
    }
}