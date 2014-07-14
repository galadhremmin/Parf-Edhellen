var LANGDict = {
  Config: {
    SLIDE_SPEED: 1000
  },
  lastHash: null,
  Loader: {
    inst: 0,
    inc: function() {
      ++this.inst;
      
      var targ = $('#loading');
      if (targ.is(':hidden') && this.inst > 0) {
        targ.show(LANGDict.Config.SLIDE_SPEED);
      }
    },
    dec: function() {
      --this.inst;
      
      if (this.inst < 1) {
        this.inst = 0;
        $('#loading').hide(LANGDict.Config.SLIDE_SPEED);
      }
    }
  },
  init: function() {
    $(window).bind(
      'hashchange',
      $.proxy(
        LANGDict.hashChanged, this
      )
    );

    $('.question-mark').bind(
      'click',
      function(ev) {
        var rel = $(this).attr('rel');
        if (rel && LANGDict.messages[rel]) {
          alert(LANGDict.messages[rel]);
        }
        return false;
      }
    );
    
    // check if there are actual browse to links around:
    var gotoElement = /browseTo=([a-z0-9]+)/.exec(window.location.search);
    if (gotoElement && gotoElement.length > 1) {
      var elem = $('a[name="' + gotoElement[1] + '"]');
      
      if (elem.length > 0) {
        LANGAnim.scroll(elem.offset().top);
      }
    }
    
    this.hashChanged();
  },
  contentLoaded: function(word, content) {
    var c = $('#result').html(content);
    
    c.find('h3, [rel=trans-translation], .word-comments').each(function () {
      LANGDict.highlight(this, word);
    });

    // select the item based on its hash
    LANGSearch.select(word);
  },
  highlight: function(container, what) {
    var content = container.innerHTML,
        pattern = new RegExp('\\b(' + what.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&") + ')\\b','g'),
        replaceWith = '<span class="highlight">$1</span>',
        highlighted = content.replace(pattern, replaceWith);
    container.innerHTML = highlighted;
  },
  submit: function(item) {
    if (!item) {
      item = $('#search-query-field').val();
    }
    
    location.hash = '#' + encodeURIComponent(item);
    return false;
  },
  hashChanged: function() {
    if (window.location.hash) {
      var s = new String(window.location.hash);
      this.load(decodeURIComponent(s.substr(1)));
      delete s;
    } else if (window.location.pathname && LANGDict.lastHash) {
        $.get(window.location.pathname + (window.location.search || ''), function (data) {
          $('#result').html($(data).find('#result')[0].innerHTML);
        });
    }

    window.scrollTo(0, 0);
  },
  load: function(item) {
    $('#result').html('<div class="loading">Loading...</div>');
    $.get('translate.php', { term: item, ajax: true }, function(data) {
      LANGDict.contentLoaded(item, data);
    });
    
    LANGDict.lastHash = item;
  }
};

var LANGCookies = function() {
  return {
    read: function(name) {
      var nameEQ = name + "=";
      var ca = document.cookie.split(';');
      for(var i = 0; i < ca.length; ++i) {
        var c = ca[i];
        while (c.charAt(0)==' ') {
          c = c.substring(1,c.length);
        }
        
        if (c.indexOf(nameEQ) == 0) {
          return c.substring(nameEQ.length,c.length);
        }
      }
      return null;
    },
    create: function(name,value,days) {
      if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        var expires = "; expires="+date.toGMTString();
      } else {
        var expires = "";
      }
      
      document.cookie = name+"="+value+expires+"; path=/";
    }
  };
}();

var LANGSearch = function() {
  var hook = function() {
    $(window).keydown(function(ev) {
      var keys = {
        submit: 13, // enter
        left: 37,
        right: 39,
        up: 38,
        down: 40,
        searchField: 83, // = S
        browseDown: 34, // = Page Down
        browseUp: 33 // = Page Up
      };
            
      switch (ev.keyCode) {
        case keys.submit:
          if (document.activeElement && document.activeElement.id === 'search-query-field') {
            ev.preventDefault();
            window.setTimeout(function() { 
              toggleSuggestions(false);
              location.hash = $('a.search-result-item:first-child').prop('href').split('#')[1]; 
            }, 0); // break out of the event asynchronously
          }
          break;
        case keys.browseDown:
          LANGDict.nextResult(1);
          break;
        case keys.browseUp:
          LANGDict.nextResult(-1);
          break;
        default:
        /*
          if (/^[a-zA-Z0-9]$/.test(String.fromCharCode(ev.keyCode)) ) {
            
            if (document.activeElement && document.activeElement.id !== 'search-query-field') {
              var target = $('#search-query-field');
              
              if (target.offset().top < $('body').scrollTop()) {
                LANGAnim.scroll(0);
              }

              target.focus();
              target.select();
            }
          } 
          */
      }
    });
  };
  
  var getFilter = function() {
    var selectBox = document.getElementById('search-language-select');
    
    if (selectBox.selectedIndex < 0) {
      return 0;
    }
    
    return selectBox.options[selectBox.selectedIndex].value;
  };
  
  var performSearch = function(request, response) {
    if (!request || !request.hasOwnProperty('term')) {
      request = {'term': $('#search-query-field').val()};
    }
    
    if (/^[\s\t\/\\]*$/.test(request['term'])) {
      return;
    }
  
    request['language-filter'] = getFilter();
    request['reversed'] = document.getElementById('search-reverse-box').checked ? 1:0;
    
    $.ajax({
      url: 'api/word/search',
      data: request,
      dataType: 'json',
      type: 'post',
      success: function(data) {
        if (data.succeeded) {
          LANGSearch.set(data.response);
        }
      }
    });
  };
  
  var toggleSuggestions = function (inferredState) {
    var toggler = $('#search-result-wrapper-toggler'), removeClassName, addClassName, action;
    
    if (typeof inferredState !== "boolean") {
      inferredState = ! toggler.hasClass('glyphicon-minus');
    }
    
    if (inferredState) {
      removeClassName = 'plus';
      addClassName = 'minus';
      action = 'show';
    } else {
      removeClassName = 'minus';
      addClassName = 'plus';
      action = 'hide';
    }
    
    toggler.removeClass('glyphicon-' + removeClassName);
    toggler.addClass('glyphicon-' + addClassName);
    
    $('#search-result-description').parent()[action]();
    $('#search-result').parent()[action](600);
  }
  
  var currentWordIndex = 0;
  
  return {
    init: function() {
      hook();
      $('#search-query-field').autocomplete({
        minLength: 1,
        source: performSearch
      }).focus();
      
      $('#search-reverse-box,#search-language-select').on('change', performSearch);
      $('#search-result-wrapper-toggler-title').on('click', toggleSuggestions);
      $('#search-result-navigator-backward').on('click', $.proxy(function() {
        this.gotoResult(-1);
      }, this));
      $('#search-result-navigator-forward').on('click', $.proxy(function() {
        this.gotoResult(+1);
      }, this));
    },
    set: function(data) {
      var items = ['<ul>'];
      
      for (var i = 0; i < data.words.length; i += 1) {
        items.push('<li><a class="search-result-item" href="#' + encodeURIComponent(data.words[i].nkey) + '">' + data.words[i].key + '</a></li>');
      }
      
      items.push('</ul>');
         
      var $result = $('#search-result');
      $result.html(items.join('')).find('a').on('click', function(ev) {
        ev.preventDefault();
        
        // Slide up the suggestions for smaller screens.
        if ($('body').outerWidth() < 800) {
          toggleSuggestions(false);
        } else {
          LANGAnim.scroll($('#result').offset().top - 50);
        }
        
        return false;
      });
      
      $result = $('#search-result-count');
      $result.text(data.words.length);
      
      $result = $('#search-result-wrapper');
      if (items.length > 0) {
        $result.removeClass('hidden');
        toggleSuggestions(true);
      } else {
        $result.addClass('hidden');
      }
      
      currentWordIndex = 0;
    },
    reset: function() {
      currentWordIndex = 0;
    },
    select: function (term) {
      var term = encodeURIComponent(term);
      $('a.search-result-item').each(function (index, item) {
        var element = $(this), 
            uri = element.prop('href').split('#');
        
        if (uri.length === 2 && uri[1] === term) {
          element.addClass('selected');
          currentWordIndex = index;
        } else {
          element.removeClass('selected');
        }
      });
      
      this.gotoResult(0);
    },
    gotoResult: function(dir) {
      var canGoBack = true, canGoForward = true;
      
      currentWordIndex += dir;
      
      if (currentWordIndex < 0) {
        currentWordIndex = 0;
      }
      
      if (currentWordIndex === 0) {
        canGoBack = false;
      }
      
      var links = $('#search-result li > a');
      if (currentWordIndex === links.length - 1) {
        canGoForward = false;
      }
      
      if (dir !== 0) {
        var hash = decodeURIComponent(links.get(currentWordIndex).href.split('#')[1]);
        toggleSuggestions(false);
        location.hash = '#' + hash;
      }
      
      var navigator = $('#search-result-navigator');
      if (!links.length || (!canGoBack && !canGoForward)) {
        navigator.addClass('hidden');
      } else {
        // Display the navigator
        navigator.removeClass('hidden');
        
        // Show/hide the back button and assign the previous word to its label
        var elem = $('#search-result-navigator-backward')[canGoBack ? 'show' : 'hide']();
        if (canGoBack) {
          elem.find('.word').text(links.get(currentWordIndex - 1).innerText);
        }
        
        // Show/hide the forward button and assign the next word to its label
        elem = $('#search-result-navigator-forward')[canGoForward ? 'show' : 'hide']();
        if (canGoForward) {
          elem.find('.word').text(links.get(currentWordIndex + 1).innerText);
        }
      }
    }
  };
}();

var LANGAnim = function() {
  return {
    scroll: function(offset) {
      var bodyTag = $('body');
      var increasing = $(window).scrollTop() < offset;
      if (!increasing && bodyTag.scrollTop() < 10) {
        bodyTag.scrollTop(0);
      } else {      
        bodyTag.animate({ scrollTop: offset }, 800);
      }
    },
    scrollTop: function (ev) {
      LANGAnim.scroll(0);
      return false;
    }
  };
}();

$(function() {
  LANGDict.init();
  LANGSearch.init();
});
