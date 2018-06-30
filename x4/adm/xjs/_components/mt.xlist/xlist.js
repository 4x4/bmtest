    /*
    *кнопка и ее атрибуты для вызова диалога     
    * <a href="#" data-topindex="35" data-callback="pages.onDialogGroup" data-wintext="Заголовок окна" data-destination="showGroup"  data-winheight="600" data-winwidth="600" data-info="any data" class="button xList">
    * xList - обязательный класс для вызова xlist
    * data-destination - селектор назначение  куда должен придти результат выбора
    * data-winHeight  - высота окна
    * data-winWidth  - ширина окна
    * data-topIndex  - z индекс окна
    * data-callback -функция которую необходимо вызвать для ослуживания например pages.onTreeGroups
    */
    
    
var xListServer= new Class({

    Implements: [Options],

    options: {        
        template: '<li><a href="{href}"  id="{id}" class="button">{name}</a></li>',
        callerButtonSelector: '.xList',
        height:500,
        width:700,
        text:'New window'
    },
    
    window:null,
    callbacks:[],
    
    jQuery: 'xlist',
    
    
    initialize: function (options) 
    {
        this.setOptions(options); 
        this.container = jQuery(this.options.callerButtonSelector); 
        $(document).on('click',this.options.callerButtonSelector, this.callButtonHandler.bind(this));        
        
    },
    
    
    pushCallBack:function(index,callFunc)
    {
       this.callbacks[index] =callFunc;
    },
    
        returnData:function(data)
    {            
            $('#'+this.returnElement).val(data.name); 
            $('#'+this.returnElement+'Id').val(data.id); 
            
            
            $('#'+this.returnElement).trigger("change");
            $('#'+this.returnElement+'Id').trigger("change");
            
            if(this.onDataReturn)
            {                        
               this.callBacks(this.onDataReturn,data);
            }
            
            that=this;
            //bug avoid;
            setTimeout(function(){that.window.close();},100);
            
    },
    
    
     callBacks:function(callback,data)
     {
                             
            callback=callback.split('.');
            
            if(callback.length>1)
            {                                  
                if(module=AI.loadModule(callback[0],'silent',true))
                {
                      if(typeof module[callback[1]]=='function')
                      {
                              module[callback[1]](this,data);        
                      }
                    
                }
            
            }else{
                
               
                  if(typeof this.callbacks[callback[0]]=='function')
                  {
                          this.callbacks[callback[0]](this,data);        
                  } 
                
            }
    
         
     },
    
    callButtonHandler:function(e)
    {
        
        if(!$(e.target).is('a'))return;
        
        e.preventDefault();
        
        if(!(width=$(e.target).data('winwidth')))
        {            
            width= this.options.width;   
        }
        
        if(!(height=$(e.target).data('winheight')))
        {            
            height= this.options.height;   
        }
        
        this.window = AI.dhxWins.createWindow("xlistWindow", 20, 10, width, height,1);
        
            if(!(txt=$(e.target).data('wintext')))
            {            
                txt= this.options.text;   
            }
        
        
        this.window.button('park').hide();
        
        this.info=$(e.target).data('info');
        this.currentElement=e.target;
        this.window.attachEvent("onHide", function(win){win.close();});            
        this.window.setText(txt);        
     
       
        this.window.centerOnScreen();
        if(!(this.returnElement=$(e.target).data('destination')))
        {
            
                debug.log('xlist:return element not found for - ');
                debug.log(e.target);
        }
        
      
         if(val=$(e.target).data('topindex'))
            {
                    this.window.bringToTop(val);        
            }
        
        if(onDataReturn=$(e.target).data('onDataReturn'))
        {
          this.onDataReturn=onDataReturn;               
        }
            
        if(callback=$(e.target).data('callback'))
        {                        
           this.callBacks(callback);
        }

    }
    

});

