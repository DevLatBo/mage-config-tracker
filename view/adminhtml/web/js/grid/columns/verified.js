define([
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
       defaults: {
           bodyTmpl: 'Devlat_Settings/ui/grid/cells/verified'
       },
        getOrderStatusColor: function (row) {
           if (row.verified == '0') {
               return 'not-verified';
           } else if (row.verified == '1') {
               return 'verified';
           }
           return '#303030';
        },
    });
});
