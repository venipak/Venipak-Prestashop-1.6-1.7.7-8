$(document).ready(function () {
    var venipak_form = document.getElementById('venipak_order_form');
    var packs = document.getElementById('venipak-packs');
    var is_cod = document.getElementById('venipak-cod');
    var cod_amount = document.getElementById('venipak-cod-amount');
    var is_pickup = document.getElementById('venipak-carrier');
    var is_multi = document.getElementById('venipak-multi');
    var $pickup_points = $('.venipak select[name="id_pickup_point"]');
    var $extra_services = $('.venipak .extra-services-container');
    var $response = $('.venipak .response');
    var venipak_buttons = {
        print: document.getElementById('venipak_print_label_btn'),
        save: document.getElementById('venipak_save_cart_info_btn'),
        generate: document.getElementById('venipak_generate_label_btn')
    }

    toggleCodAmount();
    togglePickupPoints();
    // toggleMultiParcel();
    $response.hide();

    $(packs).on('change', function(e){
        // toggleMultiParcel();
    });
    $(is_cod).on('change', function(e){
        toggleCodAmount();
    });
    $(is_pickup).on('change', function(e){
        togglePickupPoints();
    });

    $(venipak_buttons.print).on('click', function(e) {
        if (typeof venipak_buttons.print.dataset.disabled != 'undefined' && venipak_buttons.print.dataset.disabled == 'true') {
            e.preventDefault();
        }
    });

    $(venipak_buttons.save).on('click', function(e){
        e.preventDefault();

        if (is_pickup.value == '1' && $pickup_points.val() == 0) {
            warning('{l s="Pickup point not selected" mod="venipakshipping"}');
            return false;
        }
        let form_data = new FormData(venipak_form);
        //disableButtons();
        saveVenipakCart(form_data);
    });

    $(venipak_buttons.generate).on('click', function(e){
        e.preventDefault();

        let form_data = new FormData(venipak_form);
        //disableButtons();
        generateVenipakLabel(form_data);
    });

    function toggleCodAmount() {
        cod_amount.disabled = is_cod.value == '0';
    }

    function togglePickupPoints() {
        if (is_pickup.value == pickup_reference) {
            $('.pickup-point-container').show();
            disableExtraInfo();
        } else {
            $('.pickup-point-container').hide();
            enableExtraInfo();
        }
    }

    function disableExtraInfo() {
        // reset packs to 1
        // packs.value = 1;
        // $(packs).trigger('change');
        // // disable COD
        // // is_cod.value = 0;
        // $(is_cod).trigger('change');

        // packs.disabled = true;
        // is_cod.disabled = true;
        // is_multi.disabled = true;

        $extra_services.hide();
    }

    function enableExtraInfo() {
        packs.disabled = false;
        is_cod.disabled = false;
        // is_multi.disabled = false;
        // toggleMultiParcel();
        $extra_services.show();
    }

    function toggleMultiParcel() {
        var $multi = $('#multi_parcel_chb');
        if (parseInt(packs.value) > 1) {
            $multi.show();
            // is_multi.disabled = false;
        } else {
            $multi.hide();
            is_multi.disabled = true;
        }
    }

    function saveVenipakCart(form_data) {
        form_data.set('ajax', 1);
        form_data.set('id_order', order_id);

        disableButtons();
        $.ajax({
            type: "POST",
            url: venipak_save_order_url,
            async: false,
            processData: false,
            contentType: false,
            cache: false,
            dataType: "json",
            data: form_data,
            success: function (res) {
                console.log(res);
                if (typeof res.errors != 'undefined') {
                    showResponse(res.errors, 'danger');
                } else {
                    showResponse([res.success], 'success');
                    window.location.href = location.href;
                }
            },
            complete: function (jqXHR, status) {
                enableButtons();
            }
        });
    }

    function generateVenipakLabel(form_data) {
        form_data.set('ajax', 1);
        form_data.set('id_order', order_id);

        disableButtons();
        $.ajax({
            type: "POST",
            url: venipak_generate_label_url,
            async: false,
            processData: false,
            contentType: false,
            cache: false,
            data: form_data,
            success: function (res) {
                console.log(res);
                res = JSON.parse(res);
                if (typeof res.errors != 'undefined') {
                    showResponse(res.errors, 'danger');
                    console.log(res);
                    return false;
                } else {
                    console.log(res);
                    showResponse(res.success, 'success');
                    location.reload();
                }
            },
            complete: function(jqXHR, status) {
                enableButtons();
            }
        });
    }

    function disableButtons() {
        venipak_buttons.save.disabled = true;
        venipak_buttons.generate.disabled = true;
        venipak_buttons.print.dataset.disabled = true;
    }

    function enableButtons() {
        venipak_buttons.save.disabled = false;
        venipak_buttons.generate.disabled = false;
    }

    window.venipak_disable = disableButtons;
    window.venipak_enable = enableButtons;

    function warning(text) {
        if (!!$.prototype.fancybox) {
            $.fancybox.open([
                    {
                        type: 'inline',
                        autoScale: true,
                        minHeight: 30,
                        content: '<p class="fancybox-error">' + text + '</p>'
                    }],
                {
                    padding: 0
                });
        } else {
            alert(text);
        }
    }

    function showResponse(msg, type) {
        //console.log(msgs, type);
        $response.removeClass('alert-danger alert-success');
        $response.addClass('alert-' + type);
        $response.html('');
        var ol = document.createElement('ol');
        //msgs.forEach(function (txt) {
        var li = document.createElement('li');
        li.innerText = msg;
        ol.appendChild(li);
        //});
        $response.append(ol);
        $response.show();
    }
});