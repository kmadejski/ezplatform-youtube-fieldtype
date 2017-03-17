YUI.add('yt-editview', function(Y) {
   "use strict";

   Y.namespace('km');

   const FIELDTYPE_IDENTIFIER = 'ezyt',
       VIDEO_ID_SELECTOR = '.ez-yt-videoId-field-value',
       TITLE_SELECTOR = '.ez-yt-title-field-value',
       WIDTH_SELECTOR = '.ez-yt-width-field-value',
       HEIGHT_SELECTOR = '.ez-yt-height-field-value',
       AUTOPLAY_SELECTOR = '.ez-yt-autoplay-checkbox',
       EVENTS = {};

   EVENTS[VIDEO_ID_SELECTOR] = {
       'blur': '_validateVideoId',
       'valuechange': '_validateVideoId'
   };
   EVENTS[TITLE_SELECTOR] = {
       'blur': '_validateTitle',
       'valuechange': '_validateTitle'
   };
   EVENTS[WIDTH_SELECTOR] = {
       'blur': '_validateWidth',
       'valuechange': '_validateWidth'
   };
   EVENTS[HEIGHT_SELECTOR] = {
       'blur': '_validateHeight',
       'valuechange': '_validateHeight'
   };


   Y.km.YtEditView = Y.Base.create('ytEditView', Y.eZ.FieldEditView, [], {
      events: EVENTS,

      initializer: function() {
          this.after(
              ['videoIdErrorStatusChange', 'titleErrorStatusChange', 'widthErrorStatusChange', 'heightErrorStatusChange'],
              this._uiUserError
          );
      },

      validate: function() {
          this._validateVideoId();
          this._validateTitle();
          this._validateWidth();
          this._validateHeight();
      },

      _variables: function() {
          return {
              "isRequired": this.get('fieldDefinition').isRequired,
              "isVideoIdRequired": this._isVideoIdRequired(),
              "isTitleRequired": this._isTitleRequired(),
              "isHeightRequired": this._isHeightRequired(),
              "isWidthRequired": this._isWidthRequired(),
              "isAutoplayChecked": this._isAutoplayChecked(),
          };
      },

       _isFieldRequired: function () {
           return this.get('fieldDefinition').isRequired;
       },

      _getInputValidity: function(property) {
          return this.get('container').one('.ez-yt-' + property + '-field-value').get('validity');
      },

      _getFieldValue: function() {
          var container = this.get('container');

          return {
              videoId: container.one(VIDEO_ID_SELECTOR).get('value'),
              title: container.one(TITLE_SELECTOR).get('value'),
              width: container.one(WIDTH_SELECTOR).get('value'),
              height: container.one(HEIGHT_SELECTOR).get('value'),
              autoplay: container.one(AUTOPLAY_SELECTOR).get('checked')
          };
      },

      _getInputFieldValue: function (property) {
          return this.get('container').one('.ez-yt-' + property + '-field-value').get('value');
      },

      _validateVideoId: function() {
          if (this._isVideoIdRequired() && !this._getInputFieldValue('videoId')) {
              this.set('videoIdErrorStatus', Y.eZ.trans('ezyt.vid.required', {}, 'validators'));
          }
          else {
              this.set('videoIdErrorStatus', false);
          }
      },

       _validateTitle: function() {
            if (this._isTitleRequired() && !this._getInputFieldValue('title')) {
                this.set('titleErrorStatus', Y.eZ.trans('ezyt.title.required', {}, 'validators'));
            }
            else {
                this.set('titleErrorStatus', false);
            }
       },

       _validateWidth: function() {
            var width = this._getInputFieldValue('width');
            var pattern = /^\d+$/;
            if (width && !pattern.test(width)) {
                this.set('widthErrorStatus', Y.eZ.trans('ezyt.width.not_integer', {}, 'validators'));
            }
            else if (width && width <= 0) {
                this.set('widthErrorStatus', Y.eZ.trans('ezyt.width.too_small', {}, 'validators'));
            }
            else {
                this.set('widthErrorStatus', false);
            }
       },

       _validateHeight: function() {
            var height = this._getInputFieldValue('height');
            var pattern = /^\d+$/;
            if (height && !pattern.test(height)) {
                this.set('heightErrorStatus', Y.eZ.trans('ezyt.height.not_integer', {}, 'validators'));
            }
            else if (height && height <= 0) {
                this.set('heightErrorStatus', Y.eZ.trans('ezyt.height.too_small', {}, 'validators'));
            }
            else {
                this.set('heightErrorStatus', false);
            }
       },

       _isVideoIdRequired: function() {
          return true;
       },

       _isTitleRequired: function() {
            return true;
       },

       _isHeightRequired: function() {
            return false;
       },

       _isWidthRequired: function() {
           return false;
       },

       _isAutoplayChecked: function() {
            var field = this.get('field').fieldValue;
            if (field == null) {
                return false;
            }
            return field.autoplay == "1";
       },

       _uiUserError: function (e) {
           var errorNode = this._getErrorDOMNode(e.attrName),
               subContainer = this._getSubContainer(e.attrName);
           if ( !e.newVal ) {
               errorNode.setContent('');
               subContainer.removeClass(this._errorClass);
           } else {
               errorNode.setContent(e.newVal);
               subContainer.addClass(this._errorClass);
           }
       },

       _getErrorDOMNode: function (attributeName) {
           var selectorPart = this._attributeNametoSelectorPart(attributeName);
           return this.get('container').one('.ez-editfield-yt-' + selectorPart + '-error-message');
       },

       _getSubContainer: function (attributeName) {
           var selectorPart = this._attributeNametoSelectorPart(attributeName);

           return this.get('container').one('.ez-editfield-row-yt-' + selectorPart);
       },

       _attributeNametoSelectorPart: function (attributeName) {
           return attributeName.replace('ErrorStatus', '');
       },
   }, {
       ATTRS: {
           errorStatus: {
               getter: function() {
                   return !!(
                       this.get('videoIdErrorStatus') ||
                       this.get('titleErrorStatus') ||
                       this.get('widthErrorStatus') ||
                       this.get('heightErrorStatus')
                   )
               }
           },

           videoIdErrorStatus: {
               value: false,
           },

           titleErrorStatus: {
               value: false,
           },

           widthErrorStatus: {
               value: false,
           },

           heightErrorStatus: {
               value: false,
           }
       }
   });

   Y.eZ.FieldEditView.registerFieldEditView(
       FIELDTYPE_IDENTIFIER, Y.km.YtEditView
   );
});