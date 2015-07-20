define(['exports', 'utilities'], function (exports, util) {
  
  var CTranslationView = function () {
    this.parentElement = null;
  };
  
  /**
   * Assigns the specified element as the root element. The element will
   * be used in the load method.
   *
   * @method setElement
   * @param {jQuery} element  Wrapped parent element.
   */
  CTranslationView.prototype.setElement = function (element)  {
    util.CAssert.jQuery(element);
    
    this.parentElement = element;
  }

  /**
   * Initializes the extra features for the translation view.
   *
   * @method load
   */
  CTranslationView.prototype.load = function () {
    // Activate all favourite buttons...
    var _this = this;
    this.parentElement.find('.ed-favourite-button').on('click', function (ev) {
      ev.preventDefault();
      var glyph = $(this).find('span');

      _this.favourite($(this).data('translation-id'), ! glyph.hasClass('glyphicon-heart')).then(function (added) {
        if (added) {
          glyph.addClass('glyphicon-heart');
          glyph.removeClass('glyphicon-heart-empty');
        } else {
          glyph.addClass('glyphicon-heart-empty');
          glyph.removeClass('glyphicon-heart');
        }
      });
    });

    this.parentElement.find('.ed-delete-button').on('click', function (ev) {
      ev.preventDefault();

      if (! confirm('Are you sure you want to delete this item?')) {
        return;
      }

      var id = $(this).data('translation-id');
      var block = $(this).parents('blockquote');
      _this.deleteTranslation(id).then(function (data) {
        if (data.succeeded === 'true') {
          block.fadeOut();
        }
      });
    })
  };
  
  CTranslationView.prototype.favourite = function (id, add) {
    util.CAssert.number(id);
    util.CAssert.boolean(add);
    
    return $.ajax({
      url: '/api/profile/favourite',
      data: { translationID: id, add: add },
      method: 'post'
    }).then(function (data) {
      return data.response.add;
    });
  };

  CTranslationView.prototype.deleteTranslation = function (id) {
    util.CAssert.number(id);

    return $.ajax({
      url: '/api/translation/delete',
      data: {translationID: id},
      method: 'post'
    });
  };

  return new CTranslationView();
});
