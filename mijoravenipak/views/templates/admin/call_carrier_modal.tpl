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