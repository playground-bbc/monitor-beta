'use strict';



/**
 * [sleep time]
 * @return {[Promise]}       
 */
function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

  /* find the value in array */
function inArray(val, arr) {
  var i, n = arr.length;
  val = val.replace('…', ''); // remove ellipsis

  for (i = 0; i < n; ++i) {
    if (i in arr && 0 === arr[i].label.indexOf(val)) {
      return i;
    }
  }

  return -1;
}


/* add a link to each label */
function addLink(data, id) {
  var n, p, info = [],
    ns = 'http://www.w3.org/1999/xlink';

  // make an array for label and link.
  info = [];
  n = data.getNumberOfRows();
  for (let i = 0; i < n; ++i) {
    info.push({
      label: data.getValue(i, 0),
      link: data.getValue(i, 5)
    });
  }
  /*var element = document.querySelector('#' + id);
  console.log(element);*/
  
  $('#' + id).find('text').each(function(i, elm) {
        p = elm.parentNode;
        if ('g' === p.tagName.toLowerCase()) {
          i = inArray(elm.textContent, info);
          if (-1 !== i) {
            n = document.createElementNS('http://www.w3.org/2000/svg', 'a');
            n.setAttributeNS(ns, 'xlink:href', info[i].link);
            n.setAttributeNS(ns, 'title', info[i].label);
            n.setAttribute('target', '_blank');
            n.setAttribute('class', 'city-name');
            n.appendChild(p.removeChild(elm));
            p.appendChild(n);
            info.splice(i, 1); // for speeding up
          }
        }
      });
   
}


/**
 * [metodo inicializa lac tabla de menciones]
 */
function initSearchTable(){
	// Setup - add a text input to each footer cell
    $('#list-mentions thead th').each( function () {
        var title = $('#list-mentions tfoot th').eq( $(this).index() ).text();
        $(this).html(  '<input type="text" size="10" placeholder="Search '+title+'"  />'  );
    } );

    var table = $('#list-mentions').DataTable(tableConfigMentions);
    // Apply the search
    table.columns().eq( 0 ).each( function ( colIdx ) {
        $( 'input', table.column( colIdx ).header() ).on( 'keyup change', function () {
            table
                .column( colIdx )
                .search( this.value )
                .draw();
        } );
    } );

    return table;
}


function modalFinish(count, baseUrlView,id){

  const swalWithBootstrapButtons = Swal.mixin({
      customClass: {
        confirmButton: 'btn btn-info',
        cancelButton: 'btn btn-success'
      },
      buttonsStyling: true
    })

    var title = '<strong>Alerta Finalizada</strong>';
    var msg = (parseInt(count)) ? message_with_data : message_not_data; 
    var icon = (parseInt(count)) ? 'success' : 'warning'; 
    var is_continue = (parseInt(count)) ? true : false; 

    swalWithBootstrapButtons.fire({
      title: title ,
      icon: icon,
      html: msg,
      showCancelButton: is_continue,
      showConfirmButton: is_continue,
      confirmButtonText: 'Generar Informe!',
      cancelButtonText: 'Continuar!',
      reverseButtons: true,
      footer: '<a class="btn btn-dark" href= '+ baseUrlView + 'update?id='+ id + '&fresh=true' +'>update the alert?</a>'
    }).then(function(result){
        if(result.value){
          axios.post(baseUrlDocument + 'document' , {
              chart_bar_resources_count: data_chart['chart_bar_resources_count'],
              post_mentions: data_chart['post_mentions'],
              products_interations: data_chart['products_interations'],
              date_resources: data_chart['date_resources'],
              alertId: id,
          })
          .then(function (response) {
            var link = document.createElement('a');
            link.href = origin + response.data.data;
            link.download = response.data.filename;
            link.dispatchEvent(new MouseEvent('click'));
            
              
          })
          .catch(function (error) {
             console.log(error);
          });
        }
       // console.log(result)
    });

}
