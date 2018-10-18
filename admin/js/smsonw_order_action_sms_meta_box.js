(function( $ ) {
    'use strict';

    $( document ).ready(function() {
        $("#egoi_send_order_sms_button").on("click", function () {
            if ($("#egoi_send_order_sms_message").val() == '') {
                $("#egoi_send_order_sms_notice").hide();
                $("#egoi_send_order_sms_error").show();
                return false;
            }
            $("#egoi_send_order_sms_error").hide();
            $("#egoi_send_order_sms_notice").show();
            var data = {
                'action': 'smsonw_order_action_sms_meta_box',
                'order_id': $("#egoi_send_order_sms_order_id").val(),
                'country': $("#egoi_send_order_sms_order_country").val(),
                'recipient': $("#egoi_send_order_sms_recipient").val(),
                'message': $("#egoi_send_order_sms_message").val(),
                'security' : ajax_object.ajax_nonce
            };
            $.post(ajax_object.ajax_url, data, function(response) {
                var note = jQuery.parseJSON(response);
                if (note.message) {
                    $(".order_notes").prepend(
                        "<li class='note system-note'>" +
                        "<div class='note_content'><p>" + note.message + "</p></div>" +
                        "<p class='meta'>" +
                        "<abbr class='exact-date'>" + note.date + "</abbr>" +
                        "</li>");
                    $("#egoi_send_order_sms_notice").hide();
                } else if (note.errorCode) {
                    $("#egoi_send_order_sms_notice").hide();
                    $("#egoi_send_order_sms_error").show().text(note.errors[0]);
                }
            });
        });
    });

})( jQuery );