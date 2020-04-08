MailingList.grid.Files = function(config)
{
	config = config || {};
	
	var tbar =
	[
		{
			text: 'Добавить файл',
			handler: this.addItem,
		}
	];
	
	Ext.applyIf(config,
	{
		autoHeight: true,
		collapsible: true,
		resizable: true,
		loadMask: true,
		paging: false,
		autosave: false,
		remoteSort: true,
		primaryKey: 'id',
		isModified : false,
		viewConfig:
		{
			emptyText: 'No items found',
			forceFit: true,
			autoFill: true
		},
		url : MailingList.config.connector_url,
		baseParams:
		{ 
			action: 'mgr/mailinglist/getFilesList',
			mailinglist: MailingList.config.id,
			'HTTP_MODAUTH': MailingList.config.auth
		},
		fields: ['id','filename','attachname'],
		columns:
		[
			{
				"header": "Файл",
				"width": "200",
				"sortable": "false",
				"editor":
				{ 
					xtype: 'mailinglist-file-select',
					listeners:
					{
						'select': {fn: function(data)
						{
							var sm = this.getSelectionModel();
							var rec_index = sm.grid.getStore().indexOfId(sm.selections.items[0].id);
							sm.grid.getStore().data.items[rec_index].data['filename'] = data.fullRelativeUrl;
							sm.grid.getView().refresh();
							sm.grid.updateHiddenField();
							MODx.fireResourceFormChange();
						},scope:this}
					}
				},
				"dataIndex": "filename"
			},
			{
				"header": "Название",
				"width": "200",
				"sortable": "false",
				"editor":
				{ 
					xtype: 'textfield',
					listeners:
					{
						'change': {fn: function(f,e)
						{
							var sm = this.getSelectionModel();
							sm.grid.getStore().data.items[f.gridEditor.row].data.attachname = e;
							sm.grid.getView().refresh();
							sm.grid.updateHiddenField();
							MODx.fireResourceFormChange();
						},scope:this}
					}
				},
				"dataIndex": "attachname"
			}
		],
		tbar:tbar
	});
	MailingList.grid.Files.superclass.constructor.call(this,config);
	this.getStore().load({
		scope:this,
		callback:this.afterLoaded
	});
}
Ext.extend(MailingList.grid.Files,MODx.grid.Grid,
{
	afterLoaded:function(records, operation, success)
	{
		var hiddenField = Ext.get(this.hiddenField);
		var rows = [];
		for(var id in records)
		{
			if(!records.hasOwnProperty(id))continue;
			rows.push(records[id].data);
		}
		hiddenField.dom.value = Ext.util.JSON.encode(rows);
	},
	addItem: function(btn,e)
	{
		var s=this.getStore();
		var c = s.getCount();
		var lr = s.getAt(c-1);
		var rec_id = 0;
		if(lr!==undefined)rec_id = lr.id+1;
		record = new Ext.data.Record({'id':rec_id,'filename':'','attachname':''},rec_id);
		s.add([record]);
		this.updateHiddenField();
		MODx.fireResourceFormChange();
	},
	remove: function()
	{
		var _this=this;
		Ext.Msg.confirm(_('warning') || '','Действительно хотите открепить файл?' || '',function(e) {
		if (e == 'yes')
		{
					_this.getStore().removeAt(_this.getStore().indexOfId(_this.menu.record.id));
					_this.getView().refresh();
					_this.updateHiddenField();
					MODx.fireResourceFormChange();	
		}
		}),this;		
	}
	,getMenu: function()
	{
		var n = this.menu.record; 
		var m = [];
		m.push({
			text: 'Открепить'
			,handler: this.remove
		});
		return m;
	},
	updateThis: function()
	{
		this.getStore().load({
			scope:this,
			callback:this.afterLoaded
		});
	},
	updateHiddenField: function()
	{
		var hiddenField = Ext.get(this.hiddenField);
		var rows = [];
		var records = this.getStore().data.items;
		for(var id in records)
		{
			if(!records.hasOwnProperty(id))continue;
			rows.push(records[id].data);
		}
		//console.log(Ext.util.JSON.encode(rows));
		hiddenField.dom.value = Ext.util.JSON.encode(rows);
	}
});
Ext.reg('mailinglist-grid-files',MailingList.grid.Files);




MailingList.combo.fileSelect = function(config)
{
    config = config || {};
    if (config.length !== 0 && typeof config.openTo !== "undefined") {
        if (!/^\//.test(config.openTo)) {
            config.openTo = '/' + config.openTo;
        }
        if (!/$\//.test(config.openTo)) {
            var tmp = config.openTo.split('/');
            delete tmp[tmp.length - 1];
            tmp = tmp.join('/');
            config.openTo = tmp.substr(1);
        }
    }

    Ext.applyIf(config,{
        width: 300,
        triggerAction: 'all'
    });
    MailingList.combo.fileSelect.superclass.constructor.call(this,config);
    this.config = config;
};
Ext.extend(MailingList.combo.fileSelect,Ext.form.TriggerField,{
    browser: null,
    onTriggerClick : function(btn){
        if (this.disabled){
            return false;
        }

        if (this.browser === null) {
            this.browser = MODx.load({
                xtype: 'modx-browser',
                id: Ext.id(),
                multiple: false,
                source: this.config.source || MODx.config.default_media_source,
                rootVisible: this.config.rootVisible || false,
                allowedFileTypes: this.config.allowedFileTypes || '',
                wctx: this.config.wctx || 'web',
                openTo: this.config.openTo || '',
                rootId: this.config.rootId || '/',
                hideFiles: this.config.hideFiles || true,
                listeners: {
                    'select': {fn: function(data) {
                        this.setValue(data.fullRelativeUrl);
                        this.fireEvent('select',data);
                        MODx.fireResourceFormChange();
						//console.log(this,data);
                    },scope:this}
                }
            });
        }
        this.browser.win.buttons[0].on('disable',function(e) {
            this.enable();
        });
        this.browser.win.tree.on('click', function(n,e) {
                path = this.getPath(n);
                this.setValue(path);
            },this
        );
        this.browser.win.tree.on('dblclick', function(n,e) {
                path = this.getPath(n);
                this.setValue(path);
                this.browser.hide();
				//console.log(this,path);
            },this
        );
        this.browser.show(btn);
        return true;
    },
    onDestroy: function(){
        MODx.combo.Browser.superclass.onDestroy.call(this);
    },
    getPath: function(n) {
        if (n.id == '/') {return '';}
        data = n.attributes;
        path = data.path + '/';

        return path;
    }
});
Ext.reg('mailinglist-file-select',MODx.combo.Browser);









MailingList.tree.Users = function(config)
{
    config = config || {};
     
    Ext.applyIf(config,{
		url : MailingList.config.connector_url
		,action: 'mgr/mailinglist/getUsersList'
		,baseParams:
		{
			mailinglist: MailingList.config.id,
			currentState: config.hiddenFieldValue,
			'HTTP_MODAUTH': MailingList.config.auth
		}
        ,autoHeight: true
        ,root_id: 'root'
        ,root_name: _('mailinglist.users_groups')
        ,rootVisible: false
        ,enableDD: false
        ,ddAppendOnly: true
        ,useDefaultToolbar: true
        ,stateful: false
        ,collapsed: false
    });
    MailingList.tree.Users.superclass.constructor.call(this,config);
	this.on('checkchange', function(node,checked)
	{
		this.cascadeToggleCheck(node,checked);
		this.updateHiddenField();
	});
};
Ext.extend(MailingList.tree.Users,MODx.tree.Tree,
{
	cascadeToggleCheck: function(node,checked)
	{
		var tree = this;
		if(node.loaded&&node.childNodes.length>0)
		{
			node.eachChild(function(n)
			{                    
                n.getUI().toggleCheck(checked);
				n.attributes.checked=checked;
				tree.cascadeToggleCheck(n,checked);
            });
		}
	},
	updateHiddenField: function()
	{
		var hiddenField = Ext.get(this.hiddenField);
		var Current = Ext.util.JSON.decode(hiddenField.dom.value);
		Current = this.getTree(this.root,Current);
		hiddenField.dom.value = Ext.util.JSON.encode(Current);
		this.baseParams.currentState = hiddenField.dom.value;
	},
	getTree: function(node,current)
	{
		if(!node.isRoot)
		{
			var checkedParent = this.getCheckedParent(node);			
			if(!checkedParent)
			{
				if(node.attributes.checked)
				{
					if(current[node.id]===undefined)current[node.id]={};
					current[node.id].checked = node.attributes.checked;
				}
				else
				{
					if(current[node.id]!==undefined)delete(current[node.id]);
				}
			}
			else
			{
				if(current[node.id]!==undefined)delete(current[node.id]);
				if(!node.attributes.checked)
				{
					if(current[checkedParent.id]===undefined)current[checkedParent.id]={checked:true};
					if(current[checkedParent.id].exclude===undefined||current[checkedParent.id].exclude===null)current[checkedParent.id].exclude=[];
					if(!this.hasUnchekedParent(node))
					{
						if(current[checkedParent.id].exclude.indexOf(node.id)===-1)
						{
							current[checkedParent.id].exclude.push(node.id);
						}
					}
				}
				else
				{
					if(this.hasUnchekedParent(node))
					{
						if(current[node.id]===undefined)current[node.id]={};
						current[node.id].checked = node.attributes.checked;
					}
					current = this.removeFromParents(node,current,node.parentNode);
				}
			}
		}
			
		if(node.attributes.hasChildren||node.isRoot)
		{
			if(node.childNodes.length>0)
			{
				node.eachChild(function(n)
				{                    
					current = this.getTree(n,current);
				},this);
			}
		}
		return current;
	},
	getCheckedParent: function(node)
	{
		if(node.isRoot)return false;
		if(!node.parentNode.attributes.checked)return this.getCheckedParent(node.parentNode);
		return node.parentNode;
	},
	hasUnchekedParent: function(node)
	{
		if(node.parentNode.isRoot)return false;
		if(!node.parentNode.attributes.checked)return true;
		else return false;
		return this.hasUnchekedParent(node.parentNode);
	},
	removeFromParents: function(node,current,parent)
	{
		if(parent.isRoot)return current;
		if(current[parent.id]!==undefined&&current[parent.id].exclude!==undefined&&current[parent.id].exclude!==null)
		{
			if(current[parent.id].exclude.indexOf(node.id)!==-1)
			{
				current[parent.id].exclude.splice(current[parent.id].exclude.indexOf(node.id),1);
			}
		}
		return this.removeFromParents(node,current,parent.parentNode);
	}
});
Ext.reg('mailinglist-tree-users',MailingList.tree.Users);



MailingList.grid.Anonymous = function(config)
{
	config = config || {};
	
	var tbar =
	[
		{
			text: 'Добавить подписчика',
			handler: this.addItem,
		}
	];
	
	Ext.applyIf(config,
	{
		autoHeight: true,
		collapsible: true,
		resizable: true,
		loadMask: true,
		paging: false,
		autosave: false,
		remoteSort: true,
		primaryKey: 'id',
		isModified : false,
		viewConfig:
		{
			emptyText: 'No items found',
			forceFit: true,
			autoFill: true
		},
		url : MailingList.config.connector_url,
		baseParams:
		{ 
			action: 'mgr/mailinglist/getAnonymousList',
			mailinglist: MailingList.config.id,
			'HTTP_MODAUTH': MailingList.config.auth
		},
		fields: ['id','fullname','email'],
		columns:
		[
			{
				"header": "ID",
				"width": "30",
				"sortable": "false",
				"editable":false,
				"dataIndex": "id"
			},
			{
				"header": "ФИО",
				"width": "200",
				"sortable": "false",
				"editor":
				{ 
					xtype: 'textfield',
					listeners:
					{
						'change': {fn: function(f,e)
						{
							var sm = this.getSelectionModel();
							sm.grid.getStore().data.items[f.gridEditor.row].data.fullname = e;
							sm.grid.getView().refresh();
							sm.grid.updateHiddenField();
							MODx.fireResourceFormChange();
						},scope:this}
					}
				},
				"dataIndex": "fullname"
			},
			{
				"header": "Email",
				"width": "200",
				"sortable": "false",
				"editor":
				{ 
					xtype: 'textfield',
					listeners:
					{
						'change': {fn: function(f,e)
						{
							var sm = this.getSelectionModel();
							sm.grid.getStore().data.items[f.gridEditor.row].data.email = e;
							sm.grid.getView().refresh();
							sm.grid.updateHiddenField();
							MODx.fireResourceFormChange();
						},scope:this}
					}
				},
				"dataIndex": "email"
			}
		],
		tbar:tbar,
	});
	MailingList.grid.Anonymous.superclass.constructor.call(this,config);
	this.getStore().load({
		scope:this,
		callback:this.afterLoaded
	});
}
Ext.extend(MailingList.grid.Anonymous,MODx.grid.Grid,
{
	afterLoaded:function(records, operation, success)
	{
		this.updateHiddenField(records);
	},
	addItem: function(btn,e)
	{
		var s=this.getStore();
		var c = s.getCount();
		var lr = s.getAt(c-1);
		var rec_id = 0;
		if(lr!==undefined)rec_id = lr.id+1;
		record = new Ext.data.Record({'id':rec_id,'fullname':'','email':''},rec_id);
		s.add([record]);
		this.updateHiddenField();
		MODx.fireResourceFormChange();
	},
	remove: function()
	{
		var _this=this;
		Ext.Msg.confirm(_('warning') || '','Действительно хотите удалить подписчика?' || '',function(e) {
		if (e == 'yes')
		{
					_this.getStore().removeAt(_this.getStore().indexOfId(_this.menu.record.id));
					_this.getView().refresh();
					_this.updateHiddenField();
					MODx.fireResourceFormChange();	
		}
		}),this;		
	}
	,getMenu: function()
	{
		var n = this.menu.record; 
		var m = [];
		m.push({
			text: 'Удалить'
			,handler: this.remove
		});
		return m;
	},
	updateThis: function()
	{
		this.getStore().load({
			scope:this,
			callback:this.afterLoaded
		});
	},
	clearAnonymous: function()
	{
		var hiddenField = Ext.get(this.hiddenField);
		var Current = Ext.util.JSON.decode(hiddenField.dom.value);
		for(var id in Current)
		{
			if(!Current.hasOwnProperty(id))continue;
			if(id.indexOf("anonym_")!==-1)delete(Current[id]);
		}
		hiddenField.dom.value = Ext.util.JSON.encode(Current);
	},
	updateHiddenField: function(records)
	{
		this.clearAnonymous();
		var hiddenField = Ext.get(this.hiddenField);
		var Current = Ext.util.JSON.decode(hiddenField.dom.value);
		if(records===undefined)records = this.getStore().data.items;
		//console.log(records);
		for(var id in records)
		{
			if(!records.hasOwnProperty(id))continue;
			Current['anonym_'+records[id].data.id] = records[id].data;
		}
		//console.log(Current);
		hiddenField.dom.value = Ext.util.JSON.encode(Current);
	}
});
Ext.reg('mailinglist-grid-anonym',MailingList.grid.Anonymous);

MailingList.panel.Instance = function(config)
{
	config = config || {};
	Ext.applyIf(config,
	{
		title:'Рассылка №'+config.options.id,
		cls:'instance-wrapper',
		anchor: "100%",
		animCollapse: false,
		autoHeight: true,
		border: false,
		collapsible: true,
		hideMode: "offsets",
		id: "mailinglist-instance"+config.options.id,
		labelAlign: "top",
		labelSeparator: "",
		tbar:[],
		items:[],
	});
	MailingList.panel.Instance.superclass.constructor.call(this,config);
	this.toolbars[0].add(this.getTbar());
	this.add(this.getItems());
	this.doLayout();
	
	
	this.on('afterrender', function(_this, eOpts)
	{
		if(_this.config.options.status=='created')
		{
			_this.FillQueues();
		}
	});
}
Ext.extend(MailingList.panel.Instance,MODx.Panel,
{
	getTbar: function()
	{
		var fields =
		[
			{
				html:'Статус: '+_('mailinglist_instance_status_'+this.config.options.status),
				cls:'tbar-status'
			}
		];
		if(this.config.options.status=='prepared'||this.config.options.status=='pause')
		{
			fields.push
			(
				{
					text:'Обновить список подписчиков',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.FillQueues();
						},scope:this}
					}
				}
			);
		}
		if(this.config.options.status=='process')
		{
			fields.push
			(
				{
					text:'Обновить',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.updateStatus();
						},scope:this}
					}
				}
			);
		}
		fields.push('->');
		if(this.config.options.status=='prepared')
		{
			fields.push
			(
				{
					text:'Запустить',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.Play(this.config);
						},scope:this}
					}
				},
				{
					text:'Удалить',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.Delete(this.config);
						},scope:this}
					}
				}
			);
		}
		if(this.config.options.status=='pause')
		{
			fields.push
			(
				{
					text:'Возобновить',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.Play(this.config);
						},scope:this}
					}
				},
				{
					text:'Остановить',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.Stop(this.config);
						},scope:this}
					}
				}
			);
		}
		if(this.config.options.status=='process')
		{
			fields.push
			(
				{
					text:'Приостановить',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.Pause(this.config);
						},scope:this}
					}
				},
				{
					text:'Остановить',
					listeners:
					{
						'click': {fn: function(data)
						{
							this.Stop(this.config);
						},scope:this}
					}
				}
			);
		}
		
		return fields;
	},
	getItems: function()
	{
		var fields =
		[
			{
				xtype: "fieldset",
				autoHeight:true,
				anchor: "75%",
				items: [{
					layout: "column",
					anchor: "0",
					items: [{
						columnWidth: .5,
						title: 'Ожидают отправки',
						items:
						{
							xtype: 'mailinglist-grid-expected'
							,name: 'mailinglist-expected'+this.config.options.id
							,options: this.config.options
						}
					},{
						columnWidth: .5,
						title: 'Отправлено',
						items:
						{
							xtype: 'mailinglist-grid-sended'
							,name: 'mailinglist-sended'+this.config.options.id
							,options: this.config.options
						}
					}]
				}]
			}
		];
		
		return fields;
	},
	UpdateView: function()
	{
		this.toolbars[0].removeAll();
		this.removeAll();
		this.toolbars[0].add(this.getTbar());
		this.add(this.getItems());
		this.doLayout();
	},
	FillQueues: function()
	{
		var _this = this;
		this.el.mask(_('mailinglist_instance_loading'));
		
		Ext.Ajax.request
		(
			{
				url: MailingList.config.connector_url,
				params:
				{
					action: 'mgr/mailinglist/FillQueues',
					mailinglist: MailingList.config.id,
					instance: this.config.options.id,
					'HTTP_MODAUTH': MailingList.config.auth
				},
				callback: function(options,success,response)
				{
					data = Ext.util.JSON.decode(response.responseText);
					if(data.results.proccess)
					{
						_this.FillQueues();
					}
					else
					{
						if(_this.config.options.status=='created')
						{
							_this.changeStatus('prepared');
						}
						_this.UpdateView();
						_this.el.unmask();
					}
				}
			}
		);
	},
	Delete:function()
	{
		var resource = Ext.getCmp('modx-panel-resource');
		resource.RemoveInstance(this);
	},
	changeStatus: function(status)
	{
		var _this = this;
		this.el.mask(_('mailinglist_instance_loading'));
		
		Ext.Ajax.request
		(
			{
				url: MailingList.config.connector_url,
				params:
				{
					action: 'mgr/mailinglist/changeInstanceStatus',
					mailinglist: MailingList.config.id,
					instance: this.config.options.id,
					status: status,
					'HTTP_MODAUTH': MailingList.config.auth
				},
				callback: function(options,success,response)
				{
					data = Ext.util.JSON.decode(response.responseText);
					_this.config.options.status=status;
					_this.UpdateView();
					_this.el.unmask();
				}
			}
		);
	},
	updateStatus: function(status)
	{
		var _this = this;
		this.el.mask(_('mailinglist_instance_loading'));
		
		Ext.Ajax.request
		(
			{
				url: MailingList.config.connector_url,
				params:
				{
					action: 'mgr/mailinglist/getInstanceStatus',
					mailinglist: MailingList.config.id,
					instance: this.config.options.id,
					'HTTP_MODAUTH': MailingList.config.auth
				},
				callback: function(options,success,response)
				{
					data = Ext.util.JSON.decode(response.responseText);
					_this.config.options.status=data.results.status;
					_this.UpdateView();
					_this.el.unmask();
				}
			}
		);
	},
	Play: function()
	{
		var _this = this;
		this.el.mask(_('mailinglist_instance_loading'));
		
		Ext.Ajax.request
		(
			{
				url: MailingList.config.connector_url,
				params:
				{
					action: 'mgr/mailinglist/InstancePlay',
					mailinglist: MailingList.config.id,
					instance: this.config.options.id,
					'HTTP_MODAUTH': MailingList.config.auth
				},
				callback: function(options,success,response)
				{
					data = Ext.util.JSON.decode(response.responseText);
					_this.config.options.status='process';
					_this.UpdateView();
					_this.el.unmask();
				}
			}
		);
	},
	Pause: function()
	{
		this.changeStatus('pause');
	},
	Stop: function()
	{
		var _this = this;
		this.el.mask(_('mailinglist_instance_loading'));
		
		Ext.Ajax.request
		(
			{
				url: MailingList.config.connector_url,
				params:
				{
					action: 'mgr/mailinglist/InstanceStop',
					mailinglist: MailingList.config.id,
					instance: this.config.options.id,
					'HTTP_MODAUTH': MailingList.config.auth
				},
				callback: function(options,success,response)
				{
					data = Ext.util.JSON.decode(response.responseText);
					_this.config.options.status='stoped';
					_this.UpdateView();
					_this.el.unmask();
				}
			}
		);
	}
});
Ext.reg('mailinglist-panel-instance',MailingList.panel.Instance);





MailingList.grid.Expected = function(config)
{
	config = config || {};
	
	Ext.applyIf(config,
	{
		autoHeight: true,
		collapsible: true,
		resizable: true,
		loadMask: true,
		paging: true,
		autosave: false,
		remoteSort: false,
		primaryKey: 'id',
		isModified : false,
		viewConfig:
		{
			emptyText: 'No items found',
			forceFit: true,
			autoFill: true
		},
		url : MailingList.config.connector_url,
		baseParams:
		{ 
			action: 'mgr/mailinglist/getExpectedList',
			mailinglist: MailingList.config.id,
			instance:config.options.id,
			'HTTP_MODAUTH': MailingList.config.auth
		},
		fields: ['id','email','fullname'],
		columns:
		[
			{
				"header": "Email",
				"width": "30",
				"sortable": "false",
				"editable":false,
				"dataIndex": "email"
			},
			{
				"header": "ФИО",
				"width": "30",
				"sortable": "false",
				"editable":false,
				"dataIndex": "fullname"
			}
		]
	});
	MailingList.grid.Expected.superclass.constructor.call(this,config);
	this.getStore().load({
		scope:this,
		callback:this.afterLoaded
	});
}
Ext.extend(MailingList.grid.Expected,MODx.grid.Grid,
{
	afterLoaded:function(records, operation, success)
	{
		
	},
	updateThis: function()
	{
		this.getStore().load({
			scope:this,
			callback:this.afterLoaded
		});
	},
});
Ext.reg('mailinglist-grid-expected',MailingList.grid.Expected);






MailingList.grid.Sended = function(config)
{
	config = config || {};
	
	Ext.applyIf(config,
	{
		autoHeight: true,
		collapsible: true,
		resizable: true,
		loadMask: true,
		paging: true,
		autosave: false,
		remoteSort: false,
		primaryKey: 'id',
		isModified : false,
		viewConfig:
		{
			emptyText: 'No items found',
			forceFit: true,
			autoFill: true
		},
		url : MailingList.config.connector_url,
		baseParams:
		{ 
			action: 'mgr/mailinglist/getSendedList',
			mailinglist: MailingList.config.id,
			instance:config.options.id,
			'HTTP_MODAUTH': MailingList.config.auth
		},
		fields: ['id','email','fullname'],
		columns:
		[
			{
				"header": "Email",
				"width": "30",
				"sortable": "false",
				"editable":false,
				"dataIndex": "email"
			},
			{
				"header": "ФИО",
				"width": "30",
				"sortable": "false",
				"editable":false,
				"dataIndex": "fullname"
			}
		],
	});
	MailingList.grid.Sended.superclass.constructor.call(this,config);
	this.getStore().load({
		scope:this,
		callback:this.afterLoaded
	});
}
Ext.extend(MailingList.grid.Sended,MODx.grid.Grid,
{
	afterLoaded:function(records, operation, success)
	{
		
	},
	updateThis: function()
	{
		this.getStore().load({
			scope:this,
			callback:this.afterLoaded
		});
	},
});
Ext.reg('mailinglist-grid-sended',MailingList.grid.Sended);