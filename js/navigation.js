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
    
    $(window).on('hashchange', function (ev) {
      ev.preventDefault();
      
      _this.navigate(String(window.location.hash).substr(1));
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
      
      $(window).trigger('navigator.navigated', [term]);
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
    
    this.searchField      = null;
    this.resultContainer  = null;
    this.resultWrapper    = null;
    this.resultCountLabel = null;
    this.buttonWrapper    = null;
    this.buttonForward    = null;
    this.buttonBackward   = null;
    this.titleElement     = null;
    
    this.currentDigest    = 0;
    this.changeTimeout    = 0;
    this.languageId       = 0;
    
    this.isReversed       = false;
    this.suggestionsArray = null;
    this.iterationIndex   = 0;
    
    this.resultVisibility = true;
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
    
    // Find all result container. References to them are retained for 
    // performance reasons.
    this.searchField      = document.getElementById(this.searchFieldId);
    this.resultContainer  = document.getElementById(this.searchResultId);
    this.resultWrapper    = document.getElementById(this.searchResultId + '-wrapper');
    this.resultCountLabel = document.getElementById(this.searchResultId + '-count');
    this.buttonWrapper    = document.getElementById(this.searchResultId + '-navigator');
    this.buttonForward    = document.getElementById(this.searchResultId + '-navigator-forward');
    this.buttonBackward   = document.getElementById(this.searchResultId + '-navigator-backward');
    this.titleElement     = document.getElementById(this.searchResultId + '-wrapper-toggler-title');
    
    // Attach events
    $(this.searchField).on('keyup', function (ev) {
      _this.beginSpringSuggestions(ev);
      return false;
    }).on('keypress', function (ev) {
      // Route the enter-key
      if ((ev.keyCode || ev.which) === 13) {
        ev.preventDefault();
        _this.enableNavigationBar(0);
        _this.navigateToSuggestion();
      }
    }).on('click', function (ev) {
      $(this).select();
    });
    
    $(this.buttonForward).on('click', function (ev) {
      ev.preventDefault();
      _this.enableNavigationBar(1);
      _this.navigateToSuggestion();
    });
    
    $(this.buttonBackward).on('click', function (ev) {
      ev.preventDefault();
      _this.enableNavigationBar(-1);
      _this.navigateToSuggestion();
    });
    
    $(this.titleElement).on('click', function () {
      _this.toggleSearchResults();
    });
    
    $('#' + this.languageFilterId).on('change', function () {
      var languageId = parseInt(this.options[this.selectedIndex].value);
      _this.changeLanguage(languageId);
    });
    
    $('#' + this.reversedSearchId).on('change', function () {
      var reversed = this.checked;
      _this.changeReversed(reversed);
    });
    
    $(window).on('navigator.navigated', function (ev, term) {
      _this.updateSelectedSuggestion(term);
    });
    
    // Free up resources no longer needed
    this.searchResultId   = undefined;
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
    
    // Search conditions have changed! Request new suggestions.
    this.endSpringSuggestions();
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
    
    // Search conditions have changed! Request new suggestions.
    this.endSpringSuggestions();
  }
  
  /**
   * Invokes the mehod endSpringSuggestions asynchronously, and erases previous
   * instances of the same method awaiting invocation. This timeout mechanism is
   * meant to throttle search queries while the client is still typing.
   *
   * @private
   * @method beginSpringSuggestions
   */
  CSearchNavigator.prototype.beginSpringSuggestions = function (ev) {
    if (ev) {
      var direction = null;
      switch (ev.keyCode || ev.which) {
        case 38:
          direction = -1;
          break;
        case 40:
          direction = 1;
          break;
        case 13:
          direction = 0;
          break;
      }
      
      if (direction !== null) {
        ev.preventDefault();
        
        this.enableNavigationBar(direction);
        this.navigateToSuggestion();
        
        return false;
      }
    }
    
    if (this.changeTimeout) {
      window.clearTimeout(this.changeTimeout);
    }
    
    var _this = this;
    this.changeTimeout = window.setTimeout(function () {
      _this.changeTimeout = 0;
      _this.endSpringSuggestions();
    }, 250);
    
    return true;
  }
    
  /**
   * Invokes springSuggestions provided that the element's value digest differ
   * from the digest for the loaded suggestions.
   *
   * @private
   * @method endSpringSuggestions
   */
  CSearchNavigator.prototype.endSpringSuggestions = function () {
    var value = this.searchField.value,
        digest = (value + ',' + this.isReversed + ',' + this.language).hashCode(),
        term;
    
    if (digest === this.currentDigest) {
      // No change, so return!
      return;
    }
    
    term = $.trim(value);
    if (term.length < 1) {
      return;
    }
    
    this.requestSuggestions(term);
    this.currentDigest = digest;
  }
   
  /**
   * Retrieves suggestions for the specified term through an AJAX request to 
   * the web service.
   *
   * @private
   * @method requestSuggestions
   * @param {String} term  Term to retrieve suggestions for.
   */
  CSearchNavigator.prototype.requestSuggestions = function (term) {
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
  
  /**
   * Presents the provided array with suggestions. This method assumes that the 
   * array contains objects with at least two properties, nkey and key.
   *
   * @private
   * @method presentSuggestions
   * @param {String} suggestions  An array with objects containing key and nkey.
   */
  CSearchNavigator.prototype.presentSuggestions = function (suggestions) {
    util.CAssert.array(suggestions);
    
    var items = [], suggestion, filteredSuggestions = [], i;
    
    for (i = 0; i < suggestions.length; i += 1) {
      suggestion = suggestions[i];
      
      // This happens sometimes, that the normalized keys are the same. This is usually
      // due to words being both verbs (thus ending with a hyphen) and nouns. It
      // doesn't matter that they are filtered out, as their translation will yield
      // both variants.
      if (i > 0 && suggestions[i - 1].nkey === suggestions[i].nkey)  {
        continue;
      }
      
      items.push('<li><a href="#' + encodeURIComponent(suggestion.nkey) + 
        '" data-hash="' + suggestion.nkey.hashCode() + '">' + suggestion.key + 
        '</a></li>');
        
      filteredSuggestions.push(suggestions[i]);
    }
    
    if (this.resultCountLabel) {
      this.resultCountLabel.innerText = items.length;
    }
    
    // Open/close the wrapper depending on the result set.
    if (items.length > 0) {
      $(this.resultWrapper).removeClass('hidden');
    
      // Wrap the items in <ul> tags and and update the result container
      items.unshift('<ul>');
      items.push('</ul>');
      
      this.resultContainer.innerHTML = items.join('');
      
      // Hacky solution for scrolling into view ... I just can't think of a 
      // pretty way of achieving this result.
      $(this.resultContainer).find('a').on('click', function () {
        var wrapper = $('#search-result-wrapper');
        var newY = wrapper.offset().top + wrapper.height() - 50;
        
        $('body').animate({ scrollTop: newY + 'px' }, 500);
      });
    } else {
      $(this.resultWrapper).addClass('hidden');
    }
    
    this.suggestionsArray = filteredSuggestions;
    this.iterationIndex = 0;
    
    this.toggleSearchResults(true);
    this.enableNavigationBar();
  }
  
  /**
   * Toggles the navigation bar based on the item currently enabled. The 
   * direction parameter is optional, and might be used to modify the current
   * selection.
   *
   * @private
   * @method enableNavigationBar
   * @param {Number} direction  Direction modifier, either positive or negative.
   */
  CSearchNavigator.prototype.enableNavigationBar = function (direction) {
    if (!this.suggestionsArray) {
      return;
    }
    
    if (direction !== undefined) {
      util.CAssert.number(direction);      
      this.iterationIndex += direction;
    }
    
    // Constraint within the boundaries of the array with suggestions
    if (this.iterationIndex < 0) {
      this.iterationIndex = 0;
    }
    
    if (this.iterationIndex >= this.suggestionsArray.length) {
      this.iterationIndex = this.suggestionsArray.length - 1;
    }
    
    // Determine whether the client can go back / forth from the current position.
    var canGoForward  = this.iterationIndex + 1 < this.suggestionsArray.length,
        canGoBackward = this.iterationIndex > 0;
    
    if (!canGoForward && !canGoBackward) {
      // Hide the navigation bar if navigation isn't available. 
      $(this.buttonWrapper).addClass('hidden');
    } else  {
      $(this.buttonWrapper).removeClass('hidden');
    }
    
    this.buttonForward.style.display  = canGoForward  ? '' : 'none';
    this.buttonBackward.style.display = canGoBackward ? '' : 'none';
    
    var word;
    if (canGoForward) {
      word = this.buttonForward.querySelector('.word');
      if (word) {
        word.innerText = this.suggestionsArray[this.iterationIndex + 1].key;
      }
    }
    
    if (canGoBackward) {
      word = this.buttonBackward.querySelector('.word');
      if (word) {
        word.innerText = this.suggestionsArray[this.iterationIndex - 1].key;
      }
    }
  }
  
  /**
   * Navigates to the suggestion item currently active.
   *
   * @private
   * @method navigateToSuggestion
   */
  CSearchNavigator.prototype.navigateToSuggestion = function () {
    if (!this.suggestionsArray || this.suggestionsArray.length < this.iterationIndex) {
      return;
    }
    
    var hash = encodeURIComponent(this.suggestionsArray[this.iterationIndex].nkey);
    window.location.hash = '#' + hash;
  }
  
  /**
   * Selects the suggestion item currently active.
   *
   * @private
   * @method updateSelectedSuggestion
   * @param {String} term Normalized term.
   */
  CSearchNavigator.prototype.updateSelectedSuggestion = function (term) {
    util.CAssert.string(term);
    
    var hash = term.hashCode(),
        items = $(this.resultContainer).find('li a'),
        selectedItem;
    
    selectedItem = items.filter('[data-hash="' + hash + '"]');
    
    selectedItem.addClass('selected');
    items.not(selectedItem).removeClass('selected');
    
    if (this.suggestionsArray) {
      this.iterationIndex = selectedItem.parents('li').index();
      this.enableNavigationBar();
    }
  }
  
  /**
   * Toggles the search results view.
   *
   * @private
   * @method toggleSearchResults
   * @param {Boolean} visibility 
   */
  CSearchNavigator.prototype.toggleSearchResults = function (visibility) {
    if (visibility !== undefined && this.resultVisibility === visibility) {
      return;
    }
    
    var results = $(this.resultWrapper).find('.panel-body'),
        arrows  = $(this.titleElement).find('.glyphicon');
      
    results.toggle();
    arrows.toggleClass('glyphicon-minus');
    arrows.toggleClass('glyphicon-plus');
    
    this.resultVisibility = !this.resultVisibility;
  }
  
  exports.CSearchNavigator = CSearchNavigator;
});
