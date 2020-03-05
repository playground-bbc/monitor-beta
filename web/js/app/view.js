// flag to chart line
let loadedChart = false;

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
	  icon: 'warning',
	  showCancelButton: true,
	  confirmButtonColor: '#3085d6',
	  cancelButtonColor: '#d33',
	  confirmButtonText: 'Si, eliminar la Alerta!'
	}).then((result) => {
	  if (result.value) {
	    Swal.fire(
	      'Eliminada!',
	      '',
	      'success'
	    )
	    setTimeout(() => {  okCallback(); }, 4000);
	    
	  }
	})
};


/**
 * [componente que muestra el button report]
 * @param  {[count]} )   
 * template: '#view-button-report [description]
 * @return {[component]}           [component]
 */
const report_button = Vue.component('button-report',{
	props: ['count'],
	template: '#view-button-report',
	data: function () {
		return {
			isdisabled: true,
		}
	},
	mounted(){
		setInterval(function () {
			if(this.count > 0 && loadedChart){
				this.isdisabled = false;
			}
	    }.bind(this), 2000);
		
	},
	methods:{
		send(event){
			if(this.count > 0 && loadedChart){
				modalFinish(this.count,baseUrlView,id);
			}
			
		}
	}
});

/**
 * [indicador de status por cada red social]
 * template: '#status-alert' [description]
 * @return {[component]}           [component]
 */
const statusAlert = Vue.component('status-alert',{
	'template' : '#status-alert',
	'props': ['resourceids'],
	data: function () {
	    return {
	    	response:null,
	    	status: null,
	    	resourceId: this.resourceids,
	    	classColor:'status-indicator',
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchStatus();
	    }.bind(this), refreshTime);
	},
	methods:{
		fetchStatus(){
			axios.get(baseUrlApi + 'status-alert' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.data;
		    })
		},
	},
	computed:{
		colorClass(){
			var valueClass = 'status-indicator--yellow';
			if(this.response != undefined || this.response != null){
				var search_data_response = this.response.search_data;
				for(let propeties in search_data_response){
					var span = document.getElementById(search_data_response[propeties].resourceId);
					if(search_data_response[propeties].status == 'Finish'){
						span.className = 'status-indicator status-indicator--red';
					}else{
						span.className = 'status-indicator status-indicator--green';
					}
				}

			}
			
			return valueClass;
		}
	}

});

/**
 * [componente que muestra el total de menciones]
 * @param  {[count]} )   
 * template: '#view-total-mentions' [description]
 * @return {[component]}           [component]
 */
const count_mentions = Vue.component('total-mentions',{
	props: ['count','shares','likes','coments','likes_comments'],
	data: function () {
	    return {
	    }
	},
	template: '#view-total-mentions',
});

/**
 * [componente que muestra las cajas de cada red social]
 * template: '#view-box-sources' [description]
 * @return {[component]}           [component]
 */
const box_sources = Vue.component('box-sources',{
	template: '#view-box-sources',
	data: function(){
		return {
			loaded: false,
			response: null,
			counts: 0,
			isseven: false,
			column: null,
		}
	},
	mounted(){
		setInterval(function () {
			this.fetchStatus();
	    }.bind(this), refreshTime);
		
	},
	methods:{
		fetchStatus(){
			axios.get(baseUrlApi + 'box-sources-count' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.data;
		        this.counts = this.response.length;
		        this.loaded = true;
		    })
		},
		calcColumns(){
			if(this.counts == 7){
				this.isseven = true;
			}
			return columnsName[this.counts - 1];

		},
		getIcon(resourceName){
			return resourceIcons[resourceName];
		}
	},
	filters:{
		ensureRightPoints: function(value) {
			 if (!value) return '';
			 value = value.toString();
			 if(value.length > 12){
			 	value = value.slice(0,11);
			 	value = value.concat('...');
			 }
			 return value;
		}
	}
});


/**
 * [componente que muestra grafico de menciones x red social]
 * template: '#view-total-resources-chart' [description]
 * @return {[component]}           [component]
 */
const count_resources_chat = Vue.component('total-resources-chart',{
	template: '#view-total-resources-chart',
	data: function () {
	    return {
	    	alertId:id,
	    	response: [],
	    	loaded: false,
	    	dataTable: ['Red Social', 'Shares/Retweets', 'Likes Post', 'Likes','Total'],
	    	view:null,
	    	column: [0,
	    		1,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation" 
                },
                2,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" 
                },
                3,
                { 
                	calc: "stringify",
                    sourceColumn: 3,
                    type: "string",
                    role: "annotation" 
                },
                4,
                { 
                	calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" 
                },
               
            ],
            

            options: {
	          chart: {
	            title: '',
	            subtitle: '',
	          },
	          theme: 'material',
	          bars: 'vertical',
	          vAxis: {format: 'decimal'},
	          colors: ['#1b9e77', '#d95f02', '#7570b3','#2f1bad','#bf16ab','#b5d817'],
	        },
	    }
	},
	mounted(){
		this.response = [this.dataTable];
		// Load the Visualization API and the corechart package.
     	google.charts.load('current', {'packages':['corechart']});
		setInterval(function () {
			if(this.loaded){
				google.charts.setOnLoadCallback(this.drawColumnChart);	
			}
		   
	      this.fetchResourceCount();
	    }.bind(this), refreshTime);
		
	},
	methods: {
		fetchResourceCount(){
			axios
		      .get(baseUrlApi + 'count-sources-mentions' + '?alertId=' +this.alertId)
		      .then(response => {
		      	if(typeof this.response === 'object'){

		      		this.response.splice(1,response.data.data.length);
			      	for(let index in response.data.data){
			      		this.response.push(response.data.data[index]);
			      	}
		      		this.loaded = true;
		      	}

		      })
		      .catch(error => console.log(error))
		},
		drawColumnChart(){
			var data = google.visualization.arrayToDataTable(this.response);
			var view = new google.visualization.DataView(data);
			view.setColumns(this.column);
			var chart = new google.visualization.ColumnChart(document.getElementById("resources_chart_count"));

			google.visualization.events.addListener(chart, 'ready', function () {
	          data_chart['chart_bar_resources_count'] = chart.getImageURI();

	        });

		    var options = {
		        title: 'Gráfico de número de interacciones por red social',
		        vAxis: {format: 'decimal'},
		        width: 1200,
            	height: 400,
		        colors: ['#1b9e77', '#d95f02', '#7570b3','#2f1bad','#bf16ab'],
		        animation: {
		          startup: true,
		          duration: 1500,
		          easing: 'out',
		      },
		    }

			chart.draw(view, options);
		},

	}

});


/**
 * [componente que muestra grafico de post con mas menciones]
 * template: '#view-total-resources-chart' [description]
 * @return {[component]}           [component]
 */
const post_interations_chart = Vue.component('post-interation-chart',{
	template: '#view-post-mentions-chart',
	data: function () {
	    return {
	    	alertId:id,
	    	response: [],
	    	loaded: false,
	    	render: false,
	    	dataTable: ['Post Titulo', 'Share', 'Like Post','Likes Comments','Total','link'],
	    	view:null,
	    	column: [0,
	    		1,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation" 
                },
                2,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" 
                },
                3,
                { 
                	calc: "stringify",
                    sourceColumn: 3,
                    type: "string",
                    role: "annotation" 
                },
                4,
                { 
                	calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" 
                },
               
            ],
            

            options: {
	          chart: {
	            title: '',
	            subtitle: '',
	          },
	          theme: 'material',
	          bars: 'vertical',
	          vAxis: {format: 'decimal'},
	          colors: ['#1b9e77', '#d95f02', '#7570b3','#2f1bad','#bf16ab','#b5d817'],
	        },
	    }
	},
	mounted(){
		this.response = [this.dataTable];
		// Load the Visualization API and the corechart package.
     	google.charts.load('current', {'packages':['corechart']});
        
		setInterval(function () {
	      this.fetchResourceCount();
		   if(this.loaded){
		   	google.charts.setOnLoadCallback(this.drawColumnChart);
		   }
		   this.fetchResourceCount();	
	    }.bind(this), refreshTime);
		
	},
	methods: {
		fetchResourceCount(){
			axios
		      .get(baseUrlApi + 'top-post-interation' + '?alertId=' +this.alertId)
		      .then(response => {
		      	if(typeof this.response === 'object'){
					if(response.data.status){
						this.response.splice(1,response.data.data.length);
				      	for(let index in response.data.data){
				      		this.response.push(response.data.data[index]);
				      	}
			      		this.render = true;
			      		this.loaded = true;
					}		      		
		      	}

		      })
		      .catch(error => console.log(error))
		},
		drawColumnChart(){
			var data = google.visualization.arrayToDataTable(this.response);
			var view = new google.visualization.DataView(data);
			view.setColumns(this.column);
			var chart = new google.visualization.ColumnChart(document.getElementById("post_mentions"));

			google.visualization.events.addListener(chart, 'ready', function () {
	          data_chart['post_mentions'] = chart.getImageURI();

	        });

		    var options = {
		        title: 'Gráfico Post con mas interaciones',
		        vAxis: {format: 'decimal'},
		        width: 1200,
            	height: 400,
		        colors: ['#1b9e77', '#d95f02', '#7570b3','#2f1bad','#bf16ab'],
		    }

			chart.draw(view, options);
			addLink(data, 'post_mentions');
		},

	}

});

/**
 * [componente que muestra grafico de productos con mas menciones]
 * template: '#view-products-interations-chart' [description]
 * @return {[component]}           [component]
 */
const products_interations_chart = Vue.component('products-interations-chart',{
	template: '#view-products-interations-chart',
	data: function () {
	    return {
	    	alertId:id,
	    	response: [],
	    	loaded: true,
	    	dataTable: ['Producto', 'Shares', 'Like Post','Likes','Retweets','Likes Twitter','Total'],
	    	view:null,
	    	column: [0,
	    		1,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 1,
                    type: "string",
                    role: "annotation" 
                },
                2,
	    		{ 
                	calc: "stringify",
                    sourceColumn: 2,
                    type: "string",
                    role: "annotation" 
                },
                3,
                { 
                	calc: "stringify",
                    sourceColumn: 3,
                    type: "string",
                    role: "annotation" 
                },
                4,
                { 
                	calc: "stringify",
                    sourceColumn: 4,
                    type: "string",
                    role: "annotation" 
                },
                5,
                { 
                	calc: "stringify",
                    sourceColumn: 5,
                    type: "string",
                    role: "annotation" 
                },
                6,
                { 
                	calc: "stringify",
                    sourceColumn: 6,
                    type: "string",
                    role: "annotation" 
                },
               
            ],
	    }
	},
	mounted(){
		this.response = [this.dataTable];
		// Load the Visualization API and the corechart package.
     	google.charts.load('current', {'packages':['corechart']});
        this.fetchResourceCount();
		setInterval(function () {
		   google.charts.setOnLoadCallback(this.drawColumnChart);	
	      this.fetchResourceCount();
	    }.bind(this), refreshTime);
		
	},
	methods:{
		fetchResourceCount(){
			axios
		      .get(baseUrlApi + 'product-interation' + '?alertId=' +this.alertId)
		      .then(response => {
		      	if(typeof this.response === 'object'){
		      		this.response.splice(1,response.data.data.length);
		      		for(let index in response.data.data){
			      		this.response.push(response.data.data[index]);
			      	}
			      	//console.log(this.response);
		      		this.loaded = true;
		      	}

		      })
		      .catch(error => console.log(error))
		},
		drawColumnChart(){
			var data = google.visualization.arrayToDataTable(this.response);
			var view = new google.visualization.DataView(data);
			view.setColumns(this.column);
			var chart = new google.visualization.ColumnChart(document.getElementById("products-interation-chart"));

			google.visualization.events.addListener(chart, 'ready', function () {
	          data_chart['products_interations'] = chart.getImageURI();

	        });

		    var options = {
		        title: 'Gráfico de número de interacciones por productos',
		        vAxis: {format: 'decimal'},
		        //hAxis: {minValue: 50},
		        width: 1200,
            	height: 400,
		        colors: ['#1b9e77', '#d95f02', '#7570b3','#2f1bad','#bf16ab'],
		        animation: {
		          startup: true,
		          duration: 1500,
		          easing: 'out',
		      },

		    }

			chart.draw(view, options);

		}

	}
	
});


/**
 * [componente que muestra grafico de post por fecha (no terminado en el backend)]
 * template: '#view-total-resources-chart' [description]
 * @return {[component]}           [component]
 */
const count_resources_date_chat = Vue.component('count-date-resources-chart',{
	template: '#view-date-resources-chart',
	data: function () {
	    return {
	    	alertId:id,
	    	response: [],
	    	headers:[],
	    	loaded: false,
	    	dataTable: null,
	    	view:null,
	    }
	},
	mounted(){
		// Load the Visualization API and the corechart package.
	    google.charts.load('current', {'packages':['corechart','line']});
	    
		setInterval(function () {
	      this.fetchResourceCount();
		  if(this.loaded){
		  	google.charts.setOnLoadCallback(this.drawColumnChart);
		  }
		  

	    }.bind(this), refreshTime);	
	},
	methods: {
		fetchResourceCount(){
			axios
		      .get(baseUrlApi + 'mention-on-date' + '?alertId=' +this.alertId)
		      .then(response => {
		      	if(typeof this.response === 'object'){
		      		this.response = response.data.model;
		      		this.headers = response.data.resourceNames;
		      		this.loaded = true;
		      		
		      	}

		      })
		      .catch(error => console.log(error))
		},
		drawColumnChart(){
			

			var data = new google.visualization.DataTable();
			
			data.addColumn('string', 'Date');
      		
      		for (var i = 0; i < this.headers.length; i++) {
      			data.addColumn('number', this.headers[i]);
      		}
      		
      		data.addRows(this.response);
      		
      		var view = new google.visualization.DataView(data);

			var column = [0];

			for (var i = 0; i < this.headers.length; i++) {
				column.push(i +1)
				column.push({
					calc: "stringify",
			        sourceColumn: i + 1,
			        type: "string",
			        role: "annotation" 

				});
			}

			view.setColumns(column);
			var options = {
				title: 'Grafico total de registros por fecha y recurso',
				width: 1200,
            	height: 400,
		        vAxis:{title:'Cantidad',textStyle:{color: '#005500',fontSize: '12', paddingRight: '100',marginRight: '100'}},
   				hAxis: { title: 'Fechas', textStyle: { color: '#005500', fontSize: '12', paddingRight: '100', marginRight: '100'} },
		        series: {
		          1: {curveType: 'function'}
		        },
		        animation: {
		          startup: true,
		          duration: 1500,
		          easing: 'out',
		      	},
		    };
		    
		    

		    var chart = new google.visualization.AreaChart(document.getElementById('date-resources-chart'));

		    google.visualization.events.addListener(chart, 'ready', function () {
	          data_chart['date_resources'] = chart.getImageURI();

	        });
		    chart.draw(view, options);
    		
    		loadedChart = true;
		},

	}
});


/**
 * [tabla de menciones]
 * template: '#mentions-list' [description]
 * @return {[component]}           [component]
 */
const listMentions = Vue.component('list-mentions',{
	template: '#mentions-list',
	data: function () {
	    return {
	    	table:null
	    }
	},
	mounted(){
		var table = this.setDataTable();
		setInterval( function () {
		    table.ajax.reload();
		}, refreshTimeTable );
	},
	methods:{
		setDataTable(){
			return initSearchTable();
		}
	}
});


/**
 * [nuebe de palabras del diccionario]
 * template: '#mentions-list' [description]
 * @return {[component]}           [component]
 */
const cloudWords = Vue.component('cloud-words',{
    'template': '#cloud-words',
	data: function () {
	    return {
	    	response:null,
	    	loaded: false
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchWords();
	    }.bind(this), refreshTime);
		
		
	},
	methods:{
		fetchWords(){
			axios.get(baseUrlApi + 'list-words' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.wordsModel
		        if(this.response.length){
		        	this.loaded = true;
		        	var words = this.handlers(this.response);
		        	var some_words_with_same_weight =
					    $("#jqcloud").jQCloud(words, {
					      width: 1000,
      					  height: 350,
      					  delay: 50
					});
		        }
		        
		    })
		},
		reload(){
			var words = this.handlers(this.response);
			$('#jqcloud').jQCloud('update', words);
		},
		handlers(response){
			var words = response.map(function(r){
				r.handlers = {click: function() {
			      $("#list-mentions").DataTable().search(r.text).draw();
			      $('html, body').animate({
                    	scrollTop: $("#mentions-list").offset().top
                	}, 2000);
			    }};
			    r.html = {'class': 'pointer-jqcloud'};
			    return r;
			});
			return words;
		},
		scroll(){
			console.log(1);
		}

	},

});


/**
 * [tabla de las fechas de las mencines por x red social]
 * template: '#mentions-list' [description]
 * @return {[component]}           [component]
 */
const tableDate = Vue.component('resource-date-mentions',{
	template: '#resource-date-mentions',
	data: function () {
	    return {
	    	response:null,
	    	loaded: false
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchMentionsDate();
	    }.bind(this), refreshTime);
	},
	methods:{
		fetchMentionsDate(){
			axios.get(baseUrlApi + 'resource-on-date' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.resourceDateCount
		        if(typeof this.response === 'object'){
		      		this.loaded = true;
		      	}
		    })
		    .catch(error => console.log(error))

		},
		collapseValue(target="",index){
			return `${target}collapse${index + 1}`;
		}
	}
});

/**
 * [liswtado de emojis encontrados]
 * template: '#emojis-list' [description]
 * @return {[component]}           [component]
 */
const listEmojis = Vue.component('list-emojis',{
    'template' : '#emojis-list',
	data: function () {
	    return {
	    	response:null,
	    	loaded: false
	    }
	},
	mounted(){
		setInterval(function () {
	      this.fetchEmojis();
	    }.bind(this), refreshTime);
	},
	methods:{
		fetchEmojis(){
			axios.get(baseUrlApi + 'list-emojis' + '?alertId=' + id )
		      .then((response) => {
		        if(typeof response.data.data.length === 'undefined'){
		        	this.response = response.data.data;
		        	this.loaded = true;
		        }
		    })
		},
	},
});

/**
 * [modal de sweetalert]
 * template: '#modal-alert' [description]
 * @return {[component]}           [component]
 */

const sweetAlert = Vue.component('modal-alert',{
	'template' : '#modal-alert',
	data: function () {
	    return {
	    	alertId:id,
	    	response:null,
	    	isShowModal:false,
	    	count:0,
	    	flag:false
	    }
	},
	mounted(){

		setInterval(function () {
	      this.fetchCount();
	      if (this.count) {
	      	this.fetchStatus();
	      	if (this.isShowModal && !this.flag) {this.modal();}
	      }
	    }.bind(this), refreshTime);
	   
	},
	methods:{
		fetchCount (){
			axios.get(baseUrlApi + 'count-mentions' + '?alertId=' +this.alertId)
		      .then(response => {
		      	this.count = response.data.count;
		      })
		      .catch(error => console.log(error))

		},
		fetchStatus(){
			axios.get(baseUrlApi + 'status-alert' + '?alertId=' + id )
		      .then((response) => {
		        this.response = response.data.data;
		        if(this.response != undefined || this.response != null){
		        	this.setStatus();
		        }
		        
		    })
		},
		setStatus(){
			if(this.response != undefined || this.response != null){
				var resources = document.getElementsByClassName("label-info");
				var search_data = this.response.search_data;
				var statuses = Object.keys(search_data).filter(function(key) {
				   return search_data[key].status <= "Finish";
				});

				if(statuses.length == resources.length){
					this.isShowModal = true;
				}else{
					this.isShowModal = false;
				}


			}
		},
		modal(){
			this.flag = true;
			modalFinish(this.count,baseUrlView,id);

		}
	},

});



/**
 * [componente principal de vue]
 * template: '#mentions-list' [description]
 * @return {[component]}           [component]
 */
const vm = new Vue({
	el: '#alerts-view',
	data: {
		alertId:id,
		isData: false,
		count: 0,
		shares: 0,
		likes: 0,
		coments: 0,
		likes_comments: 0,
		resourcescount:[],
	},
	mounted(){
		/*if (!localStorage.init) {
			console.log("runnig init");
			this.init();
			localStorage.init = 1;
		}*/
		//this.init();
		setInterval(function () {
	      this.fetchIsData();
	    }.bind(this), refreshTime);
	    

	},
	methods: {
		fetchIsData(){
			axios
		      .get(baseUrlApi + 'count-mentions' + '?alertId=' +this.alertId)
		      .then(response => {
		      	this.count = response.data.count;
		      	this.shares = response.data.shares;
		      	this.likes = response.data.likes;
		      	this.coments = response.data.coments;
		      	this.likes_comments = response.data.likes_comments;
		      })
		      .catch(error => console.log(error))
		    if(this.count > 0){
		    	this.isData = true; 
		    } 
		},
		init(){
			axios
		      .get(baseUrlApi)
		      .then(response => (console.log("calling cronb tab")))
		      .catch(error => console.log(error))
		}
	},
	components:{
		report_button,
		count_mentions,
		box_sources,
		count_resources_chat,
		post_interations_chart,
		products_interations_chart,
		count_resources_date_chat,
		//count_resources,
		listMentions,
		cloudWords,
		tableDate,
		//listEmojis,
		sweetAlert,

	}	
});







