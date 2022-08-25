<script>
    var venipakCarrierID = "{$venipakCarrierID}";
</script>
<div id="mjvp-courier-extra-fields" class="container-fluid mjvp-courier-extra-fields">
    {if isset($show_door_code) && $show_door_code}
        {if isset($notifications.error.mjvp_door_code)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.mjvp_door_code}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Door code' mod='mijoravenipak'} ({l s='optional' mod='mijoravenipak'})
            </label>
            <div class="col-xs-8">
                <input class="form-control" name="mjvp_door_code" type="text" {if isset($door_code) && $door_code}value="{$door_code}"{/if} maxlength="10">
            </div>
        </div>
    {/if}
    {if isset($show_cabinet_number) && $show_cabinet_number}
        {if isset($notifications.error.mjvp_cabinet_number)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.mjvp_cabinet_number}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Cabinet number' mod='mijoravenipak'} ({l s='optional' mod='mijoravenipak'})
            </label>
            <div class="col-xs-8">
                <input class="form-control" name="mjvp_cabinet_number" type="text" {if isset($cabinet_number) && $cabinet_number}value="{$cabinet_number}"{/if} maxlength="10">
            </div>
        </div>
    {/if}
    {if isset($show_warehouse_number) && $show_warehouse_number}
        {if isset($notifications.error.mjvp_warehouse_number)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.mjvp_warehouse_number}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Warehouse number' mod='mijoravenipak'} ({l s='optional' mod='mijoravenipak'})
            </label>
            <div class="col-xs-8">
                <input class="form-control" name="mjvp_warehouse_number" type="text" {if isset($warehouse_number) && $warehouse_number}value="{$warehouse_number}"{/if}  maxlength="10">
            </div>
        </div>
    {/if}
    {if isset($show_delivery_time) && $show_delivery_time && isset($delivery_times) && !empty($delivery_times)}
        {if isset($notifications.error.mjvp_delivery_time)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.mjvp_delivery_time}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Select a delivery time (optional)' mod='mijoravenipak'}
            </label>
            <div class="col-xs-8">
                <select name="mjvp_delivery_time" class="form-control form-control-select">
                    {foreach from=$delivery_times key=id item=time}
                        <option value="{$id}" {if isset($delivery_time) && $id == $delivery_time}selected{/if}>{$time}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    {/if}
    {if isset($show_carrier_call) && $show_carrier_call}
        {if isset($notifications.error.mjvp_call_carrier)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.mjvp_call_carrier}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                <input name="mjvp_carrier_call" type="checkbox" class="not_uniform" {if isset($carrier_call) && $carrier_call}checked{/if}>
                {l s='Carrier will call you before delivery' mod='mijoravenipak'}
            </label>
        </div>
    {/if}
</div>