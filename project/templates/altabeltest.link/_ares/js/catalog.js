$(document).ready(function(){

    var catalog=x4.getModule('catalogFront');
    var pages=x4.getModule('pagesFront');
    
    function renderFilterResults(formid)
    {
                serialized=$('#'+formid).serialize();
				
				
				
				
				
                serialized=decodeURIComponent(serialized);                				
				
				serialized = serialized.replace('tovarbase.firstname', 'Base');
				
				index=location.pathname.indexOf('/--');
				
				if(index!=-1)
					{
						pathname=location.pathname.slice(0 , index);
						
					}else{
					
						pathname=location.pathname;
					}
					
                gpath=location.host+pathname+'/?'+serialized;								
                gpath=gpath.replace(/\/\//g,"/");
				
				 
				
			var     url=location.protocol + '//' + gpath;

            $('.catalogListHolder').css('opacity',0.5);
            res=pages.renderSlot(url,['center'],
            function(xres,bres)
            {
                slots=bres.result.slots;
				fragment = document.createDocumentFragment();		
				var range = document.createRange();
				range.selectNode(document.body);
				var df = range.createContextualFragment( slots['center'] );				
				table=$(df).find('.col').html();			
                $('.col .table').replaceWith(table);
                $('.col .table').css('opacity',1);
                
                
    			 catalog.execute({buildUrlTransformation:{url:url}});
                 window.history.replaceState("object or string", "Title",catalog.connector.result.url);


            }
        );
    }

	$(document).on("change", "#mainFilter #lastEvent", function(e)
    {
	         now=Math.floor(Date.now() / 1000);
			 if($(this).val()==''){
				past=0;
				}else{
					past=now-$(this).val();
			 }
			 $('#fmin').val(past);
			 $('#fmax').val(now);
	 });


  
    $('#mainFilter .reset_filter_btn').click(function(){
		catalog.execute({clearSessionFilter:true});
	});

	
    $(document).on("change",'#sortMenu',function(){document.location.href=$(this).val();});

	$(document).on("keyup", "#mainFilter input", function(e)
    {
        renderFilterResults('mainFilter');
    });
	
	
    $(document).on("click", "#mainFilter input", function(e)
    {
        renderFilterResults('mainFilter');
    });
	
	$(document).on("change", "#mainFilter select", function(e)
    {
	         renderFilterResults('mainFilter');
	 });
	



});

