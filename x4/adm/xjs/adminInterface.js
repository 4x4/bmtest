var AI = null;
var TH = null;


var moduleSleepEvent = new jQuery.Event("moduleSleep");




var _adminInterface = new Class({
    Implements: [Options],
    Extends: Router,

    routes: {
        '#e/:module\.:plugin/:action/*': 'moduleActionDispatch',
        '#e/:module/:action/*': 'moduleActionDispatch'
        
        
    },

    initialize: function (options) {

        AI = this;
        this.setOptions(options);
        this.backOptions = null;
        this.currentModule = null;
        this.storedJs = new _storageProxy('js');
        this.calledModules = {};
        this.factoryStorage = {};
        
        this.loadedJs = [];
        this.connector = new Connector('AdminPanel');
        this.dhxWins = new dhtmlXWindows();
        this.dhxWins.setImagePath('/x4/adm/xjs/_components/dhtmlx/windows/imgs/');
        this.initials();
        this.lastActionDispatched = null;
        this.globalContainer = jQuery('#globalContainer');
        this.loadModule('dashboard', 'silent', false);
        this.loadModule('settings', 'silent',false);        
        this.parent();
        this.x = 0;

    },

    notfound: function () {
        this.navigate('e/dashboard/start/');
    },

    refreshPage: function (params) {

        cnp = Object.clone(AI.currentHashParams);
        cnp = Object.merge(AI.currentHashParams, params);
        cnp['rand'] = Math.random();
        AI.navigate(AI.navHashCreate(this.currentModule.name, AI.lastActionDispatched, cnp));

    },

    initials: function () {
        TH.loadModuleTpls('AdminPanel', ['paginationGrid', 'treeView', 'emptyView', 'listView', 'fileManager', 'tagManager', 'dash']);
        this.connector.execute({initial: true});
        this.backOptions = this.connector.result.data;
           
        this.attachListeners();
        
    },

    getModuleConfig:function(module)
    {
         return AI.backOptions.modulesList[module];
    },
    
    getModulesList: function () {
        var modules = {};
        return AI.backOptions.modulesList;
    },

    factor: function (classname, params, store) {
        debug.info('trying to factor:' + classname);

        if (typeof this.factoryStorage[classname] !== 'undefined')return this.factoryStorage[classname];

        if (typeof window[classname] == 'function') {
            inst = new window[classname](params);
            if (store)this.factoryStorage[classname] = inst;
            return inst;
        }

        debug.info('factorying failed:' + classname);
    },

    clearCacheNow:function(e)
    {
        e.preventDefault();
        this.connector.execute({clearCache:true});     
    },
    
    


    frontEditor:function(e)
    {
        
        e.preventDefault();
        element = e.target;
        
        if(!jQuery(element).hasClass('active'))
        {
            jQuery(element).addClass('btn-success').addClass('active');    
              this.connector.execute({enableFrontEditor:true});     
        }else{
            jQuery(element).removeClass('btn-success').removeClass('active');    
            this.connector.execute({disableFrontEditor:true});     
        }
        
        
        
    },
    
    onCtrlSkey:function()
    {        
        this.currentModule.mainViewPortFind('.onCtrlS,a.save').trigger('click');
    },
    
    
      onCtrlQkey:function()
    {        
        this.connector.execute({clearCache:true});   
        $.growler.notice({message:'cache-cleared',title:_lang['common']['info']});
    },
    
    
    
    attachListeners: function () {
        var cr = new cardeonMonitor(); //слушаем скрывающиеся аккардеоны
        jQuery('#modulesMenu').click(this.onModulesMenuButton.bind(this));
        jQuery('.clearCacheNow').click(this.clearCacheNow.bind(this));
        jQuery('#frontEditor').click(this.frontEditor.bind(this));
        
        this.xListServer = new xListServer();
        this.fileManager = new FileMan();

        $('.file-manager').click(this.fileManager.open.bind(this.fileManager));
        that=this;
         
         $(document).bind('keydown', 'ctrl+s', function(e)
         {
             e.preventDefault();
             setTimeout(that.onCtrlSkey.bind(that),0);
         });
         
         $(document).bind('keydown', 'ctrl+e', function(e)
         {
             e.preventDefault();
             setTimeout(that.onCtrlQkey.bind(that),0);
         });
         
         $(document).bind('keydown', 'ctrl+q', function(e)
         {
             e.preventDefault();
             setTimeout(that.onCtrlQkey.bind(that),0);
         });
    },


    onModuleInterfaceBuildedAI: function () 
    {
        
    },




    onModulesMenuButton: function (e) {
        element = e.target;
        e.preventDefault();
        if (jQuery(element).next().is(':visible')) {
            jQuery(element).next().hide();
        } else {
            jQuery(element).next().show();
        }

    },

    afterModuleSleep: function () {

        
        if(this.param.module.indexOf('.')!=-1)
          {
            exp=this.param.module.split('.');    
            this.param.plugin=exp[1];
            this.param.module=exp[0];
          }
                   
        this.queryDispatch();
        this.currentModule.wakeUp();
    },

    queryDispatch: function () {

                    
        isPlugin=this.param.plugin?true:false;
		 
        AI.currentHashParams = this.query;

        
        if(isPlugin)
        {
             load=  this.param.module+'.'+this.param.plugin;
        
        }else{
            
             load= this.param.module;
        }
 
        if (module = this.loadModule(load, 'normal', true, isPlugin)) 
        {

            if (module['onHashDispatch']) {
                // Диспечиризация в случае возврата true
                if (module['onHashDispatch'](this.param.action, this.query)) {
                    if (typeof module[this.param.action] == 'function') {
                        module[this.param.action](this.query);
                    } else {

                        if (typeof module.innerRoutes[this.param.action] == 'object') {

                            innerRoute = module.innerRoutes[this.param.action];
                            innerRoute.bind[innerRoute.method].apply(innerRoute.bind, [this.query]);
                        } else {

                            debug.info('action dispatched fail - method doesnt exists:', this.param.module, this.query);
                            return;
                        }
                    }

                }

            } else {

                if (typeof module.innerRoutes[this.param.action] == 'object') {
                    innerRoute = module.innerRoutes[this.param.action];
                    innerRoute.bind[innerRoute.method].apply(innerRoute.bind, [this.query]);
                } else {

                    debug.info('action dispatched fail - method doesnt exists:', this.param.module, this.query);
                    return;
                }

            }

            this.lastActionDispatched = this.param.action;
            debug.info('action dispatched success:', this.param, this.query);
        }

    },

    

    moduleActionDispatch: function () {
              
         
        debug.info('trying to dispatch module action - params:', this.param, this.query);
       
        if (!AI)AI = this;
                   
        
          if(!this.param.plugin && (this.param.module.indexOf('.')!=-1))
          {    
            return false;    
          }
          
    
        
        if (this.currentModule) 
        {
            if(this.param.plugin)
                {
                    
                    if(this.currentModule.name == this.param.plugin)
                    {                    
                       this.queryDispatch(this.param.plugin); 
                       
                    }else{
                        this.currentModule.sleep('hashDispatch');
                    }
                    
                } else{  
                
                        if (this.currentModule.name != this.param.module) 
                        {                            
                            this.currentModule.sleep('hashDispatch');

                        } else {
                            
                            this.queryDispatch(this.param.plugin);
                            
                        }
                }

        } else {
             
            this.queryDispatch(this.param.plugin);
            this.currentModule.wakeUp();
        }


    },

    navHashCreate: function (module, action, params) {
        q = '#e/' + module + '/' + action + '/';
        if (params) q += '?' + jQuery.param(params);
        return q;
    },

    loadMultiJs: function (arrJs) {
        Array.each(arrJs, function (path, index) {
            this.loadJs(path);
        });

    },

    loadJs: function (path, store) {

        path = path.replace('*', '/x4/adm/xjs/');

        hashPath = path.toHashCode();
        if (this.loadedJs.indexOf(hashPath) != -1) {
            return false;
        }

        if (!(code = this.storedJs.get(hashPath))) {
            var code = '';

            jQuery.ajax({
                url: path,
                async: false,
                complete: function (data) {
                    code = data.responseText;
                    if (store) this.storedJs.set(hashPath, code);

                }.bind(this)
            });

        }

        if (code.clean() == '') {
            debug.warn(path + ' trying to load but javascript file is empty.');
            return false;

        } else {
            code += ' //# sourceURL=' + path; //debug issues
            this.loadedJs.push(path);
            window.eval(code);
            debug.info(path + ' load success');
            return true;

        }


    },

    translate: function (key, word) {
        
        if (typeof _lang[key] != 'undefined') {
            if (_lang[key][word]) {
                return _lang[key][word];

            } else {
                return word;
            }
        } else {
            debug.info(key + ' lang source not defined');
        }
    },

    setupLayout: function () {

        if (this.currentModule.layout) {
            layout = TH.getTplHB('AdminPanel', this.currentModule.layout);
            data = {module: this.currentModule.name, moduleName: AI.translate(this.currentModule.name, 'name')};
            data = $.extend(data, this.currentModule.layoutParams);
            
            this.globalContainer.append(layout({data: data}));

        } else {

            debug.warn(this.currentModule.name + ' layout not defined ');
        }

    },


    loadLang: function (module)
    {
        this.connector.execute({getLangForModule: {module: module}});
           _lang[module]={};
           _lang[module]=this.connector.result.lang;
           
    },
    
    
    loadModule: function (module, calltype, loadJs,isPlugin) {
        
        var x_name = "x_" + module;
        if (typeof this.calledModules[x_name] != 'object') {

            if (loadJs) {

                this.loadLang(module);

                if(!isPlugin)
                {
                    if (!this.loadJs('/x4/modules/' + module + '/js/' + module + 'Back.js', true)) return;

                }else{

                    parts=module.split('.');
                    if (!this.loadJs('/project/plugins/' + module + '/js/' + parts[1] + 'Back.js', true)) return;
                    module=  parts[1];
                    initialModule=  parts[0];
                }

            }


            module = this.factor(module + 'Back', module);

            if (typeof module.setupListeners == 'function'&&isPlugin) {

                module.initialModule=this.currentModule;
                module.setupListeners();
            }


            if ((calltype == 'normal') && (!module.interfaceBuilded)) {
                this.currentModule = module;
                if (typeof this.currentModule.buildInterface == 'function') {

                    this.currentModule.applyPluginListeners();
                    this.setupLayout();
                    this.currentModule.buildInterface();
                    this.currentModule.interfaceBuilded = true;

                    var interfaceBuildedEvent = new jQuery.Event("interfaceBuilded");

                    jQuery(window).trigger('interfaceBuilded',{module:this.currentModule});

                    this.onModuleInterfaceBuildedAI();
                    if (typeof this.currentModule.onModuleInterfaceBuildedAfter == 'function') this.currentModule.onModuleInterfaceBuildedAfter();


                }
                this.calledModules[x_name] = this.currentModule;
            } else {
                //кешируем silent модуль

                this.calledModules[x_name] = module;
            }

        } else {

            if (calltype == 'normal') {

                this.currentModule = this.calledModules[x_name];
                if (!this.currentModule.interfaceBuilded) if (typeof this.currentModule.buildInterface == 'function') {
                    this.setupLayout();
                    this.currentModule.buildInterface();
					if (typeof this.currentModule.onModuleInterfaceBuildedAfter == 'function') this.currentModule.onModuleInterfaceBuildedAfter();
                    this.currentModule.interfaceBuilded = true
                }

            }
        }

        return this.calledModules[x_name];
    }

});


jQuery(document).ready(function () {
    var e = jQuery.Event("mutate");

    if (MutationObserver = window.MutationObserver || window.WebKitMutationObserver) {
        // define a new observer
        var obs = new MutationObserver(function (mutations, observer) {
            jQuery(window).trigger('mutate');

            /*    for(var j=0; j<mutations[i].addedNodes.length; ++j) {
             if(!jQuery(mutations[i].addedNodes[j]).hasClass('firebugResetStyles'))
             {

             }
             }
             */

        });

        obs.observe(jQuery("body").get(0), {childList: true, subtree: true});
    }


    jQuery(window).on('mutate', function () {

        $(".datepicker-input").each(function () {
            $(this).parent().datetimepicker({maskInput: true});
        });
        

        /*  if($('div.persistentGrid').length>0)
         {
         jQuery(AI.currentModule.mainViewPort).addClass('grid-view');
         }*/


    });

     st=new _storage();
     st.clear();
     

    TH = new _templateHolder();
//dsipatcher here must be last
    AI = new _adminInterface();

    jQuery(window).on('moduleSleep', AI.afterModuleSleep.bind(AI));

});


    