"use strict";

const origin = location.origin;
const appId = location.pathname.split("/")[1];

const baseDetailApi = `${origin}/${appId}/web/monitor/api/detail/`;

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
      axios
        .get(
          `${baseDetailApi}count?alertId=${this.alertid}&resourceId=${this.resourceid}&term=${this.term}`
        )
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
          this.loading = true;
        } else {
          this.term = "";
        }
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
    setInterval(
      function () {
        if (this.isChange) {
          this.fetchBoxInfo();
        }
      }.bind(this),
      10000 // numbers second reload
    );
  },
  methods: {
    fetchBoxInfo() {
      axios
        .get(
          `${baseDetailApi}box-info?alertId=${this.alertid}&resourceId=${this.resourceid}&term=${this.term}`
        )
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

const detail = new Vue({
  el: "#alerts-detail",
});

let columnsName = [
  "col-md-12",
  "col-md-6",
  "col-md-4",
  "col-md-3",
  "col-md-5",
  "col-md-2",
  "col-md-1",
];