SafeDeal.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'safedeal-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('safedeal') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('safedeal_items'),
                layout: 'anchor',
                items: [{
                    html: _('safedeal_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'safedeal-grid-items',
                    cls: 'main-wrapper',
                }]
            }]
        }]
    });
    SafeDeal.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(SafeDeal.panel.Home, MODx.Panel);
Ext.reg('safedeal-panel-home', SafeDeal.panel.Home);
