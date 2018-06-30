var pfx = ["webkit", "moz", "MS", "o", ""];
function PrefixedEvent(element, type, callback) {
    
    
    for (var p = 0; p < pfx.length; p++) {
        if (!pfx[p]) type = type.toLowerCase();
        element.addEventListener(pfx[p]+type, callback, false);
    }
}

function PrefixedEventRemove(element, type, callback) {
    
    
    for (var p = 0; p < pfx.length; p++) {
        if (!pfx[p]) type = type.toLowerCase();
        element.removeEventListener(pfx[p]+type, callback, false);
    }
}




var _xModuleBack = new Class(
    {

        Implements: [Options, Events],
        interfaceBuilded: false,
        objTypeScope: [],
        name: null,
        animationSleep: 'fadeOutUp',
        animationWake: 'fadeInDown',
        layout: null,
        treeClickMap: {},
        innerRoutes: {},
        CRUN: null,
        config:null,
        //animationSleep:'slideOutRight',
        //animationWake:'slideInRight',


        initialize: function () {
            this.connector = new Connector(this.name);
            this.config=AI.backOptions.modulesList[this.name];

        },
        
        applyPluginListeners:function(){

            if((this.config)&&typeof this.config.plugins!='undefined'){
                var i=null;
                for(i in this.config.plugins ){
                    if(this.config.plugins[i].jsFile) {
                        plugin = AI.loadModule(this.config.plugins[i].jsFile, 'silent', true, true);

                    }
                    }
            }
        },

        getPermission:function(permission)
        {
             this.connector.execute(
                {
                    getPermission: {
                        
                        permission: permission
                    }
                });       
                return   this.connector.result[permission];   
        },
        
        addInnerRoute: function (route, method, bind) {
            this.innerRoutes[route] = {method: method, bind: bind}
        },

        navigate: function (to, params) {

            AI.navigate(AI.navHashCreate(this.name, to, params));
        },

        /**
         * добавить обработчик кликов по элементам дерева и грида
         */
        pushToTreeClickMap: function (objType, func) {
            this.treeClickMap[objType] = func;
        },

        /**
         * Обработчик кликов по элементам дерева и грида
         */

        treeObjectClicked: function (id) {
            objType = this.tree.getRowAttribute(id, "obj_type");

            if (this.treeClickMap[objType]) {
                AI.navigate(AI.navHashCreate(this.name, this.treeClickMap[objType], {'id': id}));
            }
        },

        
       
       enableObject: function (gridContext, backEndCallFunction) 
           {

                if (!backEndCallFunction) {
                    backEndCallFunction = 'enableObjects';
                }

                // selected = gridContext.getSelectedRowId(true);


                if (selected = gridContext.getSelectedId()) {
                    selected = selected.split(',');
                } else {
                    return;
                }
                
                    cdf = {};
                    cdf[backEndCallFunction] = {id: selected};
                    this.connector.execute(cdf);

                    

                
        },
        
        
           disableObject: function (gridContext, backEndCallFunction) 
           {


            if (!backEndCallFunction) {
                backEndCallFunction = 'disableObjects';
            }

            // selected = gridContext.getSelectedRowId(true);


            if (selected = gridContext.getSelectedId()) {
                selected = selected.split(',');
            } else {
                return;
            }

            if (selected.length > 1) {
                result = confirm(AI.translate('common', 'you_really_wish_to_disable_this_objects'));
            }
            else {
                result = confirm(AI.translate('common', 'you_really_wish_to_disable_this_object'));
            }

            if (result) {
                cdf = {};
                cdf[backEndCallFunction] = {id: selected};
                this.connector.execute(cdf);

                if (this.connector.result.disabledList) 
                {
                }

            }
        },
        
          
        setGridView: function (id, height, setPaginator) {
            
            html='<div id="' + id + '" style="min-height:' + height + 'px"></div>';
            if(setPaginator)
            {
                html+='<footer class="panel-footer paginator"> </footer>';
            }
            
            jQuery(this.mainViewPort).html(html);            
            $('#' + id).attr('type', 'persistentGrid');

        },

        onTreeGridDrag: function (idNode, idTo, drop, bex, zer) {

            parentSource = this.tree.getParentId(idNode);

            if (bex.dragContext.dropmode != 'child') {
                parentTarget = this.tree.getParentId(idTo);

            } else {
                parentTarget = idTo;
            }


            if (parentSource != parentTarget) {
                ancestorChanged = true;
            } else {
                ancestorChanged = false;
            }

            this.connector.execute(
                {
                    changeAncestorGrid: {
                        id: idNode,
                        pointNode: idTo,
                        ancestor: parentTarget,
                        ancestorChanged: ancestorChanged,
                        relative: bex.dragContext.dropmode
                    }
                });

            if (this.connector.result['dragOK']) {
                return true;
            }

            return false;
        },

        refreshTree: function (a, anc) {
            this.tree.refreshItem(anc);
        },


        //proxy
        execute: function (data) {
            return this.connector.execute(data);

        },

        setMainViewPort: function (html) {
            jQuery(this.mainViewPort).html(html);
        },

        mainViewPortFind: function (selector) {

            return jQuery(this.mainViewPort).find(selector);
        },


        preloadModuleTemplates: function () {
            tplArr = [];
            if (this.objTypeScope.length > 0) {

                for (i = 0; i < this.objTypeScope.length; i++) {
                    tplArr.push(this.objTypeScope[i]);
                    tplArr.push(this.objTypeScope[i] + '@edit');
                }
            }

            if (this.loadDefaultTpls) {
                if (this.loadDefaultTpls.length > 0) {
                    for (i = 0; i < this.loadDefaultTpls.length; i++) {
                        tplArr.push(this.loadDefaultTpls[i]);
                    }
                }

            }

            if (tplArr.length > 0)TH.loadModuleTpls(this.name, tplArr);
        },

        
        onModuleSearchClick: function (e) {

            e.preventDefault();
            
            word=this.viewPort.find('.searchInModuleInput').val();

            if ('' != word) {
                AI.navigate(AI.navHashCreate(this.name, 'searchInModule', {'word': encodeURIComponent(word)}));

            } else {

                alert(AI.translate('common', 'enter-any-word-to-search'));
            }

        },
    
        buildInterface: function () {

            this.viewPort = jQuery('#' + this.name);

            
            if (!this.layout)this.layout = 'emptyView';

            if (this.layout == 'treeView') {
                this.tree = null;
                this.treeViewPort = this.viewPort.find('.treeBox')[0];
            }

            this.tabsViewPort = this.viewPort.find('.tabs')[0];
            this.mainViewPort = this.viewPort.find('.col2-inner')[0];
            
            this.viewPort.find('.searchInModule button').click(this.onModuleSearchClick.bind(this));
            this.viewPort.find('.searchInModuleInput').enterKey(this.onModuleSearchClick.bind(this));
            
            
            
            this.preloadModuleTemplates();
            if (this.CRUN) {
                this.CRUN();
            }

        },

        translate: function (word) {
            return AI.translate(this.name, word);

        },

        treeDynamicXLS: function (id) {
            this.connector.execute({treeDynamicXLS: {id: id}});
            if (this.connector.result) {
                if (id == 0) {
                    this.tree.parse(this.connector.result.data_set, "xjson")
                } else {
                    this.tree.json_dataset = this.connector.result.data_set;
                }
            }
            return true;
        },


        // treeView,normalView

        setLayoutScheme: function (layout, layoutParams) {
            this.layout = layout;
            this.layoutParams = layoutParams;
        },


        getTreePathAncestor: function (objGetParent) {
            if (selected = this.tree.getSelectedRowId()) {
                objType = this.tree.getRowAttribute(selected, "obj_type");

                if (objGetParent) {
                    if (objGetParent.indexOf(objType) != -1) {
                        selected = this.tree.getParentId(selected)
                    }
                }

                return this.tree.getParentPath(selected, 0).join('/');

            }
        },

        setName: function (name) {
            this.name = name;
        },


        //усыпление модуля при переключении : скрыть активные окна и т.д
        sleep: function (status) {


            AI.dhxWins.forEachWindow(function (win) {

                win.hide();
            });


                AnimationListener = function () {

                
                    $("body").trigger({type:"moduleSleep"});

                    this.viewPort.removeClass('animated ' + this.animationSleep).hide();
                    
                    PrefixedEventRemove(this.viewPort[0], "AnimationEnd", AnimationListener,false)

            }.bind(this);

        
        
            PrefixedEvent(this.viewPort[0], "AnimationEnd", AnimationListener,false);
            this.viewPort.addClass('animated ' + this.animationSleep);



        },

        wakeUp: function () {
            this.viewPort[0].addEventListener("animationend", tz = function () {
                this.viewPort.removeClass('animated ' + this.animationWake).show();
                this.viewPort[0].removeEventListener("animationend", tz, false);

            }.bind(this), false);

            this.viewPort.addClass('animated ' + this.animationWake).show();
        },

        //функция по умолчанию вызываемая по старту модуля
        start: function () {            
            this.viewPort.show();
        },
        //заглушка
        dummy: function () {
        },

        consoleIt: function (kid, id) {
            this.connector.execute({consoleIt: {id: id}});
            debug.log(this.connector.result.console);
        },


        searchInModule: function (params) {
            this.connector.execute({onSearchInModule: params});

            if (Object.getLength(this.connector.result) > 0) {
                if (typeof this.onSearchInModule == 'function') {
                    this.onSearchInModule(this.connector.result.searchResult);
                }
            }
        },


        getTplHB: function (tpl) {
            return TH.getTplHB(this.name, tpl);
        },

        getTpl: function (tpl) {
            return TH.getTpl(this.name, tpl);
        },

        copyObjectToBufferGrid: function (gridContext) {

            if (selected = gridContext.getSelectedId(true)) {
                gridContext.selectedBuffer = selected;
            }

        },

        pasteObjectGrid: function (gridContext, ancestor, backEndCallFunction,buffer) {

            if (!backEndCallFunction) {
                backEndCallFunction = 'copyObj';
                
            }
            if(!buffer)buffer=gridContext.selectedBuffer;
            
            if (buffer) {
                cdf = {};
                cdf[backEndCallFunction] = {ancestor: ancestor, id: buffer};
                this.connector.execute(cdf);

                if (this.connector.result.copied) {

                }

            }

        },


        deleteObjectGrid: function (gridContext, backEndCallFunction,useFirstColumnAsID) {


            if (!backEndCallFunction) {
                backEndCallFunction = 'deleteObj';
            }

            // selected = gridContext.getSelectedRowId(true);


            if (selected = gridContext.getSelectedId()) {
                selected = selected.split(',');
            } else {
                return;
            }
            
            if(useFirstColumnAsID)
            {
                
                for(i=0;i<selected.length;i++)
                {
                        cell=gridContext.cellById(selected[i],0);            
                        selected[i]=cell.getValue();
                       
                }
                
                
                
            }

            if (selected.length > 1) {
                result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_objects'));
            }
            else {
                result = confirm(AI.translate('common', 'you_really_wish_to_remove_this_object'));
            }

            if (result) {
                cdf = {};
                cdf[backEndCallFunction] = {id: selected};
                this.connector.execute(cdf);

                if (this.connector.result.deletedList) {
                    gridContext.deleteSelectedRows();
                }

            }
        }


    });


var CRUN = new Class(
    {
        Implements: [Options],

        options: {objType: null, autoCreateMethods: false},


        initialize: function (context, options) {
            this.context = context;
            this.setOptions(options);

            modArray = ['create', 'edit', 'save', 'saveEdited'];

            Array.each(modArray, function (mod, index) {
                name = mod + this.options.objType;

                this.context.__proto__[name] = this[mod].bind(this);
            }.bind(this));

        },


        edit: function (data) {
            if (data && data.id) {
                this.selectedId = data.id;

            } else if (this.context.tree)this.selectedId = this.context.tree.getSelectedRowId();

            this.context.setMainViewPort(TH.getTpl(this.context.name, this.options.objType + '@edit'));
            this.form = this.context.mainViewPortFind("#edit" + this.options.objType);
            this.form.validationEngine();
            //this.context.mainViewPortFind("#edit"+this.options.objType+' a.save').unbind('click').click(this.saveEdited.bind(this));
            this.context.mainViewPortFind('a.save').unbind('click').click(this.saveEdited.bind(this));
        },

        create: function (data) {
            this.context.setMainViewPort(TH.getTpl(this.context.name, this.options.objType));
            this.form = jQuery("#create" + this.options.objType);
            this.form.validationEngine();
            if (!data) {
                data = true;
            }
            cr = [];
            cr['onCreate' + this.options.objType] = data;

            this.context.connector.execute(cr);
            this.data = this.context.connector.result;
            this.context.mainViewPortFind('a.save').unbind('click').click(this.save.bind(this));
        },

        save: function (e) {

            this.validated = this.context.mainViewPortFind("#create" + this.options.objType).validationEngine('validate');
        },

        saveEdited: function (e) {

            this.validated = this.context.mainViewPortFind("#edit" + this.options.objType).validationEngine('validate');

        }

    });
