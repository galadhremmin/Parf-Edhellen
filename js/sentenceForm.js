define(['exports', 'utilities'], function (exports, util) {
  var CSentenceForm = function () {
    this.parentElement   = null;
    this.htmlTemplate    = null;
    this.fragmentElement = null;
    this.fragments       = [];
    this.dataSourceField = null;
    this.lastCommitted   = null;
  };

  /**
   * Assigns the specified element as the root element. The element will
   * be used in the load method.
   *
   * @method setElement
   * @param {jQuery} element  Wrapped parent element.
   */
  CSentenceForm.prototype.setElement = function (element)  {
    util.CAssert.jQuery(element);

    this.parentElement = element;

    // Retrieve the data source
    this.dataSourceField = element.find('#ed-sentence-definitions');
    if (this.dataSourceField.length < 1) {
      throw 'Failed to find data source field.';
    }
    this.fragments = JSON.parse(this.dataSourceField.val());

    // Retrieve fragment item templates
    var templateElement = this.parentElement.find('.ed-sentence-template');
    this.fragmentElement = templateElement.parent();
    this.htmlTemplate = this.fragmentElement.html();
    templateElement.remove(); // template acquisitioned, so remove it from the DOM

    // Read and save the sentence, as it's to be considered the one "last committed"
    this.lastCommitted = this.parentElement.find('#ed-sentence-full').val();

    // Listen to sentence changes
    var _this = this;
    this.parentElement.find('#ed-sentence-full').on('keyup', function () {
      _this.commitChange( this.value );
    });
  };

  /**
   * Initializes and runs the form for this context.
   *
   * @method load
   */
  CSentenceForm.prototype.load = function () {
    this.renderWords();
  };
  
  CSentenceForm.prototype.renderWords = function() {
    var replacer, result, fragment, i, rows = [];

    for (i = 0; i < this.fragments.length; i += 1) {
      fragment = this.fragments[i];
      fragment.index = i;

      replacer = new util.CTokenReplacer(this.htmlTemplate, fragment);
      result = replacer.replace();

      rows.push(result);
    }

    this.fragmentElement.html(rows.join(''));
  };

  CSentenceForm.prototype.commitChange = function (sentence) {

    // Don't change sentences already committed
    if (this.lastCommitted === sentence) {
      return;
    }

    this.lastCommitted = sentence;

    var i, word, fragment, words = sentence.split(' '), interpunctuationReg = /[!?,]/;

    // Pre-processing the word array
    for (i = 0; i < words.length; i += 1) {
      word = words[i];
      // Look for interpunctuation...
      var match = interpunctuationReg.exec(word);
      if (match) {
        var pos = word.indexOf(match[0]);

        words[i] = word.substr(0, pos);
        words.splice(i + 1, 0, word.substr(pos));

        i += 1; // Increment by one, or we'll stumble onto the character we just added...
      } else {
        // Look for empty items
        if (word.length < 1) {
          words.splice(i, 1);
          i -= 1; //  try again
        }
      }
    }

    for (i = 0; i < words.length; i += 1) {
      word = words[i];

      if (this.fragments.length > i) {
        fragment = this.fragments[i];
      } else {
        fragment = {
          fragment: null,
          index: i,
          comments: '',
          tengwar: '',
          translationID: null
        };

        // help with basic interpunctuation
        switch (word) {
          case '!':
            fragment.tengwar = 'Á';
            fragment.comments = 'Exclamation mark';
            break;
          case ',':
            fragment.tengwar = '=';
            fragment.comments = 'Comma';
            break;
          case '?':
            fragment.tengwar = 'À';
            fragment.comments = 'Question mark';
            break;
        }

        this.fragments.push(fragment);
      }

      fragment.fragment = word;
    }

    if (i < this.fragments.length) {
      this.fragments.splice(i);
    }

    this.renderWords();
  };

  return new CSentenceForm();
});