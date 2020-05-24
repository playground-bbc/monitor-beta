let id = document.getElementById("alertId").value;
//console.log(location);
const origin = location.origin;
const appId = location.pathname.split("/")[1];

//const apiUrl = `${origin}/${appId}/api/v1/mentions/`;

const baseUrlApi = `${origin}/${appId}/web/monitor/api/mentions/`;
const baseUrlDocument = `${origin}/${appId}/web/monitor/pdf/`;
const baseUrlView = `${origin}/${appId}/web/monitor/alert/`;
// 1000 = 1 seg

let refreshTime = 15000;
let refreshSweetAlert = 30000;
let refreshTimeTable = 40000;
let data_chart = new Object();
let controllerName = {
  Twitter: "Twitter",
  "Live Chat": "Live-chat",
  "Live Chat Conversations": "Live-chat-conversations",
  "Facebook Comments": "Facebook-comments",
  "Facebook Comments": "Facebook-comments",
};
let tableConfigMentions = {
  scrollY: "400px",
  scrollCollapse: true,
  //'serverSide': true,
  processing: true,
  ajax: {
    url: baseUrlApi + "list-mentions?alertId=" + id,
    //"dataSrc": "mentions"
  },
  fixedColumns: true,
  columns: [
    {
      data: "recurso",
      width: "200px",
    },
    {
      data: "term_searched",
      width: "200px",
    },
    {
      data: "created_time",
      type: "date",
      render: function (data) {
        var a = new Date(data * 1000);
        var months = [
          "Jan",
          "Feb",
          "Mar",
          "Apr",
          "May",
          "Jun",
          "Jul",
          "Aug",
          "Sep",
          "Oct",
          "Nov",
          "Dec",
        ];
        var year = a.getUTCFullYear();
        var month = months[a.getUTCMonth()];
        var date = a.getUTCDate();
        var hour = a.getUTCHours();
        var min = a.getUTCMinutes();
        var sec = a.getUTCSeconds();
        var time =
          date + " " + month + " " + year + " " + hour + ":" + min + ":" + sec;
        return time;
      },
      width: "200px",
    },
    {
      data: "name",
      width: "200px",
    },
    {
      data: "screen_name",
      width: "200px",
    },
    {
      data: "subject",
      width: "200px",
    },
    {
      data: "message_markup",
      width: "40%",
    },
    {
      data: "url",
      render: function (data) {
        var link = "-";
        if (data !== null && data !== "-") {
          var href = "<a href=";
          var url = data;
          var target = " target=" + ".$target.";
          var text = ">link</a>";
          link = href.concat(url, target, text);
          return link;
        }
        return "-";
      },
      width: "10%",
    },
  ],
};

let columnsName = [
  "col-md-12",
  "col-md-6",
  "col-md-4",
  "col-md-3",
  "col-md-5",
  "col-md-2",
  "col-md-1",
];

let resourceIcons = {
  Twitter: "socicon-twitter",
  "Live Chat": "socicon-googlegroups",
  "Live Chat Conversations": "socicon-twitch",
  "Facebook Comments": "socicon-facebook",
  "Instagram Comments": "socicon-instagram",
  "Facebook Messages": "socicon-messenger",
  "Excel Document": "socicon-windows",
  "Paginas Webs": "socicon-internet",
};
// title alert view
let title_with_data = "<strong>Alerta Activa</strong>";
let title_not_data = "<strong>Alerta Finalizada</strong>";

// messages sweet alert
let message_with_data =
  "Usted puede pulsar en <b>continuar</b>, para mantenerse en esta vista <hr> Puede pulsar en <b> Generar Informe </b> para recibir el documento pdf y la Alerta pasara a status <b>Finalizada</b> <hr> Puede pulsar en <b>actualizar la alerta</b> para buscar bajo otros parametros";
let message_not_data =
  "Opps no se encontraron resultados. <hr> Puede pulsar en <b>actualizar la alerta</b> para buscar bajo otros parametros";

// message sweealert delete button
let title_delete = "Usted desea eliminar esta Alerta?";
let text_delete =
  "Se procedera a <b>borar</b> los datos obtenidos por la alerta.";

let smallboxProperties = {
  count: {
    title: "Total de Entradas",
    class: "small-box bg-info",
    icon: "glyphicon glyphicon-hdd",
  },
  retweet_count: {
    title: "Total Retweet",
    class: "small-box bg-success",
    icon: "socicon-twitter",
  },
  favorite_count: {
    title: "Total Favoritos",
    class: "small-box bg-danger",
    icon: "socicon-twitter",
  },
  shares: {
    title: "Total Post Facebook Share",
    class: "small-box bg-info",
    icon: "socicon-facebook",
  },
  like_count: {
    title: "Total Likes Post Instagram",
    class: "small-box bg-danger",
    icon: "socicon-instagram",
  },
  total_comments: {
    title: "Total Comentarios",
    class: "small-box bg-warning",
    icon: "glyphicon glyphicon-comment",
  },
  total_inbox: {
    title: "Total Inbox Facebook",
    class: "small-box bg-info",
    icon: "socicon-messenger",
  },
};
