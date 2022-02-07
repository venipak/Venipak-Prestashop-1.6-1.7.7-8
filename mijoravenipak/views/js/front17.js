$( document ).ready(function() {
  if (typeof(mjvp_country_code) != 'undefined' && mjvp_country_code != null) {
    mjvp_registerSelection('mjvp-selected-terminal');
  }
  if($('#mjvp-courier-extra-fields .alert-danger').length != 0 || $('.mjvp-pp-container .alert-danger').length != 0)
    $('#notifications .alert-danger').hide();

    $(".mjvp-pickup-filter").on('click', e => {
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
                            terminals.push(terminal);
                        }
                    });
                    // venipak_custom_modal.tmjs.terminals_cache = terminals;
                    if(terminals.length == 0)
                    {
                        venipak_custom_modal.tmjs.map._markerLayer.clearLayers();
                    }
                    else
                    {
                        venipak_custom_modal.tmjs.setTerminals(terminals);
                        venipak_custom_modal.tmjs.dom.renderTerminalList(venipak_custom_modal.tmjs.map.locations);
                    }
                }
            },
        });
    });
});

$(document).on("change", "input[name^='delivery_option[']", function() {
    if(typeof event.target.value !== "undefined" && typeof venipakCarrierID !== "undefined" && parseInt(event.target.value) == parseInt(venipakCarrierID))
        mjvp_registerSelection('mjvp-selected-terminal');
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

    var terminal = null;
    if(ajaxData.selected_terminal != 0)
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
        if (typeof jqXHR === 'object' && jqXHR !== null && 'msg' in jqXHR) {
            console.log(jqXHR.msg);
        } else {
            console.log(jqXHR);
        }
    });
}