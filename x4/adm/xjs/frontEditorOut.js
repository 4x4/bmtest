var __x4frontEditorModule= new Class(
{

    initialize:function(domReplica,moduleData)
    {
        this.domReplica=domReplica;
        this.moduleData=moduleData;
        this.connector = new Connector('fronteditor');
        this.getModuleParams();
        this.moduleRender();
    },
    
    
    getModuleParams:function()
    {        
        this.connector.execute({getModuleParams:{id:this.moduleData.id}});        
        this.moduleParams=this.connector.result.module;
       
        
    },
    
    moduleRender:function()
    {
       
        li='';
        if(this.moduleParams.templates)
        {
            
           $.each(this.moduleParams.templates,function(k,v)
           {                
                li+='<li><a target="_blank" href="/admin.php?#e/templates/edit_FILE/?id='+v.fullPathBase+'"> '+v.name+'</a></li>';     
           })
          
                              
        }
         
        moduleData='<ul>'+        
        '<li>'+this.moduleData.type+'</li>'+li+
        '<li>'+this.moduleData.executeTime+'</li>';
        '</ul>';
        
        this.domReplica.append(moduleData);
        
    }
    
    
});

var __x4frontEditor= new Class(
{
      
    initialize:function()
    {
        this.modules=[];
        this.slotz=[];
        this.switchView=true;
        this.connector = new Connector('fronteditor');
        
        jQuery(document).bind("keyup keydown", function(e){
             if(e.ctrlKey)
             {
                if(this.switchView)
                {                               
                    $('.__x4Slot,.__x4module').hide();  
                    this.switchView=false;            
                }else{
                    $('.__x4Slot,.__x4module').show();  
                    this.switchView=true;                                
                }
                
             }
             });
        
        setTimeout(function()
        {
            this.absolutizeSlotz();
            this.absolutizeModules();
            
        }.bind(this),1000);
        
    },

    
    absolutizeSlotz : function() {
       
        
     slotz = $('.__x4SlotMap');      
     slotz.each(function(k,v)
      {
         data=$(v).data('info');                      
         replica = this.absolutize(v, 998,null,null,true);         
         replica.addClass('__x4Slot');          
         replica.attr('sourceid',data['id']);
         
         totalHeight= $(v).prop('scrollHeight');
         totalWidth= $(v).prop('scrollWidth');
         
         replica.html(data['name']);
         replica.css( {
             border : '2px dotted #A1A1A1', opacity:0.5,
             minHeight : '20px',
             minWidth : '20px',
          
             height:totalHeight,
             width:totalWidth
         }
         );
            
         this.slotz[slotz[i].id] = replica;
         
      
/*         _edit = document.createElement('a');
         _edit.onclick = function() {
            alert('ddd');
            };
         _edit.style.display = 'none';
         _edit.innerHTML = slotz[i].getAttribute('alias');
    
         Droppables.add(replica.id , {
             accept: ['__amodule'],
             onDrop: function(drag,drop) 
             {   
                 
                 if(drag.esource&&FXTR_pages.change_module_slot(drag.esource.pid,drop.esource.pid))
                 {
                         cl = drag.esource.cloneNode(true);
                         drop.esource.appendChild(cl);
                         cl.show();
                         drag.esource.remove();
                         this.refresh();
                 }
                 
             }.bind(this)
             });
             

         replica.appendChild(_edit);
         */  
         
         
      }.bind(this));
      
      
      
      
   },
   
   
    absolutizeModules : function() 
    {
      oldpid = null;
      modules = $('.__x4moduleMap');
      
      modules.each(function(k,v)
      {
         replica = this.absolutize(v, 999,50,50,true);         
         
         data=$(v).data('info');                 
         replica.addClass('__x4module');          
         replica.attr('sourceid',data['id']);
         replica.css( 
           {
            minHeight : '20px',
            minWidth : '20px',  
               padding:'8px',          
            border:'1px solid red',
            background:'white'
           }
         );
         
         this.modules[data['id']]= new __x4frontEditorModule(replica,data);
      
          /*var mydrag = new Draggable(replica.id, { revert: true,
             onStart :this.module_drag_start.bind(this),
             onEnd: this.module_drag_end.bind(this)
         });
  
         if(oldpid != modules[i].parentNode.id)nh = new Hash();
         
         replica.esource.pid = modules[i].id.substr(2);
         nh[modules[i].id] = replica;
         this.modules[modules[i].parentNode.id] = nh;         
         oldpid = modules[i].parentNode.id 
         */
      }.bind(this));
      
    }

   
   ,absolutize : function (element, zindex,w,h,dchs) 
   {
      
      if ($(element).css("position") == 'absolute') return;

      _elementHover=jQuery('<div />',{});
      
      var p = $(element).offset();

      
      
      _elementHover.css({
          'position':'absolute',
          'z-index':zindex,                    
          'top':p.top,
          'left':p.left
      });
      
      $('body').append(_elementHover);
      
      return _elementHover;
      
      /*
      var delta = [0, 0];
      var parent = null;
      if (Element.getStyle(element, 'position') == 'absolute') 
      {
             parent = element.getOffsetParent();
             delta = parent.viewportOffset();
      }
      
      _element.style.left =(p[0] - delta[0] ) + 'px';
      _element.style.top = (p[1] - delta[1] ) + 'px';
      
      if(!dchs)
      {
      if(!w)
      {      
          if(element.clientWidth < this.options.slot_min_width) {
             w = this.options.slot_min_width;
             }
          else {
             w = element.clientWidth;
             }
      }
      
      if(!h)
      {
          if(element.clientHeight < this.options.slot_min_height) {
             h = this.options.slot_min_height;
             }
          else {
             h = element.clientHeight;
             }
      }
      _element.style.width = w + 'px';
      _element.style.height = h + 'px';
      
      }
      //добавим реплику
      document.body.appendChild(_element);
      return _element;
      */
      }
      
      
});