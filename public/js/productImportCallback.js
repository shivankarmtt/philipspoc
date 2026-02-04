pimcore.registerNS("pimcore.plugin.processmanager.executor.callback.productImportCallback");
pimcore.plugin.processmanager.executor.callback.productImportCallback = Class.create(pimcore.plugin.processmanager.executor.callback.abstractCallback, {
    
    name: "productImportCallback",
    
    getFormItems: function () {
        var items = [];
    
        var itemChannel = {
            itemSelectorConfig: {
                type: ["asset"]
            },
            label: "Input Product Template Sheet",
            mandatory: true, 
        };
        items.push(this.getHref('productSheetAttachment',itemChannel));

        return items;
    },

    getConfigSelection : function () {
        var configStore = new Ext.data.Store({
            autoLoad : true,
            proxy: {
                url: '/admin/elementsprocessmanager/callback-settings/list?type=' + this.name,
                type: 'ajax',
                reader: {
                    type: 'json',
                    rootProperty: "data"
                }
            },
            fields: ["id","name","description","settings","type"]
        });

        return this.predefinedConfig;
    },

    execute: function () {
        this.openConfigWindow();
    }
});
