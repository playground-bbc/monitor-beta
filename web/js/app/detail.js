"use strict";
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

Vue.filter("formatNumber", function (value) {
  return value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
});
/**
 * detailComponent: send call to api if there record load the rest the components or load spinder in th template
 */
const detailComponent = Vue.component("detail", {
  props: {
    alertid: {
      type: Number,
      required: true,
    },
    resourceid: {
      type: Number,
      required: true,
    },
  },
  template: "#detail",
  data: function () {
    return {
      loading: true,
      isChange: false,
      count: 0,
      term: "",
      socialId: "",
      msg: `<strong>Info!</strong> No se encuentra datos disponible`,
    };
  },
  mounted() {
    this.getSelect();
    setInterval(
      function () {
        // console.log(this.term);
        this.fetchIsData();
      }.bind(this),
      10000 // numbers second reload
    );
  },
  methods: {
    fetchIsData() {
      getCountMentionsDetail(this.alertid, this.resourceid, this.term)
        .then((response) => {
          if (response.status == 200) {
            this.count = response.data.countMentions;
            this.loading = false;
            //this.loading = this.count > 0 ? false : true;
          }
        })
        .catch((error) => {
          console.log(error);
          // see error by dialog
        });

      if (
        localStorage.getItem(`detail_count_${this.alertid}_${this.resourceid}`)
      ) {
        var count_storage = localStorage.getItem(
          `detail_count_${this.alertid}_${this.resourceid}`
        );
        if (count_storage != this.count) {
          localStorage.setItem(
            `detail_count_${this.alertid}_${this.resourceid}`,
            this.count
          );
          this.isChange = true;
        } else {
          this.isChange = false;
        }
      } else {
        localStorage.setItem(
          `detail_count_${this.alertid}_${this.resourceid}`,
          this.count
        );
        console.info("set storage ...");
      }
    },
    getSelect() {
      $("#w0").change((e) => {
        var text = $("#w0 option:selected").text();
        if (text !== "Terminos...") {
          // v-model looks for
          this.term = $("#w0 option:selected").text();
          this.setCallSelectDepen();
        } else {
          this.term = "";
          $("#depend_select").empty().trigger("change");
        }
        this.loading = true;
      });

      $("#depend_select").change((e) => {
        var text = $("#depend_select option:selected").text();
        if (text !== "Tickets a Buscar") {
          // v-model looks for
          this.socialId = $("#depend_select option:selected").val();
        } else {
          this.socialId = "";
        }
        //this.loading = true;
      });
    },
    setCallSelectDepen() {
      if (document.body.contains(document.getElementById("depend_select"))) {
        $("#depend_select").empty().trigger("change");
        getDataSelectDetail(this.alertid, this.resourceid, this.term)
          .then((response) => {
            if (response.status == 200) {
              if (response.data.data.length) {
                response.data.data.forEach(function (element) {
                  var option = new Option(element.text, element.id, true, true);
                  $("#depend_select").append(option);
                });
                $("#depend_select").val("").trigger("change");
              } else {
                $("#depend_select").empty().trigger("change");
              }
            }
          })
          .catch((error) => {
            console.error(error);
            // see error by dialog
          });
      }
    },
  },
});
/**
 * boxComponent: send call to api and display content
 */
const boxComponent = Vue.component("box-detail", {
  props: {
    alertid: {
      type: Number,
      required: true,
    },
    resourceid: {
      type: Number,
      required: true,
    },
    term: {
      type: String,
      required: true,
    },
    socialId: {
      type: String,
      required: false,
      default: "",
    },
    isChange: {
      type: Boolean,
      required: true,
      default: false,
    },
  },
  template: "#box-info-detail",
  data: function () {
    return {
      box_properties: [],
    };
  },
  mounted() {
    this.fetchBoxInfo();
  },
  watch: {
    isChange: function (val, oldVal) {
      if (val) {
        this.fetchBoxInfo();
      }
    },
    socialId: function (val, oldVal) {
      if (val || val == "") {
        this.fetchBoxInfo();
      }
    },
  },
  methods: {
    fetchBoxInfo() {
      getBoxInfoDetail(this.alertid, this.resourceid, this.term, this.socialId)
        .then((response) => {
          if (response.status == 200) {
            this.box_properties = response.data.propertyBoxs;
            //console.log("call api box-info");
          }
        })
        .catch((error) => {
          console.error(error);
          // see error by dialog
        });
    },
    sorted(attribute) {
      if (attribute.length) {
        $('input[name="sort"]').attr("value", `-${attribute}`);
        $("#mentionsearch-id").attr("value", this.alertid);
        $("#mentionsearch-social_id").attr("value", this.socialId);
        $("#mentionsearch-resourceid").attr("value", this.resourceid);
        $("#search").click();
      }
    },
    searched(attribute) {
      for (var [key, value] of Object.entries(attribute)) {
        //console.log(key + " " + value);
        $(`#mentionsearch-${key}`).attr("value", value);
      }
      $("#mentionsearch-id").attr("value", this.alertid);
      $("#mentionsearch-social_id").attr("value", this.socialId);
      $("#mentionsearch-resourceid").attr("value", this.resourceid);
      $("#search").click();
    },
    filter(method, attribute) {
      switch (method) {
        case "sort":
          this.sorted(attribute);
          break;
        case "search":
          this.searched(attribute);
          break;
        default:
          break;
      }
      //console.log(method, attribute);
    },
  },
  computed: {
    calcColumns() {
      var size = Object.keys(this.box_properties).length;
      return columnsName[size - 1];
    },
  },
});
/**
 * boxCommonWordsComponent: send call to api and display content words most repeated
 */
const boxCommonWordsComponent = Vue.component("common-words-detail", {
  props: {
    alertid: {
      type: Number,
      required: true,
    },
    resourceid: {
      type: Number,
      required: true,
    },
    term: {
      type: String,
      required: true,
    },
    socialId: {
      type: String,
      required: false,
      default: "",
    },
    isChange: {
      type: Boolean,
      required: true,
      default: false,
    },
  },
  template: "#box-common-words-detail",
  data: function () {
    return {
      words: [],
    };
  },
  mounted() {
    this.fetchCommonWords();
  },
  watch: {
    isChange: function (val, oldVal) {
      if (val) {
        this.fetchCommonWords();
      }
    },
    socialId: function (val, oldVal) {
      if (val || val == "") {
        this.fetchCommonWords();
      }
    },
  },
  methods: {
    fetchCommonWords() {
      getBoxCommonWordsDetail(
        this.alertid,
        this.resourceid,
        this.term,
        this.socialId
      )
      .then((response) => {
        if (response.status == 200) {
          this.words = response.data.words;
        }
      })
      .catch((error) => {
        console.error(error);
        // see error by dialog
      });
    },
  },
});
/**
 * graphCommonWordsComponent: send call to api and display graph words most repeated
 */
const graphCommonWordsComponent = Vue.component("graph-common-words-detail", {
  props: {
    alertid: {
      type: Number,
      required: true,
    },
    resourceid: {
      type: Number,
      required: true,
    },
    term: {
      type: String,
      required: true,
    },
    socialId: {
      type: String,
      required: false,
      default: "",
    },
    isChange: {
      type: Boolean,
      required: true,
      default: false,
    },
  },
  template: "#graph-common-words-detail",
  data: function () {
    return {
      words: [],
    };
  },
  mounted() {
    this.fetchCommonWords();
  },
  watch: {
    isChange: function (val, oldVal) {
      if (val) {
        this.fetchCommonWords();
      }
    },
    socialId: function (val, oldVal) {
      if (val || val == "") {
        this.fetchCommonWords();
      }
    },
  },
  methods: {
    fetchCommonWords() {
      //this.drawPieGraph();
      getBoxCommonWordsDetail(
        this.alertid,
        this.resourceid,
        this.term,
        this.socialId
      )
      .then((response) => {
        if (response.status == 200) {
          this.words = [];
          response.data.words.forEach(function(value){
            let tmp = {
              'name': value.name,
              'y': parseInt(value.total),
            };
            this.words.push(tmp);
          }.bind(this));
          
          if(this.words.length > 0){
            this.drawPieGraph();
          }
        }
      })
      .catch((error) => {
        console.error(error);
        // see error by dialog
      });
    },
    drawPieGraph(){
      // Make monochrome colors
      var pieColors = this.pieColors();

      // Build the chart
      Highcharts.chart('graph-common-words', {
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
                colors: pieColors,
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
            data: this.words
        }]
      });
    },
    pieColors(){
      var colors = [],
          base = Highcharts.getOptions().colors[0],
          i;

      for (i = 0; i < 10; i += 1) {
          // Start out with a darkened base color (negative brighten), and end
          // up with a much brighter color
          colors.push(Highcharts.color(base).brighten((i - 3) / 7).get());
      }
      return colors;
      
    }
  },
});
/**
 * mapUserComponent: send call to api and display user map
 */
const mapUserComponent = Vue.component("map-user-detail", {
  props: {
    alertid: {
      type: Number,
      required: true,
    },
    resourceid: {
      type: Number,
      required: true,
    },
    term: {
      type: String,
      required: true,
    },
    socialId: {
      type: String,
      required: false,
      default: "",
    },
    isChange: {
      type: Boolean,
      required: true,
      default: false,
    },
  },
  template: "#map-user-detail",
  data: function () {
    return {
      regions_count: [],
    };
  },
  mounted() {
    this.fetchRegionsCount();
  },
  watch: {
    isChange: function (val, oldVal) {
      if (val) {
        this.fetchRegionsCount();
      }
    },
    socialId: function (val, oldVal) {
      if (val || val == "") {
        this.fetchRegionsCount();
      }
    },
  },
  methods: {
    fetchRegionsCount() {
      getRegionLiveChatDetail(
        this.alertid,
        this.resourceid,
        this.term,
        this.socialId
      )
        .then((response) => {
          if (response.status == 200) {
            this.regions_count = response.data.regions_count;
            if (this.regions_count.length) {
              this.drawMapsRegions();
            }
          }
        })
        .catch((error) => {
          console.error(error);
          // see error by dialog
        });
    },
    drawMapsRegions() {
      let data = this.regions_count;
      let alertid = this.alertid;
      let resourceid = this.resourceid;
      let social_id = this.socialId;

      Highcharts.getJSON(
        "https://gist.githubusercontent.com/spiderbbc/3cb18e8ec1832a6895f5e4eef4355dfe/raw/ece51b4fc0e1496d8b7839a5f66599f9a762f5d6/GeoChile.json",
        function (topology) {
          // Create the chart
          var chart = Highcharts.mapChart("map-user", {
            chart: {
              map: topology,
              events: {
                load: function () {
                  //this.mapZoom(-0.3, 0, 0, 0.5, 0);
                  //console.log(this.get()); //zoom to the country using "id" from data serie
                },
              },
            },

            title: {
              text: "Mapa de Usuarios",
            },
            mapNavigation: {
              enabled: true,
              enableDoubleClickZoomTo: true,
            },

            subtitle: {
              text:
                'Source map: <a href="http://code.highcharts.com/mapdata/countries/cl/cl-all.js">Chile</a>',
            },
            plotOptions: {
              series: {
                events: {
                  click: function (e) {
                    let text;
                    getCityLiveChatDetail(
                      alertid,
                      resourceid,
                      e.point.options,
                      social_id
                    )
                      .then((response) => {
                        if (response.status == 200) {
                          var text = "<b>Ciudad || Total</b>: <br>";
                          response.data.forEach(
                            (element) =>
                              (text += `<b>${element.city}</b>: ${element.num_city}<br>`)
                          );
                          if (!this.chart.clickLabel) {
                            this.chart.clickLabel = this.chart.renderer
                              .label(text, 0, 10)
                              .css({
                                width: "180px",
                              })
                              .add();
                          } else {
                            this.chart.clickLabel.attr({
                              text: text,
                            });
                          }
                        }
                      })
                      .catch((error) => {
                        console.error(error);
                        // see error by dialog
                      });
                  },
                },
              },
            },

            mapNavigation: {
              enabled: true,
              buttonOptions: {
                verticalAlign: "bottom",
              },
            },

            colorAxis: {
              min: 0,
            },

            series: [
              {
                data: data,
                name: "Total de Usuarios",
                states: {
                  hover: {
                    color: "#BADA55",
                  },
                },
                dataLabels: {
                  enabled: true,
                  format: "{point.name}",
                },
              },
            ],
          });
        }
      );

      // zoon
      //chart.zoomTo();
    },
  },
});
/**
 * gridMentions: display grid content
 */
const gridMentions = Vue.component("grid-detail", {
  props: {
    isChange: {
      type: Boolean,
      required: true,
    },
    alertid: {
      type: Number,
      required: true,
    },
    resourceid: {
      type: Number,
      required: true,
    },
    // resourceName: {
    //   type: String,
    //   required: true,
    // },
    term: {
      type: String,
      required: true,
    },
    socialId: {
      type: String,
      required: false,
    },
  },
  template: "#grid-mention-detail",
  data: function () {
    return {};
  },
  mounted() {
    this.searchForm();
  },
  watch: {
    isChange: function (val, oldVal) {
      if (val) {
        this.searchForm();
      }
    },
    socialId: function (val, oldVal) {
      if (val) {
        this.searchForm();
      }
    },
  },

  methods: {
    searchForm() {
      // $('input[name="MentionSearch[message_markup]"]').attr("value", "");
      // $("#mentionsearch-message_markup").attr("value", "");
      $("#mentionsearch-id").attr("value", this.alertid);
      // get resource name
      var resourceName = document.querySelector(".resourceName");
      $("#mentionsearch-resourcename").attr("value", resourceName.innerText);

      if (
        this.resourceid == 5 ||
        this.resourceid == 6 ||
        this.resourceid == 7
      ) {
        $("#mentionsearch-publication_id").attr("value", this.socialId);
      } else {
        $("#mentionsearch-social_id").attr("value", this.socialId);
      }

      //$("#mentionsearch-termsearch").attr("value", "Facebook Messages");
      $("#mentionsearch-resourceid").attr("value", this.resourceid);
      $("#mentionsearch-termsearch").attr("value", this.term);
      $("#search").click();
    },
  },
});

const detail = new Vue({
  el: "#alerts-detail",
});
