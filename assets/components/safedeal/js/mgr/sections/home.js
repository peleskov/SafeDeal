SafeDeal.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'safedeal-panel-home',
            renderTo: 'safedeal-panel-home-div'
        }]
    });
    SafeDeal.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(SafeDeal.page.Home, MODx.Component);
Ext.reg('safedeal-page-home', SafeDeal.page.Home);