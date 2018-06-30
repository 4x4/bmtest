

$.fn.btnSwitch=function(options)
{
   var  selector=this.selector;
   var currentTarget=null;
    $(this).each(function () 
    {            
        if(!$('.'+$(this).data('target')).hasClass('hide'))currentTarget=$('.'+$(this).data('target'));

        $(this).click(function(ev)
        {
           ev.preventDefault();
           
           currentTarget.addClass('hide').removeClass('slideUp');                                  
           $(selector).removeClass('btn-info');
          
           $(this).addClass('btn-info');
           $('.'+$(this).data('target')).removeClass('hide').addClass('slideUp');
           currentTarget=$('.'+$(this).data('target')); 
      
        });
    });
};
                 
$.fn.enterKey = function (fnc) {
    return this.each(function () {        
        $(this).keypress(function (ev) {
            var keycode = (ev.keyCode ? ev.keyCode : ev.which);
            if (keycode == '13') {
                fnc.call(this, ev);
            }
        })
    })
};

 
var  _clearNearest= new Class({
    
       Implements: [Options],      

    initialize: function(options){
        this.setOptions(options);        
        jQuery(document).on('click','.clearNearest', 
        function()
        {
            $(this).parents('.input-group').find('input').val('');
        }
        );   
    }

}); 

clearNearest = new  _clearNearest();


/*
 var codeMirrorMonitor = new Class({
    
    Implements: [Options, Events],      

    
    initialize: function(options){
        
        this.setOptions(options);   
        
         if(!this.jsLoaded)
        {
            AI.loadJs('*_components/codemirror/codemirror.js',true);  
            this.jsLoaded=true;              
        }
             
        jQuery(document).on('click','.cmEditorApplyButton', this.clickHandler.bind(this));  
        jQuery(document).on('dblclick','.cm-apply', this.clickHandler.bind(this));  
        jQuery(document).on('click','.cm-apply-here', this.clickHandlerHere.bind(this));  
    },
    
    
    
    clickHandlerHere: function(event)
    {
        
        event.preventDefault();
        txtarea=$(event.target).parent().parent().find('textarea');      
        
        this.codeMirror = CodeMirror.fromTextArea(txtarea[0],
                {
                    lineNumbers:true,
                    theme:"eclipse",
                    pollInterval:100,
                    lineWrapping: true,            
                    matchBrackets:true                
                });
        
        
    },
    
    
         
        onTargetClick:function()
        {
           
            $(this.targetElement).val(this.codeMirror.getValue());
            this.window.hide();
        },
    
        
    
    applyEditor:function(value,options)
    {
       
        
   
        if(!this.window)
        {    
            this.window=AI.dhxWins.createWindow("cmEditor", 20, 10, 1000, 600, 1);
            this.window.centerOnScreen();
            this.window.setModal(false);
            this.window.attachHTMLString('<textarea id="cmEditor"></textarea><a class="green-button save-cm-editor" href="#">'+AI.translate('common','save')+'</a>');
            this.window.setText(AI.translate('common','editing'));            
            this.window.button('park').hide();
            $(this.window.dhxContGlobal.dhxcont.mainCont).css({'overflow-y':'auto'});
            $('.save-cm-editor').click(this.onTargetClick.bind(this));
                
            this.codeMirror = CodeMirror.fromTextArea($('#cmEditor')[0],
                {
                    lineNumbers:true,
                    theme:"eclipse",
                    lineWrapping: true,            
                    matchBrackets:true                
                }
            );
            
            
        }    
        
        if(options)
        {
            Object.each(options,function(key,val)
                {                    
                    this.codeMirror.setOption(val,key);   
                                        
                }.bind(this));
            
        }else{
       
          this.codeMirror.setOption('mode', "htmlmixed");     
            
        }
        
          
        this.window.show();
        this.codeMirror.setValue(value);  
        this.codeMirror.refresh();
        
    
    },
    
    clickHandler: function(event)
    {
        
        event.preventDefault();
        this.targetElement=event.target;              
        options=$(this.targetElement).data();        
        this.applyEditor($(this.targetElement).val(),options);  
        
        
    }
    
 });

 */
 
 
 
 var _tagManMonitor = new Class({
    
    Implements: [Options, Events],      

    
    initialize: function(options)
    {
        this.setOptions(options);        
        jQuery(document).on('click','.openTagManager', this.clickHandler.bind(this)); 
        this.connector=new Connector('tagManager','.class'); 
        
    },
        deleteTag:function(id,kid)
        {
            
                selected = this.gridlist.getSelectedRowId(true);
                
                if(selected.length>0)
                {
                    cells=[];
                    for(i=0;i<selected.length;i++)
                    {
                        cell=this.gridlist.cellById(selected[i],0);
                        cells.push(cell.getValue());    
                    }
                    
                     this.connector.execute({deleteTags:{id:cells}});
                    
                }
                
                              
                if(this.connector.result.deleted)
                {
                     this.gridlist.deleteSelectedRows();    
                }
            
          
        },
        
           showTagsList: function (data) 
        {
       
            menu = new dhtmlXMenuObject();
            menu.renderAsContextMenu();
            
        
            menu.addNewChild(menu.topId, 0, "delete", AI.translate('common','delete'), false, '', '', this.deleteTag.bind(this));
            
            
            this.gridlist = new dhtmlXGridObject('tagTable');
            this.gridlist.selMultiRows = true;
            this.gridlist.enableMultiline(true);
            this.gridlist.setImagePath("/x4/adm/xres/ximg/grid/imgs/");
            this.gridlist.setHeader('id,'   + ',' + AI.translate('common', 'tag') + ',' + AI.translate('common', 'module') );

            this.gridlist.setInitWidths("80,130,*");
            this.gridlist.setColAlign("center,center,center");


            this.gridlist.attachEvent("onRowDblClicked",function(kid)
            {
               
                
            }.bind(this));    

            
            this.gridlist.setColTypes("ro,ro,ro");

            this.gridlist.enableAutoWidth(true);

            this.gridlist.enableContextMenu(menu);  
            this.gridlist.init();
            this.gridlist.setSkin("modern");
            this.gridlist.onPage=200;
            //$(this.gridlist.objBox).parent().addClass('persistentGrid');
            this.listTags(); 
             
             /*var  pg = new paginationGrid(this.gridlist, {
                target: this.mainViewPortFind('.paginator'),
                pages: this.connector.result.pagesNum,
                url: AI.navHashCreate(this.name, 'news', {id:data.id}) //,

            });*/
                    

    },
          
          
    listTags: function (id,page) 
    {
        this.gridlist.clearAll();
        this.connector.execute({
            tagsTable: {
                id:id,            
                page: page,
                onPage: this.gridlist.onPage
            }
        });
      
        if (this.connector.result.data_set) {
            
            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }

    },
    
    
    addNewTag:function(e)
    {
        e.preventDefault();        
        data=xoad.html.exportForm('addNewTag');
        this.connector.execute({addTag:data});
        this.listTags(); 
    },
    
    bindEvents:function()
    {
         this.tagManContext.find('.addNewTag').click(this.addNewTag.bind(this));
    },
    
    setupTagsData:function()
    {
        this.connector.execute({getTaggedModulesSelector:true});
        
        xoad.html.importForm('addNewTag',this.connector.result.data);
    },
    
    tagManAdd:function()
    {
        if(!this.window)
        {    
            this.window=AI.dhxWins.createWindow("cmEditor", 20, 10, 800, 700, 1);
            this.window.centerOnScreen();
            this.window.setModal(true);
            this.window.attachHTMLString(TH.getTpl('AdminPanel','tagManager'));
            this.window.setText(AI.translate('common','tagManager'));            
            this.window.button('park').hide();
            this.tagManContext=$(this.window.dhxContGlobal.dhxcont.mainCont);
            this.setupTagsData();            
            $(this.tagManContext).css({'overflow-y':'auto'});
            
            this.window.attachEvent("onHide", function(win)
                {
                    tags=this.getTagsChosenSelector();
                    if(tags)
                    {
                        form=this.input.closest('form');
                        this.input.children().remove();
                        data[this.input.attr('name')]=tags;
                        xoad.html.importForm(form.attr('id'),data);
                        this.input.trigger("chosen:updated");    
                    }
                    
                  
                }.bind(this));    
                
            this.bindEvents();
            
                       
        }    
          
        this.window.show();
        this.showTagsList();
        
    
    },
    
    getTagsChosenSelector:function()
    {    
        selected=this.input.val();
        this.connector.execute(
        {
            getTagsChosenSelector:{selected:selected}
        });  
        
        
        return  this.connector.result.tagList;
    },
    
    clickHandler: function(event)
    {
        event.preventDefault();
        eTarget=$(event.target);
        this.input=$(eTarget).parent().prev();
        this.tagManAdd();
    },
    
    /*
    *   эта функция должна вызываться в мутаторе 
    */
    applyTagMans:function()
    {
        that=this;
        
        $('.tagManSource').each(function()
        {
                var that=this; 
                setTimeout(function(){
                
                    chs=$(that).chosen();
                    
                    if((chs.next('span.pull-right').length==0)&&(!$(that).hasClass('noTagButton')))
                    {
                        $(that).after('<span class="pull-right"><a class="btn btn-xs btn-default openTagManager" href="#">'+AI.translate('common','tag_editor')+'</a></span>');
                    }    
                    
                },200)
                
        
                
        })
        
       
        
    }
    
 });
 
 
 var innerLock;
 
 
var tagManMonitor =new _tagManMonitor();

 var paginationGrid= new Class({
    
    Implements: [Options],      
                                 
        options: {
            target:'.catGroupListPaginator', 
            pages:0,
            url:'', 
            onPageSelect:function(e)
            {   
         
                   paginationGrid.paginationHolder[this.gridInstanceName]=$(e.target).val();                    
                   AI.refreshPage({page:1});
                
            },
            pageSelect:[20,30,50,100,500]      
        },
        
        
                                          
    initialize: function(gridInstanceName,options)
    {
           this.gridInstanceName=gridInstanceName;
           this.setOptions(options);

              pagesNum={}; 
           
              if(this.options.pages>1)
              {                                 
                      for(i=1;i<=this.options.pages;i++)
                      {
                          pagesNum[i]=this.options.url+'&page='+i;    
                      }
              }      
                    tpl=TH.getTplHB('AdminPanel','paginationGrid');
                    if(AI.currentHashParams.page){
                        selected=AI.currentHashParams.page;}else{
                        selected=1;    
                    }
                    
                $(this.options.target).html(tpl({selected:selected,onPage:paginationGrid.paginationHolder[gridInstanceName],pageSelect:this.options.pageSelect,pages:pagesNum}));  
                $(this.options.target).find('.onpage').change(this.options.onPageSelect.bind(this));
    },
    
    handleClick:function(){}
    
    
 });
 
 //static pager storage;
 paginationGrid.paginationHolder={};
 
 paginationGrid.getOnPage=function(gridInstanceName)
    {
            var  onPage=null;
            if((gridInstanceName in paginationGrid.paginationHolder))
            {
                 onPage=paginationGrid.paginationHolder[gridInstanceName];
            }
           return   onPage;
    };
    
    
    paginationGrid.setOnPage=function(gridInstanceName,onPage)
    {
            if(!(gridInstanceName in paginationGrid.paginationHolder))
           {
                paginationGrid.paginationHolder[gridInstanceName]=onPage
           }
    };
      
 var TYPOGRAF = new Typograf({locale: ['ru', 'en-US']});    

 var ckMonitor = new Class({
    
    Implements: [Options, Events],      

    
    initialize: function(options){
        this.setOptions(options);        
        jQuery(document).on('click','.editorApplyButton', this.clickHandler.bind(this));  
        jQuery(document).on('click','.typographApplyButton', this.onTypographClick.bind(this));  
        jQuery(document).on('dblclick','.ck-apply', this.clickHandler.bind(this));          
        jQuery(document).on('keyup','.ck-apply',this.charCounter.bind(this));
        jQuery(document).on('focus','.ck-apply',this.charCounter.bind(this));        
        jQuery(document).on('keyup','.counterMonitor',this.charCounter.bind(this));
        jQuery(document).on('focus','.counterMonitor',this.charCounter.bind(this));
        
        
        
    },
    
    
    charCounter:function(event){
        
         element=event.target; 
         var length = $(element).val().length;   
         
         counter=$(element).closest('.form-group').find('.charsCount');      
        
         if(counter.length){
            $(counter).text(length);    
         }
         
    },
    
    onTypographClick:function(event){
       
         event.preventDefault();
         element=event.target;
         
          
        if(jQuery(element).is('a'))
        {
        
            if(this.txtarea=jQuery(element).parent().parent().find('.ck-apply'))
            {
                $(this.txtarea).val(TYPOGRAF.execute($(this.txtarea).val()));      
            }
			
			if(input=jQuery(element).parent().parent().find('input'))
            {
                $(input).val(TYPOGRAF.execute($(input).val()));      
            }

            $.growler.notice({message:AI.translate('common','typographed'),title:_lang['common']['info']});
        }
        
          
    },
       

    
    onTargetClick:function(e)
    {
        if(typeof this.txtarea[0].changeIt!='undefined')
        {
            this.txtarea[0].changeIt(this.CK.getData());
        }
        
        this.txtarea.val(this.CK.getData());
        
    },
    
    maxiWin:function()
    {
    
    if(!this.window)
        {    
            this.window=AI.dhxWins.createWindow("ckEditor", 20, 10, 980, 650, 1);
            this.window.centerOnScreen();
            this.window.setModal(false);
            this.window.attachHTMLString('<textarea id="ckEditor"></textarea>');            
            this.window.setText(AI.translate('common','editing'));
            this.window.button('park').hide();
                    
            $(this.window.dhxContGlobal.dhxcont.mainCont).css({'overflow-y':'auto'});
            CKEDITOR.config.height = 510;            
            this.ckLink=CKEDITOR.replace('ckEditor');
            
            this.window.attachEvent("onHide", this.onTargetClick.bind(this));
                        
        }else{
        
            this.window.centerOnScreen();
            
        }
        
        return this.window;

    
    },
    
    
    clickHandler: function(event)
    {
        
        event.preventDefault();
        element=event.target;
        
        disabled=$(element).closest('.form-group').find('.editorApplyButton').is(':hidden');
        if(disabled)return;
        
        ckWin=this.maxiWin();    
        this.CK=this.ckLink;
        
                
        if(jQuery(element).is('a'))
        {
        
            if(this.txtarea=jQuery(element).parent().parent().find('.ck-apply'))
            {
                this.CK.setData($(this.txtarea).val());   
            }
        
        }else{        
        
            this.txtarea=$(element);
            this.CK.setData($(element).val());   
        }
        

        ckWin.show();
        
    }
    
 });

 var CKmonitor =new ckMonitor();
 
 

    