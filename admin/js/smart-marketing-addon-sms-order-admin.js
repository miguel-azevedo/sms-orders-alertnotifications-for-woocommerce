(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    $( document ).ready(function() {

    	var languages = ["en", "es", "pt", "pt_BR"];
		var form_id = $("#form_info").data('form-id');
		var form_lang = $("#form_info").data('form-lang');

		if ($( "#form_info" ).length && form_id != 'form-sms-order-senders' && form_id != 'form-sms-order-tests' && form_id != '') {
			var form_type = form_id.split("-");
            activeConfigTag("#nav-tab-sms-"+form_type[3]);
            showConfigWrap("#tab-sms-"+form_type[3]);

			$("#sms_text_language").val(form_lang);
            disableAllSmsOrderTexts(languages);
            enableSmsOrderText(form_lang);
		} else {
            $("#tab-sms-senders").show();
            disableAllSmsOrderTexts(languages);
		}

        $(".nav-tab-addon").on("click", function () {
            activeConfigTag(this);

            var tab = $(".nav-tab-active").attr("id");
            var wrap = "#"+tab.substring(4);
            showConfigWrap(wrap);
        });

        $("#sms_text_language").on("change", function () {
            var lang = $(this).val();

            disableAllSmsOrderTexts(languages);
            enableSmsOrderText(lang);
        });

        $("#button-test-sms").on("click", function () {
        	$("#test-sms").show();
		});

        var text_el = '';
        var position = 0;
        $(".sms_texts_tags_button").on("click", function () {
			var cod = $(this).data('text-cod');
			if (text_el !== '') {
				var text = text_el.val();
				var new_text = text.substring(0, position) + cod + text.substring(position);
				text_el.val(new_text).focus();
			}
        });
        $("#form-sms-order-texts textarea").focusout( function () {
			text_el = $(this);
            position = $(this).getCursorPosition();
        });

        var sender = $("#sender_hash");
        if (sender.val() == null) {
            $("#form-sms-order-senders :input").prop("disabled", true);
            sender.prop("disabled", false);
        }
        sender.on("change", function () {
            $("#form-sms-order-senders :input").prop("disabled", false);
            toggleAdminOrders();
        });

        if ($("#admin_phone").length) {
            toggleAdminOrders();
            $("#admin_phone").on("input", function () {
                toggleAdminOrders();
            });
        }

        $("#button_view_help").on("click", function () {
            activeConfigTag("#nav-tab-sms-help");
            showConfigWrap("#tab-sms-help");
        });

    });



    function activeConfigTag(tag) {
        $(".nav-tab-addon").each(function () {
            $(this).attr("class", "nav-tab nav-tab-addon");
        });
        $(tag).attr("class", "nav-tab nav-tab-addon nav-tab-active");
    }

    function showConfigWrap(wrap) {
        $(".wrap-addon").each(function () {
            $(this).hide();
        });
        $(wrap).show();
    }

    function disableAllSmsOrderTexts(languages) {
        languages.forEach(function (lang) {
            $("#sms_order_texts_"+lang).hide();
            $("#sms_order_texts_"+lang+" :input").attr("disabled", true);
        });
		$("#sms_texts_tags").hide();
    }

    function enableSmsOrderText(language) {
        $("#sms_order_texts_"+language).show();
        $("#sms_order_texts_"+language+" :input").attr("disabled", false);
        $("#sms_texts_tags").show();
    }

    function toggleAdminOrders() {
        var admin_phone = $("#admin_phone").val();
        if (admin_phone.length >= 6) {
            $(".admin-order-status").prop("disabled", false);
        } else {
            $(".admin-order-status").prop("disabled", true);
        }
    }


})( jQuery );

(function ($) {
    $.fn.getCursorPosition = function() {
        var el = $(this).get(0);
        var pos = 0;
        if('selectionStart' in el) {
            pos = el.selectionStart;
        } else if('selection' in document) {
            el.focus();
            var Sel = document.selection.createRange();
            var SelLength = document.selection.createRange().text.length;
            Sel.moveStart('character', -el.value.length);
            pos = Sel.text.length - SelLength;
        }
        return pos;
    }
})(jQuery);