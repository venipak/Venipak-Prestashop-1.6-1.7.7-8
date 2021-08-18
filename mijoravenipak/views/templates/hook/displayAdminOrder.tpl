<script type="text/javascript">
    var venipak_label = '{$orderVenipakCartInfo["label_number"]}';
    var pickup_reference = '{$pcikup_reference}';
</script>
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
                            <input type="text" name="weight" {if isset($orderVenipakCartInfo.order_weight)} value="{$orderVenipakCartInfo.order_weight}" {else} value="1" {/if}/>
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
                            <input type="text" name="cod_amount" id="venipak-cod-amount" value="{if isset($orderVenipakCartInfo.cod_amount)}{$orderVenipakCartInfo.cod_amount}{/if}" disabled="disabled"/>
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
                <div class="row">
                    <div class="venipak-extra-header">
                        <div class="row">
                            <div class="col-xs-12">
                                <span>{l s="Extra carrier info" mod='venipakshipping'}:</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <span>{l s='Select a delivery time' mod='mijoravenipak'}:</span>
                            <span>
                                <select name="venipak_extra['delivery_time']" class="form-control form-control-select">
                                    {foreach from=$delivery_times key=id item=time}
                                        <option value="{$id}" {if isset($delivery_time) && $id == $delivery_time}selected{/if}>{$time}</option>
                                    {/foreach}
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <span>{l s="Door code" mod='venipakshipping'}:</span>
                            <span>
                            <input type="text" value="{if isset($venipak_other_info.door_code)}{$venipak_other_info.door_code}{/if}" name="venipak_extra['door_codes']" >
                        </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <span>{l s="Cabinet number" mod='venipakshipping'}:</span>
                            <span>
                                <input type="text" value="{if isset($venipak_other_info.cabinet_number)}{$venipak_other_info.cabinet_number}{/if}" name="venipak_extra['cabinet_number']" >
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <span>{l s="Warehouse number" mod='venipakshipping'}:</span>
                            <span>
                                <input type="text" value="{if isset($venipak_other_info.warehouse_number)}{$venipak_other_info.warehouse_number}{/if}" name="venipak_extra['warehouse_number']" >
                            </span>
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