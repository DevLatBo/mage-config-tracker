define([
    'Magento_Ui/js/grid/columns/select'
],function(Column){
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Devlat_Settings/ui/grid/cells/configuratedBy'
        },
        getConfiguratedBy: function (row) {
            return row.configurated_by;
        },
        hasConfiguratedBy: function (row) {
            var configBy = row.configurated_by;
            var flag = false;
            if (configBy) {
               flag = true;
            }
            return flag;
        }
    });
});
