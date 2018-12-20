(function( $ ) {
	'use strict';

    $( document ).ready(function() {

    	var languages = ['en', 'es', 'pt', 'pt_BR'];
    	var methods = ['multibanco', 'payshop', 'boleto'];
		var form_id = $("#form_info").data('form-id');
		var form_lang = $("#form_info").data('form-lang');
        var form_method = $("#form_info").data('form-method');

		if ($( "#form_info" ).length && form_id != 'form-sms-order-senders' && form_id != 'form-sms-order-tests' && form_id != '') {
			var form_type = form_id.split("-");
			var element = 'tab-sms-'+form_type[3];
            if (typeof form_type[4] !== "undefined") {
                element = element+'-'+form_type[4];
            }

            activeConfigTab("#nav-"+element);
            showConfigWrap("#"+element);

			$("#sms_text_language").val(form_lang);
            $("#sms_payment_methode").val(form_method);
            disableAllSmsOrderTexts(languages, methods);
            enableSmsOrderText(form_lang, form_method);
		} else {
            showConfigWrap('#tab-sms-senders');
            disableAllSmsOrderTexts(languages, methods);
		}

        $(".nav-tab-addon").on("click", function () {
            activeConfigTab(this);

            var tab = $(".nav-tab-active").attr("id");
            var wrap = "#"+tab.substring(4);

            showConfigWrap(wrap);
        });

        $("#sms_text_language").on("change", function () {
            var lang = $(this).val();
            var method = $('#sms_payment_method').val();

            disableAllSmsOrderTexts(languages, methods);
            enableSmsOrderText(lang, method);
        });

        $("#sms_payment_method").on("change", function () {
            var method = $(this).val();
            var lang = $("#sms_text_language").val();

            disableAllSmsOrderTexts(languages, methods);
            enableSmsOrderText(lang, method);
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
        $("#form-sms-order-payment-texts textarea").focusout( function () {
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
            activeConfigTab("#nav-tab-sms-help");
            showConfigWrap("#tab-sms-help");
        });

    });



    function activeConfigTab(tag) {
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

    function disableAllSmsOrderTexts(languages, methods) {
        languages.forEach(function (lang) {
            $("#sms_order_texts_"+lang).hide();
            $("#sms_order_texts_"+lang+" :input").attr("disabled", true);
        });

        methods.forEach(function (method) {
            $("#sms_order_payment_texts_"+method).hide();
            $("#sms_order_payment_texts_"+method+" :input").attr("disabled", true);
        });

		$("#sms_texts_tags").hide();
        $("#sms_payment_texts_tags").hide();
    }

    function enableSmsOrderText(language, method) {
        if (language) {
            $("#sms_order_texts_" + language).show();
            $("#sms_order_texts_" + language + " :input").attr("disabled", false);
            $("#sms_texts_tags").show();
        }
        if (method) {
            $("#sms_order_payment_texts_" + method).show();
            $("#sms_order_payment_texts_" + method + " :input").attr("disabled", false);
            $("#sms_payment_texts_tags").show();
        }
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