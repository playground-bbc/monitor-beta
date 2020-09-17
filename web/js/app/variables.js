// 1000 = 1 seg
let refreshTime = 15000;
let refreshSweetAlert = 30000;
let refreshTimeTable = 40000;
let data_chart = new Object();

let columnsName = [
  "col-md-12",
  "col-md-6",
  "col-md-4",
  "col-md-3",
  "col-md-5",
  "col-md-2",
  "col-md-1",
  "col-xs-4 col-sm-3 col-md-8r",
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
// property for each box on resource social
let smallboxProperties = {
  total_web_records_found: {
    title: "Total Coincidencias",
    class: "small-box bg-info",
    icon: "socicon-internet",
    name: "Paginas Webs",
  },
  total_chats: {
    title: "Total Chats Livechats",
    class: "small-box bg-info",
    icon: "socicon-twitch",
    name: "Live Chat Conversations",
  },
  total_tickets: {
    title: "Total Tickets Livechats",
    class: "small-box bg-warning",
    icon: "socicon-googlegroups",
    name: "Live Chat",
  },
  total_tweets: {
    title: "Total Tweets",
    class: "small-box bg-info",
    icon: "socicon-twitter",
    name: "Twitter",
  },
  total_comments_instagram: {
    title: "Total Comentarios",
    class: "small-box bg-danger",
    icon: "socicon-instagram",
    name: "Instagram Comments",
  },
  total_comments_facebook_comments: {
    title: "Total Comentarios",
    class: "small-box bg-info",
    icon: "socicon-facebook",
    name: "Facebook Comments",
  },
  total_inbox_facebook: {
    title: "Total Inbox Facebook",
    class: "small-box bg-info",
    icon: "socicon-messenger",
    name: "Facebook Messages",
  },
};

// header to dataTable on graph
let dataTableHeaders = {
  Twitter: ["Retweets", "Favoritos", "Total"],
  "Instagram Comments": ["Shares", "Instagram Likes"],
};

// color by social media
let socialColors = {
  // twitter
  Retweets: "#3CAAED",
  Favoritos: "#E30934",
  Likes: "#EC1F2E",
  // Instagram
  Shares: "#DB23EA",
  "Instagram Likes": "#E8A0C1",
  // total
  Total: "#05BD2C",
};
