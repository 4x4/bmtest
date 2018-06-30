var map;
var carMarker;
var renderNearestPOI=null;
var carStop=false;
var routeOrigin=null;
var routeDestination=null;
var directionsDisplay;
var initCarMovement;
var carSuspend=false;
var beforeInitMap=false; 
var setCarRoute;



function initMap() {

if(!document.getElementById('map'))return;
	
google.maps.Polyline.prototype.getDistance = function(n) {
  var a = this.getPath(n),
    len = a.getLength(),
    dist = 0;
  for (var i = 0; i < len - 1; i++) {
    dist += google.maps.geometry.spherical.computeDistanceBetween(a.getAt(i), a.getAt(i + 1));
  }
  return dist / 1000;
}

	
	
    var routeOrigin = { lat: 41.85, lng: -87.65 };
    //var routeDestination = { lat: 39.79, lng: -86.14 };
	
	
	
     map = new google.maps.Map(document.getElementById('map'), {
        center: routeOrigin,
        zoom: 7,
		mapTypeId: google.maps.MapTypeId.HYBRID
    });
	
	directionsDisplay = new google.maps.DirectionsRenderer({
					map: map
				});
				
	beforeInitMap();
}
	
	
	

	
 function initCustomBox() {
	   var trafficLayer = new google.maps.TrafficLayer();
	   var trafficControlDiv = document.createElement('div');
	   var trafficConstruct = new TrafficConstruct(trafficControlDiv,
		  map, trafficLayer);
	 
	   trafficLayer.setMap(map);
	   trafficControlDiv.index = 1;
	   map.controls[google.maps.ControlPosition.TOP_LEFT].push(trafficControlDiv);
	}
	 
	function TrafficConstruct(controlDiv, map, trafficLayer) {
		// Set CSS for the control border
		var trafficUI = document.createElement('div');
		trafficUI.id = 'trafficButtonUI';
		trafficUI.title = 'Click to toggle traffic layer';
		controlDiv.appendChild(trafficUI);
	 
		// Set CSS for the control interior
		var trafficText = document.createElement('div');
		trafficText.id = 'trafficButtonText';
		trafficText.innerHTML = 'Traffic';
		trafficText.style.fontWeight = 'bold';
		trafficUI.appendChild(trafficText);
	 
		trafficUI.addEventListener('click', function(){
			var trafficState = trafficLayer.getMap();
			if ( trafficState != null){
				trafficLayer.setMap(null);
				trafficText.style.fontWeight = 'normal';
			} else {
				trafficLayer.setMap(map);
				trafficText.style.fontWeight = 'bold';
			}
		});
	}
	
	

				
	setCarRoute=function (origin,destination,callBack)
	{	
		routeOrigin=origin;
		routeDestination=destination;
					
		var request = {
			destination: destination,
			origin: origin,
			travelMode: 'DRIVING'
		};
	 	
		// Pass the directions request to the directions service.
		var directionsService = new google.maps.DirectionsService();
		
		directionsService.route(request, callBack);
		
	}
	
	
	

      var step = 1; // metres
      var tick = 100; // milliseconds
      var poly;
      var eol;
      var k=0;
      var stepnum=0;
      var speed = "";   
	  var marker;	  
	  var directionResponse;
	  var d=0;
	  var moveIntervalHandler=null;
	  
	  
     
	 function animateMoveCar() {    
		if (d>=poly.getPath()) return;
        
		
		if(!carStop)
		{
			var p = poly.getPath().getAt(d);			
			if (k++>=180/step) {
			  map.panTo(p);
			  k=0;
			}
			marker.setPosition(p);
		}
		d=d+step;		
      }

      

      
	  initCarMovement=function(response) {  
	  
				poly=getRoutePolyline(response)[0];				
				eol=poly.getDistance();
				map.setCenter(poly.getPath().getAt(0),17);
				directionResponse=response;
				  
				 if(carMarker){				 
					clearInterval(moveIntervalHandler);					
					carMarker.setMap(null);				 
					
				 }
					   
				carMarker=marker=new google.maps.Marker({
						position: poly.getPath().getAt(0),
						map: map,
						icon:'/project/templates/altabeltest.link/_ares/icons/bmwcar.png',
						title: 'Car'
					});
				
				d=0;
				moveIntervalHandler=setInterval(animateMoveCar,300);  // Allow time for the initial map display
      }

    
		function getRoutePolyline(response){
				var polyline = new google.maps.Polyline({
					  path: [],			
				});
				
				var bounds = new google.maps.LatLngBounds();
				
				var legs = response.routes[0].legs;
				for (i=0;i<legs.length;i++) {
				  var steps = legs[i].steps;
				  for (j=0;j<steps.length;j++) {
					var nextSegment = steps[j].path;
					for (k=0;k<nextSegment.length;k++) {
					  polyline.getPath().push(nextSegment[k]);
					  bounds.extend(nextSegment[k]);
					}
				  }
				}

				return [polyline,bounds];
			}
			



/*	var service = new google.maps.places.PlacesService(map);
			service.nearbySearch({
			  location: p,
			  radius: 500,
			  type: ['store',"political"]
			}, renderNearestPOI);
		*/	