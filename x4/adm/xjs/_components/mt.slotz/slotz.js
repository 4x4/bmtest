var moduleItem = new Class({
    Implements: Options,
    params: null,
    options: {
        id: null,
        _Type: null,
        _Name: null,
        _ActionAlias: null,
        _Action: null,
        _ObjectPath: null,
        _Cache: 0,
        _Priority: 0
    },
    set: function (options, params)
    {
        this.setOptions(options);
        this.params = params;
    },
    initialize: function (options, params)
    {
        this.set(options, params);
    },
    get: function ()
    {
        return {
            id: this.options.id,
            params: jQuery.extend(this.params, this.options)
        }
    }
});
var Slotz = new Class({
    Implements: Options,
    options: {
        pageId: null,
        connector: null,
        slotsContainer: '#slotz',
        currentModule: null,
        modulesStorage: [
        ],
        modulesMap: [
        ],
        modulesStorageIterator: 0,
        newPage: true,
        state: null,
        slotlist: [
        ],
        slotsSelector: '.def-slot ul',
        slotsInstance: {
        },
        slotTemplate: '<div data-basic="{basic}" class="def-slot  bg-light {lter} list-group-item">    <a class="add-mod m-r-xs btn btn-xs btn-info"> <i class="fa fa-plus text"></i> </a> ' +
        '{alias} <ul style="min-height:3px" class="list-group m-t-sm list-group-sp connectedSortable"></ul></div>',
        
        
        moduleTemplate: '<li data-id="{id}" class="list-group-item"> <div class="media"><span class="pull-right"> ' +
        '<a class="b-edit"  href="#"><i class="fa fa-pencil fa-fw m-r-xs"></i></a>' +
        '<a class="b-active"  href="#"><i class="fa {active} fa-fw m-r-xs"></i></a>' +
        '<a class="b-del"  href="#"><i class="fa fa-times fa-fw"></i></a> </span>' +
        '<span class="pull-left thumb-xs"><i style="font-size:16px" class="i icon m-t-xs  {_icon}"></i> </span>' +
        '<div class="media-body"><small class="text-muted"> {params._Name}:</small> {params.frontActionName}  ' +
        '<div><small class="text-muted "> {params._ObjectPath}  </small></div></div></div>' +
        '</li>',
        moduleSelectTemplate: '<div>' + /*'<h5>'+AI.translate('pages','choose-module')+'</h5>' +*/ '{modules}' + '</div>',
        moduleSelectElement: '<div class="col-lg-4 lter m-t-sm"><a href="#" class="{name}"><div class="wrapper bg-gradient b-dark" ><i class="i {iconClass} icon fa-2x m-t m-l-sm m-b-sm "></i><p>{moduleName}</p></div></a> </div>',        
        actionElement: '<li class="b-b "><a  data-action={action} href="#"><i class="fa fa-chevron-right pull-right m-t-xs text-xs icon-muted"></i>{alias}</a></li>',
        viewPort: null
    },
    initialize: function (options) {
        this.setOptions(options);
		
		
        this.container = jQuery(this.options.slotsContainer);
        this.initiateSlotz(this.options.slotsInstance);
        jQuery(document).off('click', this.options.slotsContainer + ' li a.b-active');
        jQuery(document).off('click', this.options.slotsContainer + ' li a.b-del');
        jQuery(document).off('click', this.options.slotsContainer + ' li a.b-edit');
        jQuery(document).off('click', this.options.slotsContainer + ' .connectedSortable>li');
        jQuery(document).on('click', this.options.slotsContainer + ' li a.b-del', this.deleteModule.bind(this));
        jQuery(document).on('click', this.options.slotsContainer + ' li a.b-edit', this.editModule.bind(this));
        jQuery(document).on('click', this.options.slotsContainer + ' li a.b-active', this.activateModule.bind(this));
 //       jQuery(document).on('dblclick', this.options.slotsContainer + ' .connectedSortable>li', this.editModule.bind(this));
    },
    addModule: function (slotId, module, reinit) {
        
        if(module.params.Active){module.active='fa-check-square-o'}else{module.active='fa-square-o';}                    
        moduleConfig=AI.getModuleConfig(module.params._Type);
        module._icon=moduleConfig.iconClass;
        li = $.nano(this.options.moduleTemplate, module);
        $('div[data-basic=' + slotId + '] ul').append(li);
        this.options.modulesStorage[module.id] = module;
        this.makeSortable(reinit);
    },
    replaceModule: function (slotId, module, reinit) {
        li = $.nano(this.options.moduleTemplate, module);
        $('div[data-basic=' + slotId + '] ul li [data-id=' + module.id + ']').replaceWith(li);
        this.options.modulesStorage[module.id] = module;
        this.makeSortable(reinit);
    },
    editModule: function (e)
    {
        e.preventDefault();
        this.windowedEditorStart();
        target = $(e.target).closest('li');
        this.options.state = 'edit';
        this.slotInstance = jQuery(target).parents('.def-slot');
        this.currentSlotId = this.slotInstance.data('basic');
        
        this.currentModuleId = target.data('id');
        module = this.options.modulesStorage[this.currentModuleId];
        this.currentModule = AI.loadModule(module.params._Type, 'silent', true);
        this.prepareModuleView();
        this.currentAction = module.params._Action;
        this.viewPort.find('.chooseAction a[data-action="' + module.params._Action + '"]').addClass('bg-success');
        this.renderActionView(module.params._Action, module);
        this.viewPort.find('.save-and-close-slot').click(this.saveAndCloseEditedSlotEditor.bind(this));
        if (typeof module.params.secondaryAction != 'undefined')
        {
            this.secondaryActionRender(module.params.secondaryAction, module);
        }
        //xoad.html.importForm('slotz_moduleTunesForm',module.params);    

    },
    removeSlotz: function ()
    {
        this.container.children().remove();
    },
    renderActionList: function () {
        this.currentModule.connector.execute({
            getModuleActions: true
        });
        str = '';
        if (this.currentModule.connector.result.moduleActions) {
            Object.each(this.currentModule.connector.result.moduleActions, function (item, key) {
                str += jQuery.nano(this.options.actionElement, {
                    alias: item.frontName,
                    action: key
                });
            }.bind(this));
            return str;
        } else {
            alert('no actions defined');
        }
    },
    renderAction: function ()
    {
        this.currentModule();
    },
    renderModuleList: function () {
        var str = '';
        var modulesStr = '';  
        
        Object.each(AI.backOptions.modulesList, function (items, i)
        {
                if(items.actionable)
            {
                        modulesStr += jQuery.nano(this.options.moduleSelectElement, items)
            }

        
        }.bind(this));
        
        str += jQuery.nano(this.options.moduleSelectTemplate, {
                    modules: modulesStr
                });
        return str;
    },
    seconderizeArray: function (data, prefix)
    {
        if (data)
        {
            for (i in data)
            {
                data[prefix + i] = data[i];
                delete data[i];
            }
            return data;
        }
    },
    onSecondaryActionRender: function (e)
    {
        this.secondaryActionRender($(e.target).val(), null);
    },
    secondaryActionRender: function (secondaryAction, module)
    {
        secModule = Object.clone(module);
        if (module)
        {
            secModule.params = secModule.params.__secondary;
        }
        dataArr = this.getActionData(secondaryAction, secModule);
        this.viewPort.find('.secondaryAction').html(dataArr.tpl);
        this.viewPort.find('.secondaryAction').show();
        var dataForms = null;
        if (typeof this.currentModule['onActionRender_' + secondaryAction] == 'function')
        {
            dataForms = this.currentModule['onActionRender_' + secondaryAction](this, dataArr.actionDataForm, secModule);
        }
        if (!dataForms)
        {
            dataForms = {
                actionDataForm: actionDataForm
            };
            if (secModule) dataForms['moduleData'] = secModule.params;
        }
        this.viewPort.find('.secondaryAction input, .secondaryAction select, .secondaryAction textarea').each(function ()
        {
            
            id = '__secondary_' + $(this).attr('id');
            $(this).attr('id', id);
            name = '__secondary.' + $(this).attr('name');
            $(this).attr('name', name);
        });
        this.viewPort.find('.secondaryAction a.xList').each(function ()
        {
            $(this).data('destination', '__secondary_' + $(this).data('destination'));

        });
        dataForms.actionDataForm = this.seconderizeArray(dataForms.actionDataForm, '__secondary.');
        dataForms.moduleData = this.seconderizeArray(dataForms.moduleData, '__secondary.');
        xoad.html.importForm('slotz_moduleTunesForm', dataForms.actionDataForm);
        xoad.html.importForm('slotz_moduleTunesForm', dataForms.moduleData);
    },
    getActionData: function (action, module)
    {
        this.currentModule.connector.execute({
            getActionProperties: {
                action: action,
                data: module
            }
        });
        tpl = this.currentModule.connector.result.tpl;
        actionDataForm = this.currentModule.connector.result.actionDataForm;
        return {
            tpl: tpl,
            actionDataForm: actionDataForm
        };
    },
    renderActionView: function (action, module)
    {                        
        if(this.currentModuleClicked&&!module)module=this.currentModuleClicked;
        dataArr = this.getActionData(action, module);
        this.viewPort.find('.actionInfo').html(dataArr.tpl);
        this.viewPort.find('#secondaryAction').change(this.onSecondaryActionRender.bind(this));
        dataForms = null;
        if (typeof this.currentModule['onActionRender_' + action] == 'function')
        {
            dataForms = this.currentModule['onActionRender_' + action](this, dataArr.actionDataForm, module.params);
        }
        if (!dataForms)
        {
            dataForms = {
                actionDataForm: actionDataForm
            };
            if (module) dataForms['moduleData'] = module.params;
        }
        
        xoad.html.importForm('slotz_moduleTunesForm', dataForms.actionDataForm);
        xoad.html.importForm('slotz_moduleTunesForm', dataForms.moduleData);
        
        this.viewPort.find('.nav-buttons,.save-and-close-slot').removeClass('hide');
        this.viewPort.find('.nav-buttons .prev').addClass('hide');
    },
    onActionSelectClick: function (e)
    {
        e.preventDefault();
        this.viewPort.find('.save-and-close-slot').removeClass('hide');
        this.currentAction = $(e.target).data('action');
        this.currentActionAlias = $(e.target).text();
        this.viewPort.find('.chooseAction a').removeClass('bg-success');
        jQuery(e.target).addClass('bg-success');
        this.renderActionView(this.currentAction);
        this.viewPort.find('.action-info-right').fadeIn(400);
    },
    prepareModuleView: function ()
    {
        this.viewPort.find('.chooseAction').html(this.renderActionList());
        this.viewPort.find('.chooseAction a').click(this.onActionSelectClick.bind(this))
    },
    onModuleSelectClick: function (e) {
        e.preventDefault();

        target=$(e.target).closest('a');
        module = $(target).attr('class');
        this.currentModule = AI.loadModule(module, 'silent', true);
        this.prepareModuleView();
        mlist = AI.getModulesList();        
        this.viewPort.find('#_Alias').val(mlist[module].alias);
        this.currentModuleClicked=mlist[module];
        this.sEditorWindowSlider.goForward();
        this.viewPort.find('.action-info-right').hide();
        this.viewPort.find('.nav-buttons').removeClass('hide');
    },
    windowedEditorStart: function (e)
    {
        this.sEditorWindow = AI.dhxWins.createWindow('slotEditor', 20, 10, 920, 760, 1);
        //this.sEditorWindow.title = _lang_pages['slot_editor'];        
        
        this.sEditorWindow.setText(AI.translate('pages', 'slot-editor'));
        this.sEditorWindow.attachEvent('onHide', function (win) {
            win.close();
        });
        this.sEditorWindow.attachHTMLString(TH.getTpl('pages', 'slotzInterface'));
        this.sEditorWindow.button('park').hide();
        this.sEditorWindow.bringToTop(45);
        this.sEditorWindow.centerOnScreen();
        this.viewPort = jQuery('.slotzEditorSlider');
    },
    onSlotClick: function (e) {
        e.preventDefault();
        this.options.state = 'create';
        slotInstance = jQuery(e.target).parents('.def-slot');
        this.currentSlotId = slotInstance.data('basic');
        this.windowedEditorStart();
        this.viewPort.find('.modulesList').html(this.renderModuleList());
        this.sEditorWindowSlider = this.viewPort.find('.unoSlider').unoSlider({
            animSpeed: 500,
            auto: false,
            prev: '.nav-buttons .prev',
            selector: 'li.slide'
        });
        this.viewPort.find('.modulesList a').click(this.onModuleSelectClick.bind(this));
        this.viewPort.find('.save-and-close-slot').click(this.saveAndCloseSlotEditor.bind(this));
    },
    deleteModule: function (e)
    {
        
        e.preventDefault();
        li = jQuery(e.target).closest('.list-group-item');        
        slotName = jQuery(e.target).parents('.def-slot').data('basic');
        var  id = li.data('id');
        delete this.options.modulesStorage[id];
        this.removeFromModuleMap(slotName,id);        
        li.remove();
    },
    
    activateModule: function (e)
    {
        e.preventDefault();
        target= jQuery(e.target);
        li = target.closest('.list-group-item');
        id = li.data('id');
        if(target.is( "a" ))
        {
           target=target.find('i'); 
        } 
     
        if(this.options.modulesStorage[id]['params']['Active'])
        {
            target.removeClass('fa-check-square-o').addClass('fa-square-o');    
            this.options.modulesStorage[id]['params']['Active']=0;
        }else{
            target.removeClass('fa-square-o').addClass('fa-check-square-o');    
            this.options.modulesStorage[id]['params']['Active']=1;
            
        }
        
        this.options.modulesStorage[id];

    },
    
    
    
    rebuildModulesMap: function (e,kid)
    {
        
        this.options.modulesMap = this.options.slotlist;
        jQuery(this.options.slotsContainer + '>div').each(function (i, item)
        {
            
            if(kid&&kid.sender)
            {
                _slot=kid.sender.parent().data('basic');    
                _module=kid.item.data('id');    
                this.removeFromModuleMap(_slot,_module)            
            }
            
            var id = jQuery(item).data('basic');
            modules = $(item).find('ul li');
            
            if (modules.length > 0)
            {
                this.clearModuleMap(id);
                modules.each(function (k, module)
                {
                    
                    if (moduleId = jQuery(module).data('id'))
                    {
                        this.addToModuleMap(id, moduleId);
                    }
                }.bind(this));
            }
        }.bind(this));
    },
    exportModules: function ()
    {
        exp = this.options.modulesMap;
        result = {
        };
        
        Object.each(exp, function (module, slot)
        {
        
            if (module.length > 0)
            {
                Array.each(module, function (moduleId)
                {
                    if (moduleId)
                    {
                        if (!result[slot]) result[slot] = [];
                        result[slot].push(this.options.modulesStorage[moduleId]);
                    }
                }.bind(this));
            }
        }.bind(this));
        return result;
    },
    
    clearModuleMap:function(slotId)
    {    
      this.options.modulesMap[slotId]=[];  
    },
    
    addToModuleMap: function (slotId, moduleId)
    {                
        
        this.options.modulesMap[slotId].push(moduleId);
        
    },
    
    removeFromModuleMap: function (slotId, moduleId)
    {
        ind=this.options.modulesMap[slotId].indexOf(moduleId);
        if(ind!==-1)
        {
            delete this.options.modulesMap[slotId][ind];
        }
        
        
        
    },
    
    createModuleItem: function ()
    {
        data = xoad.html.exportForm('slotz_moduleTunesForm');
        data['_Action'] = this.currentAction;
        mItem = new moduleItem({
            _Type: this.currentModule.name,
            _Name: data._Alias,
            _ActionAlias: this.currentActionAlias,
            frontActionName:this.currentActionAlias,
            _Action: data._Action,
            _Cache: data._Cache,
             Active:true,
            
            _Priority: data._Priority
        }, data);
        return mItem;
    },
    saveAndCloseEditedSlotEditor: function (e)
    {
        e.preventDefault(e);
        mItem = this.createModuleItem();
        mItem.setOptions({
            id: this.currentModuleId
        });
        this.replaceModule(this.currentSlotId, mItem.get(), true);
        this.sEditorWindow.close();
    },
    saveAndCloseSlotEditor: function (e)
    {
        e.preventDefault(e);
        data = xoad.html.exportForm('slotz_moduleTunesForm');
        this.options.modulesStorageIterator++;
        mItem = this.createModuleItem();
        mItem.setOptions({
            id: this.options.modulesStorageIterator
        });
        this.addToModuleMap(this.currentSlotId, mItem.options.id);
        
        //this.options.connector.execute({saveModule:{slot:this.currentSlotId,pageId:pageId,data:mItem.get()}});    
        this.addModule(this.currentSlotId, mItem.get(), false);
        this.sEditorWindow.close();
    },
    buildSlots: function (slots) {
        
		Object.each(slots, function (item) {
		
            i++;
            if (i % 2 == 0)
            {
                item.lter = 'lter';
            }
            this.container.append(jQuery.nano(this.options.slotTemplate, item));
            this.options.slotlist[item.basic] = [
            ];
            if (this.options.modulesInstance)
            {
                if (moduleItems = this.options.modulesInstance[item.basic]) {
                    Array.each(moduleItems, function (mItem) {
                        this.addModule(item.basic, mItem, false);
                    }.bind(this));
                }
            }
        }.bind(this));
        this.rebuildModulesMap();
        jQuery('a.add-mod').click(this.onSlotClick.bind(this));
    },
    
    
    
    makeSortable: function (destroyFirst) {
        sortSel = $(this.options.slotsSelector);
        if (destroyFirst) sortSel.sortable('destroy');
        sortSel.sortable({
            axis: 'y',
            update: this.rebuildModulesMap.bind(this),
            revert: 100
        }).disableSelection();
        sortSel.sortable('option', 'connectWith', '.connectedSortable');
    },
    initiateSlotz: function (slots, saveModulesState) {
        this.removeSlotz();
        if (saveModulesState)
        {
            this.options.modulesInstance = this.options.modulesStorage;
        }
        this.buildSlots(slots);
        this.makeSortable();
    }
});
