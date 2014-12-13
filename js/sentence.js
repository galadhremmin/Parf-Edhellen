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
    var fragmentID    = parseInt( fragment.data('fragment-id') );
    this.beginLoadTranslation(fragmentID, translationID);
  }
  
  CSentence.prototype.beginLoadTranslation = function (fragmentID, translationID) {
    util.CAssert.number(translationID);
    
    // 0 isn't a valid ID
    if (translationID === 0) {
      return;
    }
    
    var _this = this;
    $.get('/api/translation/' + translationID, function (data) {
      _this.endLoadTranslation(fragmentID, data);
    });
  }
  
  CSentence.prototype.endLoadTranslation = function (fragmentID, data) {
    if (!data) {
      return;
    }
    
    // Escape if the web server failed to process the request.
    if (data.error) {
      (console.error || console.log).call(console, data.error);
      return;
    }
    
    // Find the dialogue, and escape if it doesn't exist.
    var dialogue = $('#fragment-dialogue-' + fragmentID);
    if (dialogue.length < 1) {
      return;
    }
    
    // Populate the dialogue with the information we received from the web API.
    var t = data.response;
    
    // A quick little inline helper function for showing/hiding empty paragraphs.
    var hideIfEmpty = function (s, value) {
      if (!value || value.length < 1) {
        s.hide();
      } else {      
        s.html(value).show();
      }
    }
    
    hideIfEmpty(dialogue.find('.ed-comments'), t.comments);
    hideIfEmpty(dialogue.find('.ed-translation'), t.translation);
    
    dialogue.find('.ed-word').html(t.word);
    dialogue.find('.ed-source').html(t.source);
    dialogue.find('.ed-etymology').html(t.etymology);
    
    // Open the dialogue
    var options = {
      keyboard: true,
      backdrop: true,
      show: true
    };
    
    dialogue.modal(options);
  }
  
  return new CSentence();
});
