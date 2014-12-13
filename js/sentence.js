define(['exports', 'utilities'], function (exports, util) {
  /**
   * Sentence navigator.
   *
   * @class CSentence
   * @constructor
   */
  var CSentence = function () { 
    this.parentElement = null;
  }
  
  /**
   * Assigns the specified element as the root element. The element will
   * be used in the load method.
   *
   * @method setElement
   * @param {jQuery} element  Wrapped parent element.
   */
  CSentence.prototype.setElement = function (element)  {
    util.CAssert.jQuery(element);
    
    this.parentElement = element;
  }
  
  /**
   * Initializes and runs the sentence navigator.
   *
   * @method load
   */
  CSentence.prototype.load = function () {
    // * We assume that parentElement is set, as the setElement method is always
    //   automatically invoked by the elfdict initializer.
    // util.CAssert.jQuery(this.parentElement);
    
    var _this = this;
    
    this.parentElement.find('h3 a').each(function () {
      // Hook onto the default behaviour of clicking and touching.
      $(this).on('click touchstart', function (ev) {
        ev.preventDefault();
        _this.fragmentInteracted( $(this) );
      });
    });
  }
  
  CSentence.prototype.fragmentInteracted = function (fragment) {
    util.CAssert.jQuery(fragment);
    
    var translationID = parseInt( fragment.data('translation-id') );
    this.beginLoadTranslation(translationID);
  }
  
  CSentence.prototype.beginLoadTranslation = function (translationID) {
    util.CAssert.number(translationID);
    
    // 0 isn't a valid ID
    if (translationID === 0) {
      return;
    }
    
    var _this = this;
    $.get('/api/translation/' + translationID, function (data) {
      _this.endLoadTranslation(data);
    });
  }
  
  CSentence.prototype.endLoadTranslation = function (data) {
    if (!data) {
      return;
    }
    
    console.log(data.response);
  }
  
  return new CSentence();
});
