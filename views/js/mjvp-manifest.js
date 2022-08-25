var venipak_manifest_id = 0;
var venipak_modal = false;
$(document).ready(function () {
    create_venipak_modal();
    $('#arrival-time-from').datetimepicker({
            dateFormat: 'yy-mm-dd'
    });
    $('#arrival-time-to').datetimepicker({
            dateFormat: 'yy-mm-dd'
    });

    venipak_modal.modal({ show: false });
    $(document).on('click', 'a[data-manifest]', function(e) {
        $('.warehouse-warning').hide();
        e.preventDefault();
        venipak_manifest_id = this.dataset.manifest || 0;
        if(this.dataset.warehouse == 0)
        {
            $('.warehouse-warning').show();
        }
        venipak_modal.modal('show');
    });
});

function create_venipak_modal() {

    var confirmModal = $('#venipak-modal');
    confirmModal.find('#confirm_modal_left_button').click(function () {
        cleanErrors();
        if (!venipak_manifest_id) {
            showErrorMessage(call_errors.manifest);
        }
        validateArrivalDate();
        if($('#call-modal-errors li').length == 0)
        {
            cleanErrors();
            sendCall();
        }
    });
    confirmModal.find('#confirm_modal_right_button').click(function () {
        cleanErrors();
        confirmModal.modal('hide');
    });

    venipak_modal = confirmModal;
}

function sendCall() {
    $.ajax({
        type: "POST",
        url: call_url + "&ajax=1",
        dataType: "json",
        data : {
            'id_manifest' : venipak_manifest_id,
            'call_comment' : $('#call_comment').val(),
            'arrival_date_from' : $('#arrival-time-from').val(),
            'arrival_date_to' : $('#arrival-time-to').val(),
        },
        success: function (res) {
            if (typeof res['error'] != 'undefined') {
                if(Array.isArray(res['error']))
                {
                    res['error'].forEach((error, i) => {
                        showErrorMessage(error);
                    });
                }
                else
                    showErrorMessage(res['error']);
                return false;
            }
            showSuccessMessage(res['success']);
            $('#venipak-modal').modal('hide');
            location.reload();
        },
        error: function (res) {
            showErrorMessage(call_errors.request);
        }
    });
}

function validateArrivalDate()
{
    const dateFromVal = $('#arrival-time-from').val();
    const dateToVal = $('#arrival-time-to').val();
    const dateNow = new Date();
    if(!dateFromVal || !dateToVal)
        showErrorMessage(call_errors.arrival_times);
    let dateFrom = new Date(dateFromVal);
    let dateTo = new Date(dateToVal);
    if(dateFrom - dateTo > 0)
    {
        showErrorMessage(call_errors.invalid_dates);
    }
    if(Math.abs((dateTo - dateFrom)) / (3600 * 1000)  < call_min_difference)
    {
        showErrorMessage(call_errors.date_diff);
    }
    if(dateFrom <= dateNow)
    {
        showErrorMessage(call_errors.past_date);
    }
    // To check if quarterly, convert to minutes and check if divisible by 15.
    if((dateFrom.valueOf() / (60 * 1000)) % 15 !== 0 || (dateTo.valueOf() / (60 * 1000)) % 15 !== 0)
    {
        showErrorMessage(call_errors.minutes_quarterly);
    }
}

function showErrorMessage(message)
{
    $('#call-modal-errors ul').append(`<li>${message}</li>`);
    $('#call-modal-errors').show();
}

function cleanErrors()
{
    $('#call-modal-errors li').remove();
    $('#call-modal-errors').hide();
}
