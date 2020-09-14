//var element = $("#productsIds");
const origin = location.origin;
const root = location.pathname.split("/")[1];
const appId = root != "web" ? `${root}/web` : "web";
const baseUrl = `${origin}/${appId}/web/monitor/alert/`;

let message_error_no_dates = "Debe de escojer fecha de Inicio y fecha Final";
let message_more_than_one_month =
  "Consultar las paginas web tiene que ser un rango menor de <b>1 mes</b>";
console.log(origin + `/${appId}/web/monitor/alert/update-history-search`);

$("#urls").on("select2:unselecting", function (e) {
  var urls = $(this).val();
  if (urls.length == 1) {
    remove_value_selector("social_resourcesId", "4");
  }
});

/**
 * [event when selecting select2 of urls: adding web page to select2 resourceID]
 */
$("#urls").on("select2:select", function (e) {
  var selectId = "social_resourcesId";
  var data = {
    id: "4",
    text: "Paginas Webs",
  };
  console.log(data);
  add_options_select(selectId, data);
});
/**
 * [event when unselecting select2 of resourceId: if resource is web page clean the select web urls]
 */
$("#social_resourcesId").on("select2:unselecting", function (e) {
  var resource = e.params.args.data;
  console.log(resource);
  if (resource.text == "Paginas Webs") {
    var urls = $("#urls");
    urls.val(null).trigger("change"); // Select the option with a value of '1'
  }
});

/**
 * [add_options_select adding data to select2]
 * @param  {[type]} string [id to select2]
 * @param  {[type]} obj [data to select2]
 */
function add_options_select(selectId, data) {
  var social = $("#" + selectId);
  var current_values = social.val();

  // Set the value, creating a new option if necessary
  if (social.find("option[value=" + data.id + "]").length) {
    current_values.push(data.id);
    social.val(current_values).trigger("change");
  } else {
    // Create a DOM Option and pre-select by default
    var newOption = new Option(data.text, data.id, true, true);
    // Append it to the select
    social.append(newOption).trigger("change");
  }
}

const reloadButton = Vue.component("sync-product", {
  template: "#sync-product-id",
  data() {
    return {
      msg: "Reload",
    };
  },
  methods: {
    reload() {
      this.msg = "Loading";
      fetch(`${baseUrl}reload-products`)
        .then((response) => {
          return response.json();
        })
        .then((data) => {
          // Work with JSON data here
          if (data.status) {
            $("#productsIds").trigger("change");
            location.reload();
            this.msg = "Reload";
          }
        })
        .catch((err) => {
          // Do something for an error here
        });
    },
  },
});

// vue here
var vm = new Vue({
  el: "#views-alert",
  data: {
    loaded: true,
  },
  mounted() {},
  components: {
    reloadButton,
  },
});

/**
 * [modalReosurces method that validates according to the time and the social network chosen the type of message to show the user]
 * @param  {[type]} event [event calendar]
 */
function modalReosurces(resourceName) {
  var format = "DD/MM/YYYY";

  var start_date = $("#start_date")[0].value;
  var end_date = $("#end_date")[0].value;
  var social = $("#social_resourcesId").select2("data");

  var resource = resourceName;

  switch (resource) {
    case "Web page":
      const days_web = 29;
      var start_date = $("#start_date")[0].value;
      var end_date = $("#end_date")[0].value;

      if (start_date.length && end_date.length) {
        var days_ago = check_if_it_exceeds_the_limit(
          start_date,
          days_web,
          "days"
        );
        if (days_ago) {
          swal_modal_info(resource, days_web, days_ago);
        }
      } else {
        swal_modal("error", "Opps", message_error_no_dates);
        clean_select2(social, resource);
      }
      break;

    case "Twitter":
      const days_twitter = 7;
      if (start_date.length && end_date.length) {
        var days_ago = check_if_it_exceeds_the_limit(
          start_date,
          days_twitter,
          "days"
        );
        if (days_ago) {
          swal_modal_info(resource, days_twitter, days_ago);
        }
      } else {
        swal_modal("error", "Opps", message_error_no_dates);
        clean_select2(social, resource);
      }

      break;
  }
}

/**
 * [check_if_it_exceeds_the_limit if the date exceeds the limit returns the day in which it is within the range of the limit]
 * @param  {[type]} start_date [start date alert]
 * @param  {[type]} limit      [number of day or month]
 * @param  {[type]} period     [ej: 'days' or 'month']
 * @return {[type]}            [the optimal day to start the alert]
 */
function check_if_it_exceeds_the_limit(start_date, limit, period) {
  var now = moment();
  var format = "DD/MM/YYYY";
  var days_ago = null;

  var afterTime = moment(start_date, format);
  var diff_start_date = now.diff(afterTime, period);

  if (diff_start_date > limit) {
    days_ago = moment().subtract(limit, period).format(format);
  }

  return days_ago;
}

/**
 * [swal_modal_error fire up a simple swal modal]
 * @param  {[type]} icon    [succes,error,warning]
 * @param  {[type]} title   [title to content]
 * @param  {[type]} message [message to content]
 */
function swal_modal(icon, title, message) {
  Swal.fire({
    icon: icon,
    title: title,
    html: message,
  });
}

/**
 * [swal_modal_info informs the user of the days on which he should initiate an alert if a date is exceeded]
 * @param  {[type]} resource [resource name]
 * @param  {[type]} days     [total days]
 * @param  {[type]} days_ago [days ago]
 * @return {[type]}          [description]
 */
function swal_modal_info(resource, days, days_ago) {
  Swal.fire({
    icon: "warning",
    title: "Oops...",
    html: `<b>${resource}</b> realiza una búsqueda en una muestra de registros recientes publicados en los últimos ${days} días.<hr> La alerta comenzara a recabar data a partir ${days_ago} para ${resource}`,
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "Si, Deseo cambiar la fecha!",
    cancelButtonText: `Quitar ${resource} de los recursos!`,
  }).then((result) => {
    if (result.value) {
      //if yes
      $("#start_date").kvDatepicker.defaults.format = "dd/mm/yyyy";
      $("#start_date").kvDatepicker("update", days_ago);
      //$('#end_date').kvDatepicker('clearDates');
    } else {
      var social = $("#social_resourcesId").select2("data");
      //var social = $('#social_resourcesId');
      clean_select2(social, resource);
    }
  });
}

/**
 * [swal_modal_error show error modal indicating to user not range date for twitter]
 * @param  {[type]} days_ago [days ago for call twitter api]
 */
function swal_modal_error(days_ago) {
  Swal.fire({
    icon: "error",
    title: "Opps",
    html:
      "<b>Twitter API</b> no estara disponible para este rango de fechas <hr> realiza una búsqueda a partir de: <b>" +
      days_ago +
      "</b>",
  });
}

/**
 * [validator_date change the end date based on the start date ]
 * @param  {[type]} event
 */
async function validator_date(event) {
  var start_date = $("#start_date").val().split("/").reverse().join("-");
  var end_date = $("#end_date").val().split("/").reverse().join("-");

  // check if have resource clicked
  var resources = $("#social_resourcesId").select2("data");
  for (var i = 0; i < resources.length; i++) {
    var resourceName = resources[i].text;
    modalReosurces(resourceName);
    await new Promise((r) => setTimeout(r, 4000));
  }

  if (end_date != "") {
    if (moment(start_date).isAfter(end_date)) {
      Swal.fire({
        icon: "error",
        title: "Opps",
        html: "Fecha Final no puede ser menor que Fecha de Inicio",
      });
      var date = $("#start_date").val();
      $("#end_date").kvDatepicker("update", date);
    }
  }
  $("#end_date").kvDatepicker("setStartDate", event.date);
}

/**
 * [clean_select2 clean select2 select option]
 * @param  {[type]} social [element select2]
 */
function clean_select2(social, resource_to_delete = null) {
  if (Array.isArray(social)) {
    var new_values = [];
    for (var i = 0; i < social.length; i++) {
      if (resource_to_delete != social[i].text) {
        new_values.push(social[i].id);
      }
    }
    var social = $("#social_resourcesId");
    social.val(new_values).trigger("change");
  }

  /*var current_values = social.val();
    console.log(current_values);
    current_values.splice( current_values.indexOf('1'), 1 );
    console.log(current_values);
    social.val(current_values).trigger('change');*/
}
