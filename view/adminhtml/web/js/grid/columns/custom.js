define([
    'underscore',
    'Magento_Ui/js/grid/columns/select'
], function (_, Column) {
    'use strict';

    return Column.extend({
       defaults: {
           bodyTmpl: 'Devlat_Tracker/ui/grid/cells/text'
       },
        getOrderStatusColor: function (row) {
           if (row.verified == '0') {
               return 'not-verified';
           } else if (row.verified == '1') {
               return 'verified';
           }
           return '#303030';
        },
        getLabel: function(row) {
           if (row.verified == '0') {
               return "No";
           } else if (row.verified == '1') {
               return "Yes";
           }
        }
    });
});
