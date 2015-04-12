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
      _this.favourite($(this).data('translation-id'), true);
    });
  }
  
  CTranslationView.prototype.favourite = function (id, add) {
    util.CAssert.number(id);
    util.CAssert.boolean(add);
    
    $.ajax({
      url: '/api/profile/favourite',
      data: { translationID: id, add: add }
    }).done(function (data) {
      console.log(data);
    });
  }

  return new CTranslationView();
});
