'use strict'

// A $( document ).ready() block.
$( document ).ready(function() {
    var options_select_resourcesId = $('#topic_resourcesId').find(':selected');
    var options_select = [];
    for (var i = 0; i < options_select_resourcesId.length; i++) {
    	options_select.push(options_select_resourcesId[i].label);
    }
    
    if (options_select.indexOf("Twitter") == -1) {
    	$("#resourceId_row").attr("class", "col-md-6");
    	$("#locationId_row").hide();
    	$("#dictionaryId_row").attr("class", "col-md-6");
    }else{
    	$("#resourceId_row").attr("class", "col-md-4");
    	$("#locationId_row").show().attr("class", "col-md-4");
    	$("#dictionaryId_row").attr("class", "col-md-4");
    }
});


/**
 * [event when selecting select2 of urls: adding web page to select2 resourceID]
 */
$('#topic_urls').on('select2:select', function (e) {
    var selectId = 'topic_resourcesId';
    var data = {
        id: '2',
        text: 'Paginas Webs'
    };
    add_options_select(selectId,data);
    
});

/**
 * [event when unselecting select2 of resourceId: if resource is web page clean the select web urls]
 */
$('#topic_resourcesId').on('select2:unselecting', function (e) {
    var resource = e.params.args.data;
    if(resource.text == "Paginas Webs"){
    	var urls = $('#topic_urls')
    	urls.val(null).trigger('change'); // Select the option with a value of '1'
    }

    if (resource.text == "Twitter") {
    	$("#resourceId_row").attr("class", "col-md-6");
    	$("#locationId_row").hide().attr("class", "");
    	$("#dictionaryId_row").attr("class", "col-md-6");
    }

});

$('#topic_resourcesId').on('select2:select', function (e) {
    var resource = e.params.data.text;
    if (resource == "Twitter") {
    	$("#resourceId_row").attr("class", "col-md-4");
    	$("#locationId_row").show().attr("class", "col-md-4");
    	$("#dictionaryId_row").attr("class", "col-md-4");
        var selectId = 'topic_locationId';
        var data = {
            id: '1',
            text: 'Chile'
        };
        add_options_select(selectId,data);
    }

});



/**
 * [add_options_select adding data to select2]
 * @param  {[type]} string [id to select2]
 * @param  {[type]} obj [data to select2]
 */
function add_options_select(selectId,data) {
	var social = $('#'+selectId);
	var current_values = social.val();

    // Set the value, creating a new option if necessary
    if (social.find('option[value=' + data.id +']').length) {
        current_values.push(data.id);
        social.val(current_values).trigger('change');
    } else { 
        // Create a DOM Option and pre-select by default
        var newOption = new Option(data.text, data.id, true, true);
        // Append it to the select
        social.append(newOption).trigger('change');
    }
}