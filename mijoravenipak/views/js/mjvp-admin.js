$(document).ready(function () {

    // Configuration page
    if($('#MJVP_COURIER_DELIVERY_TIME_on').is(':checked'))
        $('.delivery-checkbox').removeClass('hide');
    $('#MJVP_COURIER_DELIVERY_TIME_on, #MJVP_COURIER_DELIVERY_TIME_off').on('change', () =>{
        if($('#MJVP_COURIER_DELIVERY_TIME_on').is(':checked'))
            $('.delivery-checkbox').removeClass('hide');
        else
            $('.delivery-checkbox').addClass('hide');
    });

    // Admin order JavaScript
    if ($('#venipak_order_form').length != 0)
    {
        var venipak_form = document.getElementById('venipak_order_form');
        var packs = document.getElementById('venipak-packs');
        var is_cod = document.getElementById('venipak-cod');
        var cod_amount = document.getElementById('venipak-cod-amount');
        var is_pickup = document.getElementById('venipak-carrier');
        var $pickup_points = $('.venipak select[name="id_pickup_point"]');
        var $extra_services = $('.venipak .extra-services-container');
        var $response = $('.venipak .response');
        var venipak_buttons = {
            print: document.getElementById('venipak_print_label_btn'),
            save: document.getElementById('venipak_save_cart_info_btn'),
            generate: document.getElementById('venipak_generate_label_btn')
        }

        cod_amount.disabled = is_cod.value == '0';
        togglePickupPoints();
        $response.hide();

        $(is_cod).on('change', function(e){
            cod_amount.disabled = is_cod.value == '0';
        });
        $(is_pickup).on('change', function(e){
            togglePickupPoints();
        });

        $(venipak_buttons.save).on('click', function(e){
            e.preventDefault();

            if (is_pickup.value == '1' && $pickup_points.val() == 0) {
                warning(terminal_warning);
                return false;
            }
            let form_data = new FormData(venipak_form);
            saveVenipakCart(form_data);
        });

        $(venipak_buttons.generate).on('click', function(e){
            e.preventDefault();

            let form_data = new FormData(venipak_form);
            generateVenipakLabel(form_data);
        });


        function togglePickupPoints() {
            if (is_pickup.value == pickup_reference) {
                $('.pickup-point-container').show();
                $extra_services.hide();
            } else {
                $('.pickup-point-container').hide();
                enableExtraInfo();
            }
        }

        function enableExtraInfo() {
            packs.disabled = false;
            is_cod.disabled = false;
            $extra_services.show();
        }


        function saveVenipakCart(form_data) {
            form_data.set('ajax', 1);
            form_data.set('id_order', order_id);
            $response.html('');
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
                        showResponse(res.success[0], 'success');
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
            $response.html('');
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
                        if(Array.isArray(res.errors))
                        {
                            res.errors.forEach((error) => {
                                showResponse(error, 'danger');
                            });
                        }
                        else
                        {
                            showResponse(res.errors, 'danger');
                        }
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
            $response.removeClass('alert-danger alert-success');
            $response.addClass('alert-' + type);

            if($response.find('ol').length == 0)
                $response.append('<ol></ol>');

            // Clean html tags
            if(Array.isArray(msg))
                msg = msg[0];
            msg = msg.replace(/<\/?[^>]+(>|$)/g, "");
            $response.find('ol').addClass('mb-0').append(`<li>${msg}</li>`);
            $response.show();
        }
    }
});