 var cardeonMonitor = new Class({
    
    Implements: [Options, Events], 

        options: {
            container:'.auto-show', 
            header:'.panel-toggle',
            speed:200         
        },

    jQuery: 'cardeonMonitor',

    initialize: function(options){
        this.setOptions(options); // inherited from Options like jQuery.extend();
 
        this.options.clickTarget=this.options.header;
                     
        jQuery('#globalObserve').on('click', this.options.clickTarget,this.clickHandler.bind(this));  
        jQuery(window).on('mutate',function(){this.autoShow();}.bind(this));

    },
    

    clickHandler: function(event)
    {
    
        var e = jQuery.Event("cardeon:close");  
        var z = jQuery.Event("cardeon:open");  
        
    
        event.preventDefault();
        element=event.target;
        
        if(jQuery(element).parents('header').next().hasClass('collapse'))
        {
            
            jQuery(window).trigger('cardeon:open',event);
            
            jQuery(element).parents('header').next().removeClass('collapse');
         
            
        }else{
            
           jQuery(window).trigger('cardeon:close',event);
           
           jQuery(element).parents('header').next().addClass('collapse');
          
        }
        
    },

    autoShow: function()
    {
        jQuery(this.options.container+' input,'+this.options.container+' textarea').each(function(n,e)
        {
            if(jQuery(e).val())
            {
                jQuery(e).parents(".collapse").removeClass('collapse');
            }
        }); 
    }

});
