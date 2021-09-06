<div id="vp-order-modal-wrapper">
    <div class="bootstrap modal fade" id="venipak-modal-tracking">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <a class="close" data-dismiss="modal" >&times;</a>
                    <h3>
                        <img src="{$module_dir}/views/images/venipak-main.svg" class="venipak-logo" alt="Smartpost Logo">
                        {l s='Venipak Shipping' mod='venipakshipping'}
                    </h3>
                </div>
                <div id='call-modal-errors' class="alert alert-danger">
                    <ul>
                    </ul>
                </div>
                <div class="modal-body">
                    {$tracking_output}
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
</div>