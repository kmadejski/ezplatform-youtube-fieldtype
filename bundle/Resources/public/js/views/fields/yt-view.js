YUI.add('yt-view', function(Y) {
    "use strict";

    Y.namespace('km');

    Y.km.YtView = Y.Base.create('ytView', Y.eZ.FieldView, [], {
        _getFieldValue: function() {
            return this.get('field').fieldValue;
        }
    });

    Y.eZ.FieldView.registerFieldView('ezyt', Y.km.YtView);
});