//fix block height on scale>150%

var host='http://altabeltest.link/';
var currentPOIID=null;
var currentPreviewId=120637;
var poiStack=[];
var currentChildFavoriteId=120684;

$(window).resize(function() 
{
	var viewportWidth = $(window).width();
	var viewportHeight = $(window).height();

	setTimeout(function(){	

		if(viewportHeight<800){
			
			
			$('.block-scroll').css({'height':'400px'});	
			
			}
	},50);

});




function compileTemplate(tpl)
{
	var Template = $('#'+tpl).html();  
	return Handlebars.compile(Template);
}

function compileVar(perem){
		var returnVar = {};
		var keyBuf = null;
		for (key in perem) {
			 keyBuf = key.replace('.', "_");	
			 returnVar[keyBuf] = perem[key];
		};
		return returnVar;
	};
	
function compileVarDot(data,prefix){
		var returnVar = {};
		for (key in data) {			 
			 returnVar[prefix+'.'+key] = data[key];
		};
		return returnVar;
	};	

function scrollTo(el){	
		$('.block-scroll').animate({scrollTop: ($(el).offset().top)},500);
	}

	
function apiGetObject(id,callback)
{	
		$.ajax({
			type: "GET",
			url: host+'~api/json/catalog/getObject/id/'+id,             
			dataType : "json"
		}).done(callback);	
}

function apiGetChilds(id,callback)
{	
		$.ajax({
			type: "GET",
			url: host+'~api/json/catalog/getChilds/id/'+id,             
			dataType : "json"
		}).done(callback);	
}

function setPOISerchResultsNum(num)
{
	$('.block-report .countPois').text(num);
}



function overlayTopWindow(id)
{
	$('.overlay-top').addClass('is-active');
	$('#'+id).addClass('is-active');		
}

function overlayTopWindowClose(id)
{
	$('.overlay-top').removeClass('is-active');
	$('#'+id).removeClass('is-active');		
}

function overlayWindow(id)
{
	$('.overlay').addClass('is-active');
	$('#'+id).addClass('is-active');		
}

function overlayWindowClose(id){
	$('.overlay').removeClass('is-active');
	$('#'+id).removeClass('is-active');	
}

 
function getPOIStack(id)
{
	return poiStack[id];
}

function addPOIStack(id,poi)
{
	poiStack[id]=poi;
}

function removePOIStack(id)
{
	if(typeof poiStack[id]=='object') delete poiStack[id];
}

var POISearchResultTemplateCompiled = compileTemplate('POISearchResultTemplate');
		
function renderPOIstack()
{		
		var result=[];		 
		for(i in poiStack){
		 if (poiStack.hasOwnProperty(i)) {			 
			 result.push(compileVar(poiStack[i].params));
			}
		}
		setPOISerchResultsNum(result.length);
		$("#foundPous").html(POISearchResultTemplateCompiled({result:result}));
}


(function(){

	$('.updatePosition').click(function(){
			map.setZoom(17);
			map.panTo(carMarker.position);
			getGooglePlacesFromPosition();
			carStop=true;
		
	});
		
	
     var hash = window.location.hash.substr(1);
	 if(hash=='poi')$('.block-report').addClass('is-active');
	
	function getAndDisplayNewAddress(position) {

		 var geocoder = new google.maps.Geocoder();

		// Find out longitude and latitude
		geocoder.geocode({
				'latLng' : carMarker.position
		}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {

					 $('#display_of_street').val(results[0].formatted_address);

				 }
		});

	};
	
	function getGooglePlacesFromPosition()
		{
	 		var service = new google.maps.places.PlacesService(map);
				service.nearbySearch({
				  location: carMarker.position,
				  radius: 500,
				  type: ['store',"political"]
				}, renderNearestPOI);
		
		}
		
		
	  renderNearestPOI=function(results,status)
	   {	   	   
			if (status === google.maps.places.PlacesServiceStatus.OK) {
					createGooglePOI(results);
			  }
	  }

      function createGooglePOI(data) 
	  {
		var POIListItemTemplate = $('#POIListItemTemplate').html();
		var template = Handlebars.compile(POIListItemTemplate);
			
			result=[];
			
			for (var i = 0; i < data.length; i++) {
		      
				result.push({
					icon : data[i].icon,
					name : data[i].name,
					scope : data[i].scope,
					vicinity:data[i].vicinity,
					distance:google.maps.geometry.spherical.computeDistanceBetween (data[i].geometry.location, carMarker.position).toFixed(2)
				});
			
			}
			$(".POIListItems").html(template({result:result}))
	  }
	   
	
	$('#closeTheCall').click(function(event) {
		
		if($('#foundPous .pois-item').length>0)
		{
			overlayTopWindow('closeCallMore');
			$('#closeCallMore .countPois').text($('#foundPous .pois-item').length);
			
		}else{
			overlayTopWindow('closeCall');			
		}
		
		
	});
	
})();


(function(){

		$(document).on('click', '#sendSuccess',function(e){		
			e.preventDefault();			
			overlayTopWindow('success');			
			poiStack=[];
			$('#closeCallOpen').removeClass('disabled');
		});


	   

    function createNewPOIEvent(e)
	{
		e.preventDefault();
		createNewPOI();
	}
	
	//fdata=xoad.html.importForm('createPOIForm',data);
	
	
	function createNewPOI()
	{	
	    fdata=xoad.html.exportForm('createPOIForm');
		fdata.params=compileVarDot(fdata.POI,'POI');
		fdata.params.id=fdata.id;
		delete fdata.POI;
				
		fdata.ancestor=120639;
		fdata.basic="test1"+ Math.random();
		fdata.objType='_CATOBJ';
		fdata.params.PropertySetGroup=4022;			
		
		$.ajax({
			type: "POST",
			url: host+'~api/json/catalog/createNewObject',             
			contentType: "application/json; charset=utf-8",
			traditional: true,
			data:JSON.stringify(fdata)

		}).done(function(data){
			if(data.id){					
				$('#createPOIForm').find("input[type=text], textarea").val("");
				overlayWindowClose('createPoi');
				swal("Good job!", "POI saved", "success");

			}else{
				alert(data.error);
			}
		});
	
	}	 
	
	function saveEditedPOIEvent()
	{	
	    fdata=xoad.html.exportForm('editPOIForm');
		fdata.params=compileVarDot(fdata.POI,'POI');
		fdata.params.Name=fdata.Name;
		delete fdata.POI;
				

		$.ajax({
			type: "POST",
			url: host+'~api/json/catalog/setObjectParams/id/'+currentPOIID,             
			contentType: "application/json; charset=utf-8",
			traditional: true,
			data:JSON.stringify(fdata.params)

		}).done(function(data){
			if(data.result){					
				$('#createPOIForm').find("input[type=text], textarea").val("");
				overlayWindow('editPoi');				
				swal("Good job!", "POI saved", "success");

			}else{
				alert(data.error);
			}
		});
	
	}	
	
		$('.creatPoi-save-edited').click(saveEditedPOIEvent);		
		$('.creatPoi-save').click(createNewPOIEvent);
	
	
	var searchResultPOIItems=[];
	
	function showPOIsearchResult(data){
		
		var POISearchResultTemplate = $('#POIListItemTemplate').html();  // changed
		var template = Handlebars.compile(POISearchResultTemplate);
		var result=[];
		for(i in data){
		 if (data.hasOwnProperty(i)) {						
			data[i].params.id=data[i].id;		
		    searchResultPOIItems[data[i].id]=data[i];			
			result.push(compileVar(data[i].params));
			}
		}
		
		
		$(".pois-list").html(template({result:result})); // changed

		scrollTo("#foundPous");
	}

	
	var poiSearch=function(e)
	{
		 e.preventDefault();
		 var form=$(event.target).closest('form');
		 var poiName=form.find('.POILocationInput').val();
		 var param='Name';
		 
			if(form.parent()=='form-address')
			{
				param='POI.address';
			}
		
		if(poiName!='')
		{
			$.ajax({
				type: "GET",
				url: host+'~api/json/catalog.mf/searchPOI/param/'+param+'/value/'+poiName,             
				dataType : "json",
				}).done(function(data){
					if(typeof data.error!='undefined'){alert(data.error);}else{
						showPOIsearchResult(data);
					   
					}					
					
				});

		
		}else{
			alert('You must enter POI name');
		}
		
	
	}
	
	$('.searchPOI').click(poiSearch);
	
	$(document).on('click', '#foundPous .pois-item .flag-poi',function(e){
		
		var id=$(this).closest('.pois-item').data('id');	
		
		poi=getPOIStack(id);
		
		setCarRoute(routeOrigin,{ lat: parseFloat(poi.params['POI.lat']), lng: parseFloat(poi.params['POI.lon'])},
		function(response, status) {
			if (status == 'OK') {
				directionsDisplay.setDirections(response);
				initCarMovement(response);
			}
		});
		
	});	
	
	
	$(document).on('click', '.pois-list-item .flag-poi-test',function(e){
		
		var id=$(this).closest('.pois-item').data('id');	
		poi=searchResultPOIItems[id];
		
		setCarRoute(routeOrigin,{ lat: parseFloat(poi.params['POI.lat']), lng: parseFloat(poi.params['POI.lon'])},
		function(response, status) {
			if (status == 'OK') {
				directionsDisplay.setDirections(response);
				initCarMovement(response);
			}
		});
		
	});	
	
	
	$(document).on('click', '.edit-poi',function(e){		
		e.preventDefault();					
		var id=$(this).closest('.pois-item').data('id');		
		overlayWindow('editPoi');
		  
		  apiGetObject(id,
				function(data){			
						currentPOIID=id;
						xoad.html.importForm('editPOIForm',data.params);				
					});
		
	});

	var deleteFavoriteId=null;
	
	$('#removeFavorites .removeFavorites').click(function() {
			removePOIStack(deleteFavoriteId);
			renderPOIstack();
			overlayTopWindowClose('removeFavorites');
	});
	
		
	$(document).on('click', '.close-poi',function(event) {
		var id=$(this).closest('.pois-item').data('id');			
		deleteFavoriteId=id;		
		overlayTopWindow('removeFavorites');
				
	});
	
	
	$(document).on('click', '.pois-list-header .add-poi',function(e){
	
		var id=$(this).closest('.pois-list-item').data('id');		
		addPOIStack(id,searchResultPOIItems[id]);
		renderPOIstack();
	});		

	$(document).on('click', '.pois-list-name',function(e){		
		e.preventDefault();		
		var display = $(this).closest(".pois-list-header").css("display");
		if(display == "flex")
			$(this).closest(".pois-list-header").find(".pois-list-content").css("display", "none");
		else{
			$('.pois-list-item .pois-list-content').css("display", "none");
			$(this).closest(".pois-list-header").find(".pois-list-content").css("display", "flex");
		}
	});


})();


(function(){

	function createReport(e)
	{	
		$('.btn-color-red.ml-auto').removeClass('disabled');
		
		
		overlayTopWindow('reportSuccess');
	}
		
	$('.createReport-save').click(createReport);

})();



(function(){
	
	    apiGetObject(currentPreviewId,
				function(data){									
					initPage(data);		
					var	car=data.params['tovarbase.Car'];		
						car=JSON.parse(car);							
						apiGetObject(car[0],
							function(dataInfo){												
							initVehiclePage(dataInfo);
					});	
	});



	function initPage(call){	
		var templateScript = $('#headerTempl').html();
		var template = Handlebars.compile(templateScript);
		$("#firstBlock .accardion-content").html(template(compileVar(call.params)));
	};

		
	function initVehiclePage(call){	
		var templateScript = $('#vehicleTempl').html();
		var template = Handlebars.compile(templateScript);
		$(".vehicleInformation").html(template(compileVar(call.params)));

	};

})();





	


