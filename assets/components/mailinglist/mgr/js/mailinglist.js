var MailingList = function(config) {
	config = config || {};
	MailingList.superclass.constructor.call(this,config);
};

Ext.extend(MailingList,Ext.Component,{
	page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('mailinglist',MailingList);

MailingList = new MailingList();