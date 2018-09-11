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

		if ($( "#form_info" ).length && form_id != 'form-sms-order-senders' && form_id != '') {
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
    }

    function enableSmsOrderText(language) {
        $("#sms_order_texts_"+language).show();
        $("#sms_order_texts_"+language+" :input").attr("disabled", false);
    }

})( jQuery );
