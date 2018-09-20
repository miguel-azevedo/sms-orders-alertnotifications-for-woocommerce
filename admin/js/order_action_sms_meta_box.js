(function( $ ) {
    'use strict';

    $( document ).ready(function() {
        $("#egoi_send_order_sms_button").on("click", function () {
            var data = {
                'action': 'order_action_sms_meta_box',
                'order_id': $("#egoi_send_order_sms_order_id").val(),
                'country': $("#egoi_send_order_sms_order_country").val(),
                'recipient': $("#egoi_send_order_sms_recipient").val(),
                'message': $("#egoi_send_order_sms_message").val()
            };
            $.post(ajax_object.ajax_url, data, function(response) {
                console.log(response);
                var note = jQuery.parseJSON(response);
                $(".order_notes").prepend(
                    "<li class='note system-note'>" +
                    "<div class='note_content'><p>" + note.message + "</p></div>" +
                    "<p class='meta'>" +
                    "<abbr class='exact-date'>" + note.date + "</abbr>" +
                    "</li>");
            });
        });
    });

})( jQuery );