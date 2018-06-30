
(function(){

	let tickData={assignedToMe:0,usersLoggedin:0,allRequests:0,unassigned:0};
	$('.assignedToMe,.usersLoggedin,.allRequests,.unassigned').text(0);
	
	var rand = function(min, max) {
		return Math.random() * (max - min) + min;
	};
	
	var probabilityGen=function(probability){
			
		return rand(0,100)<probability*10;
	}
 
	generateDynamicPanel=function()
	{		
		if(probabilityGen(0.1)){
			tickData.assignedToMe++;		
			$('.assignedToMe').text(tickData.assignedToMe);
			$('.assignedToMe').addClass('red');
		}
		
		if(probabilityGen(0.05)){
			tickData.usersLoggedin++;		
			$('.usersLoggedin').text(tickData.usersLoggedin);
		}
		
		if(probabilityGen(0.5)){
			tickData.allRequests++;		
			$('.allRequests').text(tickData.allRequests);
		}
		
		if(probabilityGen(0.1)){
			tickData.unassigned++;		
			$('.unassigned').text(tickData.unassigned);
		}
		
		
	}

	setInterval(generateDynamicPanel,1000);

})();

