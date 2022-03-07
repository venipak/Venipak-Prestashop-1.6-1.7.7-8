$( document ).ready(function() {
    if (typeof(mjvp_country_code) != 'undefined' && mjvp_country_code != null) {
        mjvp_registerSelection('mjvp-selected-terminal');
    }
    if($('#mjvp-courier-extra-fields .alert-danger').length != 0 || $('.mjvp-pp-container .alert-danger').length != 0)
        $('#notifications .alert-danger').hide();

    loadCarrierContent();

    // reloadCarrier content after quick order refresh
    if(typeof page_name != "undefined" && page_name == 'order-opc')
    {
        $( document ).ajaxComplete(function( event, xhr, settings ) {
            if ( typeof settings.data !== 'undefined' && settings.data.includes('updateCarrierAndGetPayments')) {
                loadCarrierContent();
            }
        });
    }
    else
    {
        $('[id^="delivery_option"]').on('click', () => {
            loadCarrierContent();
        });
    }

    $(document).on("change", "input[name^='delivery_option[']", function() {
        mjvp_registerSelection('mjvp-selected-terminal');
    });

    $(document).on("change", "#mjvp-terminal-select-field", function() {
        document.getElementById("mjvp-selected-terminal").value = this.value;
        mjvp_registerSelection('mjvp-selected-terminal');
    });
});

function loadCarrierContent() {
    var selectedCarrier = $('[id^="delivery_option"]:checked');
    if(selectedCarrier.parents('.delivery_option').find('.venipak-service-content').length != 0)
        return;
    $('.venipak-service-content').remove();
    var contentHolder = selectedCarrier.parents('.delivery_option').find('.delivery_option_logo + td');
    $.ajax({
        type: "POST",
        url: mjvp_carriers_controller_url,
        dataType: "json",
        data: {
            'id_carrier' : selectedCarrier.val()
        },
        success: function (res) {
            if(typeof res.carrier_content != 'undefined' && typeof res.mjvp_map_template != 'undefined' && res.carrier_content)
            {
                mjvp_map_template = res.mjvp_map_template;
                contentHolder.append(`<div class="venipak-service-content">${res.carrier_content}</div>`);
                venipak_custom_modal();
                filterEventListener();
                addExtraCarrierInfoEventListener();
                addTerminalValidateListener();
            }
        },
    });
}

function filterEventListener()
{
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
                // $('.tmjs-search-input').val('');
                // $('#terminal-search-radius').val('');
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
}

function addTerminalValidateListener()
{
    $('#HOOK_PAYMENT').on('click', () => {
        if($('#mjvp-selected-terminal').length != 0 && !$(event.target).hasClass('venipakcod'))
        {
            event.preventDefault();
            mjvp_registerSelection('mjvp-selected-terminal', {
                'update-data-opc' : 1
            }, {
                'href' : $(event.target).attr('href'),
                'scrollToError' : 1,
            });
        }
    });
}

function addExtraCarrierInfoEventListener()
{
    // Updated venipak carrier on TOS selection in quick order
    $('#mjvp-courier-extra-fields input, #mjvp-courier-extra-fields select').on('focusout', function(e){
        if($('#mjvp-selected-terminal').length == 0 && $('.venipak-service-content').length == 1)
            mjvp_registerSelection('mjvp-selected-terminal', {
                'mjvp_carrier_call' : $("[name='mjvp_carrier_call']").is(':checked'),
                'mjvp_door_code' : $("[name='mjvp_door_code']").val(),
                'mjvp_cabinet_number' : $("[name='mjvp_cabinet_number']").val(),
                'mjvp_warehouse_number' : $("[name='mjvp_warehouse_number']").val(),
                'mjvp_delivery_time' : $("[name='mjvp_delivery_time']").val(),
                'update-data-opc' : 1
            });
    });
}

function mjvp_registerSelection(selected_field_id, ajaxData = {}, params = {}) {
    ajaxData.carrier_id = $("input[name^='delivery_option[']:checked").val().split(',')[0];
    if(document.getElementById(selected_field_id))
        ajaxData.selected_terminal = document.getElementById(selected_field_id).value;
    if(document.getElementById("mjvp-pickup-country"))
        ajaxData.country_code = document.getElementById("mjvp-pickup-country").value;

    var terminal = null;
    mjvp_terminals.forEach((val, i) => {
        if(parseInt(val.id) == parseInt(ajaxData.selected_terminal)) {
            terminal = val;
        }
    });
    ajaxData.terminal = terminal;

    $('.alert.alert-danger').remove();
    $.ajax(
    {
      url: mjvp_front_controller_url,
      data: ajaxData,
      type: "POST",
      dataType: "json",
    })
    .always(function (jqXHR, status) {
        if(typeof jqXHR.errors != 'undefined')
        {
            $('[id^="delivery_option"]:checked').parents('.delivery_option ').prepend(jqXHR.errors);
            if(typeof params.scrollToError != "undefined")
            {
                $([document.documentElement, document.body]).animate({
                    scrollTop: $(".alert.alert-danger").offset().top - 100
                }, 800);
            }
        }
        else if(typeof params.href != "undefined")
        {
            document.location = params.href;
        }
      if (typeof jqXHR === 'object' && jqXHR !== null && 'msg' in jqXHR) {
        console.log(jqXHR.msg);
      } else {
        console.log(jqXHR);
      }
    });
}