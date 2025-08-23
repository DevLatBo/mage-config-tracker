define([
    'Magento_Ui/js/grid/columns/select'
],function(Column){
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Devlat_Settings/ui/grid/cells/verifiedBy'
        },
        getVerifiers: function (row) {
            return row.verified_by;
        }
    });
});
