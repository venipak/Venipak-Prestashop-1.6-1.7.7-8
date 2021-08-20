<div class="bootstrap modal fade" id="venipak_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <a class="close" data-dismiss="modal" >&times;</a>
                <h3>{l s="Call Venipak Courier" mod="mijoravenipak"}</h3>
            </div>
            <div class="modal-body">
                <p>
                    {l s="Please select warehouse to call courier to:" mod="mijoravenipak"}
                    <select id="id_venipak_warehouse" class="chosen">
                        {foreach from=$warehouses item=warehouse}
                            <option value="{$warehouse.id}" {if $warehouse.default_on}selected{/if}>{$warehouse.warehouse_name}</option>
                        {/foreach}
                    </select>
                </p>
                <p id="warehouse_info"></p>
                <p>
                    {l s="Coment to courier (optional)" mod="mijoravenipak"}
                    <input type="text" name="courier_comment">
                </p>
                <div class="alert alert-warning">
                    {l s='Must be at least 2 hours difference between TIME FROM and TIME TO' mod="mijoravenipak"}
                </div>
                <p>
                    {l s='Carrier arrival time (from)' mod="mijoravenipak"}
                    <input id="arrival-time-from" type="text" class="input-medium" name="arrival_time_from" value=""/>
{*                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>*}
                </p>
                <p>
                    {l s='Carrier arrival time (to)' mod="mijoravenipak"}
                    <input id="arrival-time-to" type="text" class="input-medium" name="arrival_time_to" value=""/>
{*                    <span class="input-group-addon"><i class="icon-calendar-empty"></i></span>*}
                </p>
            </div>
            <div class="modal-footer">
                <a href="#" id="confirm_modal_left_button" class="btn btn-success">
                    {l s="Send request" mod="mijoravenipak"}
                </a>
                <a href="#" id="confirm_modal_right_button" class="btn btn-danger">
                    {l s="Cancel" mod="mijoravenipak"}
                </a>
            </div>
        </div>
    </div>
</div>