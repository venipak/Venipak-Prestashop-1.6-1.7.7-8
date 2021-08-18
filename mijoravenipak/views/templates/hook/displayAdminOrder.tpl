<div class="row venipak">
    <div class="col-lg-6 col-md-6 col-xs-12 panel">

        <div class="panel-heading">
            <img src="{$module_dir}/views/images/venipak.svg" class="venipak-logo" alt="Smartpost Logo">
            {l s='Venipak Shipping' mod='venipakshipping'}
        </div>

        {if $venipak_error}
            {$venipak_error}
        {/if}

        <form action="{$venipak_module_url}" method="post" id="venipak_order_form">

            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="field-row">
                        <span>{l s="Packets (total)" mod='venipakshipping'}:</span>
                        <span>
              <select name="packs" id="venipak-packs" class="">
                {for $amount=1 to 10}
                    <option value="{$amount}" {if isset($orderVenipakCartInfo.packs) && $orderVenipakCartInfo.packs==$amount} selected="selected" {/if}>{$amount}</option>
                {/for}
              </select>
            </span>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="field-row">
                        <span>{l s="Weight (kg)" mod='venipakshipping'}:</span>
                        <span>
              <input type="text"
                     name="weight" {if isset($orderVenipakCartInfo.order_weight)} value="{$orderVenipakCartInfo.order_weight}" {else} value="1" {/if}/>
            </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="field-row">
                        <span>{l s="C.O.D" mod='venipakshipping'}:</span>
                        <span>
              <select name="is_cod" id="venipak-cod">
                <option value="0">{l s='No' mod='venipakshipping'}</option>
                <option value="1" {if $orderVenipakCartInfo.is_cod} selected {/if}>{l s='Yes' mod='venipakshipping' }</option>
              </select>
            </span>
                    </div>
                </div>
                <div class="col-md-6 col-xs-12">
                    <div class="field-row">
                        <span>{l s="C.O.D. amount" mod='venipakshipping'}:</span>
                        <span>
              <input type="text" name="cod_amount" id="venipak-cod-amount"
                     value="{if isset($orderVenipakCartInfo.cod_amount)}{$orderVenipakCartInfo.cod_amount}{/if}" disabled="disabled"/>
            </span>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="field-row">
                        <span>{l s="Carrier" mod='venipakshipping'}:</span>
                        <span>
              <select name="is_pickup" id="venipak-carrier" class="chosen">
                  {foreach from=$venipak_carriers key=reference item=carrier}
                      <option value="{$reference}" {if $reference == $carrier_reference} selected {/if}>{$carrier}</option>
                  {/foreach}
              </select>
            </span>
                    </div>
                </div>
            </div>

            <div class="row pickup-point-container">
                <div class="col-xs-12">
                    <div class="field-row">
                        <span>{l s="Pickup point" mod='venipakshipping'}:</span>
                        <span>
              <select name="id_pickup_point" class="chosen">
                <option value="0">{l s='Select pickup point' mod='venipakshipping'}</option>
                {foreach from=$venipak_pickup_points item=pickup}
                    <option value="{$pickup->id}" {if $order_terminal_id == $pickup->id}selected="selected"{/if}>{$pickup->city}, {$pickup->address} ({$pickup->name})</option>
                {/foreach}
              </select>
            </span>
                    </div>
                </div>
            </div>

            <div class="row extra-services-container">
                <div class="col-xs-12">

                    <div class="venipak-extra-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <span>{l s="Extra carrier info" mod='venipakshipping'}:</span>
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-xs-12">
                            <label class="checkbox-inline">
                                {l s="Delivery time" mod='venipakshipping'}
                                    <div class="form-group row">
                                        <label class="col-xs-12 form-control-label">
                                            {l s='Select a delivery time' mod='mijoravenipak'} ({l s='optional' mod='mijoravenipak'})
                                        </label>
                                        <div class="col-xs-6">
                                            <select name="venipak_extra['delivery_time']" class="form-control form-control-select">
                                                {foreach from=$delivery_times key=id item=time}
                                                    <option value="{$id}" {if isset($delivery_time) && $id == $delivery_time}selected{/if}>{$time}</option>
                                                {/foreach}
                                            </select>
                                        </div>
                                    </div>
                            </label>
                            <label class="checkbox-inline">
                                {l s="Door code" mod='venipakshipping'}
                                <input type="text" value="{if isset($venipak_other_info.door_code)}{$venipak_other_info.door_code}{/if}" name="venipak_extra['door_codes']" >
                            </label>
                            <label class="checkbox-inline">
                                {l s="Cabinet number" mod='venipakshipping'}
                                <input type="text" value="{if isset($venipak_other_info.cabinet_number)}{$venipak_other_info.cabinet_number}{/if}" name="venipak_extra['cabinet_number']" >
                            </label>
                            <label class="checkbox-inline">
                                {l s="Warehouse number" mod='venipakshipping'}
                                <input type="text" value="{if isset($venipak_other_info.warehouse_number)}{$venipak_other_info.warehouse_number}{/if}" name="venipak_extra['warehouse_number']" >
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="response alert">
        </div>
        <div class="panel-footer venipak-footer">
      <span>
        <a href="{$venipak_print_label_url}" data-disabled="{if $orderVenipakCartInfo.label_number != ''}false{else}true{/if}" target="_blank" name="venipak_print_label" id="venipak_print_label_btn" class="btn btn-success"><i class="icon-file-pdf-o"></i> {l s="Print" mod='venipakshipping'}</a>
      </span>
            <span>
        <button type="submit" name="venipak_save_cart_info" id="venipak_save_cart_info_btn" class="btn btn-success"><i class="icon-save"></i> {l s="Save" mod='venipakshipping'}</button>
        <button type="submit" name="venipak_generate_label" id="venipak_generate_label_btn" class="btn btn-success"><i class="icon-tag"></i> {l s="Generate label" mod='venipakshipping'}</button>
      </span>
        </div>
    </div>
</div>

{literal}
    <style type="text/css">
        .venipak {
            margin: 0;
        }

        .venipak-logo {
            height: 50%;
            padding-right: 5px;
        }

        .venipak .panel-footer {
            height: auto!important;
            display: flex;
            justify-content: space-between;
        }

        .venipak .panel-footer .btn {
            text-transform: uppercase;
            font-weight: 900;
        }

        .venipak-extra-header {
            padding-top: 1em;
            padding-bottom: 0.5em;
            font-size: 1.1em;
            font-weight: 900;
        }

        #venipak_print_label_btn,
        #venipak_save_cart_info_btn,
        #venipak_generate_label_btn {
            border: none;
            margin-bottom: 1em;
        }

        #venipak_print_label_btn {
            background-color: #0b7489;
        }

        #venipak_print_label_btn[data-disabled="true"] {
            -webkit-box-shadow: none;
            box-shadow: none;
            cursor: not-allowed;
            filter: alpha(opacity=65);
            opacity: .65;
            pointer-events: none;
        }

        #venipak_save_cart_info_btn {
            background-color: #829191;
            margin-right: 1em;
        }

        #venipak_generate_label_btn {
            background-color: #6eb257;
        }

        .venipak .panel-footer .btn [class^=icon-] {
            padding-right: 5px;
        }

        .venipak .field-row {
            margin-bottom: 1em;
        }

        .venipak .response {
            margin-top: 1em;
        }
        #venipak_order_form .extra-services-container .checkbox-inline {
            margin-left: 0;
        }
    </style>
{/literal}


<script type="text/javascript">
    $(document).ready(function () {
        var venipak_label = '{$orderVenipakCartInfo["label_number"]}';
        var venipak_form = document.getElementById('venipak_order_form');
        var packs = document.getElementById('venipak-packs');
        var is_cod = document.getElementById('venipak-cod');
        var cod_amount = document.getElementById('venipak-cod-amount');
        var is_pickup = document.getElementById('venipak-carrier');
        var is_multi = document.getElementById('venipak-multi');
        var $pickup_points = $('.venipak select[name="id_pickup_point"]');
        var $extra_services = $('.venipak .extra-services-container');
        var $response = $('.venipak .response');
        var pickup_reference = '{$pcikup_reference}';
        var venipak_buttons = {
            print: document.getElementById('venipak_print_label_btn'),
            save: document.getElementById('venipak_save_cart_info_btn'),
            generate: document.getElementById('venipak_generate_label_btn')
        }
        console.log('label',venipak_label);
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
            packs.value = 1;
            $(packs).trigger('change');
            // disable COD
            is_cod.value = 0;
            $(is_cod).trigger('change');

            packs.disabled = true;
            is_cod.disabled = true;
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
            form_data.set('id_order', '{$order_id}');

            disableButtons();
            $.ajax({
                type: "POST",
                url: "{$venipak_save_order_url}",
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
            form_data.set('id_order', '{$order_id}');

            disableButtons();
            $.ajax({
                type: "POST",
                url: "{$venipak_generate_label_url}",
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
                        venipak_label = true;
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
            if (venipak_label) {
                venipak_buttons.print.dataset.disabled = false;
            }
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
</script>