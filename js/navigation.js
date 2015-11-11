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
    
    this.currentTerm     = undefined;
    this.containerId     = containerId;
    this.loader          = util.CLoadingIndicator.shared('search-query-field-loading');
    this.onNavigated     = null;
    this.languageId      = 0;
  };

  CNavigator.usesHistoryManipulation = function () {
    return (typeof window.history.replaceState === 'function')
  };
  
  /**
   * Binds the hash change event to the window, and processes existing
   * hashes. This method should only be invoked once.
   *
   * @method listen
   */
  CNavigator.prototype.listen = function ()  {
    var _this = this;

    if (CNavigator.usesHistoryManipulation) {
      $(window).on('popstate', function (ev) {
        if (!_this.process(false)) {
          window.location.reload();
        }
      });
    } else {
      $(window).on('hashchange', function (ev) {
        ev.preventDefault();
        _this.process(false);
      });
    }

    $(window).on('navigator.history', function (ev, address) {
      window.history.pushState('', null, address);
      _this.process(false);
    });

    $(window).on('navigator.navigate', function (ev, hash) {
      _this.navigate(hash, false, 0); // reload!
    });

    $(window).on('navigator.language', function (ev, languageId) {
      _this.languageId = languageId;
      _this.process(true);
    });

    this.process(false);
  };

  /**
   * Retrieves the current term.
   * @param {String} newAddress Optional address to process. If left undefined, the current address is examined.
   * @returns {String} current term, or undefined if none exists
   */
  CNavigator.prototype.getTerm = function (newAddress) {
    var hash = String(window.location.hash).substr(1);
    var term = undefined;

    if (hash.length < 1) {
      if (CNavigator.usesHistoryManipulation) {
        var parts = /\/w\/([^\/]+)$/.exec(newAddress || window.location.href);
        if (parts && parts.length >= 2) {
          return decodeURIComponent(parts[1]);
        }
      }

      return term;
    }

    var hashbang = /[!&]w=([^&]+)/.exec(hash);
    if (hashbang && hashbang.length >= 2) {
      term = hashbang[1]; // retrieve the value of the key-value pair.
    }

    if (!term) {
      // attempt to retrieve the word by the deprecated method.
      term = hash;
    }

    return term;
  };

  /**
   * Processes hashbangs and manipulation of history.
   * @param reload whether to reload the page on navigation
   * @param address optional parameter which specifies the address to process
   * @returns {Boolean} returns true if the current state was processed
   */
  CNavigator.prototype.process = function (reload, address) {
    util.CAssert.boolean(reload);

    var term = this.getTerm(address);
    if (term) {
      this.navigate(term, reload); // reload!
    }

    return !!term;
  };
  
  /**
   * Navigates to the specified hash. This method is invoked when the
   * hash changes.
   *
   * @method navigate
   * @param {String} hash Hash argument in its raw form (location.hash).
   * @param {Boolean} disableScroll Optional parameter to disable scrolling.
   * @param {Number} overrideLanguage Optional parameter to temporarily override language.
   */
  CNavigator.prototype.navigate = function (hash, disableScroll, overrideLanguage) {
    util.CAssert.string(hash);

    if (this.currentTerm === hash) {
      return;
    }
    
    console.log('CNavigator: new navigation request for "' + hash + '".');
    
    var term = $.trim(hash);
    if (term.length < 1 || term.indexOf('_=_') > -1) {
      console.log('CNavigator: invalid term. Cancelling.');
      return;
    }
    
    if (term.indexOf('%') > -1) {
      term = decodeURIComponent(term);
    }
    
    this.loader.loading();

    var data = { term: term, ajax: true };
    if (overrideLanguage !== undefined) {
      data.languageId = overrideLanguage;
    } else if (this.languageId) {
      data.languageId = this.languageId;
    }

    var _this = this;
    $.get('translate.php', data).done(function (data) {
      console.log('CNavigator: successfully retrieved term "' + term + '".');
      
      _this.navigated(data, disableScroll);
      _this.currentTerm = term;

      // In some cases, the hash might not be set.
      if (CNavigator.usesHistoryManipulation) {
        window.history.replaceState(null, '', window.location.protocol + '//' + window.location.host + '/w/' + encodeURIComponent(term));
      } else if (window.location.hash !== '#!w=' + term) {
        window.location.hash = '#!w=' + term;
      }
      
      $(window).trigger('navigator.navigated', [term]);
    }).fail(function (ex) {
      console.log('CNavigator: failed to retrieve "' + term + '".', ex);
    }).always(function () {
      _this.loader.loaded();
    });
  };
  
  /**
   * Handles the specified data string once it has been successfully 
   * received by AJAX. This method is triggered by the method navigate.
   * This method changes the DOM.
   *
   * @method navigate
   * @param {String} data Data from the AJAX request.
   */
  CNavigator.prototype.navigated = function (data, disableScroll) {
    util.CAssert.string(data);
    
    var result = document.getElementById(this.containerId);
    if (!result) {
      console.log('CNavigator: failed to present the result. Can\'t find container "' + this.containerId + '"');
      return;
    }
    
    result.innerHTML = data;
    console.log('CNavigator: inserted result into "' + this.containerId + '".');
    
    // Ensure that the result view is within the viewport, and animate the 
    // transition into the viewport if it isn't.
    if (! disableScroll && ! util.isElementInViewport(result)) {
      var position = Math.floor($(result).offset().top - 40); // 40 = magic number! ;)
      $('body,html').animate({scrollTop: position}, 500);
    }
    
    if (typeof this.onNavigated === 'function') {
      this.onNavigated.call(this);
    }
  };
  
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
    reversedSearchId, languageFilterId, loadingIndicatorId) {
    util.CAssert.string(searchFieldId, searchResultId);
    
    this.searchFieldId      = searchFieldId;
    this.searchResultId     = searchResultId;
    this.reversedSearchId   = reversedSearchId;
    this.languageFilterId   = languageFilterId;
    
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
  };
  
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
    this.loader           = util.CLoadingIndicator.shared(this.searchFieldId + '-loading');
    
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
  };
  
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

    // Inform potential listeners that language has changed
    $(window).trigger('navigator.language', [id]);
  };
  
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
  };
  
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
  };
    
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
      $(this.resultWrapper).addClass('hidden');
      return;
    }
    
    this.requestSuggestions(term);
    this.currentDigest = digest;
  };
   
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
    
    this.loader.loading();
    
    var _this = this;
    $.ajax({
      url: '/api/word/search',
      data: requestData,
      dataType: 'json',
      type: 'post'
    }).done(function(data) {
      if (data.succeeded) {
        _this.presentSuggestions(data.response.words);
      }
    }).always(function () {
      _this.loader.loaded();
    });
  };
  
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
    
    var items = [], 
        filteredSuggestions = [], 
        suggestion, 
        compatibilityMode = false, 
        itemsPerColumn = 0,
        columnIsClosed = true,
        i, j,
        url,
        manip = CNavigator.usesHistoryManipulation;
    
    if (Modernizr && !Modernizr.csscolumns) {
      compatibilityMode = true;
      columnIsClosed = true;
      itemsPerColumn = suggestions.length;
      
      if (itemsPerColumn > 3) {
        itemsPerColumn = Math.ceil(itemsPerColumn / 3);
      }
      
      this.resultContainer.style.width = '100%';
    }
    
    for (i = 0; i < suggestions.length; i += 1) {
      suggestion = suggestions[i];
      
      // This happens sometimes, that the normalized keys are the same. This is usually
      // due to words being both verbs (thus ending with a hyphen) and nouns. It
      // doesn't matter that they are filtered out, as their translation will yield
      // both variants.
      if (i > 0 && suggestions[i - 1].nkey === suggestions[i].nkey)  {
        continue;
      }
      
      if (compatibilityMode && columnIsClosed) {
        items.push('<div class="col-sm-4">');
        columnIsClosed = false;
      }

      url = (manip ? '/w/' : '#!w=') + encodeURIComponent(suggestion.nkey);
      items.push('<li><a href="' + url + '" data-hash="' + suggestion.nkey.hashCode() + '">' + suggestion.key +
        '</a></li>');
      
      if (compatibilityMode) {
        j = filteredSuggestions.length; 
        
        if (j > 0 && j % itemsPerColumn === 0) {
          items.push('</div>');
          columnIsClosed = true;
        }
      }
      
      filteredSuggestions.push(suggestions[i]);
    }
    
    if (compatibilityMode && !columnIsClosed) {
      items.push('</div>');
      columnIsClosed = true;
    }
    
    if (this.resultCountLabel) {
      this.resultCountLabel.innerHTML = filteredSuggestions.length;
    }
    
    // Open/close the wrapper depending on the result set.
    if (filteredSuggestions.length > 0) {
      // Wrap the items in <ul> tags and and update the result container
      items.unshift('<ul>');
      items.push('</ul>');
    } else {
      
    }
    
    this.resultContainer.innerHTML = items.join('');
    
    // display the list of suggestions
    $(this.resultWrapper).removeClass('hidden');
    $(this.resultWrapper).find('.results-panel')[items.length ? 'removeClass' : 'addClass']('hidden');
    $(this.resultWrapper).find('.results-empty')[items.length ? 'addClass' : 'removeClass']('hidden');

    // intercept links as they're clicked if history manipulation mode is active
    if (manip) {
      $(this.resultContainer).find('li > a').on('click', function (ev) {
        ev.preventDefault();
        $(window).trigger('navigator.history', [this.href]);
      });
    }

    this.suggestionsArray = filteredSuggestions;
    this.iterationIndex = 0;
    
    this.toggleSearchResults(true);
    this.enableNavigationBar();
  };
  
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
    
    var word, element;
    if (canGoForward) {
      element = this.buttonForward.querySelector('.word');
      if (element) {
        word = this.suggestionsArray[this.iterationIndex + 1].key;
        element.innerHTML = this.trimWordsWithEllipsis(word);
      }
    }
    
    if (canGoBackward) {
      element = this.buttonBackward.querySelector('.word');
      if (element) {
        word = this.suggestionsArray[this.iterationIndex - 1].key;
        element.innerHTML = this.trimWordsWithEllipsis(word);
      }
    }
  };
  
  /**
   * Shortens long string with an ellipsis.
   *
   * @private
   * @method trimWordsWithEllipsis
   * @param {String} word Word(s) to shorten with an ellipsis
   */
  CSearchNavigator.prototype.trimWordsWithEllipsis = function (word) {
    util.CAssert.string(word);
    var maxLength = 24;
    
    if (word.length > maxLength) {
      var words = word.split(' ');
      var length = 0;
      var i = 0;
      
      if (words.length === 1) {
        return words.substr(0, 24) + '...';
      }
      
      for (i = 0; i < words.length; i += 1) {
        if (length + words[i].length > maxLength) {
          return words.slice(0, i).join(' ') + ' ...';
        }
        
        length += words[i].length + 1;
      }
    }
    
    return word;
  };
  
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
    
    var hash = null;
    if (this.suggestionsArray && this.suggestionsArray.length) {
      if (this.iterationIndex < 0) {
        this.iterationIndex = 0;
      }

      hash = encodeURIComponent(this.suggestionsArray[this.iterationIndex].nkey);
    } else {
      hash = encodeURIComponent( $(this.searchField).val() );
    }
    
    // ensure that the client is searching from the front-page, and not a sub-page:
    if (!CNavigator.usesHistoryManipulation) {
      if (window.location.pathname.length <= 1 || window.location.pathname.indexOf('/index.page') === 0) {
        window.location.hash = '#!w=' + hash;
      } else {
        window.location.href = '/index.page#!w=' + hash;
      }
    }
  };
  
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
  };
  
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
  };
  
  exports.CSearchNavigator = CSearchNavigator;
});
