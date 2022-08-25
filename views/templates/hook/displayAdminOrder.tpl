<script type="text/javascript">
    var pickup_reference = '{$pickup_reference}';
    var order_id = '{$order_id}';
    var terminal_warning = '{l s="Pickup point not selected" mod='venipakshipping'}';
</script>
<div class="row venipak">
    <div class="col-lg-6 col-md-6 col-xs-12 panel">

        <div class="panel-heading">
            <img src="{$module_dir}/views/images/venipak-logo-square.png" class="venipak-logo" alt="Venipak Logo">
            {l s='Venipak Shipping' mod='venipakshipping'}
        </div>

        {if $venipak_error}
            {$venipak_error}
        {/if}

        <form method="post" id="venipak_order_form">

            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <div class="field-row">
                        <span>{l s="Packets (total)" mod='venipakshipping'}:</span>
                        <span>
                          <select name="packs" id="venipak-packs" class="">
                            {for $amount=1 to 10}
                                <option value="{$amount}" {if isset($orderVenipakCartInfo.packages) && $orderVenipakCartInfo.packages==$amount} selected="selected" {/if}>{$amount}</option>
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

            {if isset($warehouses) && !empty($warehouses)}
                <div class="row">
                    <div class="col-xs-12">
                        <div class="field-row">
                            <span>{l s="Warehouse" mod='venipakshipping'}:</span>
                            <span>
                                <select name="warehouse" class="chosen">
                                    {foreach from=$warehouses key=reference item=warehouse}
                                        <option value="{$warehouse.id}" {if $order_warehouse == $warehouse.id} selected {/if}>{$warehouse.name}</option>
                                    {/foreach}
                                </select>
                            </span>
                        </div>
                    </div>
                </div>
            {/if}

            <div class="row">
                <div class="col-xs-12">
                    <div class="field-row">
                        <input name="mjvp_return_service" type="checkbox"
                               {if isset($return_service) && $return_service}checked{/if}>
                        {l s='Return service' mod='mijoravenipak'}
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
                                <select name="venipak_extra[delivery_time]" class="form-control form-control-select">
                                    {foreach from=$delivery_times key=id item=time}
                                        <option value="{$id}" {if isset($venipak_other_info.delivery_time) && $id == $venipak_other_info.delivery_time}selected{/if}>{$time}</option>
                                    {/foreach}
                                </select>
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <span>{l s="Door code" mod='venipakshipping'}:</span>
                            <span>
                            <input type="text" value="{if isset($venipak_other_info.door_code)}{$venipak_other_info.door_code}{/if}" name="venipak_extra[door_code]" >
                        </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <span>{l s="Cabinet number" mod='venipakshipping'}:</span>
                            <span>
                                <input type="text" value="{if isset($venipak_other_info.cabinet_number)}{$venipak_other_info.cabinet_number}{/if}" name="venipak_extra[cabinet_number]" >
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <span>{l s="Warehouse number" mod='venipakshipping'}:</span>
                            <span>
                                <input type="text" value="{if isset($venipak_other_info.warehouse_number)}{$venipak_other_info.warehouse_number}{/if}" name="venipak_extra[warehouse_number]" >
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <input name="mjvp_carrier_call" type="checkbox" {if isset($venipak_other_info.carrier_call) && $venipak_other_info.carrier_call}checked{/if}>
                            {l s='Select this option if you want carrier to call client before delivery' mod='mijoravenipak'}
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <input name="mjvp_return_doc" type="checkbox" {if isset($venipak_other_info.return_doc) && $venipak_other_info.return_doc}checked{/if}>
                            {l s='Select if You want a courier to return a signed document which leads a shipment' mod='mijoravenipak'}
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="response alert">
        </div>
        <div class="panel-footer venipak-footer">
      <span>
        <div class="venipak-extra-header">
            <div class="row">
                <div class="col-xs-12">
                    <span>{l s="Shipment labels" mod='venipakshipping'}:</span>
                </div>
            </div>
        </div>
          {foreach from=$shipment_labels item=label}
              <a href="{$venipak_print_label_url}&label_number={$label}" target="_blank" name="venipak_print_label" id="venipak_print_label_btn" class="btn btn-success">{$label}</a>
          {/foreach}
      </span>
      <span>
        <button type="submit" name="venipak_save_cart_info" id="venipak_save_cart_info_btn" class="btn btn-success"><i class="icon-save"></i> {l s="Save" mod='venipakshipping'}</button>
        <button type="submit" name="venipak_generate_label" id="venipak_generate_label_btn" class="btn btn-success"><i class="icon-tag"></i> {l s="Generate label" mod='venipakshipping'}</button>
      </span>
        </div>
    </div>
</div>