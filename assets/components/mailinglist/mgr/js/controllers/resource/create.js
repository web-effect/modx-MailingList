MailingList.page.CreateResource = function(config)
{
	config = config || {record: {}};
	config.record = config.record || {};
	Ext.applyIf(config, {
		panelXType: 'mailinglist-panel-create'
	});
	config.canDuplicate = true;
	config.canDelete = true;
	MailingList.page.CreateResource.superclass.constructor.call(this, config);
};
Ext.extend(MailingList.page.CreateResource, MODx.page.CreateResource);
Ext.reg('mailinglist-page-create', MailingList.page.CreateResource);

MailingList.panel.CreateResource = function(config)
{
	config = config || {};
	MailingList.panel.CreateResource.superclass.constructor.call(this, config);
};
Ext.extend(MailingList.panel.CreateResource, MODx.panel.Resource,
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
				mailinglist_settings_index = item.items.push(mailinglist_settings_tab);
				mailinglist_settings_index--;
				item.items.push(mailinglist_subscribers_tab);
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
	}
});
Ext.reg('mailinglist-panel-create', MailingList.panel.CreateResource);