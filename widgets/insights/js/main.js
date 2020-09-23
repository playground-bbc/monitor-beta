"use strict";

/**
 * [componente que muestra cuantos card widget se mostraran en pantalla]
 * template: '#card-template [description]
 * @return {[component]}           [component]
 */
const cardWidget = Vue.component("card-widget", {
  props: ["resources"],
  template: "#card-template",
});
/**
 * [componente que muestra todo el  card widget]
 * template: '#widget-template [description]
 * @return {[component]}           [component]
 */
const widget = Vue.component("widget", {
  props: ["resourceId", "index", "length"],
  template: "#widget-template",
  data: function () {
    return {
      contentPage: [],
      insightsPage: [],
      idTab: 0,
    };
  },
  mounted() {
    this.idTab = Math.floor(Math.round(Math.random() * 100 + this.resourceId));
    this.fetchPage();
  },
  methods: {
    fetchPage() {
      axios
        .get(baseUrlApi + "content-page" + "?resourceId=" + this.resourceId)
        .then((response) => {
          if (typeof response === "object") {
            this.contentPage = response.data;
            this.insightsPage = this.contentPage.wInsights;
          }
        });
    },
    getCol: function (length, index) {
      var className = "";
      var col = Math.round(12 / length);
      className = `col-sm-${col} border-right`;

      if (length == 5) {
        if (index == 3) {
          className = `col-sm-3 border-right`;
        }
        if (index == 5) {
          className = `col-sm-3`;
        }
        return className;
      }

      return className;
    },
  },
  filters: {
    imagePath: function (value) {
      var img_explode = value.split(" ");
      return baseUrlImg + img_explode[0] + ".png";
    },
    isNullValue: function (value) {
      if (!value) {
        value = 0;
      }
      return value;
    },
    setTitleInsights: function (value) {
      if (titleInsights[value]) {
        return titleInsights[value];
      }
      return value;
    },
    setTitleTooltipsInsights: function (value) {
      if (titleToolTipsInsights[value]) {
        return titleToolTipsInsights[value];
      }
      return value;
    },
  },
  computed: {
    setLinkTab: function () {
      return `#${this.idTab}a`;
    },
  },
});
/**
 * [componente que muestra el post en el card widget]
 * template: '#post-template [description]
 * @return {[component]}           [component]
 */
const PostsInsights = Vue.component("posts", {
  props: ["resourceId", "idTab"],
  template: "#post-template",
  data: function () {
    return {
      contentPosts: [],
      insightsHeader: [],
    };
  },
  created() {
    this.fetchPost();
  },
  methods: {
    fetchPost() {
      axios
        .get(baseUrlApi + "posts-insights" + "?resourceId=" + this.resourceId)
        .then((response) => {
          this.contentPosts = response.data;
          this.setHeaders();
        });
    },
    setHeaders() {
      var insights = this.contentPosts[1].wInsights;
      for (var i = 0; i < insights.length; i++) {
        this.insightsHeader.push(insights[i].name);
      }
    },
  },
  filters: {
    stringSubstr: function (value) {
      return value.slice(0, 20);
    },
    isNullValue: function (value) {
      if (!value) {
        value = 0;
      }
      return value;
    },
    setHeadersPost: function (value) {
      if (headersPost[value]) {
        return headersPost[value];
      }
      return value;
    },
    setHeaderToolTips: function (value) {
      if (titleInsightsTableTooltips[value]) {
        return titleInsightsTableTooltips[value];
      }
      return value;
    },
  },
  computed: {
    setidTab: function () {
      return `${this.idTab}a`;
    },
  },
});
/**
 * [componente que muestra el storys en el card widget]
 * template: '#post-template [description]
 * @return {[component]}           [component]
 */
const InsightsStrorys = Vue.component("storys", {
  props: ["resourceId", "idTab"],
  template: "#insights-template",
  data: function () {
    return {
      contentStorys: [],
      insightsStorys: [],
      storysHeader: [],
      loaded: false,
    };
  },
  mounted() {
    this.fetchStorys();
  },
  methods: {
    fetchStorys() {
      axios
        .get(baseUrlApi + "storys-insights" + "?resourceId=" + this.resourceId)
        .then((response) => {
          this.contentStorys = response.data;
          //console.log(this.contentStorys);
          if (this.contentStorys.length) {
            this.loaded = true;
            // set header
            this.setHeaders();
          } else {
            this.loaded = false;
          }
        });
    },
    setHeaders() {
      var insights = this.contentStorys[0].wInsights;
      for (var i = 0; i < insights.length; i++) {
        this.storysHeader.push(insights[i].title);
      }
    },
  },
  filters: {
    isNullValue: function (value) {
      if (!value) {
        value = 0;
      }
      return value;
    },
    getDate: function (value) {
      var a = new Date(value * 1000);
      var months = [
        "Ene",
        "Feb",
        "Mar",
        "Abr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
      ];
      var year = a.getFullYear();
      var month = months[a.getMonth()];
      var date = a.getDate();
      var hour = a.getHours();
      var min = a.getMinutes();
      var sec = a.getSeconds();
      var time =
        date + " " + month + " " + year + " " + hour + ":" + min + ":" + sec;
      return time;
    },
  },
});

Vue.filter("formatNumber", function (value) {
  return value.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
});

var vue = new Vue({
  el: "#insights",
  data: function () {
    return {
      resources: 0,
      loaded: false,
    };
  },
  mounted() {
    this.fetchStatus();
  },
  methods: {
    fetchStatus() {
      axios.get(baseUrlApi + "numbers-content").then((response) => {
        this.resources = response.data;
        if (this.resources.length) {
          this.loaded = true;
        }
      });
    },
  },
});
