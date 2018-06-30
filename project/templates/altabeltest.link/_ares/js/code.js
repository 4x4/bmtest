//fix block height on scale>150%
var host = 'http://159.69.40.171/';

var currentPOIID = null;
var currentPreviewId = 120637;
var poiStack = [];
var currentChildFavoriteId = 120684;
var searchResultPOIItems = [];
var callFlow = {
    poiSend: false,
    callClosed: false,
    reportSaved: false,
    reportFiled: false
};

$('#poiResultContainer').hide();



$(window).resize(function() {
    var viewportWidth = $(window).width();
    var viewportHeight = $(window).height();

    setTimeout(function() {

        if (viewportHeight < 800) {


            $('.block-scroll').css({
                'height': '400px'
            });

        }
    }, 50);

});

var POIListItemTemplate = $('#POIListItemTemplate').html(); // changed
var POIListItemTemplateCompiled = Handlebars.compile(POIListItemTemplate);

currentPreviewId = document.location.href.substr(document.location.href.lastIndexOf('/') + 1);
if(currentPreviewId)
{	
	if(!isNaN(parseInt(currentPreviewId))){
		window.localStorage.setItem("currentPreviewId", parseInt(currentPreviewId));
	}
}

function flowReducer() {

    if (callFlow.poiSend) {
        poiStack = [];
        renderPOIstack();
        $('.closeCallOpen').addClass('disabled');

    }

    if (callFlow.callClosed) {
        $('.createReport-save').removeClass('disabled');
        $('#closeTheCall,#closeCallOpen').addClass('disabled');
        $('.block-scroll .tabs .tabs-title a[href="#report"]').trigger('click');
    }

    if (callFlow.reportSaved) {
        $('#fileReport').removeClass('disabled');
        $('#closeCallOpen').addClass('disabled');
        $('.createReport-save').addClass('disabled');

    }

    if (callFlow.reportFiled) {
        $('#fileReport').addClass('disabled');

    }


}

function setFlow(obj) {
    var z = Object.assign(callFlow, obj);
    console.log(z);
    flowReducer();
    return z;
}

function getFlow() {
    return callFlow;
}

function convertObjectToArr(obj) {
		var arr = [];
		for (var prop in obj) {
			if (obj.hasOwnProperty(prop)) {
				arr.push(obj[prop]);
			}
		}
		
		return arr; // returns array
	}
	
function compileTemplate(tpl) {
    var Template = $('#' + tpl).html();
    return Handlebars.compile(Template);
}


function compileVar(perem) {
    var returnVar = {};
    var keyBuf = null;
    for (key in perem) {
        keyBuf = key.replace('.', "_");
        returnVar[keyBuf] = perem[key];
    };
    return returnVar;
};

function compileVarDot(data, prefix) {
    var returnVar = {};
    for (key in data) {
        returnVar[prefix + '.' + key] = data[key];
    };
    return returnVar;
};

function scrollTo(el) {
    $('.block-scroll').animate({
        scrollTop: ($(el).offset().top)
    }, 500);
}


function apiGetObject(id, callback) {
    $.ajax({
        type: "GET",
        url: host + '~api/json/catalog/getObject/id/' + id,
        dataType: "json"
    }).done(callback);
}

function apiGetChilds(id, callback) {
    $.ajax({
        type: "GET",
        url: host + '~api/json/catalog/getChilds/id/' + id,
        dataType: "json"
    }).done(callback);
}

function setPOISerchResultsNum(num) {
    $('.block-report .countPois').text(num);
}

function setPOISearchFreeNumSlots(num) {
    $('.block-report .freePois').text(num);
}

function overlayTopWindow(id) {
    $('.overlay-top').addClass('is-active');
    $('#' + id).addClass('is-active');
}

function overlayTopWindowClose(id) {
    $('.overlay-top').removeClass('is-active');
    $('#' + id).removeClass('is-active');
}

function overlayWindow(id) {
    $('.overlay').addClass('is-active');
    $('#' + id).addClass('is-active');
}

function overlayWindowClose(id) {
    $('.overlay').removeClass('is-active');
    $('#' + id).removeClass('is-active');
}

function getPOINumber(id) {
    var k = 0;
    poiStack.forEach(function(element) {
        k++
    });
    return k;
}

function addPOIStack(id, poi) {
    poiStack[id] = poi;
}

function getPOIStack(id) {
    return poiStack[id];
}


function removePOIStack(id) {
    if (typeof poiStack[id] == 'object') delete poiStack[id];
}

var POISearchResultTemplateCompiled = compileTemplate('POISearchResultTemplate');

function renderPOIstack() {
    var result = [];
    for (i in poiStack) {
        if (poiStack.hasOwnProperty(i)) {
            result.push(compileVar(poiStack[i].params));
        }
    }
    setPOISerchResultsNum(result.length);
    setPOISearchFreeNumSlots(5 - result.length);
    $("#foundPous").html(POISearchResultTemplateCompiled({
        result: result
    }));

    if (result.length == 0) $('#sendSuccess').addClass('disabled');

}

function renderSearchResultStack(stockResult) {
    var result = [];

	if(!stockResult){stockResult=searchResultPOIItems;}
	
    for (i in stockResult) {
        if (stockResult.hasOwnProperty(i)) {
            if (!stockResult[i].disabled) {
                result.push(compileVar(stockResult[i].params));
            }

        }
    }

    $(".POIListItems").html(POIListItemTemplateCompiled({
        result: result
    })); 
    decreaseItemReview();
	$('#poiResultContainer').show(200);
     scrollTo("#foundPous");
}

function getByAdress(address,innerFunc){
	
	var geocoder = new google.maps.Geocoder();
	
		geocoder.geocode( { 'address': address}, function(results, status) 
		{

			if (status == google.maps.GeocoderStatus.OK) 
			{
				var latitude = results[0].geometry.location.lat();
				var longitude = results[0].geometry.location.lng();
				innerFunc(latitude,longitude);
			} 
		}); 
	
}

function getGooglePlacesFromPosition(query,location) {

	var enableNearby=false;
	if(!location){		
		location=carMarker.position;
	}else{
		enableNearby=true;
	}
    queryObject = {
        location: location,
        radius: 50,
       // type: ['store', "art_gallery", "cafe"]
    }

    if (query) {
        queryObject = Object.assign(queryObject, {
            query: query
        });
    }
	
	
	
    var service = new google.maps.places.PlacesService(map);
    if(enableNearby){
			service.nearbySearch(queryObject, renderNearestPOI);
		}else{
			service.textSearch(queryObject, renderNearestPOI);
	}

}


renderNearestPOI = function(results, status) {

	
    if (status === google.maps.places.PlacesServiceStatus.OK) {
        createGooglePOI(results);
    }
    
	
		var poi=Object.assign({},searchResultPOIItems);		
		
		poi=convertObjectToArr(poi);
		poi = poi.slice(0);	    
		poi=sortByDistance(poi);
		renderSearchResultStack(poi);
	

}

function createGooglePOI(data) {
    var POIListItemTemplate = $('#POIListItemTemplate').html();
    var template = Handlebars.compile(POIListItemTemplate);

    result = [];

    for (var i = 0; i < data.length; i++) {
	
	
        var item = {
            icon: data[i].icon,
			id:i,
            Name: data[i].name,
            scope: 'GOOGLE',
			rating:data[i].rating,
			lat:data[i].geometry.location.lat(),			
			lon:data[i].geometry.location.lng(),
            vicinity: data[i].vicinity,
            distance: (google.maps.geometry.spherical.computeDistanceBetween(data[i].geometry.location, carMarker.position)/1000).toFixed(2)
        };
		
		item['POI.lat']=item.lat;
		item['POI.lon']=item.lon;
		item['POI.address']=item.vicinity;
		
		createPOImarker(data[i].geometry.location,data[i].name);
        searchResultPOIItems[i] = {params:item};
    }

		map.setZoom(15);
        map.panTo(carMarker.position);        
        
}

function createPOImarker(position,title){
			

        var infowindow = new google.maps.InfoWindow({
          content: title,
          maxWidth: 150
        });
		 
		 
		var	marker=new google.maps.Marker({
						position: position,
						map: map,	
						title: title,
						animation: google.maps.Animation.DROP						
					});
					
	  marker.addListener('click', function() {
          infowindow.open(map, marker);
        });
}

function getCountObjProps(obj){
	var count = 0;
	for (key in obj) {
		if(obj.hasOwnProperty(key)){
			count++;
		}
	}
	return count;
}


function sortByDistance(poi){
		 
		 poi.sort(function(a,b) {
				var x = parseFloat(a.params.distance);
				var y = parseFloat(b.params.distance);
				return x-y;
			});
		
		return poi;
	 }
	 
	 

(function() {


	$('#serviceRequestMenuItem').click(function(e){
		e.preventDefault();
		document.location.href=host+'catalog/'+window.localStorage.getItem("currentPreviewId");
		
	});
	
    $('.updatePosition').click(function() {
        map.setZoom(17);
        map.panTo(carMarker.position);
        getGooglePlacesFromPosition();
        carStop = true;

    });

	

	

	$('#sortPoiSelect').change(function(){
		
		var poi=Object.assign({},searchResultPOIItems);		
		
		poi=convertObjectToArr(poi);
		poi = poi.slice(0);
	    
		
		if($(this).val()=='Name'){			
			poi.sort(function(a,b) {
				var x = a.params.Name.toLowerCase();
				var y = b.params.Name.toLowerCase();
				return x < y ? -1 : x > y ? 1 : 0;
			});
		}
		
		if($(this).val()=='Provider'){			
			poi.sort(function(a,b) {
				var x = a.params.scope.toLowerCase();
				var y = b.params.scope.toLowerCase();
				return x < y ? -1 : x > y ? 1 : 0;
			});
        }
	
		if($(this).val()=='Distance'){		
			poi=sortByDistance(poi);
		}
		
		renderSearchResultStack(poi);
		
	});
	
    var hash = window.location.hash.substr(1);
    if (hash == 'poi') $('.block-report').addClass('is-active');

    function getAndDisplayNewAddress(position) {

        var geocoder = new google.maps.Geocoder();

        // Find out longitude and latitude
        geocoder.geocode({
            'latLng': carMarker.position
        }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {

                $('#display_of_street').val(results[0].formatted_address);

            }
        });

    };




    $('#closeTheCall,#closeCallOpen').click(function(e) {

        e.preventDefault();

        if (getPOINumber() > 0) {
            overlayTopWindow('closeCallMore');
            $('#closeCallMore .countPois').text(getPOINumber());

        } else {
            overlayTopWindow('closeCall');
        }

    });


    $('#closeCallMoreBtn').click(function() {

        setFlow({
            poiSend: true,
            callClosed: true
        });
        flowReducer();
    });

    $('#closeCallMore .modal-cancel').click(function() {

        setFlow({
            callClosed: true
        });
    });


})();


(function() {

    $(document).on('click', '#sendSuccess', function(e) {
        e.preventDefault();
        overlayTopWindow('success');
        setFlow({
            poiSend: true
        });

    });


    $('#fileReport').click(function(event) {

        overlayTopWindow('reportSuccess');
        setFlow({
            reportFiled: true
        });
        flowReducer();
    });


    function createNewPOIEvent(e) {
        e.preventDefault();
        createNewPOI(e);
    }

    //fdata=xoad.html.importForm('createPOIForm',data);


    function createNewPOI(e) {
		
		selected=$('#creatPoi .tabs-content .is-active');
        fdata = xoad.html.exportForm('createPOIForm'+selected.data('handle'));
		
        fdata.params = compileVarDot(fdata.POI, 'POI');
        fdata.params.Name = fdata.Name;
        delete fdata.POI;

        fdata.ancestor = 120639;
        fdata.basic = "test1" + Math.random();
        fdata.objType = '_CATOBJ';
        fdata.params.PropertySetGroup = 4022;

        $.ajax({
            type: "POST",
            url: host + '~api/json/catalog/createNewObject',
            contentType: "application/json; charset=utf-8",
            traditional: true,
            data: JSON.stringify(fdata)

        }).done(function(data) {
            if (data.id) {
                $('#createPOIForm').find("input[type=text], textarea").val("");
                overlayWindowClose('creatPoi');

                apiGetObject(data.id, function(datax) {
                    datax.params.id = datax.id;
                    addPOIStack(datax.id, datax);
                    renderPOIstack();
                });

            } else {
                alert(data.error);
            }
        });

    }

    function saveEditedPOIEvent() {
        fdata = xoad.html.exportForm('editPOIForm');
        fdata.params = compileVarDot(fdata.POI, 'POI');
        fdata.params.Name = fdata.Name;
        delete fdata.POI;


        $.ajax({
            type: "POST",
            url: host + '~api/json/catalog/setObjectParams/id/' + currentPOIID,
            contentType: "application/json; charset=utf-8",
            traditional: true,
            data: JSON.stringify(fdata.params)

        }).done(function(data) {
            if (data.result) {
                //$('#createPOIForm').find("input[type=text], textarea").val("");
                overlayWindow('editPoi');
                overlayWindowClose('editPoi');
            } else {
                alert(data.error);
            }
        });

    }

    $('.creatPoi-save-edited').click(saveEditedPOIEvent);
    $('.creatPoi-save').click(createNewPOIEvent);





    function showPOIsearchResult(data) {
        var result = [];
        for (i in data) {
            if (data.hasOwnProperty(i)) {

                data[i].params.id = data[i].id;

                if (data[i].params['POI.lat']) {

                    data[i].params.distance = google.maps.geometry.spherical.computeDistanceBetween(new google.maps.LatLng(data[i].params['POI.lat'], data[i].params['POI.lon']), carMarker.position);
                    data[i].params.distance = (data[i].params.distance / 1000).toFixed(2);

                }

                data[i].params.scope = 'BMW';
                data[i].disabled = 0;

                searchResultPOIItems[data[i].id] = data[i];
                //result.push(compileVar(data[i].params));
            }

        }

    }

    var dbSearchResult = null;

    var poiSearch = function(e) {
        e.preventDefault();
        var form = $(event.target).closest('form');
        var poiName = form.find('.POILocationInput').val();
        var param = 'Name';
		searchResultPOIItems=[];
        if (form.parent() == 'form-address') {
            param = 'POI.address';
        }
		
		
		
        if (poiName != '') {
            $.ajax({
                type: "GET",
                url: host + '~api/json/catalog.mf/searchPOI/param/' + param + '/value/' + poiName,
                dataType: "json",
            }).done(function(data) {
                if (typeof data.error != 'undefined') {
                    console.log(data.error);
                } else {
					
                    showPOIsearchResult(data);
                }
				carStop = true;
				
				getGooglePlacesFromPosition(poiName);

            });


        } else {
            alert('You must enter POI query');
        }


    }

	addressPoiSearch=function(val){
		   var searchResultPOIItems=[];			
		    
			getByAdress(val,function(lat,lon){				
					getGooglePlacesFromPosition(val,
						new google.maps.LatLng(lat,lon)
					);
			});
	}
	
	clickLogicPoiTabs=function(e){
			
		a=$('.poiTabsSelector .is-active').find('a');
		
		if(a.attr('href')=='#form-address'){
			addressPoiSearch($('.POILocationInputAddress').val());
		}else{
			poiSearch(e);			
		}
	}
	
    $('.searchPOI').click(clickLogicPoiTabs);

    $('.POILocationInput').keypress(function(e) {
        if (e.which == 13) {
            poiSearch(e);
            return false;
        }
    });
	
	 $('.POILocationInputAddress').keypress(function(e) {
		 
        if (e.which == 13) {           		  
		    addressPoiSearch($(this).val());
            return false;
        }
    });

	

    $(document).on('click', '#foundPous .pois-item .flag-poi', function(e) {

        var id = $(this).closest('.pois-item').data('id');

        poi = getPOIStack(id);

        setCarRoute(carMarker.position, {
                lat: parseFloat(poi.params['POI.lat']),
                lng: parseFloat(poi.params['POI.lon'])
            },
            function(response, status) {
                if (status == 'OK') {
                    directionsDisplay.setDirections(response);

                    initCarMovement(response);
                }
            });

    });



    $(document).on('click', '.pois-list-header .way-poi', function(e) {

        e.preventDefault();
        var id = $(this).closest('.pois-list-item').data('id');

        poi = searchResultPOIItems[id];

        setCarRoute({
                lat: parseFloat(poi.params['POI.lat']),
                lng: parseFloat(poi.params['POI.lon'])
            }, carMarker.position,
            function(response, status) {
                if (status == 'OK') {
                    directionsDisplay.setDirections(response);
                    initCarMovement(response);
                }
            });

    });

    $(document).on('click', '.pois-list-header .flag-poi-test', function(e) {

        e.preventDefault();
        var id = $(this).closest('.pois-list-item').data('id');

        poi = searchResultPOIItems[id];

        setCarRoute(carMarker.position, {
                lat: parseFloat(poi.params['POI.lat']),
                lng: parseFloat(poi.params['POI.lon'])
            },
            function(response, status) {
                if (status == 'OK') {
                    directionsDisplay.setDirections(response);
                    initCarMovement(response);
                }
            });

    });

    $(document).on('click', '.pois-list-header .car-poi', function(e) {
        e.preventDefault();
        overlayTopWindow('successCar');
    });




    $(document).on('click', '.edit-poi', function(e) {
        e.preventDefault();
        var id = $(this).closest('.pois-item').data('id');
        overlayWindow('editPoi');

		var  poi = getPOIStack(id);	
		
		if(poi.params.scope=='GOOGLE'){						
			currentPOIID = id;
			xoad.html.importForm('editPOIForm', poi.params);
		}else{
			
			apiGetObject(id,
				function(data) {
					currentPOIID = id;
					xoad.html.importForm('editPOIForm', data.params);
				});
		}

    });

    var deleteFavoriteId = null;

    $('#removeFavorites .removeFavorites').click(function() {
        removePOIStack(deleteFavoriteId);
        renderPOIstack();
        overlayTopWindowClose('removeFavorites');
    });


    $(document).on('click', '.close-poi', function(event) {
        var id = $(this).closest('.pois-item').data('id');
        deleteFavoriteId = id;
        overlayTopWindow('removeFavorites');

    });


    $(document).on('click', '.pois-list-header .add-poi', function(e) {

        var id = $(this).closest('.pois-list-item').data('id');
		
		if(getCountObjProps(poiStack) < 5){
			addPOIStack(id, searchResultPOIItems[id]);

			renderPOIstack();
        	overlayTopWindow('poiAdded');
		}
    });



    $(document).on('click', '.pois-list-col', function(e) {
        e.preventDefault();

        var display = $(this).closest(".pois-list-header").next().css("display");

        if (display == "block"){
            $(this).closest(".pois-list-header").next().hide();
        }
        else {
            $(this).closest(".pois-list-header").find(".pois-list-content").css("display", "flex");
            $(this).closest(".pois-list-header").next().show();
        }
    });


    $('#poiFilter').on('keyup', function(e) {

        val = $(this).val();

        val = val.toLowerCase();
        for (i in searchResultPOIItems) {
            if (searchResultPOIItems.hasOwnProperty(i)) {

                if (searchResultPOIItems[i].params.Name.toLowerCase().indexOf(val) > -1) {
                    searchResultPOIItems[i].disabled = 0;
                } else {
                    searchResultPOIItems[i].disabled = 1;
                }
            }
        }

        renderSearchResultStack();

    });

})();


beforeInitMap = function() {

    apiGetObject(currentPreviewId,
        function(data) {

            routeOrigin = new google.maps.LatLng(data.params['tovarbase.lat'], data.params['tovarbase.lon']);
            routeDestination = new google.maps.LatLng(data.params['destination.lat'], data.params['destination.lon']);

            setCarRoute(routeOrigin, routeDestination,
                function(response, status) {
                    if (status == 'OK') {
                        directionsDisplay.setDirections(response);
                        initCustomBox();
                        initCarMovement(response);
                    }
                });




            setTimeout(function() {
                initPage(data);
            }, 300);

            var car = data.params['tovarbase.Car'];
            car = JSON.parse(car);
            apiGetObject(car[0],
                function(dataInfo) {
                    initVehiclePage(dataInfo);
                });
        });


}


function initPage(call) {



    var templateScript = $('#headerTempl').html();
    var template = Handlebars.compile(templateScript);
    $("#firstBlock .accardion-content").html(template(compileVar(call.params)));

    var destinationTemplate = $('#destinationTemplate').html();
    var destinationTemplateCompiled = Handlebars.compile(destinationTemplate);


    call.params['destination.range'] = (google.maps.geometry.spherical.computeDistanceBetween(routeOrigin, routeDestination) / 1000).toFixed(2);
    call.params['destination.distance'] = call.params['destination.range'];

    $('.now-posiotion').val(call.params['tovarbase.position']);
     $('.now-destination').val(call.params['destination.destination']);
     $('#destination').val(call.params['destination.destination']);
	$('.callTypeText').text(call.params['service.service']);

    var service = new google.maps.DistanceMatrixService();
    service.getDistanceMatrix({
        origins: [routeOrigin],
        destinations: [routeDestination],
        travelMode: 'DRIVING',
        drivingOptions: {
            departureTime: new Date(Date.now()), // for the time N milliseconds from now.
            trafficModel: 'optimistic'
        }
    }, function callback(response, status) {
        call.params['destination.minutes'] = response.rows[0].elements[0].duration.text;
        $("#secondBlock").html(destinationTemplateCompiled(compileVar(call.params)));
    });



};


function initVehiclePage(call) {

    var templateScript = $('#vehicleTempl').html();



    var template = Handlebars.compile(templateScript);
    $(".vehicleInformation").html(template(compileVar(call.params)));

};