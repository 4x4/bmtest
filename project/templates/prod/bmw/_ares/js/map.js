var map;
var carMarker;
var renderNearestPOI=null;
var carStop=false;
var routeOrigin=null;
var routeDestination=null;
var directionsDisplay;
var initCarMovement;

var setCarRoute
function initMap() {

google.maps.Polyline.prototype.getDistance = function(n) {
  var a = this.getPath(n),
    len = a.getLength(),
    dist = 0;
  for (var i = 0; i < len - 1; i++) {
    dist += google.maps.geometry.spherical.computeDistanceBetween(a.getAt(i), a.getAt(i + 1));
  }
  return dist / 1000;
}


    var chicago = { lat: 41.85, lng: -87.65 };
    var indianapolis = { lat: 39.79, lng: -86.14 };

     map = new google.maps.Map(document.getElementById('map'), {
        center: chicago,
        zoom: 7,
		mapTypeId: google.maps.MapTypeId.HYBRID
    });

	
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
	
	directionsDisplay = new google.maps.DirectionsRenderer({
					map: map
				});

				
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
	
	setCarRoute(chicago,indianapolis,
		function(response, status) {
			if (status == 'OK') {
				directionsDisplay.setDirections(response);
				initCarMovement(response);
			}
		});
	
    
	
	initCustomBox();

      var step = 1; // metres
      var tick = 100; // milliseconds
      var poly;
      var eol;
      var k=0;
      var stepnum=0;
      var speed = "";   
	  var marker;	  
	  var directionResponse;

	  
	  
     
	 function animateMoveCar(d) {    
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
		
	/*	var service = new google.maps.places.PlacesService(map);
			service.nearbySearch({
			  location: p,
			  radius: 500,
			  type: ['store',"political"]
			}, renderNearestPOI);
		*/	
        
		//document.getElementById("distance").innerHTML =  "Miles: "+(d/1609.344).toFixed(2)+speed;
		
       /* if (stepnum+1 < directionResponse.routes[0].overview_polyline.length)) 
		{
          if (directionsService.getRoute(0).getStep(stepnum).getPolylineIndex() < poly.GetIndexAtDistance(d)) {
            stepnum++;
           // var steptext = directionsService.getRoute(0).getStep(stepnum).getDescriptionHtml();
          //  document.getElementById("step").innerHTML = "<b>Next:<\/b> "+steptext;
            //var stepdist = directionsService.getRoute(0).getStep(stepnum-1).getDistance().meters;
           // var steptime = directionsService.getRoute(0).getStep(stepnum-1).getDuration().seconds;
           // var stepspeed = ((stepdist/steptime) * 2.24).toFixed(0);
           // step = stepspeed/2.5;
            //speed = "<br>Current speed: " + stepspeed +" mph";
          }
        } else {
          if (directionsService.getRoute(0).getStep(stepnum).getPolylineIndex() < poly.GetIndexAtDistance(d)) {
            //document.getElementById("step").innerHTML = "<b>Next: Arrive at your destination<\/b>";
          }
        }*/
        
		
		
		setTimeout(function(){animateMoveCar(d+step)},tick);		
      }

      

      
	  initCarMovement=function(response) {  
	  
				poly=getRoutePolyline(response)[0];				
				eol=poly.getDistance();
				map.setCenter(poly.getPath().getAt(0),17);
				directionResponse=response;
				carMarker=marker=new google.maps.Marker({
						position: poly.getPath().getAt(0),
						map: map,
						icon:'/project/templates/bmw/_ares/icons/bmwcar.png',
						title: 'Hello World!'
					});
					
				setTimeout(function(){animateMoveCar(0)},500);  // Allow time for the initial map display
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
			
			
			
 
    
	

}