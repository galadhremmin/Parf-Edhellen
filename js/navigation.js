define(['exports', 'utilities'], function (exports, util) {
  /**
   * Grabs the hash on change, and passes it to the translation service. 
   *
   * @class CNavigator
   * @constructor
   * @param {String} containerId Navigation result container.
   */
  var CNavigator = function (containerId) { 
    util.CAssert.string(containerId);
    
    this.currentTerm = undefined;
    this.containerId = containerId;
  }
  
  /**
   * Binds the hash change event to the window, and processes existing
   * hashes. This method should only be invoked once.
   *
   * @method listen
   */
  CNavigator.prototype.listen = function ()  {
    var _this = this;
    var currentHash;
    
    $(window).on('hashchange', function () {
      _this.navigate( String(window.location.hash).substr(1) );
    });
    
    this.navigate(String(window.location.hash).substr(1));
  }
  
  /**
   * Navigates to the specified hash. This method is invoked when the
   * hash changes.
   *
   * @method navigate
   * @param {String} hash Hash argument in its raw form (location.hash).
   */
  CNavigator.prototype.navigate = function (hash) {
    util.CAssert.string(hash);
    
    var term = $.trim(hash);
    if (term.length < 1) {
      return;
    }
    
    if (term.indexOf('%') > -1) {
      term = decodeURIComponent(term);
    }
    
    var _this = this;
    $.get('translate.php', { term: term, ajax: true }, function (data) {
      _this.navigated(data);
      _this.currentTerm = term;
    });
  }
  
  /**
   * Handles the specified data string once it has been successfully 
   * received by AJAX. This method is triggered by the method navigate.
   * This method changes the DOM.
   *
   * @method navigate
   * @param {String} data Data from the AJAX request.
   */
  CNavigator.prototype.navigated = function (data) {
    util.CAssert.string(data);
    
    var result = document.getElementById(this.containerId);
    if (result) {
      result.innerHTML = data;
    }
  }
  
  exports.CNavigator = CNavigator;
  
  /**
   * Handles search suggestions and navigation through the search 
   * results.
   *
   * @class CSearchNavigator
   * @constructor
   * @param {String} searchFieldId    ID for a search field.
   * @param {String} searchResultId   ID for a search result field.
   * @param {String} searchResultId   ID for a reverse query checkbox.
   * @param {String} languageFilterId ID for a select box with languages.
   */
  var CSearchNavigator = function (searchFieldId, searchResultId, 
    reversedSearchId, languageFilterId) {
    util.CAssert.string(searchFieldId, searchResultId, reversedSearchId);
    
    this.searchFieldId    = searchFieldId;
    this.searchResultId   = searchResultId;
    this.reversedSearchId = reversedSearchId;
    this.languageFilterId = languageFilterId;
    this.currentDigest    = 0;
    this.changeTimeout    = 0;
    this.languageId       = 0;
    this.isReversed       = false;
  }
  
  /**
   * Binds the change event to the search field, and processes existing
   * searches. This method should only be invoked once.
   *
   * @method listen
   */
  CSearchNavigator.prototype.listen = function ()  {
    var _this = this;
    var currentHash;
    
    $('#' + this.searchFieldId).on('keyup', function (ev) {
      _this.beginSpringSuggestions(ev, $(this));
    });
    
    $('#' + this.languageFilterId).on('change', function () {
      var languageId = parseInt(this.options[this.selectedIndex].value);
      _this.changeLanguage(languageId);
    });
    
    $('#' + this.reversedSearchId).on('change', function () {
      var reversed = this.value === '1';
      _this.changeReversed(reversed);
    });
    
    // Free up resources no longer needed
    this.searchFieldId    = undefined;
    this.languageFilterId = undefined;
    this.reversedSearchId = undefined;
  }
  
  /**
   * Event handler for language selection.
   *
   * @private
   * @method changeLanguage
   * @param {Number} id  New language ID
   */
  CSearchNavigator.prototype.changeLanguage = function (id) {
    util.CAssert.number(id);
    this.language = id;
  }
  
  /**
   * Event handler for reversed search selection.
   *
   * @private
   * @method changeLanguage
   * @param {Boolean} id  New reversed state.
   */
  CSearchNavigator.prototype.changeReversed = function (reversed) {
    util.CAssert.boolean(reversed);
    this.isReversed = reversed ? 1 : 0;
  }
  
  /**
   * Invokes the mehod endSpringSuggestions asynchronously, and erases previous
   * instances of the same method awaiting invocation. This timeout mechanism is
   * meant to throttle search queries while the client is still typing.
   *
   * @private
   * @method beginSpringSuggestions
   * @param {Event} ev        The JQuery event object
   * @param {JQuery} element  A JQuery object to the active input element.
   */
  CSearchNavigator.prototype.beginSpringSuggestions = function (ev, element) {
    util.CAssert.event(ev);
    util.CAssert.jQuery(element);
        
    if (this.changeTimeout) {
      window.clearTimeout(this.changeTimeout);
    }
    
    var _this = this;
    this.changeTimeout = window.setTimeout(function () {
      _this.endSpringSuggestions(element);
    }, 250);
  }
    
  /**
   * Invokes springSuggestions provided that the element's value digest differ
   * from the digest for the loaded suggestions.
   *
   * @private
   * @method endSpringSuggestions
   * @param {JQuery} element  A JQuery object to the active input element.
   */
  CSearchNavigator.prototype.endSpringSuggestions = function (element) {
    var digest = element.val().hashCode();
    var term;
    
    if (digest === this.currentDigest) {
      // No change, so return!
      return;
    }
    
    term = $.trim(element.val());
    if (term.length < 1) {
      return;
    }
    
    this.springSuggestions(term);
    this.currentDigest = digest;
  }
  
  CSearchNavigator.prototype.springSuggestions = function (term) {
    util.CAssert.string(term);
    
    var requestData = {
      'term': term,
      'reversed': this.isReversed || 0,
      'language-filter': this.language || 0
    };
    
    var _this = this;
    
    $.ajax({
      url: 'api/word/search',
      data: requestData,
      dataType: 'json',
      type: 'post',
      success: function(data) {
        if (data.succeeded) {
          _this.presentSuggestions(data.response.words);
        }
      }
    });
  }
  
  CSearchNavigator.prototype.presentSuggestions = function (suggestions) {
    var items = ['<ul>'];
    var suggestion, i, container, wrapper;
    
    for (i = 0; i < suggestions.length; i += 1) {
      suggestion = suggestions[i];
      items.push('<li><a class="search-result-item" href="#' + encodeURIComponent(suggestion.nkey) + '">' + suggestion.key + '</a></li>');
    }
    
    items.push('</ul>');
    
    container = document.getElementById(this.searchResultId);
    container.innerHTML = items.join('');
    
    wrapper = document.getElementById(this.searchResultId + '-wrapper');
    if (items.length > 0) {
      $(wrapper).removeClass('hidden');
    } else {
      $(wrapper).addClass('hidden');
    }
  }
  
  exports.CSearchNavigator = CSearchNavigator;
});
