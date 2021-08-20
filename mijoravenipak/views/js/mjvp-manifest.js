var venipak_manifest_id = 0;
var venipak_modal = false;
$(document).ready(function () {
    create_venipak_modal();
    venipak_modal.modal({ show: false });
    venipak_modal.find('#id_venipak_warehouse').on('change', function(e){
        warehouse = findWarehouseInfo(this.value);
        if (!warehouse) {
            return false;
        }
        venipak_modal.find('#warehouse_info').html(
            '<p>'+warehouse.contact+'</p>' +
            '<p>'+warehouse.city+'</p>' +
            '<p>'+warehouse.zip_code+' '+warehouse.city+', '+warehouse.country_code+'</p>' +
            '<p>'+warehouse.phone+'</p>'
        );
    });
    venipak_modal.find('#id_venipak_warehouse').trigger('change');
    $(document).on('click', 'a[data-manifest]', function(e) {
        e.preventDefault();
        venipak_manifest_id = this.dataset.manifest || 0;
        venipak_modal.modal('show');
    });
});

function create_venipak_modal() {

    var confirmModal = $('#venipak_modal');
    confirmModal.find('#confirm_modal_left_button').click(function () {
        sendCall($('#id_venipak_warehouse').val(), venipak_manifest_id);
        confirmModal.modal('hide');
    });
    confirmModal.find('#confirm_modal_right_button').click(function () {
        confirmModal.modal('hide');
    });

    venipak_modal = confirmModal;
}

function findWarehouseInfo(id_warehouse) {
    for(var i=0; i < warehouses.length; i++) {
        if (warehouses[i].id == id_warehouse) {
            return warehouses[i];
        }
    }
    return false;
}

function sendCall(address_id, manifest_id) {
    if (!address_id) {
        showErrorMessage('{l s="No warehouse selected" mod="mijoravenipak"}');
    }
    if (!manifest_id) {
        showErrorMessage('{l s="No manifest selected" mod="mijoravenipak"}');
    }

    $.ajax({
        type: "POST",
        url: "{$call_url}&ajax=1&id_venipak_warehouse=" + address_id + "&id_manifest=" + manifest_id,
        async: false,
        processData: false,
        contentType: false,
        cache: false,
        dataType: "json",
        success: function (res) {
            if (typeof res['error'] != 'undefined') {
                showErrorMessage(res['error']);
                return false;
            }
            showSuccessMessage(res['success']);
        },
        error: function (res) {
            showErrorMessage('{l s="Failed to request Call courier" mod="mijoravenipak"}');
        }
    });
}