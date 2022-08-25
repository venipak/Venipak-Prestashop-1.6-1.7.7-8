<div class="bootstrap modal fade" id="venipak-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <a class="close" data-dismiss="modal" >&times;</a>
                <h3>{l s="Call Venipak Courier" mod="mijoravenipak"}</h3>
            </div>
            <div id='call-modal-errors' class="alert alert-danger">
                <ul>
                </ul>
            </div>
            <div class="modal-body">
                {if !isset($warehouse) || !$warehouse}
                    <div class="alert alert-warning warehouse-warning">
                        {l s='This manifest is not assigned to any warehouse. Address from your Shop settings will be used, make sure you filled out all information.' mod="mijoravenipak"}
                    </div>
                {/if}
                <p id="warehouse_info"></p>
                <p>
                    {l s="Coment to courier (optional)" mod="mijoravenipak"}
                    <input id="call_comment" type="text" name="call_comment">
                </p>
                <div class="alert alert-warning">
                    <ul>
                        <li>{l s='Must be at least 2 hours difference between TIME FROM and TIME TO' mod="mijoravenipak"}</li>
                        <li>{l s='Minutes should be indicated quarterly: 15, 30, 45, 00' mod="mijoravenipak"}</li>
                    </ul>
                </div>
                <p>
                    {l s='Carrier arrival time (from)' mod="mijoravenipak"}
                    <input id="arrival-time-from" type="text" class="input-medium" name="arrival_time_from" value=""/>
                </p>
                <p>
                    {l s='Carrier arrival time (to)' mod="mijoravenipak"}
                    <input id="arrival-time-to" type="text" class="input-medium" name="arrival_time_to" value=""/>
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