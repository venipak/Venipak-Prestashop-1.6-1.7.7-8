$( document ).ready(function() {
    if (typeof(mjvp_country_code) != 'undefined' && mjvp_country_code != null) {
        mjvp_registerSelection('mjvp-selected-terminal');
    }

    // Highlight carrier section when there are validation errors at the top
    mjvp_updateCarrierErrorState();
    var notificationsEl = document.getElementById('notifications');
    if (notificationsEl) {
        var mjvpObserver = new MutationObserver(function() {
            mjvp_updateCarrierErrorState();
        });
        mjvpObserver.observe(notificationsEl, { childList: true, subtree: true, attributes: true });
    }

    $(document).on('click', '.mjvp-pickup-filter', function(e) {
        venipak_custom_modal.tmjs.dom.addOverlay();
        const clickTarget = $(e.target);
        if(clickTarget.hasClass('reset'))
        {
            $("#filter-container input[type='checkbox']").each((i, el) => {
                $(el).prop('checked', true);
            });
        }

        var selectedFilters = {};
        var countChecked = 0;
        $("#filter-container input[type='checkbox']").each((i, el) => {
            if($(el).is(':checked'))
            {
                countChecked++;
                selectedFilters['type'] = $(el).data('filter');
            }
        });

        if(countChecked == 2)
            selectedFilters = {};
        else if(countChecked == 0)
            selectedFilters['type'] = 0;
        $('.mjvp-pickup-filter').removeClass('active');
        $.ajax({
            type: "POST",
            url: mjvp_front_controller_url + "?ajax=1&submitFilterTerminals=1&action=filter",
            dataType: "json",
            data: {
                'filter_keys' : selectedFilters
            },
            success: function (res) {
                venipak_custom_modal.tmjs.dom.removeOverlay();
                if(typeof res.mjvp_terminals != "undefined")
                {
                    var terminals = [];
                    mjvp_terminals = res.mjvp_terminals;
                    mjvp_terminals.forEach((terminal) => {
                        if(terminal.lat != 0 && terminal.lng != 0 && terminal.terminal)
                        {
                            terminal['coords'] = {
                                lat: terminal.lat,
                                lng: terminal.lng
                            };
                            terminal['identifier'] = 'venipak';
                            terminal['name'] = terminal['name'].split(', ').slice(1).join(', ');
                            terminals.push(terminal);
                        }
                    });
                    if(terminals.length == 0)
                    {
                        venipak_custom_modal.tmjs.map._markerLayer.clearLayers();
                        venipak_custom_modal.tmjs.dom.UI.terminalList.innerHTML = '';
                    }
                    else
                    {
                        venipak_custom_modal.tmjs.setTerminals(terminals);
                        var refMarker = venipak_custom_modal.tmjs.map._referenceMarker;
                        if (refMarker && refMarker._latlng) {
                            venipak_custom_modal.tmjs.dom.renderTerminalList(
                                venipak_custom_modal.tmjs.map.addDistance(refMarker._latlng), true
                            );
                        } else {
                            venipak_custom_modal.tmjs.dom.renderTerminalList(venipak_custom_modal.tmjs.map.locations);
                        }
                    }
                }
            },
        });
    });
});

$(document).on("change", "input[name^='delivery_option[']", function(e) {
    if (typeof e.target.value === "undefined") return;
    var selectedId = parseInt(e.target.value);
    var isCourier = (typeof venipakCarrierID !== "undefined" && selectedId === parseInt(venipakCarrierID))
        || (typeof mjvp_courier_carrier_id !== "undefined" && selectedId === parseInt(mjvp_courier_carrier_id));
    var isPickup = (typeof mjvp_pickup_carrier_id !== "undefined" && selectedId === parseInt(mjvp_pickup_carrier_id));
    if (isCourier || isPickup) {
        mjvp_registerSelection('mjvp-selected-terminal');
    }
});

$(document).on("change", "#mjvp-terminal-select-field", function() {
    $("#mjvp-selected-terminal").val(this.value);
    mjvp_registerSelection('mjvp-selected-terminal');
});

function mjvp_registerSelection(selected_field_id) {
    var ajaxData = {};
    ajaxData.carrier_id = $("input[name^='delivery_option[']:checked").val().split(',')[0];
    ajaxData.selected_terminal = $(`#${selected_field_id}`).length != 0 ? $(`#${selected_field_id}`).val() : 0;
    ajaxData.country_code = $("#mjvp-pickup-country").length != 0 ? $("#mjvp-pickup-country").val() : 0;

    if (ajaxData.selected_terminal != 0) {
        $('#notifications .alert-danger').hide();
        mjvp_clearCarrierErrorState();
    }

    var terminal = null;
    if(ajaxData.selected_terminal != 0 && typeof(mjvp_terminals) !== 'undefined' && mjvp_terminals.length != 0)
    {
        mjvp_terminals.forEach((val, i) => {
            if(parseInt(val.id) == parseInt(ajaxData.selected_terminal)) {
                terminal = val;
            }
        });
        ajaxData.terminal = terminal;
    }

    $.ajax(
    {
        url: mjvp_front_controller_url,
        data: ajaxData,
        type: "POST",
        dataType: "json",
    })
    .always(function (jqXHR, status) {
        /* onepagecheckoutps v5 - v4.2.3 - presteamshop */
        if (typeof OPC !== typeof undefined) {
            if (status === 'success' && $('#btn-placer_order').is(':disabled')) {
                prestashop.emit('opc-payment-getPaymentList');
            }
        }

        if (typeof jqXHR === 'object' && jqXHR !== null && 'msg' in jqXHR) {
            console.log(jqXHR.msg);
        } else {
            console.log(jqXHR);
        }
    });
}

function mjvp_updateCarrierErrorState() {
    var hasTopError = $('#notifications .alert-danger:visible').length > 0;
    var $ppContainer = $('.mjvp-pp-container');
    var $courierContainer = $('#mjvp-courier-extra-fields');

    if (!hasTopError) {
        mjvp_clearCarrierErrorState();
        return;
    }

    // Pickup: only mark if a terminal is not selected
    if ($ppContainer.length > 0) {
        var selected = $('#mjvp-selected-terminal').val();
        if (!selected || selected == 0) {
            mjvp_addErrorBlock($ppContainer);
        }
    }

    // Courier extra fields: mark if section is visible
    if ($courierContainer.length > 0 && $courierContainer.is(':visible')) {
        mjvp_addErrorBlock($courierContainer);
    }
}

function mjvp_addErrorBlock($container) {
    if ($container.hasClass('mjvp-has-error')) {
        return;
    }
    $container.addClass('mjvp-has-error');
}

function mjvp_clearCarrierErrorState() {
    $('.mjvp-pp-container, #mjvp-courier-extra-fields')
        .removeClass('mjvp-has-error')
        .find('.mjvp-carrier-error-message').remove();
}