<div id="vp-tracking-modal-wrapper">
    <div class="bootstrap modal fade" id="venipak-modal-tracking">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <a class="close" data-dismiss="modal" >&times;</a>
                    <h2 class="text-center">
                        <p align="center">
                            <img alt="Venipak" src="{$module_dir}/views/images/venipak-logo-name.png" border="0">
                        </p>
                        {l s='Venipak Shipment Tracking' mod='venipakshipping'}
                    </h2>
                </div>
                <div id='call-modal-errors' class="alert alert-danger">
                    <ul>
                    </ul>
                </div>
                <div class="modal-body">
                    <div style="margin: 10 0 0 10; font-size:12pt;">
                        {foreach $shipments as $number => $shipment}
                            {if !isset($lastOrder) || $lastOrder != $shipment.order_id}
                                <hr>
                                <h2 align="center" class="tracking-order-heading">
                                    {$shipment.heading}
                                </h2>
                            {/if}
                            {assign var='lastOrder' value={$shipment.order_id}}
                            <p align="center" class="tracking-status mb-0">Pack No.
                                <b>{$number}</b> status information:
                            </p>
                            <table border="1" align="center" cellspacing="0" cellpadding="1">
                                <thead>
                                <tr>
                                    <th>Package No.</th>
                                    <th>Shipment No.</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Terminal</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $shipment.data as $row}
                                    <tr>
                                        <td>{$row.pack_no}</td>
                                        <td>{$row.shipment_no}</td>
                                        <td>{$row.date}</td>
                                        <td>{$row.status} </td>
                                        <td>{$row.terminal} </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        {/foreach}
                    </div>
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
</div>