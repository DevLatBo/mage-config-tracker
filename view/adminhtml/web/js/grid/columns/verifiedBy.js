define([
    'Magento_Ui/js/grid/columns/select',
    'underscore'
],function(Column, _){
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Devlat_Settings/ui/grid/cells/verifiedBy'
        },
        getVerifiers: function (row) {
            return row.verified_by;
        },
        hasVerifiers: function (row) {
            var flag = false;
            var verifiers = row.verified_by;
            if (_.isArray(verifiers)) {
                flag = !_.isEmpty(verifiers);
            }
            return flag;
        }
    });
});
