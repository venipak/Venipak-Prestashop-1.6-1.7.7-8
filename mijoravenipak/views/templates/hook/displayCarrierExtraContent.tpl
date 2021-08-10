<div id="venipak-extra-fields" class="container-fluid">
    {if isset($show_door_code) && $show_door_code}
        {if isset($notifications.error.venipak_door_code)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.venipak_door_code}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Door code (optional)' mod='mijoravenipak'}
            </label>
            <div class="col-xs-8">
                <input class="form-control" name="venipak_door_code" type="text" {if isset($door_code) && $door_code}value="{$door_code}"{/if} maxlength="10">
            </div>
        </div>
    {/if}
    {if isset($show_cabinet_number) && $show_cabinet_number}
        {if isset($notifications.error.venipak_cabinet_number)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.venipak_cabinet_number}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Cabinet number (optional)' mod='mijoravenipak'}
            </label>
            <div class="col-xs-8">
                <input class="form-control" name="venipak_cabinet_number" type="text" {if isset($cabinet_number) && $cabinet_number}value="{$cabinet_number}"{/if} maxlength="10">
            </div>
        </div>
    {/if}
    {if isset($show_warehouse_number) && $show_warehouse_number}
        {if isset($notifications.error.venipak_warehouse_number)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.venipak_warehouse_number}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Warehouse number (optional)' mod='mijoravenipak'}
            </label>
            <div class="col-xs-8">
                <input class="form-control" name="venipak_warehouse_number" type="text" {if isset($warehouse_number) && $warehouse_number}value="{$warehouse_number}"{/if}  maxlength="10">
            </div>
        </div>
    {/if}
    {if isset($show_delivery_time) && $show_delivery_time}
        {if isset($notifications.error.venipak_delivery_time)}
            <div class="alert alert-danger" role="alert" data-alert="danger">
                {$notifications.error.venipak_delivery_time}
            </div>
        {/if}
        <div class="form-group row">
            <label class="col-xs-12 form-control-label">
                {l s='Select a delivery time (optional)' mod='mijoravenipak'}
            </label>
            <div class="col-xs-8">
                <select name="venipak_delivery_time" class="form-control form-control-select">
                    {foreach from=$delivery_times key=id item=time}
                        <option value="{$id}" {if isset($delivery_time) && $id == $delivery_time}selected{/if}>{$time}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    {/if}
</div>