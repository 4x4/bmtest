moduleRegister.pushModule('catalog');

var catalogFront =  new Class({

    Extends:x4FrontModule,
    preventJquery: false,
    constructor: function ()
    {     
        this.parent('catalog');
    },

    addComparse:function(id)
    {
        this.connector.execute({addComparse:{id:id}});
    },
	
	removeComparse:function(id)
	{
		    this.connector.execute({removeComparseItem:{id:id}});
	},
	
	getComparseCount:function(id)
    {
        this.connector.execute({getComparseCount:true});
    },
	
	getComparseItems:function(id)
    {
        this.connector.execute({getComparseItems:{}});
		return this.connector.result.items;
    },

    getRelativeSku:function(id,propsToFilter)
    {
            this.connector.execute({getRelativeSku:{id:id,propsToFilter:propsToFilter}});
            return this.connector.result.relativeSku;
    },
    
    jqueryRun: function ()
    {
        if (this.preventJquery) return;
    
        var catalog=this;
    
        $.fn.showKeywordSearch = function (options)
          {
            var defaults = 
            {
                                     
                resultContainer: '.showKeywordsContainerResults',
                minLength:3,
                onready:null
            };

            var options = $.extend(defaults, options);
          
            $(this).keyup(function (e)
            {
                
                  if($(this).val().length<options.minLength){
                     $(options.resultContainer).hide();    
                     return;   
                }
                
                
                
                form=$(this).closest('form');
                catalog.connector.execute({getKeywordSearch:{moduleId:form.attr('id'),keyword:$(this).val()}},function(a,obj)
                {          
                 
                        $(options.resultContainer).html(obj.result.html);
                        
                        $(options.resultContainer).show();        
                        if(typeof options.onready=='function')options.onready(e);
                    
                });
                                
                
            });

        }
        
           
		  $.fn.catalogCurrentComparseCount=function(options)
		  {
				 var defaults = 
				{                
					onSet: null	
				}
				
				var options = $.extend(defaults, options);
				
				catalog.getComparseCount();   
 				
				if(typeof catalog.connector.result.count!='undefined')
				{
					rcount=parseInt(catalog.connector.result.count);
								
					if(rcount>0)
					{
						$(this).html(rcount);	
						if(options.onSet){options.onSet($(this),rcount);}	
					}
					
					
				}
				
				
		  }
		  
          $.fn.catalogAddComparse = function (options)
          {
            var defaults = 
            {
                idAttribute: 'data-id',
                onAdded: null,
				onBeforeAdd:null,
                currentCount:0,
                maxCount:5,
                onMaxCount:null,
                countContainer: '.countComparse'
            };

            var options = $.extend(defaults, options);
          
            $(this).click(function (e)
            {
                
                if(options.maxCount<=options.currentCount)
                {
                    if(options.onMaxCount)
                    {
                            options.onMaxCount($(this));
                    }    
                }
                
              
			  
				if(options.onBeforeAdd($(this),e))
				{
						catalog.addComparse($(this).attr(options.idAttribute));                         	
						
						}else{						
						
						catalog.removeComparse($(this).attr(options.idAttribute));                         	
						
				}
					
				options.currentCount=catalog.connector.result.count;                       						
                $(options.countContainer).html(catalog.connector.result.count);
                if(options.onAdded){options.onAdded($(this),e,catalog.connector.result.count);}
                
            });

        }

    }

});

//('ds').catalogAddComparse({maxCount:5,onMaxCount:function(){alert('max!')}});

