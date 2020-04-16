//const origin = location.origin;

const baseUrlApi = `${origin}/monitor-beta/web/monitor/api/insights/`;
const baseUrlImg = `${origin}/monitor-beta/web/img/`;
const titleInsights = {
	'Número de seguidores': 'Seguidores Unicos'
}


const cardWidget = Vue.component('card-widget',{
	'props': ['resources'],
	'template' : '#card-template',
});

const widget = Vue.component('widget',{
	'props': ['resourceId','index','length'],
	'template': '#widget-template',
	data: function () {
		return {
			contentPage: [],
			insightsPage: [],
			idTab:0
		}
	},
	mounted(){
		this.idTab =  Math.floor((Math.random() * (11-5))+50+this.resourceId);
		this.fetchPage();
	},
	methods:{
		fetchPage(){
			axios.get(baseUrlApi + 'content-page'+ '?resourceId=' +this.resourceId)
		      .then((response) => {
		      	if (typeof response === 'object') {
		      		this.contentPage = response.data;
		      		this.insightsPage = this.contentPage.wInsights;
		      		
		      	}
		    })
		},
		getCol: function(length,index) {
			var className = '';
			var col = Math.round(12 / length);
			className = `col-sm-${col} border-right` 
			
			if (length == 5) {
				if (index == 3) {
					className = `col-sm-3 border-right`;
				}
				if (index == 5) {
					className = `col-sm-3`;
				}
				return className;
			}
			
			return  className; 
		},
	},
	filters: {
		imagePath: function (value) {
			var img_explode = value.split(' ');
			return baseUrlImg + img_explode[0] + '.png';
		},
		isNullValue: function (value) {
			if (!value) {
				value = 0;
			}
			return value;
		},
		setTitleInsights : function(value){
			if (titleInsights[value]) {
				return titleInsights[value];
			}
			return value;
		}

		
	},
	computed: {
		
		setLinkTab:function(){
			return `#${this.idTab}a`; 
		}
	}
});

const PostsInsights = Vue.component('posts',{
	'props': ['resourceId','idTab'],
	'template': '#post-template',
	data: function () {
	    return {
	    	contentPosts: [],
			insightsPost: [],
			insightsHeader: []
	    }
	},
	mounted(){
		this.fetchPost();
	},
	methods:{
		fetchPost(){
			axios.get(baseUrlApi + 'posts-insights'+ '?resourceId=' +this.resourceId)
		      .then((response) => {
		        this.contentPosts = response.data;
		        // set header
		        this.setHeaders();
		    })
		},
		setHeaders(){
			var insights = this.contentPosts[0].wInsights;
			for (var i = 0; i < insights.length; i++) {
				if (insights[i].name == 'post_reactions_by_type_total') {
					var title = 'like / love ';
					this.insightsHeader.push(title);
				}else{
					this.insightsHeader.push(insights[i].title);
				}
				
			}
			//console.log(this.insightsHeader);
		}
	},
	filters:{
		stringSubstr: function (value){
			return value.slice(0,20);
		},
		isNullValue: function (value) {
			if (!value) {
				value = 0;
			}
			return value;
		}
	},
	computed: {
		setidTab:function(){
			return `${this.idTab}a`; 
		}
	}

});

const InsightsStrorys = Vue.component('storys',{
	'props': ['resourceId','idTab'],
	'template': '#insights-template',
	data: function () {
	    return {
	    	contentStorys: [],
			insightsStorys: [],
			storysHeader: [],
			loaded: false,
	    }
	},
	mounted(){
		this.fetchStorys();
	},
	methods:{
		fetchStorys(){
			axios.get(baseUrlApi + 'storys-insights'+ '?resourceId=' +this.resourceId)
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
		        
		    })
		},
		setHeaders(){
			var insights = this.contentStorys[0].wInsights;
			for (var i = 0; i < insights.length; i++) {
				this.storysHeader.push(insights[i].title);
			}
			//console.log(this.storysHeader);
		}
	},
	filters:{
		isNullValue: function (value) {
			if (!value) {
				value = 0;
			}
			return value;
		}
	},
});


var vue = new Vue({
	el: '#insights',
	data: function () {
	    return {
	    	resources: 0,
	    	loaded: false,
	    }
	},
	mounted(){
		this.fetchStatus();
	},
	methods:{
		fetchStatus(){
			axios.get(baseUrlApi + 'numbers-content')
		      .then((response) => {
		        this.resources = response.data;
		        if (this.resources.length) {
		        	this.loaded = true;
		        }

		    })
		},
	},
});

