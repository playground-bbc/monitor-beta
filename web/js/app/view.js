// flag to chart line
let loadedChart = false;

let refreshTime = 15000;
let refreshSweetAlert = 30000;
let refreshTimeTable = 40000;
let data_chart = new Object();
// columns for boxes;
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
  "Usted puede pulsar en <b>continuar</b>, para mantenerse en esta vista <hr> Puede pulsar en <b> Generar Informe </b> para recibir el documento pdf <hr> Puede pulsar en <b>actualizar la alerta</b> para buscar bajo otros parametros";
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

// Load the Visualization API and the corechart package.
google.charts.load("current", { packages: ["corechart", "line"] });

Vue.filter("formatNumber", function (value) {
  return value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
});

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
  }).then((result) => {
    if (result.value) {
      Swal.fire("Eliminada!", "", "success");
      setTimeout(() => {
        okCallback();
      }, 4000);
    }
  });
};

/**
 * [componente que muestra el button report]
 * @param  {[count]} )
 * template: '#view-button-report [description]
 * @return {[component]}           [component]
 */
const report_button = Vue.component("button-report", {
  props: ["count"],
  template: "#view-button-report",
  data: function () {
    return {
      isdisabled: true,
    };
  },
  mounted() {
    setInterval(
      function () {
        if (this.count > 0 && loadedChart) {
          this.isdisabled = false;
        }
      }.bind(this),
      2000
    );
  },
  methods: {
    send(event) {
      if (this.count > 0 && loadedChart) {
        modalFinish(this.count, baseUrlView, id);
      }
    },
  },
});

/**
 * [indicador de status por cada red social]
 * template: '#status-alert' [description]
 * @return {[component]}           [component]
 */
const statusAlert = Vue.component("status-alert", {
  template: "#status-alert",
  props: ["resourceids"],
  data: function () {
    return {
      response: null,
      status: null,
      resourceId: this.resourceids,
      classColor: "status-indicator",
    };
  },
  mounted() {
    setInterval(
      function () {
        this.fetchStatus();
      }.bind(this),
      refreshTime
    );
  },
  methods: {
    fetchStatus() {
      getStatusMentionsResources(id).then((response) => {
        this.response = response.data.data;
      });
    },
  },
  computed: {
    colorClass() {
      var valueClass = "status-indicator--yellow";
      if (this.response != undefined || this.response != null) {
        var search_data_response = this.response.search_data;
        for (let propeties in search_data_response) {
          // check if element is in the doom
          if (
            document.getElementById(search_data_response[propeties].resourceId)
          ) {
            var span = document.getElementById(
              search_data_response[propeties].resourceId
            );
            if (search_data_response[propeties].status == "Finish") {
              span.className = "status-indicator status-indicator--red";
            } else {
              span.className = "status-indicator status-indicator--green";
            }
          }
        }
      }

      return valueClass;
    },
  },
});

/**
 * [componente que muestra el total de menciones]
 * @param  {[count]} )
 * template: '#view-total-mentions' [description]
 * @return {[component]}           [component]
 */
const count_mentions = Vue.component("total-mentions", {
  template: "#view-total-mentions",
  props: ["count", "resourcescount"],
  data: function () {
    return {};
  },
  methods: {
    calcColumns() {
      var size = Object.keys(this.resourcescount).length;
      return columnsName[size - 1];
    },
    getClass(resource) {
      var className = false;
      if (smallboxProperties.hasOwnProperty(resource)) {
        className = smallboxProperties[resource].class;
      }
      return className;
    },
    getTitle(resource) {
      return smallboxProperties[resource].title;
    },
    getIcon(resource) {
      return smallboxProperties[resource].icon;
    },
    getLink(resource) {
      name = smallboxProperties[resource].name;
      hiperlink = document.getElementById(name);
      return hiperlink;
    },
  },
});

/**
 * [componente que muestra grafico de menciones x red social]
 * template: '#view-total-resources-chart' [description]
 * @return {[component]}           [component]
 */
const count_resources_chat = Vue.component("total-resources-chart", {
  props: ["is_change"],
  template: "#view-total-resources-chart",
  data: function () {
    return {
      alertId: id,
      response: [],
      loaded: false,
      dataTable: ["Red Social", "Shares/Retweets", "Likes", "Total"],
      view: null,
      column: [
        0,
        1,
        {
          calc: "stringify",
          sourceColumn: 1,
          type: "string",
          role: "annotation",
        },
        2,
        {
          calc: "stringify",
          sourceColumn: 2,
          type: "string",
          role: "annotation",
        },
        3,
        {
          calc: "stringify",
          sourceColumn: 3,
          type: "string",
          role: "annotation",
        },
        // 4,
        // {
        //   calc: "stringify",
        //   sourceColumn: 4,
        //   type: "string",
        //   role: "annotation",
        // },
      ],

      options: {
        focusTarget: "category",
        title: "Gráfico de número de interacciones por red social",
        vAxis: { format: "decimal" },
        width: 1200,
        height: 400,
        colors: [],
        animation: {
          startup: true,
          duration: 1500,
          easing: "out",
        },
      },
    };
  },
  mounted() {
    this.response = [this.dataTable];
    // get firts data
    this.fetchResourceCount();
  },
  watch: {
    is_change: function (val, oldVal) {
      if (val) {
        console.log("cambio en change");
        this.fetchResourceCount();
      }
    },
  },
  methods: {
    fetchResourceCount() {
      getTotalResource(this.alertId)
        .then((response) => {
          if (typeof this.response === "object") {
            this.loaded = true;
            this.options.colors = response.data.colors;
            this.response.splice(1, response.data.data.length);
            for (let index in response.data.data) {
              this.response.push(response.data.data[index]);
            }
            this.setOnLoadCallback();
          }
        })
        .catch((error) => console.log(error));
    },
    setOnLoadCallback() {
      setTimeout(() => {
        google.charts.setOnLoadCallback(this.drawColumnChart);
      }, 1000);
      // google.charts.setOnLoadCallback(this.drawColumnChart);
    },
    drawColumnChart() {
      var data = google.visualization.arrayToDataTable(this.response);
      var view = new google.visualization.DataView(data);

      view.setColumns(this.column);
      var chart = new google.visualization.ColumnChart(
        document.getElementById("resources_chart_count")
      );

      google.visualization.events.addListener(chart, "ready", function () {
        data_chart["chart_bar_resources_count"] = chart.getImageURI();
      });

      chart.draw(view, this.options);
    },
  },
});

/**
 * [componente que muestra grafico de post con mas menciones]
 * template: '#view-total-resources-chart' [description]
 * @return {[component]}           [component]
 */
const post_interations_chart = Vue.component("post-interation-chart", {
  props: ["is_change"],
  template: "#view-post-mentions-chart",
  data: function () {
    return {
      alertId: id,
      response: [],
      loaded: false,
      render: false,
      dataTable: [
        "Post Titulo",
        "Share",
        "Like Post",
        "Likes Comments",
        "Total",
        "link",
      ],
      view: null,
      column: [
        0,
        1,
        {
          calc: "stringify",
          sourceColumn: 1,
          type: "string",
          role: "annotation",
        },
        2,
        {
          calc: "stringify",
          sourceColumn: 2,
          type: "string",
          role: "annotation",
        },
        3,
        {
          calc: "stringify",
          sourceColumn: 3,
          type: "string",
          role: "annotation",
        },
        4,
        {
          calc: "stringify",
          sourceColumn: 4,
          type: "string",
          role: "annotation",
        },
      ],

      options: {
        title: "Gráfico Post con mas interaciones",
        vAxis: { format: "decimal" },
        width: 1200,
        height: 400,
        colors: ["#1b9e77", "#d95f02", "#7570b3", "#2f1bad", "#bf16ab"],
      },
    };
  },
  mounted() {
    this.response = [this.dataTable];
    // get firts data
    this.fetchResourceCount();
  },
  watch: {
    is_change: function (val, oldVal) {
      if (val) {
        this.fetchResourceCount();
      }
    },
  },
  methods: {
    fetchResourceCount() {
      getPostInterations(this.alertId)
        .then((response) => {
          if (typeof this.response === "object") {
            this.render = true;
            this.loaded = true;
            if (response.data.status) {
              this.response.splice(1, response.data.data.length);
              for (let index in response.data.data) {
                this.response.push(response.data.data[index]);
              }
              // load chart
              this.setOnLoadCallback();
            }
          }
        })
        .catch((error) => console.log(error));
    },
    drawColumnChart() {
      var data = google.visualization.arrayToDataTable(this.response);
      var view = new google.visualization.DataView(data);
      view.setColumns(this.column);
      var chart = new google.visualization.ColumnChart(
        document.getElementById("post_mentions")
      );

      google.visualization.events.addListener(chart, "ready", function () {
        data_chart["post_mentions"] = chart.getImageURI();
      });

      chart.draw(view, this.options);
      addLink(data, "post_mentions");
    },
    setOnLoadCallback() {
      setTimeout(() => {
        google.charts.setOnLoadCallback(this.drawColumnChart);
      }, 1000);
    },
  },
});

/**
 * [componente que muestra grafico de productos con mas menciones]
 * template: '#view-products-interations-chart' [description]
 * @return {[component]}           [component]
 */
const products_interations_chart = Vue.component("products-interations-chart", {
  props: ["is_change"],
  template: "#view-products-interations-chart",
  data: function () {
    return {
      alertId: id,
      response: [],
      loaded: false,
      dataTable: ["Producto", "Shares/Retweets", "Likes", "Total"],
      view: null,
      column: [
        0,
        1,
        {
          calc: "stringify",
          sourceColumn: 1,
          type: "string",
          role: "annotation",
        },
        2,
        {
          calc: "stringify",
          sourceColumn: 2,
          type: "string",
          role: "annotation",
        },
        3,
        {
          calc: "stringify",
          sourceColumn: 3,
          type: "string",
          role: "annotation",
        },
        // 4,
        // {
        //   calc: "stringify",
        //   sourceColumn: 4,
        //   type: "string",
        //   role: "annotation",
        // },
        // 5,
        // {
        //   calc: "stringify",
        //   sourceColumn: 5,
        //   type: "string",
        //   role: "annotation",
        // },
        // 6,
        // {
        //   calc: "stringify",
        //   sourceColumn: 6,
        //   type: "string",
        //   role: "annotation",
        // },
      ],
      options: {
        focusTarget: "category",
        title: "Gráfico de número de interacciones por productos",
        vAxis: { format: "decimal" },
        hAxis: { titleTextStyle: { color: "Black" }, format: "string" },
        width: 1200,
        height: 400,
        colors: ["#3CAAED", "#EC1F2E", "#3A05BD"],
        animation: {
          startup: true,
          duration: 1500,
          easing: "out",
        },
      },
    };
  },
  mounted() {
    this.response = [this.dataTable];
    this.fetchResourceCount();
  },
  watch: {
    is_change: function (val, oldVal) {
      if (val) {
        this.fetchResourceCount();
      }
    },
  },
  methods: {
    fetchResourceCount() {
      getProductInterations(this.alertId)
        .then((response) => {
          if (typeof this.response === "object") {
            this.options.colors = response.data.colors;
            this.response.splice(1, response.data.data.length);
            for (let index in response.data.data) {
              this.response.push(response.data.data[index]);
            }
            this.loaded = true;
            this.setOnLoadCallback();
          }
        })
        .catch((error) => console.log(error));
    },
    setOnLoadCallback() {
      setTimeout(() => {
        google.charts.setOnLoadCallback(this.drawColumnChart);
      }, 1000);
    },
    drawColumnChart() {
      var data = google.visualization.arrayToDataTable(this.response);
      var view = new google.visualization.DataView(data);
      view.setColumns(this.column);
      var chart = new google.visualization.ColumnChart(
        document.getElementById("products-interation-chart")
      );

      google.visualization.events.addListener(chart, "ready", function () {
        data_chart["products_interations"] = chart.getImageURI();
        //console.log(data_chart["products_interations"]);
      });

      chart.draw(view, this.options);
    },
  },
});

/**
 * [componente que muestra grafico de productos con mas menciones]
 * template: '#view-products-interations-chart' [description]
 * @return {[component]}           [component]
 */
const count_common_words_chart = Vue.component("count-common-words-chart",{
  props: ["is_change"],
  template: "#view-count-common-words-chart",
  data: function(){
    return {
      alertId: id,
      loaded: true,
    };
  },
  mounted() {
    this.drawPieChart();
  },
  watch: {
    is_change: function (val, oldVal) {
      if (val) {
        this.drawPieChart();
      }
    },
  },
  methods:{
    getColors(){
      var colors = [], base = Highcharts.getOptions().colors[0],i;

      for (i = 0; i < 10; i += 1) {
          // Start out with a darkened base color (negative brighten), and end
          // up with a much brighter color
          colors.push(Highcharts.color(base).brighten((i - 3) / 7).get());
      }
      return colors;
    },
   
    drawPieChart() {
      let colors = this.getColors();
      $.getJSON(`${origin}/${appId}/monitor/api/mentions/common-words?alertId=` + id,
        function(response){
          
          // order data to the graph
          let getData = function (response){
            var data = [];
            response.words.forEach(function(value){
              var tmp = {
                'name': value.name,
                'y': parseInt(value.total),
              };
              data.push(tmp);
            });
            return data;
          };

          Highcharts.chart('container-common-words', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
                type: 'pie'
            },
            title: {
                text: 'Palabras mas comunes en las menciones'
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            accessibility: {
                point: {
                    valueSuffix: '%'
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    colors: colors,
                    dataLabels: {
                        enabled: true,
                        format: '<b>{point.name}</b><br>{point.percentage:.1f} %',
                        distance: -50,
                        filter: {
                            property: 'percentage',
                            operator: '>',
                            value: 4
                        }
                    }
                }
            },
            series: [{
                name: 'Total',
                data: getData(response),
            }]
        });

      });
      
    }
    
  }
});


/**
 * [componente que muestra grafico de post por fecha (Higchart)]
 * template: '#view-total-resources-chart' [description]
 * @return {[component]}           [component]
 */
const date_chart = Vue.component("date-chart", {
  props: ["is_change"],
  template: "#view-date-chart",
  data: function () {
    return {
      alertId: id,
      loaded: false,
    };
  },
  mounted() {
    this.drawColumnChart();
  },
  watch: {
    is_change: function (val, oldVal) {
      if (val) {
        this.drawColumnChart();
      }
    },
  },
  methods: {
    drawColumnChart() {
      this.loaded = true;
      $.getJSON(
        `${origin}/${appId}/monitor/api/mentions/mention-on-date?alertId=` + id,
        function (data) {
          var chart = Highcharts.stockChart("date", {
            chart: {
              type: "column",
              zoomType: "x",
            },
            credits: {
              enabled: false,
            },
            legend: {
              enabled: true,
            },
            rangeSelector: {
              selected: 4,
            },
            // time: {
            //   useUTC: false,
            // },
            // tooltip: {
            //   split: false,
            //   shared: true,
            // },
            rangeSelector: {
              enabled: false,
              selected: 1,
            },

            title: {
              text: "Grafico total de registros por fecha y recurso",
            },
            rangeSelector: {
              buttons: [
                {
                  type: "minute",
                  count: 60,
                  text: "h",
                },
                {
                  type: "day",
                  count: 1,
                  text: "d",
                },
                {
                  type: "week",
                  count: 1,
                  text: "w",
                },
                {
                  type: "month",
                  count: 1,
                  text: "m",
                },
                {
                  type: "month",
                  count: 6,
                  text: "6m",
                },
                {
                  type: "year",
                  count: 1,
                  text: "1y",
                },
                {
                  type: "ytd",
                  text: "YTD",
                },
                {
                  type: "all",
                  text: "All",
                },
              ],
              //selected: 1,
              inputEnabled: false,
            },
            global: {
              useUTC: false,
            },
            scrollbar: {
              barBackgroundColor: "grey",
              barBorderRadius: 7,
              barBorderWidth: 0,
              buttonBackgroundColor: "grey",
              buttonBorderWidth: 0,
              buttonBorderRadius: 7,
              trackBackgroundColor: "black",
              trackBorderWidth: 1,
              trackBorderRadius: 8,
              trackBorderColor: "black",
            },
            xAxis: {
              categories: [
               
              ],
            },
            tooltip: {
              pointFormat:
                '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
              valueDecimals: 1,
              split: true,
            },
            navigator: {
              basseSeries: 1,
              series: data.model,
            },
            series: data.model,
          });
          
        }
      );
    },
  },
});
/**
 * [tabla de menciones]
 * template: '#mentions-list' [description]
 * @return {[component]}           [component]
 */
const listMentions = Vue.component("list-mentions", {
  props: ["is_change"],
  template: "#mentions-list",
  data: function () {
    return {
      table: null,
    };
  },
  mounted() {
    //$.pjax.reload({ container: "#mentions", async: true });
    // $.pjax.reload({ container: "#mentions" });
    //$.pjax.reload({ container: "#mentions" });
    //jQuery.pjax.reload({ container: "#mentions" });
    $.pjax.reload({ container: "#mentions", async: false });
    setInterval(
      function () {
        if (this.is_change) {
          $.pjax.reload({ container: "#mentions", async: false });
        }
      }.bind(this),
      refreshTime + 5000
    );
  },
  methods: {
    setDataTable() {
      return initMentionsSearchTable();
    },
    reload() {
      $.pjax.reload({ container: "#mentions", async: false });
    },
  },
});

/**
 * [nuebe de palabras del diccionario]
 * template: '#mentions-list' [description]
 * @return {[component]}           [component]
 */
const cloudWords = Vue.component("cloud-words", {
  props: ["is_change"],
  template: "#cloud-words",
  data: function () {
    return {
      response: null,
      loaded: false,
    };
  },
  mounted() {
    setInterval(
      function () {
        this.fetchWords();
      }.bind(this),
      20000
    );
  },
  methods: {
    fetchWords() {
      getWords(id).then((response) => {
        this.response = response.data.wordsModel;
        if (this.response.length) {
          this.loaded = true;
          var words = this.handlers(this.response);
          var some_words_with_same_weight = $("#jqcloud").jQCloud(words, {
            width: 1000,
            height: 350,
            delay: 50,
          });
        }
      });
    },
    reload() {
      var words = this.handlers(this.response);
      $("#jqcloud").jQCloud("update", words);
    },
    handlers(response) {
      var words = response.map(function (r) {
        r.handlers = {
          click: function () {
            //$("#list-mentions").DataTable().search(r.text).draw();
            //$("#mentionsearch-id").attr("value", id);
            $("#mentionsearch-id").attr("value", id);
            $('input[name="MentionSearch[message_markup]"]').attr(
              "value",
              r.text
            );
            $("#search").click();
          },
        };
        r.html = { class: "pointer-jqcloud" };
        return r;
      });
      return words;
    },
    scroll() {
      console.log(1);
    },
  },
});

/**
 * [liswtado de emojis encontrados]
 * template: '#emojis-list' [description]
 * @return {[component]}           [component]
 */
const listEmojis = Vue.component("list-emojis", {
  template: "#emojis-list",
  data: function () {
    return {
      response: null,
      loaded: false,
    };
  },
  mounted() {
    setInterval(
      function () {
        this.fetchEmojis();
      }.bind(this),
      refreshTime
    );
  },
  methods: {
    fetchEmojis() {
      getEmojis(id).then((response) => {
        if (typeof response.data.data.length === "undefined") {
          this.response = response.data.data;
          this.loaded = true;
        }
      });
    },
  },
});

/**
 * [modal de sweetalert]
 * template: '#modal-alert' [description]
 * @return {[component]}           [component]
 */

const sweetAlert = Vue.component("modal-alert", {
  props: ["count", "is_change"],
  template: "#modal-alert",
  data: function () {
    return {
      alertId: id,
      response: null,
      isShowModal: false,
      flag: false,
    };
  },
  mounted() {
    this.fetchStatus();
    if (this.isShowModal && !this.flag) {
      this.modal();
    }
  },
  watch: {
    is_change: function (val, oldVal) {
      if (val) {
        this.fetchStatus();
        if (this.isShowModal && !this.flag) {
          this.modal();
        }
      }
    },
  },
  methods: {
    fetchStatus() {
      getStatusMentionsResources(id).then((response) => {
        this.response = response.data.data;
        if (this.response != undefined || this.response != null) {
          this.setStatus();
        }
      });
    },
    setStatus() {
      if (this.response != undefined || this.response != null) {
        var resources = document.getElementsByClassName("label-info");
        var search_data = this.response.search_data;
        var statuses = Object.keys(search_data).filter(function (key) {
          return search_data[key].status <= "Finish";
        });

        if (statuses.length == resources.length) {
          this.isShowModal = true;
        } else {
          this.isShowModal = false;
        }
      }
    },
    modal() {
      this.flag = true;
      modalFinish(this.count, baseUrlView, id);
    },
  },
});

/**
 * [componente principal de vue]
 * template: '#mentions-list' [description]
 * @return {[component]}           [component]
 */
const vm = new Vue({
  el: "#alerts-view",
  data: {
    alertId: id,
    count: 0,
    isData: false,
    //retweets: 0,
    resourcescount: [],
    is_change: false,
  },
  mounted() {
    // cheks if localStorage
    if (localStorage.getItem("alert_count_" + id)) {
      var count_storage = +localStorage.getItem("alert_count_" + id);
      if (count_storage > 0) {
        this.fetchIsData();
      }
    }

    setInterval(
      function () {
        this.fetchIsData();
      }.bind(this),
      refreshTime
    );
  },
  methods: {
    fetchIsData() {
      getCountMentions(this.alertId)
        .then((response) => {
          if (response.status == 200) {
            this.count = response.data.data.count;
            this.getOrSetStorage();
          }
        })
        .catch((error) => console.log(error));
    },
    getOrSetStorage() {
      if (this.count > 0) {
        this.isData = true;
        getPropertySourceBox(this.alertId)
          .then((response) => {
            if (response.status == 200) {
              this.resourcescount = response.data.data;
            }
          })
          .catch((error) => console.log(error));

        if (localStorage.getItem("alert_count_" + id)) {
          var count_storage = localStorage.getItem("alert_count_" + id);
          if (count_storage != this.count) {
            localStorage.setItem("alert_count_" + id, this.count);
            this.is_change = true;
          } else {
            this.is_change = false;
          }
        } else {
          localStorage.setItem("alert_count_" + id, this.count);
        }
      }
    },
  },
  // components: {
  //   report_button,
  //   count_mentions,
  //   //box_sources,
  //   count_resources_chat,
  //   post_interations_chart,
  //   products_interations_chart,
  //   count_resources_date_chat,
  //   //count_resources,
  //   listMentions,
  //   cloudWords,
  //   //tableDate,
  //   //listEmojis,
  //   sweetAlert,
  // },
});
