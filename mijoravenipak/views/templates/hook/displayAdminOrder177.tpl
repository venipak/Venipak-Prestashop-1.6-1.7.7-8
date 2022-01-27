<script type="text/javascript">
    var pickup_reference = '{$pickup_reference}';
    var order_id = '{$order_id}';
    var terminal_warning = '{l s="Pickup point not selected" mod='venipakshipping'}';
</script>
<div class="col-md-6 left-column venipak">
    <div id="venipak-order-card" class="card">

        <div class="card-header">
            <img src="{$module_dir}/views/images/venipak-logo-square.png" class="venipak-logo" alt="Veniapk Logo">
            {l s='Venipak Shipping' mod='venipakshipping'}
        </div>

        <div class="card-body">

            {if $venipak_error}
                {$venipak_error}
            {/if}

            <form method="post" id="venipak_order_form">

                <div class="row mt-3">
                    <div class="col-md-6 col-xs-12">
                        <label class="form-control-label"><span>{l s="Packets (total)" mod='venipakshipping'}:</span></label>
                        <div class="field-row">
                            <select name="packs" id="venipak-packs" class="form-control form-control-select">
                                {for $amount=1 to 10}
                                    <option value="{$amount}" {if isset($orderVenipakCartInfo.packages) && $orderVenipakCartInfo.packages==$amount} selected="selected" {/if}>{$amount}</option>
                                {/for}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <label class="form-control-label"><span>{l s="Weight (kg)" mod='venipakshipping'}:</span>
                        </label>
                        <div class="field-row">
                            <input type="text" name="weight"
                                   class="form-control" {if isset($orderVenipakCartInfo.order_weight)} value="{$orderVenipakCartInfo.order_weight}" {else} value="1" {/if}/>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6 col-xs-12">
                        <label class="form-control-label"><span>{l s="C.O.D" mod='venipakshipping'}:</span>
                        </label>
                        <div class="field-row">
                            <select name="is_cod" id="venipak-cod" class="form-control form-control-select">
                                <option value="0">{l s='No' mod='venipakshipping'}</option>
                                <option value="1" {if $orderVenipakCartInfo.is_cod} selected {/if}>{l s='Yes' mod='venipakshipping' }</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 col-xs-12">
                        <label class="form-control-label"><span>{l s="C.O.D. amount" mod='venipakshipping'}:</span>
                        </label>
                        <div class="field-row">
                            <input type="text" name="cod_amount" id="venipak-cod-amount" class="form-control"
                                   value="{if isset($orderVenipakCartInfo.cod_amount)}{$orderVenipakCartInfo.cod_amount}{/if}"
                                   disabled="disabled"/>
                        </div>
                    </div>
                </div>

                <div class="row mt-3 mb-3">
                    <div class="col-xs-12 col-md-6">
                        <label class="form-control-label"><span>{l s="Carrier" mod='venipakshipping'}:</span>
                        </label>
                        <div class="field-row">
                            <select name="is_pickup" id="venipak-carrier" class="form-control form-control-select">
                                {foreach from=$venipak_carriers key=reference item=carrier}
                                    <option value="{$reference}" {if $reference == $carrier_reference} selected {/if}>{$carrier}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-12 col-md-6">
                        <div class="pickup-point-container">
                            <label class="form-control-label"><span>{l s="Pickup point" mod='venipakshipping'}:</span>
                            </label>
                            <div class="input-group card-details-actions">
                                <select name="id_pickup_point" class="custom-select">
                                    <option value="0">{l s='Select pickup point' mod='venipakshipping'}</option>
                                    {foreach from=$venipak_pickup_points item=pickup}
                                        <option value="{$pickup->id}"
                                                {if $order_terminal_id == $pickup->id}selected="selected"{/if}>{$pickup->city}
                                            , {$pickup->address} ({$pickup->name})
                                        </option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {if isset($warehouses) && !empty($warehouses)}
                    <div class="row mt-3 mb-3">
                        <div class="col-xs-12 col-md-6">
                            <label class="form-control-label"><span>{l s="Warehouse" mod='venipakshipping'}:</span>
                            </label>
                            <div class="field-row">
                                <select name="warehouse" class="custom-select">
                                    {foreach from=$warehouses key=reference item=warehouse}
                                        <option value="{$warehouse.id}" {if $order_warehouse == $warehouse.id} selected {/if}>{$warehouse.name}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="row mt-3 mb-3">
                    <div class="col-md-6 col-xs-12">
                        <div class="field-row">
                            <input name="mjvp_return_service" type="checkbox"
                                   {if isset($return_service) && $return_service}checked{/if}>
                            {l s='Return service' mod='mijoravenipak'}
                        </div>
                    </div>
                </div>

                <div class="extra-services-container">
                    <div class="col-xs-12 card-block card-header">
                        <span>{l s="Extra carrier info" mod='venipakshipping'}:</span>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6 col-xs-12">
                            <label class="form-control-label"><span>{l s='Select a delivery time' mod='mijoravenipak'}:</span>
                            </label>
                            <div class="field-row">
                                <select name="venipak_extra[delivery_time]" class="form-control form-control-select">
                                    {foreach from=$delivery_times key=id item=time}
                                        <option value="{$id}"
                                                {if isset($venipak_other_info.delivery_time) && $id == $venipak_other_info.delivery_time}selected{/if}>{$time}</option>
                                    {/foreach}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <label class="form-control-label"><span>{l s="Door code" mod='venipakshipping'}:</span>
                            </label>
                            <div class="field-row">
                                <input type="text"
                                       value="{if isset($venipak_other_info.door_code)}{$venipak_other_info.door_code}{/if}"
                                       name="venipak_extra[door_code]" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6 col-xs-12">
                            <label class="form-control-label"><span>{l s="Cabinet number" mod='venipakshipping'}:</span>
                            </label>
                            <div class="field-row">
                                <input type="text"
                                       value="{if isset($venipak_other_info.cabinet_number)}{$venipak_other_info.cabinet_number}{/if}"
                                       name="venipak_extra[cabinet_number]" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <label class="form-control-label"><span>{l s="Warehouse number" mod='venipakshipping'}:</span>
                            </label>
                            <div class="field-row">
                                <input type="text"
                                       value="{if isset($venipak_other_info.warehouse_number)}{$venipak_other_info.warehouse_number}{/if}"
                                       name="venipak_extra[warehouse_number]" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6 col-xs-12">
                            <div class="field-row">
                                <input name="mjvp_carrier_call" type="checkbox"
                                       {if isset($venipak_other_info.carrier_call) && $venipak_other_info.carrier_call}checked{/if}>
                                {l s='Select this option if you want carrier to call client before delivery' mod='mijoravenipak'}
                            </div>
                        </div>
                        <div class="col-md-6 col-xs-12">
                            <div class="field-row">
                                <input name="mjvp_return_doc" type="checkbox"
                                       {if isset($venipak_other_info.return_doc) && $venipak_other_info.return_doc}checked{/if}>
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
                    <button type="submit" name="venipak_save_cart_info" id="venipak_save_cart_info_btn" class="btn btn-success">
                        <i class="icon-save"></i> {l s="Save" mod='venipakshipping'}
                    </button>
                    <button type="submit" name="venipak_generate_label" id="venipak_generate_label_btn" class="btn btn-success">
                        <i class="icon-tag"></i> {l s="Generate label" mod='venipakshipping'}
                    </button>
                </span>
            </div>
            <div class="col-xs-12">
                    <div class="col-xs-12 card-block card-footer mb-3">
                        <span>{l s="Shipment labels" mod='venipakshipping'}:</span>
                    </div>
                    {foreach from=$shipment_labels item=label}
                          <a href="{$venipak_print_label_url}&label_number={$label}" target="_blank" name="venipak_print_label"
                             id="venipak_print_label_btn" class="btn btn-success">{$label}</a>
                    {/foreach}
            </div>
        </div>
    </div>
</div>
