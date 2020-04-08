MailingList.page.UpdateResource = function(config)
{
	config = config || {record: {}};
	config.record = config.record || {};
	Ext.applyIf(config, {
		panelXType: 'mailinglist-panel-update'
	});
	config.canDuplicate = true;
	config.canDelete = true;
	MailingList.page.UpdateResource.superclass.constructor.call(this, config);
};
Ext.extend(MailingList.page.UpdateResource, MODx.page.UpdateResource);
Ext.reg('mailinglist-page-update', MailingList.page.UpdateResource);

MailingList.panel.UpdateResource = function(config)
{
	config = config || {};
	MailingList.panel.UpdateResource.superclass.constructor.call(this, config);
};
Ext.extend(MailingList.panel.UpdateResource, MODx.panel.Resource,
{
	getFields: function(config)
	{
		var fields = [];
		var originals = MODx.panel.Resource.prototype.getFields.call(this,config);
		var mailinglist_tabs = null;
		var mailinglist_settings_index = null;
		
		for (var i in originals)
		{
			if (!originals.hasOwnProperty(i)){continue;}
			var item = originals[i];

			if(item.id == 'modx-resource-header'){item.html = '<h2>' + _('mailinglist_new') + '</h2>';}
			if(item.id == 'modx-resource-tabs')
			{
				mailinglist_tabs = item;
				mailinglist_settings_tab = 
				{
					'id': 'mailinglist_settings',
					'title' : _('mailinglist_settings'),
					'bodyCssClass':'main-wrapper',
					'items' : this.getSettingsFields(config)
				};
				var mailinglist_subscribers_tab = 
				{
					'id': 'mailinglist_subscribers',
					'title' : _('mailinglist_subscribers'),
					'bodyCssClass':'main-wrapper',
					'items' : this.getSubscribers(config)
				}
				var mailinglist_mailsender_tab = 
				{
					'id': 'mailinglist_mailsender',
					'title' : _('mailinglist_mailsender'),
					'bodyCssClass':'main-wrapper',
					'items' : this.getMailSender(config)
				}
				mailinglist_settings_index = item.items.push(mailinglist_settings_tab);
				mailinglist_settings_index--;
				item.items.push(mailinglist_subscribers_tab);
				item.items.push(mailinglist_mailsender_tab);
			}
			if(item.id == 'modx-resource-content')
			{
				item.title = _('mailinglist_content_title'),
				mailinglist_tabs.items[mailinglist_settings_index].items.push
				(
					{
						title: '<br>',
						hideMode: 'offsets',
						anchor: '100%',
						layout: 'form',
						defaults: {
							layout: 'form',
							labelAlign: 'top',
							anchor: '100%',
							border: false,
							labelSeparator: ''
						},
						items:[item]
					}
				);
				var filesPanel =
				{
					anchor: "100%",
					animCollapse: false,
					autoHeight: true,
					bodyCssClass: "main-wrapper",
					border: false,
					collapsible: true,
					hideMode: "offsets",
					id: "modx-mailinglist-files",
					items:
					[
						{
							xtype: 'mailinglist-grid-files'
							,name: 'mailinglist-attachments'
							,hiddenField: "attachments"
						},
						{xtype:'textfield',name:'attachments',hidden:true,id:'attachments'}
					],
					labelAlign: "top",
					labelSeparator: "",
					layout: "form",
					title: _('mailinglist_files_title')
				};
				mailinglist_tabs.items[mailinglist_settings_index].items.push
				(
					{
						title: '<br>',
						hideMode: 'offsets',
						anchor: '100%',
						layout: 'form',
						defaults: {
							layout: 'form',
							labelAlign: 'top',
							anchor: '100%',
							border: false,
							labelSeparator: ''
						},
						items:[filesPanel]
					}
				);
				continue;
			}
			fields.push(item);
		}

		return fields;
	},

	getMainFields: function(config)
	{
		var fields = MODx.panel.Resource.prototype.getMainFields.call(this, config);
		return fields;
	},
	
	getSettingsFields: function(config)
	{
		var fields = 
		[
			{
				title: _('mailinglist_settings_title'),
				hideMode: 'offsets',
				anchor: '100%',
				layout: 'form',
				labelAlign: 'top',
				items: 
				[
					{
						xtype:'textfield',
						fieldLabel: _('mailinglist_emailfrom'),
						value: config.record.emailfrom || '',
						anchor: '75%',
						id:'mailinglist-emailfrom',
						name:'emailfrom'
					},
					{
						xtype: 'label',
						html: _('mailinglist_emailfrom_desc'),
						cls: 'desc-under'
					},
					{
						xtype:'textfield',
						fieldLabel: _('mailinglist_emailfromname'),
						//description: '<b>[[*uri]]</b><br />' + _('mailinglist_emailfromname_help'),
						value: config.record.emailfromname || '',
						anchor: '75%',
						id:'mailinglist-emailfromname',
						name:'emailfromname'
					},
					{
						xtype: 'label',
						html: _('mailinglist_emailfromname_desc'),
						cls: 'desc-under'
					},
					{
						xtype:'textfield',
						fieldLabel: _('mailinglist_emailreplyto'),
						//description: '<b>[[*uri]]</b><br />' + _('mailinglist_emailreplyto_help'),
						value: config.record.emailreplyto || '',
						anchor: '75%',
						id:'mailinglist-emailreplyto',
						name:'emailreplyto'
					},
					{
						xtype: 'label',
						html: _('mailinglist_emailreplyto_desc'),
						cls: 'desc-under'
					},
					{
						xtype:'textfield',
						fieldLabel: _('mailinglist_emailreplytoname'),
						//description: '<b>[[*uri]]</b><br />' + _('mailinglist_emailreplytoname_help'),
						value: config.record.emailreplytoname || '',
						anchor: '75%',
						id:'mailinglist-emailreplytoname',
						name:'emailreplytoname'
					},
					{
						xtype: 'label',
						html: _('mailinglist_emailreplytoname_desc'),
						cls: 'desc-under'
					},
					{
						xtype:'textfield',
						fieldLabel: _('mailinglist_emailsubject'),
						value: config.record.emailsubject || '',
						anchor: '75%',
						id:'mailinglist-emailsubject',
						name:'emailsubject'
					},
					{
						xtype: 'label',
						html: _('mailinglist_emailsubject_desc'),
						cls: 'desc-under'
					}
				]
			}
		];
		
		
		return fields;
	},
	getSubscribers: function(config)
	{
		var fields =
		[
			{
				hideMode: 'offsets',
				anchor: '100%',
				layout: 'form',
				defaults: {
					layout: 'form',
					labelAlign: 'top',
					anchor: '100%',
					border: false,
					labelSeparator: ''
				},
				items:
				[
					{xtype:'textfield',name:'subscribers',hidden:true,id:'subscribers',value: config.record.subscribers || '{}'},
					{
						anchor: "100%",
						animCollapse: false,
						autoHeight: true,
						bodyCssClass: "main-wrapper",
						border: false,
						collapsible: true,
						hideMode: "offsets",
						id: "mailinglist-users",
						items:
							[
								{
									xtype: 'mailinglist-tree-users'
									,name: 'mailinglist-users'
									,hiddenField: "subscribers"
									,hiddenFieldValue: config.record.subscribers || '{}'
								},
							],
						labelAlign: "top",
						labelSeparator: "",
						layout: "form",
						title: _('mailinglist_users_title')
					}
				]
			},
			{
				title:'<br>',
				hideMode: 'offsets',
				anchor: '100%',
				layout: 'form',
				defaults: {
					layout: 'form',
					labelAlign: 'top',
					anchor: '100%',
					border: false,
					labelSeparator: ''
				},
				items:
				[
					{
						anchor: "100%",
						animCollapse: false,
						autoHeight: true,
						bodyCssClass: "main-wrapper",
						border: false,
						collapsible: true,
						hideMode: "offsets",
						id: "mailinglist-anonym",
						items:
							[
								{
									xtype: 'mailinglist-grid-anonym'
									,name: 'mailinglist-anonym'
									,hiddenField: "subscribers"
								},
							],
						labelAlign: "top",
						labelSeparator: "",
						layout: "form",
						title: _('mailinglist_anonym_title')
					}
				]
			}
		];
		
		return fields;
	},
	getMailSender: function(config)
	{
		var fields =[];
		if(!config.record.taskerAvail)
		{
			fields.push
			(
				{
					html: _('mailinglist_сrontab_unavail'),
					border: false,
					bodyCssClass: 'panel-warning',
					id:'crontab_unavail'
				}
			);
		}
		fields.push
		(
			{
				title:'<br>',
				hideMode: 'offsets',
				anchor: '100%',
				layout: 'form',
				defaults: {
					layout: 'form',
					labelAlign: 'top',
					anchor: '100%',
					border: false,
					labelSeparator: ''
				},
				items:
				[
					{
						anchor: "100%",
						animCollapse: false,
						autoHeight: true,
						bodyCssClass: "main-wrapper",
						border: false,
						collapsible: true,
						hideMode: "offsets",
						id: "mailinglist-instances",
						items:this.getInstances(config),
						labelAlign: "top",
						labelSeparator: "",
						layout: "form",
						title: _('mailinglist_instances_title')
					}
				]
			}
		);
		
		return fields;
	},
	getInstances:function(config)
	{
		var fields =[];
		if(config.record.instances.length==0)
		{
			fields.push
			(
				{
					html: _('mailinglist_instances_empty'),
					border: false,
					bodyCssClass: 'panel-desc',
					id:'mailinglist_instances_empty'
				}
			);
		}
		else
		{
			fields.push({html:'<br>'});
			for(var index in config.record.instances)
			{
				if(!config.record.instances.hasOwnProperty(index))continue;
				var instance = config.record.instances[index];
				fields.push
				(
					{
						xtype: 'mailinglist-panel-instance'
						,name: 'mailinglist-instance'+instance.id
						,id: 'mailinglist-instance'+instance.id
						,options: instance
						,index: index
					}
				);
			}
		}
		fields.push({html:'<br>'});
		fields.push
		(
			{
				xtype:'button',
				text:_('mailinglist_instance_create'),
				id:'create-instance',
				listeners:
				{
					'click': {fn: function(data)
					{
						this.CreateInstance(config)
					},scope:this}
				}
			}
		);
		return fields;
	},
	CreateInstance:function(config)
	{
		var _this = this;
		var panel = Ext.getCmp('mailinglist-instances');
		var Button = Ext.getCmp('create-instance');
		Button.setDisabled(true);
		panel.el.mask(_('mailinglist_instance_loading'));
		
		Ext.Ajax.request
		(
			{
				url: MailingList.config.connector_url,
				params:
				{
					action: 'mgr/mailinglist/CreateInstance',
					mailinglist: MailingList.config.id,
					'HTTP_MODAUTH': MailingList.config.auth
				},
				callback: function(options,success,response)
				{
					data = Ext.util.JSON.decode(response.responseText);
					config.record.instances = data.results;
					panel.removeAll();
					panel.add(_this.getInstances(config));
					panel.doLayout();
					panel.el.unmask();
				}
			}
		);
	},
	RemoveInstance:function(instance)
	{
		var _this = this;
		var panel = Ext.getCmp('mailinglist-instances');
		panel.el.mask(_('mailinglist_instance_loading'));
		
		Ext.Ajax.request
		(
			{
				url: MailingList.config.connector_url,
				params:
				{
					action: 'mgr/mailinglist/removeInstance',
					mailinglist: MailingList.config.id,
					instance: instance.config.options.id,
					'HTTP_MODAUTH': MailingList.config.auth
				},
				callback: function(options,success,response)
				{
					data = Ext.util.JSON.decode(response.responseText);
					_this.config.record.instances = data.results;
					panel.removeAll();
					panel.add(_this.getInstances(_this.config));
					panel.doLayout();
					panel.el.unmask();
				}
			}
		);
	}
});
Ext.reg('mailinglist-panel-update', MailingList.panel.UpdateResource);