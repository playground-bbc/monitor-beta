"use strict";
const origin = location.origin;
const root = location.pathname.split("/")[1];
const appId = root != "web" ? `${root}/web` : "web";

let title = "Atención";
let text_inactive =
  "Será cambiado el estado de la alerta y no recabara mas informacion";
let text_active = "La alerta continuara recabando informacion";
let icon = "warning";
let confirmButtonText = "Si, deseo cambiar el estatus";

/**
 * [event status alert in index view]
 */

$(document).on("ready pjax:success", function () {
  $(".changeStatus").on("select2:select", function () {
    var $this = $(this);
    modal_status_change($this);
  });
});

$(".changeStatus").on("select2:select", function () {
  var $this = $(this);
  modal_status_change($this);
});

function modal_status_change($this) {
  var id = $($this).attr("id");
  var value = $($this).val();

  Swal.fire({
    title: title,
    text: value == 1 ? text_active : text_inactive,
    icon: icon,
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: confirmButtonText,
  }).then((result) => {
    if (result.value) {
      $.ajax({
        url: origin + `/${appId}/monitor/alert/change-status`,
        data: { id: id, value: value },
        type: "GET",
        dataType: "json",
      }).done(function (data) {
        if (data.situation != "success") {
          Swal.fire("Opss", "No se pudo realizar la operacion", "error");
        } else {
          Swal.fire("Status Cambiado", "La Alerta cambio su estado", "success");
          // $($this).val(value).trigger('change');
        }
      });
    } else {
      if (value == 1) {
        value = 0;
      } else {
        value = 1;
      }
      $($this).val(value).trigger("change");
    }
  });
}

// message sweealert delete button
let title_delete = "Usted desea eliminar esta Alerta?";
let text_delete =
  "Se procedera a <b>borar</b> los datos obtenidos por la alerta.";
/**
 * Override the default yii confirm dialog. This function is
 * called by yii when a confirmation is requested.
 *
 * @param string message the message to display
 * @param string ok callback triggered when confirmation is true
 * @param string cancelCallback callback triggered when cancelled
 */
yii.confirm = function (message, okCallback, cancelCallback) {
  Swal.fire({
    title: title_delete,
    html: text_delete,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, eliminar la Alerta!",
    cancelButtonText: "No, cancelar!",
  }).then((result) => {
    if (result.value) {
      Swal.fire("Eliminada!", "", "success");
      setTimeout(() => {
        okCallback();
      }, 3000);
    }
  });
};

var format = function (data) {
  var response = "";
  if (data.id == 0) response += '<i class="fa fa-clock-o mr5"></i>' + data.text;
  else if (data.id == 1)
    response += '<i class="fa fa-check mr5"></i>' + data.text;
  else response += '<i class="fa fa-times mr5"></i>' + data.text;

  return response;
};
