var SafeDeal = function (config) {
    config = config || {};
    SafeDeal.superclass.constructor.call(this, config);
};
Ext.extend(SafeDeal, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('safedeal', SafeDeal);

SafeDeal = new SafeDeal();