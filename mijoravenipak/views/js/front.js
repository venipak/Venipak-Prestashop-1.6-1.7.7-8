$( document ).ready(function() {
  if (typeof(mjvp_country_code) != 'undefined' && mjvp_country_code != null) {
    mjvp_registerSelection('mjvp-selected-terminal');
  }
  if($('#mjvp-courier-extra-fields .alert-danger').length != 0)
    $('#notifications .alert-danger').hide();
});

$(document).on("change", "input[name^='delivery_option[']", function() {
    if (document.getElementById('mjvp-selected-terminal')) {
        mjvp_registerSelection('mjvp-selected-terminal');
    }
});

$(document).on("change", "#mjvp-terminal-select-field", function() {
    document.getElementById("mjvp-selected-terminal").value = this.value;
    mjvp_registerSelection('mjvp-selected-terminal');
});

function mjvp_registerSelection(selected_field_id) {
  var ajaxData = {};
  ajaxData.carrier_id = $("input[name^='delivery_option[']:checked").val().split(',')[0];
  ajaxData.selected_terminal = document.getElementById(selected_field_id).value;
  ajaxData.country_code = document.getElementById("mjvp-pickup-country").value;

  $.ajax(mjvp_front_controller_url,
    {
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