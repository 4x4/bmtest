var FileMan = new Class({
  Implements: [
    Options,
    Events
  ],
  copyBuffer: null,
  copyPath: null,
  options:
  {
    prefixPath: '/media/',
    speed: 1000,
    currentPath: [
      ''
    ]
  },
  initialize: function (selector, options)
  {
    this.setOptions(options);
    jQuery(selector).click(this.open.bind(this));
    this.fileman = AI.dhxWins.createWindow('fileman', 20, 10, 930, 700, 1);
    this.fileman.setText(AI.translate('common', 'file-manager'));
    this.container = $(this.fileman.dhxContGlobal.dhxcont.mainCont);
    this.container.css({
      'overflow-y': 'hidden'
    });
    this.fileman.bringToTop(99);
    this.fileman.attachHTMLString(TH.getTpl('AdminPanel', 'fileManager'));
    this.fileman.button('minmax1').hide();
    this.fileman.attachEvent("onHide", this.onHide.bind(this));
    
    this.folderSelect=false;
    this.currentClickItemHandler = this.defaultClickItemHandler.bind(this);
    this.container.find('a.back').click(this.back.bind(this));
    
    
    
    this.container.find('.viewSelect li a').click(this.viewSelect.bind(this));
    this.container.find('a.createFolder').click(this.createFolder.bind(this));
    
    this.container.find('a.selectFolder').click(this.selectFolder.bind(this));
    
    this.filesBody = this.container.find('#files');
    this.connector = new Connector('matrixFileManager', '.class');
    //this.tree = new dhtmlXTreeObject("files-tree","100%","100%",0);
    this.imageshow = jQuery('#imageshow');
    jQuery(this.imageshow).hide();
    this.menu = new dhtmlXMenuObject();
    this.menu.renderAsContextMenu();
    this.menu.addNewChild(this.menu.topId, 0, 'refresh', AI.translate('common', 'refresh'), false, '', '', this.renderFiles.bind(this));
    this.menu.addNewChild(this.menu.topId, 0, 'select', AI.translate('common', 'select_all'), false, '', '', this.selectAll.bind(this));
    this.menu.addNewChild(this.menu.topId, 0, 'copy', AI.translate('common', 'copy'), false, '', '', this.copy.bind(this));
    this.menu.addNewChild(this.menu.topId, 0, 'paste', AI.translate('common', 'paste'), false, '', '', this.paste.bind(this));
    this.menu.addNewChild(this.menu.topId, 0, 'delete', AI.translate('common', 'delete'), false, '', '', this.deleteAll.bind(this));
    this.menu.addNewChild(this.menu.topId, 0, 'download', AI.translate('common', 'download'), false, '', '', this.download.bind(this));    
    this.menu.addNewChild(this.menu.topId, 0, 'IPTC_data', AI.translate('common', 'IPTC_data'), false, '', '', this.IPTCdata.bind(this));
    
   this.menu.attachEvent("onShow", function(id)
    {
        menuLink=this;
    });
    
    
    this.data = new dhtmlXDataView('files');
    this.data.define('type', 'ficon');
    this.data.attachEvent('onItemDblClick', this.onItemDblClick.bind(this));
    this.data.attachEvent('onBeforeContextMenu', this.onBeforeContextMenus.bind(this));
    this.data.customize({
      icons_src_dir: '/x4/adm/xjs/_components/dhtmlx/dataview/codebase/imgs'
    });
    this.fileman.attachEvent('onResizeFinish', function (win)
    {
      this.data.refresh();
    }.bind(this));
    this.fileman.hide();
    jQuery(document).on('click', '.fileManagerApplyButton', this.applyButtonClick.bind(this));
    jQuery(document).on('click', '.fileManagerClearInput', this.clearButtonClick.bind(this));
    jQuery(document).on('click', '#fm .iptccancel', this.cancelIPTCWin.bind(this));
     jQuery(document).on('click', '#fm .iptcsave', this.IPTCdataSet.bind(this));
        
    this.dropzone = new Dropzone(this.container.find('div#dropzone') [0], {
      url: '/admin.php?action=getfile',
      dictDefaultMessage: AI.translate('matrix', 'drop_files_here_to_upload'),
      dictFallbackMessage: AI.translate('matrix', 'browser_not_support_drag_upload'),
      dictFallbackText: AI.translate('matrix', 'use_form_to_upload'),
      dictFileTooBig: AI.translate('matrix', 'file_is_too_big'),
      dictInvalidFileType: AI.translate('matrix', 'cant_upload_this_file_type'),
      dictResponseError: AI.translate('matrix', 'server_respond_with_code'),
      dictCancelUpload: AI.translate('matrix', 'cancel_upload'),
      dictCancelUploadConfirmation: AI.translate('matrix', 'shure_to_cancel'),
      dictRemoveFile: AI.translate('matrix', 'remove_file'),
      dictRemoveFileConfirmation: null,
      dictMaxFilesExceeded: AI.translate('matrix', 'max_files_exceed')
    }
    );
           
    this.dropzone.on('complete', function (file)
    {
      this.dropzone.removeFile(file);
      setTimeout(this.renderFiles.bind(this), 100);
    }.bind(this));
    jQuery('#files').bind('contextmenu', this.context.bind(this))

  },
  
  
  cancelIPTCWin:function(e)
  {      
      e.preventDefault();
      $('#iptcwin').hide();      
  },
  
  IPTCdataSet:function(e)
  {
        e.preventDefault();
        data=xoad.html.exportForm('iptcForm');
        data['filename']=this.iptcFile;        
        this.connector.execute({setIPTC:data});        
        $('#iptcwin').hide();          
        
        
  },
  
  IPTCdata:function(id)
  {      
      
      items = this.getSelectedItems();
      
      if(items)
      {
                filename=this.parsePath(this.options.currentPath)+items[0];
                this.connector.execute(
                 {
                    getIPTC:
                     {
                      filename:  filename
                     }
                });
                
                if(this.connector.result.iptc.enabled)
                {
                    xoad.html.importForm('iptcForm',this.connector.result.iptc);
                    this.iptcFile=filename;
                    $('#iptcwin').show();                    
                }
                
      }
      
      
  },
  
  download:function(e)
  {
    if (name = this.getSelectedItems())
    {
        
      this.connector.execute({
        pushFileToDownload: {
          name: this.parsePath(this.options.currentPath)+name      
        }
      });
    
      if (this.connector.result.pushed)
      {
        $.fileDownload('/admin.php?action=download');
      }
    
    }
      
      
  },
  
  onHide:function(e)
  {
       AI.dhxWins.forEachWindow(function(win){          
          win.bringToTop(99);    
      });
  
  },
  
  viewSelect: function (e)
  {
    e.preventDefault();
    this.data.define('type', $(e.target).data('view'));
    this.data.customize({
      icons_src_dir: '/x4/adm/xjs/_components/dhtmlx/dataview/codebase/imgs'
    });
  },
  setPrefixPath: function (prefix)
  {
    this.options.prefixPath = prefix;
  },
  clearButtonClick: function (event)
  {
    event.preventDefault();
    target = $(event.target);
    $(target.parents('.input-group') [0]).find('input').val('');
  },
  
  dirname:function(path) {
  
  return path.replace(/\\/g, '/')
    .replace(/\/[^\/]*\/?$/, '');
  },

  applyButtonClick: function (event)
  {
    event.preventDefault();
    this.targetElement = $(event.target);
    
    
    
    if(!this.targetElement.hasClass('fileManagerApplyButton') || this.targetElement.hasClass('fa'))
    {
        this.targetElement=this.targetElement.parent();        
    }
    
    this.applyButtonOptions = $(this.targetElement).data();    
    
    this.currentClickItemHandler = this.returnPathClickHandler.bind(this);    
    
    input=$(this.targetElement).closest('.input-group').find('input'); 
    
    
    currentPath=input.val();
    
    if(currentPath)
    {
        currentPath=currentPath.replace('//',"/");
        currentPath=currentPath.replace(this.options.prefixPath,"");
        currentPath=this.dirname(currentPath);         
        currentPath=currentPath.split('/').reverse();                
        this.options.currentPath=currentPath;
    }
    
    
    if(this.applyButtonOptions&&this.applyButtonOptions.target)
    {

        switch (this.applyButtonOptions.target)        
        {
            case 'folder':
            {
                this.currentClickItemHandler = this.returnFolderClickHandler.bind(this);   
            }
             
        }
        
    }
    
   
    this.open();
  },
  defaultClickItemHandler: function (id, ev)
  {
    alert(this.data.get(id).name);
  },
  enableUpperPanel: function (state)
  {
    if (state) {
      this.container.find('.panel').show()
    } else {
      this.container.find('.panel').hide();
    }
  },
  enableBottomPanel: function (state)
  {
    if (state) {
      this.container.find('.panel').show()
    } else {
      this.container.find('.panel').hide();
    }
  },
  enableDropZone: function (state)
  {
    if (state) {
      $(this.dropzone.element).show()
    } else {
      $(this.dropzone.element).hide();
    }
  },
  
  returnFolderClickHandler:function()
  {
      
  },
  
  getPathForCurrentElement:function(id)
  {
        path=this.options.prefixPath + this.parsePath(this.options.currentPath) + this.data.get(id).name;
        return path.replace('//',"/");
  },
  
  returnPathClickHandler: function (id, ev)
  {
   
    this.fileman.hide();   
    itemPath = this.getPathForCurrentElement(id);    
    $(this.targetElement.parents('.input-group') [0]).find('input').val(itemPath);
    this.targetElement.data('itemPath', itemPath);
  },
  
  selectFolder:function(es)
  {
      this.fileman.hide();      
      itemPath = this.options.prefixPath + this.parsePath(this.options.currentPath);
      $(this.targetElement.parents('.input-group') [0]).find('input').val(itemPath);
      this.targetElement.data('itemPath', itemPath);      
  },
  
  getSelectedItems: function ()
  {
    if (selected = this.data.getSelected(true))
    {
      names = [];
      for (i = 0; i < selected.length; i++)
      {
        itemName = this.data.get(selected[i]).name;
        names.push(itemName);
      }
      return names;
    } else {
      return false;
    }
  },
  onBeforeContextMenus: function (id, e) {
    this.menu._doOnContextBeforeCall(e, {
      id: id
    });
    id = this.data.getSelected();
    return false;
  },
  context: function (e)
  {
    e.preventDefault();
    this.menu.showContextMenu(e.pageX, e.pageY);
    return false;
  },
  selectAll: function (zid, id)
  {
    this.data.select();
  },
  copy: function ()
  {
    if (names = this.getSelectedItems())
    {
      this.copyBuffer = names;
      this.copyPath = this.options.currentPath;
    }
  },
  paste: function ()
  {
    if (this.copyBuffer.length > 0)
    {
      this.connector.execute({
        copyFiles: {
          names: this.copyBuffer,
          path: this.parsePath(this.copyPath),
          currentPath: this.parsePath(this.options.currentPath)
        }
      });
      if (this.connector.result.copy)
      {
        this.renderFiles();
      }
    }
  },
  deleteAll: function ()
  {
    if ((names = this.getSelectedItems()) && (confirm(AI.translate('common', 'you_really_wish_to_remove_this_objects'))))
    {
      this.connector.execute({
        unlinkFiles: {
          names: names,
          currentPath: this.parsePath(this.options.currentPath)
        }
      });
      if (this.connector.result.unlink)
      {
        this.renderFiles();
      }
    }
  },
  back: function ()
  {
    this.options.currentPath.shift();
    this.renderFiles();
  },
  onItemDblClick: function (id, ev, html)
  {
    if (this.data.get(id).type == 'dir')
    {
          this.options.currentPath.unshift(this.data.get(id).name);
          this.renderFiles();
    } else
    {
         this.currentClickItemHandler(id, ev);
    }
  },
  createFolder: function ()
  {
    var reply = prompt(AI.translate('matrix', 'enter_folder_name'));
    reply = reply.trim();
    if (reply.length > 0)
    {
      this.connector.execute({
        createFolder: {
          name: reply
        }
      });
      if (this.connector.result.folderCreated)
      {
        this.renderFiles();
      }
    }
  },
  
  setPath: function (path)
  {
    if (path) {
      path = this.extractPath(path);
      pathArray = path.path.split('/');
      if (this.options.prefixPath) delete pathArray[1];
      this.options.currentPath = pathArray.reverse();
    }
  },
  
  
  detectTarget:function()
  {
    this.container.find('.folder-submit').hide();
    this.folderSelect=false;
     if(this.applyButtonOptions&&this.applyButtonOptions.target)
    {

        switch (this.applyButtonOptions.target)        
        {
            case 'folder':
            {
                this.container.find('.folder-submit').show(200);
                this.folderSelect=true;
            }

        }
        
    }

  },
  
  openSmall: function (path)
  {
    // this.enableUpperPanel(false);
    this.enableDropZone(false);
    
    this.container.find('.hideme').hide();
    
    this.fileman.bringToTop(35);
    this.fileman.show();
    
    
    
    this.detectTarget();
    this.fileman.centerOnScreen();
    this.setPath(this.targetElement.data('itemPath'));
    this.fileman.setDimension('640', '600');
    this.renderFiles();
    this.filesBody.css({height: 380 });
    
  },
  open: function (zindex)
  {
      if(!zindex)zindex=99;
    this.fileman.bringToTop(zindex);
    this.renderFiles();
    this.filesBody.css({
      height: 390
    });
    this.detectTarget();
    this.fileman.show();
    this.fileman.centerOnScreen();
    this.fileman.setDimension('980', '600');
  },
  
  renderFiles: function ()
  {
    this.data.clearAll();
    
    currentPath = this.parsePath(this.options.currentPath);
    this.connector.execute({
      'getWalk': {
        'path': currentPath,
        'mode': 'icons',
        'filter': '*'
      }
    });
    this.container.find('input[name="currentPath"]').val(currentPath);
    this.dropzone.options.params = {
      'path': currentPath
    };
    if (typeof this.connector.result.error == 'undefined')
    {
      for (var i in this.connector.result.filesMatrix)
      {
        if (typeof this.connector.result.filesMatrix[i].ext !== 'undefined')
        
        
        this.data.add({
          name: this.connector.result.filesMatrix[i].nam,
          mod: this.connector.result.filesMatrix[i].mod,
          type: this.connector.result.filesMatrix[i].ext,
          fullpath: '/media' + currentPath + this.connector.result.filesMatrix[i].nam,
          size: this.connector.result.filesMatrix[i].size
        });
      }
      this.data.refresh();
    }
  },
  extractPath: function (data)
  {
    var m = data.match(/(.*)[\/\\]([^\/\\]+)$/);
    if (m) return {
      path: m[1],
      last: m[2]
    }
  },
  parsePath: function (arr)
  {
      
    var path = '';
    if (arr)
    {
      for (var index = arr.length - 1; index >= 0; index--)
      {
        path = path + arr[index] + '/';
      }
    }
    return path;
  }
});
