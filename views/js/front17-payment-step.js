/**
 * Loaded only on PS 1.7+ checkout payment step.
 * Venipak carrier extra content (pickup point selection, courier fields) is not rendered on payment step,
 * but PrestaShop opens delivery step ("Edit") without page reload, so that content is missing there.
 * When delivery step is opened with Venipak carrier selected, the same carrier is selected again.
 * PrestaShop then marks delivery step as not completed and reloads checkout page (because payment step is shown),
 * so delivery step is rendered again together with Venipak carrier extra content.
 */
(function() {
    var mjvpReloading = false;

    function mjvp_getSelectedCarrierId($step) {
        var $checked = $step.find("input[name^='delivery_option[']:checked");
        if (!$checked.length || typeof $checked.val() === 'undefined') {
            return null;
        }
        return parseInt(String($checked.val()).split(',')[0]);
    }

    function mjvp_reloadDeliveryStep() {
        if (mjvpReloading || typeof mjvp_payment_step_carrier_ids === 'undefined') {
            return;
        }

        var $step = $('#checkout-delivery-step');
        if (!$step.length || !$step.hasClass('-current')) {
            return;
        }

        // Content already exists, nothing to reload
        if ($step.find('.mjvp-pp-container, #mjvp-courier-extra-fields').length) {
            return;
        }

        var carrierIds = $.map(mjvp_payment_step_carrier_ids, function(id) {
            return parseInt(id);
        });
        var selectedId = mjvp_getSelectedCarrierId($step);
        if (selectedId === null || carrierIds.indexOf(selectedId) === -1) {
            return;
        }

        console.log('[MJVP]', 'Reloading delivery step to show Venipak carrier content');
        mjvpReloading = true;
        $step.find('.content').css({ opacity: 0.5, pointerEvents: 'none' });
        $step.find("input[name^='delivery_option[']:checked").trigger('change');
    }

    function mjvp_cancelReload() {
        if (!mjvpReloading) {
            return;
        }
        mjvpReloading = false;
        $('#checkout-delivery-step .content').css({ opacity: '', pointerEvents: '' });
    }

    $(document).ready(function() {
        if (typeof prestashop === 'undefined' || typeof prestashop.on !== 'function') {
            return;
        }
        prestashop.on('changedCheckoutStep', mjvp_reloadDeliveryStep);
        prestashop.on('handleError', function(data) {
            if (data && data.eventType === 'updateDeliveryOptions') {
                mjvp_cancelReload();
            }
        });
    });
})();
