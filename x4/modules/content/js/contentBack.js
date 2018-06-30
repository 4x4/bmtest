smartBlocks = new Class({

    Implements: Options,
    blockTypes: {},
    elements: [],
    blockIdMatrix: {},
    iterator: [],
    container: null,

    options: {
        mainTpl: '<ul class="smartblocks list-group">{list}</ul>',
        elementTpl: "<li class='list-group-item'>{element}</li>",
        groupedElementTpl: "<li name='{blockId}' class='grouped list-group-item'><h4 class='ui-state-disabled'>{blockAlias}</h4>{element}" +
        "<p class='text-center ui-state-disabled'><a href='#' data-id='{id}' class='btn-sm btn-info replicate'>" + AI.translate('common', 'add') + "</a></p></li>",
        subMainTpl: '<ul class="padder" style="display:{display}">{list}<li class="list-group-item" >' +
        '<a class="btn-xs btn-warning remove" href="#">' + AI.translate('common', 'delete') + '</a>' +
        '<a class="btn-xs btn-info pull-right handle" href="#"><i class="fa  fa-arrows-alt"></i></a>' +

        '</li></ul>',
        subElementTpl: "<li>{element}</li>"
    },

    initialize: function (selector, options) {
        this.setOptions(options);
        this.container = $(selector);

        this.setBlockType('INPUT', {
            template: '<div class="form-group"><label>{blockAlias}</label><input id="{blockId}" class="form-control"  name="{blockName}"></div>'
        });

        this.setBlockType('TEXT', {
            template: '<div class="form-group"><label>{blockAlias}</label> <span class="pull-right"> <a href="#" class="btn btn-xs btn-default editorApplyButton">' + AI.translate('common', 'initiate-editor') + '</a></span>' + '<textarea rows=8 class="form-control ck-apply" id="{blockId}" name="{blockName}"></textarea></div>'
        });

        this.setBlockType('IMAGE', {
            template: '<div class="form-group"><label>{blockAlias}</label><div class="input-group">' + '<input  id="{blockId}" name="{blockName}" class="form-control validate-selection text-sm" readonly="" >' +
            '<span class="input-group-btn"><a class="fileManagerApplyButton btn btn-default"> <i class="fa fa-folder-open-o"></i></a></span>' + '<span class="input-group-btn"><a class="fileManagerClearInput btn btn-default"> <i class="fa fa-times"></i></a>' + '</span></div></div>'
        });

        jQuery(document).off("click", 'a.replicate');
        jQuery(document).off("click", 'a.remove');

        jQuery(document).on('click', 'a.replicate', this.replicate.bind(this));
        jQuery(document).on('click', 'a.remove', this.remove.bind(this));

    },

    removeAll: function (selector) {
        if (this.container.find('.smartblocks li').length > 0) {
            this.container.find('.smartblocks li').remove();
        }

        this.elements = [];
        this.blockIdMatrix = {};
        this.iterator = [];
        this.container = $(selector);

    },

    remove: function (e) {
        e.preventDefault();
        el = $(e.target);
        ul = el.parent().parent();
        ul.hide(300, function () {
            $(this).remove()
        });

    },

    colorGenerate: function () {
        return 'rgb(' + (Math.floor((256 - 229) * Math.random()) + 230) + ',' +
            (Math.floor((256 - 229) * Math.random()) + 230) + ',' +
            (Math.floor((256 - 229) * Math.random()) + 230) + ')';
    },

    /**
     * add block
     *
     * @param type
     * @param options
     */
    setBlockType: function (type, options) {
        this.blockTypes[type] = options;
    },


    setElement: function (element) {
        if (element.type != 'GROUP') element.blockName = '__root.' + element.blockName;
        this.elements.push(element);
        this.blockIdMatrix[element.blockId] = this.elements.length - 1;
    },

    replicateOnLoad: function (replics) {
        Object.each(replics, function (k, v) {


            id = this.blockIdMatrix[v];
            for (i = 0; i < k; i++) {

                this.container.find('ul li.grouped[name=' + v + '] p').before(this.renderGroupElement(this.elements[id], id, false));
            }

            this.container.find('ul li.grouped[name=' + v + ']').sortable({
                axis: "y",
                revert: 100,
                cancel: ".ui-state-disabled",
                handle: ".handle",

                cursorAt: {
                    bottom: 1
                }
            });

        }.bind(this));


    },

    replicate: function (e) {
        e.preventDefault();
        el = $(e.target);
        data = el.data();
        el.parent().before(this.renderGroupElement(this.elements[data.id], data.id, true));
        el.parent().prev().show(200);

        el.parents('ul li.grouped').sortable({
            axis: "y",
            revert: 100,
            handle: ".handle",
            cancel: ".ui-state-disabled",
            cursorAt: {
                bottom: 1
            }
        });


    },

    renderElement: function (element, id) {

        if (blockType = this.blockTypes[element.type]) {
            return $.nano(blockType.template, element);
        }

    },

    renderGroupElement: function (element, id, display) {

        elements = [];
        if (!this.iterator[id]) this.iterator[id] = 0;
        this.iterator[id]++;
        Array.each(element.elements, function (item, i) {
            bitem = jQuery.extend({}, item);
            bitem['blockName'] = element.blockName + '.' + item['blockName'] + '__' + (this.iterator[id]);
            elements[i] = bitem;
        }.bind(this));

        return $.nano(this.options.subMainTpl, {
            display: display ? 'none' : 'block',
            background: this.colorGenerate(),
            blockAlias: element.blockAlias,
            list: this.render(elements, this.subElementTpl, true)
        });

    },

    build: function () {
        list = this.render(this.elements);
        this.container.append($.nano(this.options.mainTpl, {
            list: list
        }));
    },

    render: function (elements, tpl, dnrElemnets) {
        var list = '';
        if (!tpl) tpl = this.options.elementTpl;
        Array.each(elements, function (item, i) {
            if (item.type == 'GROUP') {

                if (dnrElemnets) {
                    groupElements = this.renderGroupElement(item, i);
                } else {
                    groupElements = '';
                }
                list += $.nano(this.options.groupedElementTpl, {
                    id: i,
                    blockAlias: item.blockAlias,
                    blockId: item.blockId,
                    element: groupElements
                });

            } else {
                list += $.nano(tpl, {
                    element: this.renderElement(item, i)
                });

            }

        }.bind(this));

        return list;

    }
});


contentBack = new Class({
    Extends: _xModuleBack,
    initialize: function (name) {

        this.setName(name);
        this.parent();
        this.setLayoutScheme('treeView', {
            treeSize: 'xl',
            rightPanelWidth: '890px',
            customTreeHtml: this.getTpl('customContentTree')
        });
        this.objTypeScope = ['_CONTENT', '_CONTENTGROUP'];
        this.pushToTreeClickMap('_CONTENTGROUP', 'showContentsList');
    },

    onHashDispatch: function (e, v) {

        this.tabs.makeActive('t' + e);

        return true;
    },

    CRUN: function () {
        _CONTENTGROUP = new Class({
            Extends: CRUN,
            initialize: function (context) {
                this.parent(context, {
                    objType: '_CONTENTGROUP',
                    autoCreateMethods: true
                });

            },
            createWindow: function (txt, header) {
                this.window = AI.dhxWins.createWindow("contentCategoryWindow", 20, 10, 450, 180, 1);
                this.window.button('park').hide();

                this.window.attachEvent("onHide", function (win) {
                    this.context.navigate('dummy');
                    win.close();
                }.bind(this));
                this.window.setText(header);
                this.window.attachHTMLString(txt);
                this.window.setModal(true);
                this.window.centerOnScreen();
                this.windowContext = this.window.dhxcont;

            },

            create: function (data) {
                txt = this.context.getTpl(this.options.objType);
                this.createWindow(txt, AI.translate('content', 'add-category'));
                this.form = jQuery("#create" + this.options.objType);
                this.form.validationEngine();
                xoad.html.importForm("create" + this.options.objType, {type: this.context.propertiesSelector});
                $(this.windowContext).find('.save').click(this.save.bind(this));

            },


            save: function (e) {
                e.preventDefault();
                this.parent();

                if (this.validated) {
                    data = xoad.html.exportForm("create" + this.options.objType);
                    this.context.connector.execute({onSave_CONTENTGROUP: {data: data}});

                    if (this.context.connector.result.onSave_CONTENTGROUP) {
                        AI.navigate(AI.navHashCreate(this.context.name, 'dummy'));
                        this.context.tree.refreshItem(1);
                        this.window.close();
                    }


                }
            },


            saveEdited: function (e) {
                e.preventDefault();
                this.parent();

                if (this.validated) {
                    data = xoad.html.exportForm("edit" + this.options.objType);
                    this.context.connector.execute({onSaveEdited_CONTENTGROUP: {id: this.currentId, data: data}});

                    if (this.context.connector.result.onSaveEdited_CONTENTGROUP) {
                        AI.navigate(AI.navHashCreate(this.context.name, 'dummy'));
                        this.context.tree.refreshItem(1);
                        this.window.close();
                    }


                }
            },

            edit: function (data, kid) {

                txt = this.context.getTpl(this.options.objType + '@edit');
                this.createWindow(txt, AI.translate('content', 'edit-category'));
                this.form = jQuery("#edit" + this.options.objType);
                this.form.validationEngine();
                this.currentId = kid;
                result = this.context.connector.execute({onEdit_CONTENTGROUP: {id: kid}});

                xoad.html.importForm("edit" + this.options.objType, result.data);
                $(this.windowContext).find('.save').click(this.saveEdited.bind(this));


            }

        });

        _CONTENT = new Class({
            Extends: CRUN,
            initialize: function (context) {
                this.parent(context, {
                    objType: '_CONTENT',
                    autoCreateMethods: true
                });
            },

            buildSmartBlocks: function (tpl) {
                if (!this.context._smartBlocks) {
                    this.context._smartBlocks = new smartBlocks('#dynamicFields');
                } else {
                    this.context._smartBlocks.removeAll('#dynamicFields');
                }


                this.context.connector.execute({parseTemplate: {Template: tpl}});

                if (fields = this.context.connector.result.fields) {
                    Array.each(fields, function (item, i) {

                        element = {
                            type: item.type,
                            blockAlias: item.name,
                            blockId: item.id,
                            blockName: item.id
                        };


                        if (item.type == 'GROUP') {
                            element['elements'] = [];

                            Object.each(item.items, function (subitem, k) {

                                element['elements'].push(
                                    {
                                        type: subitem.type,
                                        blockAlias: subitem.name,
                                        blockId: k,
                                        blockName: k
                                    }
                                );
                            }.bind(this));
                        }
                        this.context._smartBlocks.setElement(element);

                    }.bind(this));

                    this.context._smartBlocks.build();
                }

            },

            onTplChange: function (e) {
                el = $(e.target);
                tpl = el.val();
                this.buildSmartBlocks(tpl);
            },

            create: function (data) {


                selectedId = this.context.tree.getSelectedRowId();
                parentData = {};

                if (selectedId) {
                    objType = this.context.tree.getRowAttribute(selectedId, "obj_type");
                    if (['_ROOT'].indexOf(objType) == -1) parentData = {
                        id: selectedId
                    }

                }

                this.parent(parentData);

                if (parentData.id) {
                    this.context.viewPort.find('input[name=ancestor]').val(this.context.getTreePathAncestor(null));
                    this.context.viewPort.find('input[name=ancestorId]').val(selectedId);

                }
                xoad.html.importForm('create_CONTENT', this.context.connector.result.data);
                this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();

                this.context.viewPort.find('select[name=Template]').change(this.onTplChange.bind(this));


            },

            saveEdited: function (e) {
                e.preventDefault();
                this.parent();
                if (this.validated) {
                    data = xoad.html.exportForm('edit_CONTENT');
                    dynamicFieldsForm = xoad.html.exportForm('dynamicFieldsForm');

                    this.context.connector.execute({
                        onSaveEdited_CONTENT: {
                            id: this.selectedId,
                            data: data,
                            dynamicFieldsForm: dynamicFieldsForm
                        }
                    });
                }

            },
            save: function (e) {
                e.preventDefault();
                this.parent();
                if (this.validated) {
                    data = xoad.html.exportForm('create_CONTENT');
                    dynamicFieldsForm = xoad.html.exportForm('dynamicFieldsForm');

                    result = this.context.connector.execute({
                        onSave_CONTENT: {
                            data: data,
                            dynamicFieldsForm: dynamicFieldsForm
                        }
                    });

                    if (result.onSave_CONTENT) {
                        this.context.navigate('showContentsList', {id: data.ancestorId});
                    }
                }

            },

            edit: function (params) {

                this.parent(params);


                this.context.tabs.addTab({
                    id: 'teditcontent',
                    name: AI.translate('content', 'edit_content'),
                    temporal: true,
                    active: true
                }, true);

                result = this.context.connector.execute({onEdit_CONTENT: {id: params.id}});


                this.buildSmartBlocks(result.content.params.tpl);
                if (result.replics) this.context._smartBlocks.replicateOnLoad(result.replics);


                xoad.html.importForm("edit" + this.options.objType, result.content.params);
                if (result.staticBlocks) xoad.html.importForm("dynamicFieldsForm", result.staticBlocks);
                if (result.dynamicBlocks) xoad.html.importForm("dynamicFieldsForm", result.dynamicBlocks);

                this.context.viewPort.find('select[name=Template]').change(this.onTplChange.bind(this));

                edits = this.context.mainViewPortFind('.ck-apply');

                if (edits.length > 0) {
                    edits.each(function (k, v) {
                        $(this).hide();
                        id = $(this).attr('name');
                        $('<div style="height:250px" id="ace_' + id + '"></div>').insertBefore($(this));
                        var editor = ace.edit($(this).prev()[0]);
                        editor.getSession().setValue($(this).val());
                        editor.getSession().setUseWrapMode(true);
                        editor.setTheme("ace/theme/textmate");
                        editor.session.setMode("ace/mode/html");

                        editor.setOptions({
                            fontSize: "12pt"
                        });
                        var that = $(this);

                        that[0].changeIt = function (txt) {
                            editor.getSession().setValue(txt);
                        };

                        editor.getSession().on('change', function () {
                            that.val(editor.getSession().getValue());
                        });

                    });
                }


                this.context.mainViewPortFind('.btn-group .btn-switch').btnSwitch();


            }

        });

        CONTENTGROUP = new _CONTENTGROUP(this);
        CONTENT = new _CONTENT(this);
    },

    deleteContent: function (kid, id) {
        this.deleteObjectGrid(this.gridlist);
    },


    copyObj: function (id, kid) {

        if (selected = this.gridlist.getSelectedId(true)) {
            this.selectedCopyBuffer = selected;
        }

    },

    pasteObj: function (id, kid, keys, gc) {

        this.pasteObjectGrid(this.gridlist, kid, 'copyContent', this.selectedCopyBuffer);
        this.refreshContentsList(this.currentListId);
    },


    showContentsList: function (id) {

        this.tabs.addTab({
            id: 'tshowContentsList',
            name: AI.translate('content', 'contents-list'),
            temporal: true,
            active: true
        }, true);

        this.setGridView('contentListContainer', 550, true);

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();

        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }

        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteContent.bind(this));
        menu.addNewChild(menu.topId, 0, "copy", AI.translate('common', 'copy'), false, '', '', this.copyObj.bind(this));
        menu.addNewChild(menu.topId, 0, "paste", AI.translate('common', 'paste'), false, '', '', this.pasteObj.bind(this));

        this.gridlist = new dhtmlXGridObject('contentListContainer');
        this.gridlist.selMultiRows = true;
        this.gridlist.setImagePath("xres/ximg/grid/imgs/");
        this.gridlist.setHeader('id,' + AI.translate('content', 'date-change') + ',' + AI.translate('content', 'contents-name') + ',' + AI.translate('common', 'tags') + ',' + AI.translate('common', 'author') + ',' + AI.translate('common', 'comments') + ',' + AI.translate('common', 'active'));

        this.gridlist.setInitWidths("80,130,*,80,80,120,90");
        this.gridlist.setColAlign("center,left,left,center,center,left");
        //        this.gridlist.attachEvent("onCheckbox",this.switch_comment.bind(this));  
        //    this.gridlist.attachHeader("#text_filter,#text_filter,#text_filter,#text_filter,#select_filter");

        this.gridlist.attachEvent("onRowDblClicked", function (kid) {
            this.navigate('edit_CONTENT', {id: kid})
        }.bind(this));
        this.gridlist.setColTypes("ro,ro,ro,ro,ro,ch");
        this.gridlist.setColSorting("int,date_rus,str,str,str");
        this.gridlist.enableAutoWidth(true);
        this.gridlist.enableDragAndDrop(true);
        this.gridlist.enableContextMenu(menu);
        this.gridlist.init();
        this.gridlist.setSkin("modern");


        this.gridlist.rowToDragElement = function (id) {
            if (this.cells(id, 2).getValue() != "") {
                return this.cells(id, 2).getValue() + "/" + this.cells(id, 1).getValue();
            } else {
                return this.cells(id, 1).getValue();
            }
        };


        this.gridlist.gridToTreeElement = function (tree, fakeID, gridID, treeID) {
            /*                        this.connector.execute({changeAncestor:{ancestor:treeID,id:gridID}});            
             if(this.connector.result.dragOK)
             {
             XTR_main.set_result(_lang_fusers['user_moved']);
             return true;
             }else{*/
            return -1;
            /*}*/

        }.bind(this);

        this.currentListId = id;
        this.refreshContentsList(id);

    },


    refreshContentsList: function (rid) {
        this.gridlist.clearAll();
        this.connector.execute({contentsTable: rid});
        if (this.connector.result.data_set) {
            this.gridlist.parse(this.connector.result.data_set, "xjson")
        }

    },


    onTreeDialogReturn: function (dialog, data) {

    },

    onDialogObjectClick: function (id) {
        nameArr = this.dialogGroupTree.getParentPath(id, 0);
        name = nameArr.join('/');
        objTypesToSelect = this.dialogContext.info.split(',');
        objType = this.dialogGroupTree.getRowAttribute(id, "obj_type");
        if (objTypesToSelect.indexOf(objType) != -1) {
            this.dialogContext.returnData({
                id: id,
                name: name
            });

        }

    },


    dialogGroupTreeDynamicXLS: function (id) {
        this.connector.execute({
            treeDynamicXLS: {
                id: id
            }
        });
        if (this.connector.result) {
            if (id == 0) {
                this.dialogGroupTree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.dialogGroupTree.json_dataset = this.connector.result.data_set;
            }
        }
        return true;
    },


    dialogGroupTreeDynamicFullXLS: function (id) {
        this.connector.execute({
            treeDynamicFullXLS: {
                id: id
            }
        });

        if (this.connector.result) {
            if (id == 0) {
                this.dialogGroupTree.parse(this.connector.result.data_set, "xjson")
            } else {
                this.dialogGroupTree.json_dataset = this.connector.result.data_set;
            }
        }
        return true;
    },


    onDialogGroup: function (dialogContext) {

        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('content', 'content-groups'));
        this.dialogGroupTree.setInitWidths("*");
        this.dialogGroupTree.setColAlign("left");
        this.dialogGroupTree.setColTypes("tree");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupTreeDynamicXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupTreeDynamicXLS(0);
        $(this.dialogGroupTree.entBox).find('.ev_dhx_skyblue').hide();
        this.dialogGroupTree.openItem(1);
    },


    onDialogGroupFull: function (dialogContext) {

        this.dialogContext = dialogContext;
        this.dialogGroupTree = dialogContext.window.attachGrid();
        this.dialogGroupTree.imgURL = "/x4/adm/xres/ximg/green/";
        this.dialogGroupTree.setHeader(AI.translate('content', 'content-groups'));
        this.dialogGroupTree.setInitWidths("*");
        this.dialogGroupTree.setColAlign("left");
        this.dialogGroupTree.setColTypes("tree");
        this.dialogGroupTree.init();
        this.dialogGroupTree.kidsXmlFile = 1;
        this.dialogGroupTree.attachEvent("onDynXLS", this.dialogGroupTreeDynamicFullXLS.bind(this));
        this.dialogGroupTree.setSkin("dhx_skyblue");
        this.dialogGroupTree.attachEvent("onRowDblClicked", this.onDialogObjectClick.bind(this));
        this.dialogGroupTreeDynamicXLS(0);
        $(this.dialogGroupTree.entBox).find('.ev_dhx_skyblue').hide();
        this.dialogGroupTree.openItem(1);
    },


    tabsStart: function () {
        var oTabs = [{
            id: 't_firstpage2',
            name: AI.translate('common', 'info'),
            temporal: true,
            active: true
        }, {
            id: 'tcreate_CONTENT',
            name: AI.translate('content', 'add_content'),
            href: AI.navHashCreate(this.name, 'create_CONTENT')
        }


        ];

        this.tabs = new Tabs(this.tabsViewPort, oTabs);

    },

    deleteGroup: function () {
        this.deleteObjectGrid(this.tree);
    },

    buildInterface: function () {

        this.parent();
        /*--tabs--*/
        this.tabsStart();
        /*--menu--*/

        menu = new dhtmlXMenuObject();
        menu.renderAsContextMenu();


        if (__globalLogLevel == 9) {
            menu.addNewChild(menu.topId, 0, "console-it", 'console-it', false, '', '', this.consoleIt.bind(this));

        }


        menu.addNewChild(menu.topId, 0, "delete", AI.translate('common', 'delete'), false, '', '', this.deleteGroup.bind(this));
        menu.addNewChild(menu.topId, 0, "refresh", AI.translate('common', 'refresh'), false, '', '', this.refreshTree.bind(this));
        menu.addNewChild(menu.topId, 0, "edit", AI.translate('common', 'edit'), false, '', '', this.edit_CONTENTGROUP.bind(this));
        //     $(this.treeViewPort).before('<a class="content-add-category" href="' + AI.navHashCreate(this.name, 'create_CONTENTGROUP') + '">' + AI.translate('content', 'add-category') + '<a>');

        this.tree = new dhtmlXGridObject(this.treeViewPort);
        this.tree.selMultiRows = true;
        this.tree.imgURL = "/x4/adm/xres/ximg/green/";
        this.tree.setHeader(AI.translate('content', 'group_name'));

        $(this.treeViewPort).find('.hdr').hide();

        this.tree.setInitWidths("*");
        this.tree.setColAlign("left");
        this.tree.setColTypes("tree");
        this.tree.enableDragAndDrop(true);
        //   tree.enableEditEvents(false,false,true);
        this.tree.attachEvent("onDrag", this.onTreeGridDrag.bind(this));
        this.tree.setDragBehavior('complex-next');
        this.tree.enableMultiselect(true);
        this.tree.enableContextMenu(menu);

        this.tree.init();
        this.tree.kidsXmlFile = 1;
        this.tree.attachEvent("onDynXLS", this.treeDynamicXLS.bind(this));
        this.tree.setSkin("dhx_skyblue");
        this.tree.attachEvent("onRowDblClicked", this.treeObjectClicked.bind(this));
        this.treeDynamicXLS(0);
        $(this.tree.entBox).find('.ev_dhx_skyblue ').hide();
        this.tree.openItem(1);

    },


    onActionRender_showContent: function (context, data) {
        context.container.find('select[name=Template]').chosen({ width: '100px'});
    }


});
