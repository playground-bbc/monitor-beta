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
        } else {
          this.term = "";
        }
        this.loading = true;
      });
    },
  },
});

/**
 * boxComponent: send call to api and display content
 */
const boxComponent = Vue.component("box-detail", {
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
  methods: {
    fetchBoxInfo() {
      getBoxInfoDetail(this.alertid, this.resourceid, this.term)
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
      console.info(this.alertid, this.resourceid, this.term);
      $('input[name="MentionSearch[message_markup]"]').attr("value", "");
      $("#mentionsearch-message_markup").attr("value", "");
      $('input[name="id"]').attr("value", this.alertid);
      $('input[name="resourceId"]').attr("value", this.resourceid);
      $("#mentionsearch-termsearch").attr("value", this.term);
      $("#search").click();
    },
  },
});

const detail = new Vue({
  el: "#alerts-detail",
});
