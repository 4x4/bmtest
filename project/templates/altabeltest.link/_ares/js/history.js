function isObject(item) {
  return (item && typeof item === 'object' && !Array.isArray(item));
}

function mergeDeep(target, source) {
  let output = Object.assign({}, target);
  if (isObject(target) && isObject(source)) {
    Object.keys(source).forEach(key => {
      if (isObject(source[key])) {
        if (!(key in target))
          Object.assign(output, { [key]: source[key] });
        else
          output[key] = mergeDeep(target[key], source[key]);
      } else {
        Object.assign(output, { [key]: source[key] });
      }
    });
  }
  return output;
}

// function typeFilterTime(){
// 	return 
// }

$(document).ready(function(){

    var catalog=x4.getModule('catalogFront');
	
	var initialFilter={filter:{f:{ancestor:{"ancestor":"120745"}}}}
	var historyTemplateCompiled = compileTemplate('historyItem');	

	function getHistoryData(filter){			
				catalog.connector.execute({getObjectsByFilter:filter},function(d,a){						
					if(typeof a.result.objects!='undefined'){				
					let objects=a.result.objects;
					
					for(i in objects){
					 if (objects.hasOwnProperty(i)) {			 
					 
					
						
					 	 objects[i].history.startTime=(new Date(objects[i].history.startTime*1000)).toUTCString()
						 objects[i].history.stopTime=(new Date(objects[i].history.stopTime*1000)).toUTCString()
						 objects[i].history.timeFiled=(new Date(objects[i].history.timeFiled*1000)).toUTCString()
						}
					}
							
				$('#historyList').html(historyTemplateCompiled({'history':objects}));
			}					
		});
	}
	
	
	$('#historyVinSearch').keyup(function(){
		
		initialFilter=mergeDeep(initialFilter,{filter:{f:{like:{"history.VIN":$(this).val()}}}});
		getHistoryData(initialFilter);
		
	});
	
	
	$('#requestType').change(function(){
		
		initialFilter=mergeDeep(initialFilter,{filter:{f:{equal:{"history.requestType":$(this).val()}}}});
		getHistoryData(initialFilter);
		
	});
	
	$('#originalService').change(function(){
		
		initialFilter=mergeDeep(initialFilter,{filter:{f:{equal:{"history.originalService":$(this).val()}}}});
		getHistoryData(initialFilter);
		
	});
	

	$('#dropdownShow').click(function(){

		var type = $('#dropdown-content-start-time').find('.form-item.checkbox.checked').attr('value');
		var numb = null;
	
		switch(type) {
			case '24':  
			numb = Math.round(Date.now() / 1000 - 24 * 3600);
			initialFilter=mergeDeep(initialFilter,{filter:{f:{from:{"history.starttime":numb}}}});
			getHistoryData(initialFilter);
				break;
		
			case '48':  
				numb = Math.round(Date.now() / 1000 - 2 * 24 * 3600);
				initialFilter=mergeDeep(initialFilter,{filter:{f:{from:{"history.starttime":numb}}}});
				getHistoryData(initialFilter);
				break;

			case 'Infinity':  
				initialFilter=mergeDeep(initialFilter,{filter:{f:{from:{"history.starttime":0}}}});
				getHistoryData(initialFilter);
				break;
			case 'from':  
				var inputs1 = $('#dropdown-content-start-time').find('#poi-filter-input-from');
				var inputs2 = $('#dropdown-content-start-time').find('#poi-filter-input-to');

				if(inputs1 && inputs2){
					var inputFrom = Math.round(inputs1[0].valueAsNumber / 1000);
					var inputTo = Math.round(inputs2[0].valueAsNumber / 1000);

					initialFilter=mergeDeep(initialFilter,{filter:{f:{from:{"history.starttime":inputFrom}}}});
					initialFilter=mergeDeep(initialFilter,{filter:{f:{to:{"history.starttime":inputTo}}}});
					getHistoryData(initialFilter);
				}
				break;		

		}
		
	});

	getHistoryData(initialFilter);
	

});