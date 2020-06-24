"use strict";

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
          if (response.status == 200 && response.statusText == "OK") {
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
          console.info("Hubo un cambio en el count");
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
        this.loading = true;
      });
    },
    setCallSelectDepen() {
      if (document.body.contains(document.getElementById("depend_select"))) {
        getDataSelectDetail(this.alertid, this.resourceid, this.term)
          .then((response) => {
            if (response.status == 200 && response.statusText == "OK") {
              if (response.data.data.length) {
                response.data.data.forEach(function (element) {
                  var option = new Option(element.text, element.id, true, true);
                  $("#depend_select").append(option).trigger("change");
                });
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
  },
  methods: {
    fetchBoxInfo() {
      getBoxInfoDetail(this.alertid, this.resourceid, this.term, this.socialId)
        .then((response) => {
          if (response.status == 200 && response.statusText == "OK") {
            this.box_properties = response.data.propertyBoxs;
            console.log("call api box-info");
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
        console.log(key + " " + value);
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
      console.log(method, attribute);
    },
  },
  computed: {
    calcColumns() {
      var size = Object.keys(this.box_properties).length;
      return columnsName[size - 1];
    },
  },
});

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
  methods: {
    searchForm() {
      // $('input[name="MentionSearch[message_markup]"]').attr("value", "");
      // $("#mentionsearch-message_markup").attr("value", "");
      $("#mentionsearch-id").attr("value", this.alertid);
      $("#mentionsearch-social_id").attr("value", this.socialId);
      $("#mentionsearch-resourceid").attr("value", this.resourceid);
      $("#mentionsearch-termsearch").attr("value", this.term);
      $("#search").click();
    },
  },
});

const detail = new Vue({
  el: "#alerts-detail",
});
