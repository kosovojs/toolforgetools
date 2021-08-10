function one_param(rnd,id,val,min,max) {
	return `    <div class="input-group">
          <span class="input-group-btn">
              <button type="button" class="btn btn-danger btn-number"  data-type="minus" data-field="quant[`+rnd+`]">
                <span class="glyphicon glyphicon-minus"></span>
              </button>
          </span>
          <input type="text" name="quant[`+rnd+`]" class="form-control input-number" id="`+id+`" value="`+val+`" min="`+min+`" max="`+max+`">
          <span class="input-group-btn">
              <button type="button" class="btn btn-success btn-number" data-type="plus" data-field="quant[`+rnd+`]">
                  <span class="glyphicon glyphicon-plus"></span>
              </button>
          </span>
      </div>`
}


//$(document).on('click', '.btn-number', function(e){

$(document).on('click', '.btn-number', function(e){
    e.preventDefault();
    
    fieldName = $(this).attr('data-field');
    type      = $(this).attr('data-type');
    var input = $("input[name='"+fieldName+"']");
    var currentVal = parseInt(input.val());
    if (!isNaN(currentVal)) {
        if(type == 'minus') {
            
            if(currentVal > input.attr('min')) {
                input.val(currentVal - 1).change();
            } 
            if(parseInt(input.val()) == input.attr('min')) {
                $(this).attr('disabled', true);
            }

        } else if(type == 'plus') {

            if(currentVal < input.attr('max')) {
                input.val(currentVal + 1).change();
            }
            if(parseInt(input.val()) == input.attr('max')) {
                $(this).attr('disabled', true);
            }

        }
    } else {
        input.val(0);
    }
});


$(document).on('focusin', '.input-number', function(){
   $(this).data('oldValue', $(this).val());
});

$(document).on('change', '.input-number', function(){
    
    minValue =  parseInt($(this).attr('min'));
    maxValue =  parseInt($(this).attr('max'));
    valueCurrent = parseInt($(this).val());
    
    name = $(this).attr('name');
    if(valueCurrent >= minValue) {
        $(".btn-number[data-type='minus'][data-field='"+name+"']").removeAttr('disabled')
    } else {
        alert('Sorry, the minimum value was reached');
        $(this).val($(this).data('oldValue'));
    }
    if(valueCurrent <= maxValue) {
        $(".btn-number[data-type='plus'][data-field='"+name+"']").removeAttr('disabled')
    } else {
        alert('Sorry, the maximum value was reached');
        $(this).val($(this).data('oldValue'));
    }
    
    
    var sum = 0;
    $('.input-number').each(function() {
        sum += Number($(this).val());
    });
	$('#complete').html('Kopējais punktu skaits par šo rakstu: '+ sum.toString());
    // here, you have your sum
});

$(document).on('keydown', '.input-number', function(e){
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
             // Allow: Ctrl+A
            (e.keyCode == 65 && e.ctrlKey === true) || 
             // Allow: home, end, left, right
            (e.keyCode >= 35 && e.keyCode <= 39)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

function html_code(newO,sizeO,imagesO,dataO,titleO,authorO) {
	
	return `<form>
  <div class="row">
  <div class="form-group">
    <label>Raksts</label><input class="form-control" id="inputArticle" value="`+titleO+`">
  </div>
  </div>
  <div class="row">
  <div class="form-group">
    <label>Raksta autors</label><input class="form-control" id="inputAuthor" value="`+authorO+`"></div>
  </div>
  <div class="row">
  <div class="form-group">
    <label>Jauns raksts</label> <span class="top" title="Ja esi izveidojis rakstu, tad saņem 1 punktu par to"><span class="glyphicon glyphicon-question-sign"></span></span>
	
    `+one_param(0,"inputNew",newO,0,1)+`
  </div>
  </div>
  <div class="row">
  <div class="form-group">
    <label>Apjoms</label> <span class="top" title="Par katru pilno baitu  tūkstoti saņem 1 punktu"><span class="glyphicon glyphicon-question-sign"></span></span>
	
    `+one_param(1,"inputSize",sizeO,0,1000)+`
  </div>
  </div>
  <div class="row">
  <div class="form-group">
    <label>Attēli</label> <span class="top" title="Par pievienotajiem attēliem saņem 1 punktu"><span class="glyphicon glyphicon-question-sign"></span></span>
	
    `+one_param(2,"inputImages",imagesO,0,1)+`
  </div>
  </div>
  <div class="row">
  <div class="form-group">
    <label>Vikidati</label> <span class="top" title="Šeit tiek ieskaitīts vienīgi papildinājums apgalvojumu (un ārējo identifikatoru) sadaļā. Maksimālais punktu skaits: 1"><span class="glyphicon glyphicon-question-sign"></span></span>
	
    `+one_param(3,"inputWikidata",dataO,0,1)+`
  </div>
  </div>
</form>`
	
}
//

function save_data(id,olddata) {
	var data = {};
	
	if ($('#inputNew').val()!=olddata.newd)
		data.new = $('#inputNew').val();
	
	if ($('#inputSize').val()!=olddata.size)
		data.size = $('#inputSize').val();
	
	if ($('#inputImages').val()!=olddata.imaged)
		data.images = $('#inputImages').val();
	
	if ($('#inputWikidata').val()!=olddata.data)
		data.wikidata = $('#inputWikidata').val();
	
	if ($('#inputArticle').val()!=olddata.title)
		data.title = $('#inputArticle').val();
	
	if ($('#inputAuthor').val()!=olddata.author)
		data.author = $('#inputAuthor').val();
	
	console.log(data);
	
	if (Object.keys(data).length>0) {
		data.id = id;
		
		$.ajax({
        type: "post",
        url: "../oauth.php",
        dataType:"json",
		//action: 'new',
        data: {'data':data,'action':'edit_data_jury'},
})
	.done(function (response) {
		
		
			if(response.status === "good") {
                console.log('yes');
				alert( 'Dati saglabāti! Izmaiņas būs redzamas pēc lapas pārlādes.' );
				
            } else if(response.status === "fail") {
                console.log('no');
				alert( 'Netika mainīti dati. Atceries, ka vari labot tikai savus datus.' );
            }
		
        })
	.fail(function (XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
			console.log(XMLHttpRequest);
		});
		
		
	} else {
		alert('Nekas netika izmainīts');
	}
}
	
	

function main_action(data) {
$.ajax({
        type: "get",
        url: "../oauth.php",
        dataType:"json",
		//action: 'new',
        data: {'data':data,'action':'get_data_jury'},
})
	.done(function (response) {
            console.log('dsfsdfsdfsdf');
            //console.log(response);
            //    console.log('yes');
				var sfsdfd = html_code(response.newd,response.size,response.imaged,response.data,response.title,response.author);
				
				$('#idMyModal').html('<div class="modal" role=dialog id=myModal tabindex=-1 aria-labelledby=myModalLabel> <div class=modal-dialog role=document> <div class=modal-content> <div class=modal-header> <button type=button class=close data-dismiss=modal aria-label=Close><span aria-hidden=true>&times;</span></button> <h4 class=modal-title id=myModalLabel>Informācijas labošana</h4> </div> <div class=modal-body><br>'+response.title+'<br><br>'+ sfsdfd+ '</div> <div class=modal-footer> <button type=button class="btn btn-default" data-dismiss=modal>Close</button> <button id="save-changes" type=button class="btn btn-primary">Save changes</button> </div> </div> </div> </div>');// onclick="get_all_data()"
				$("#myModal").modal({backdrop: 'static'});
				
				
				
$(document).on('click', '#save-changes', function(){
	//main_action($(this).data('id'));
	//console.log(data);
	save_data(data,response);

});

				
				
				
        })
	.fail(function (XMLHttpRequest, textStatus, errorThrown) {
			console.log("Status: " + textStatus);
			console.log("Error: " + errorThrown);
			console.log(XMLHttpRequest);
		});
}