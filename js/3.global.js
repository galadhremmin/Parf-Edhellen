var LANGDict = {
  Config: {
    SLIDE_SPEED: 1000
  },
  messages: {
    'q-wordID': 'This is entry order, according to the function and the sense of the word (word.1, word.2, word.3 etc.).\n\nUsually you can leave this to its default value, but when a word might have multiple meanings such as Sindarin _a_, order the word with dots: a.1, a.2 etc.'
  },
  currentWordIndex: 0,
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
    // invoke upon hash change
    this.currentWordIndex = -1;
    
    var c = $('#result').html(content);
    $('.tengwar').tengwar();
    
    c.find('h3, [rel=trans-translation], .word-comments').each(function () {
      LANGDict.highlight(this, word);
    });
  },
  highlight: function(container, what) {
    var content = container.innerHTML,
        pattern = new RegExp('([^<\\s"\'=]*)(' + what.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&") + ')([^<\\s"\'=]*)','g'),
        replaceWith = '$1<span class="highlight">$2</span>$3',
        highlighted = content.replace(pattern, replaceWith);
    container.innerHTML = highlighted;
  },
  submit: function(item) {
    if (!item) {
      item = $('.word').val();
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
          $('#result').html($(data).find('#result'));
        });
    }
  },
  load: function(item) {
    $('#result').html('<div class="loading">Loading...</div>');
    $.get('translate.php', { term: item, ajax: true }, function(data) {
      LANGDict.contentLoaded(item, data);
    });
    
    LANGDict.lastHash = item;
  },
  cancelForm: function() {
    $('.extendable-form:visible').hide();
  },
  nextResult: function(dir) {
    this.currentWordIndex += dir;
    var block = $('#translation-block-' + this.currentWordIndex);
    
    if (block.length < 1) {
      var cur = this.currentWordIndex;
      this.currentWordIndex = 0;
      
      if (cur > 0) {
        this.nextResult();
      }
    } else {
      LANGAnim.scroll(block.offset().top);
    }
  },
  showForm: function(id) {
    var values = {};
    if (id) {
      // load values here
    }

    $('#extend-form').slideToggle(LANGDict.Config.SLIDE_SPEED, function() {
      var word = new String(window.location.hash);
      if (word.length > 0) {
        word = decodeURIComponent(word.substr(1));
      }
      $('#word-input').val(word).keyup().focus();
    }); // 1 second
    
    return false;
  },
  showIndexForm: function(id) {
    $('#index-form').slideToggle(LANGDict.Config.SLIDE_SPEED, function() {
      $(this).find('input[name="word"]').focus();
    });
    
    return false;
  },
  showTranslationForm: function(translationIDOrWord, preserveForm) {
    if (/string/i.test(typeof(translationIDOrWord))) {
      $('#translation-form span[rel=word]').html(translationIDOrWord);
      
      if (!preserveForm) {
        var reset = [ 
          'language', 0,
          'namespace', 0,
          'word', '',
          'translation', '', 
          'etymology', '',
          'type', 0,
          'source', '',
          'comments', '',
          'tengwar', '',
          'id', 0
        ];
      
        for (var i = 0; i < reset.length; i += 2) {
          var affectedElem = $('#translation-form [name='+reset[i]+']');
          if (affectedElem.attr('selectedIndex') === undefined) {
            affectedElem.val(reset[i + 1]);
          } else {
            affectedElem.attr('selectedIndex', reset[i + 1]);
          }
        }
      }
    } else {
      $.ajax({
        url: 'api/translation/' + translationIDOrWord,
        type: 'post',
        dataType: 'json',
        success: function(msg) {
          if (!msg.succeeded) {
            alert(msg.error ? msg.error : 'An unidentifiable error has unfortunately occurred.\n'+
            'Please refresh the page and try again.');
          }
          
          for (var name in msg.response) {
            $('#translation-form [name='+name+']').val(msg.response[name]);
          }

          LANGDict.showTranslationForm(msg.response.word, true);
        }
      });
      
      return false; /* this method will be invoked again once data has been acquired */
    }
    
    var targ = $('#translation-form');
    if (targ.is(':hidden')) {
      // targ.slideDown(LANGDict.Config.SLIDE_SPEED);
      targ.css({
        position: 'absolute',
        left: (($(document).width() - targ.outerWidth()) * 0.5) + 'px',
        top: ($(window).scrollTop() + ($(window).height() - targ.outerHeight()) * 0.5) + 'px'
      }).show();
    }
    
    $('#translation-form span[rel=function]').html(preserveForm ? 'Edit' : 'Add');
    return false;
  },
  saveNamespace: function(identifier) {
    this.Loader.inc();
    $.ajax({
      url: 'api/namespace/save',
      type: 'post',
      data: { identifier: identifier },
      dataType: 'json',
      success: function(msg) {
        if (msg.succeeded) {
          LANGDict.load(msg.response.identifier);
          LANGDict.cancelForm();
        } else {
          alert('Unfortunately, an error occurred that prevented this record from being saved. '+
                '\n\nPlease refresh the page and try again.');
        }
        LANGDict.Loader.dec();
      }
    });
    return false;
  },
  saveIndex: function(_form) {
    var postData = this.extractValues(_form);
    
    if (postData) {
      if (!postData.word || /^\s*$/.test(postData.word)) {
        alert('Please input the keyword you believe would further enhance the quality of the search operation.');
        return false;
      }
      
      $.ajax({
        url: 'api/index/save',
        type: 'post',
        data: postData,
        dataType: 'json', 
        success: function(msg) {
          if (msg.succeeded && msg.response.identifier) {
            LANGDict.load(msg.response.identifier);
          }
        }
      });
    }
    
    return false;
  },
  saveTranslation: function(_form) {
    var postData = this.extractValues(_form);
    
    if (postData) {
      var error = null;
      if (!postData.translation || /^\s*$/.test(postData.translation)) {
        error = 'Please input suggested glose.';
      }
      
      if (error) {
        alert(error);
        return false;
      }
    
      this.Loader.inc();
    
      $.ajax({
        url: 'api/translation/register',
        type: 'post',
        data: postData,
        dataType: 'json',
        success: function(msg) {
          if (msg && msg.succeeded) {
            LANGDict.load(msg.response.key);
          } else {
            alert(msg ? msg.error : 'An unindentifiable error has occurred. Please try again later.');
          }
          LANGDict.Loader.dec();
        }
      });
    }
    
    return false;
  },
  saveProfile: function(parentForm) {
    if (!parentForm || !parentForm.elements || parentForm.elements.length < 1) {
      return false;
    }
    
    var postData = {};
    for (var i = 0; i < parentForm.elements.length; ++i) {
      var elem = parentForm.elements[i];
      postData[elem.name] = elem.value;
    }
    
    $.ajax({
      url: 'api/profile/edit',
      data: postData,
      type: 'post',
      dataType: 'json',
      success: function(msg) {
        if (msg.succeeded) {
          window.location.reload();
        }
      }
    });
    
    return false;
  },
  removeIndex: function(id) {
    if (confirm('Are you sure you want to mark this keyword for deletion?')) {
      $.ajax({
        url: 'api/index/remove',
        data: { id: id },
        type: 'post',
        dataType: 'json', 
        success: function(msg) {
          if (msg.succeeded && msg.response.id) {
            $('span[rel="keyword-' + msg.response.id + '"]').remove();
          }
        }
      });
    }
    
    return false;
  },
  extractValues: function(_form) {
    var postData = null;
  
    for (var i = 0; i < _form.elements.length; ++i) {
      var item = _form.elements[i];
      
      if (!item.name || item.name.length < 1) {
        continue;
      }
      
      var value = null;
      if (item.selectedIndex !== undefined) {
        value = item.options[item.selectedIndex].value;
      } else {
        value = item.value;
      }
      
      if (value !== null) {
        if (!postData) {
          postData = {};
        }
        postData[item.name] = value;
      }
    }
    
    return postData;
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
        left: 37,
        right: 39,
        up: 38,
        down: 40,
        searchField: 83, // = S
        browseDown: 34, // = Page Down
        browseUp: 33 // = Page Up
      };
            
      var tag = 'a';
      var indexThreshold = 2;
      var target = $(document.activeElement);
      var index = target.attr('tabindex');
      var isSuggestion = target.parent().hasClass('search-suggestion');
      
      if (target.parents('form').attr('id') != 'search-form' && !isSuggestion) {
        return;
      }
      
      if (!isSuggestion) {
        tag = indexThreshold = undefined;
      }
      
      switch (ev.keyCode) {
        case keys.left: 
          index -= 5;
          break;
        case keys.right: 
          index += 5;
          break;
        case keys.up:
          index -= 1;
          break;
        case keys.down:
          index += 1;
          break;
        case keys.browseDown:
          LANGDict.nextResult(1);
          isSuggestion = false;
          break;
        case keys.browseUp:
          LANGDict.nextResult(-1);
          isSuggestion = false;
          break;
        default:
          if (/^[a-zA-Z0-9]$/.test(String.fromCharCode(ev.keyCode))) {
            if (target.offset().top < $(window).scrollTop()) {
              LANGAnim.scroll(0);
            }

            $('#search-query-field').focus();
            isSuggestion = false;
          } else {
            return;
          }
      }
      
      if (isSuggestion) {
        if (!isNaN(index) && (indexThreshold === undefined || index >= indexThreshold)) {
          ev.preventDefault();
          
          if (tag !== undefined) {
            var tagObj = $(tag + '[tabindex=' + index + ']');
            tagObj.focus();
          }
        }
      }
    });
    
    var lastValue = LANGCookies.read('filter-field-value');
    if (!lastValue || isNaN(lastValue)) {
      lastValue = 0;
    }    
    
    $('#search-filter-field').change(searchFilterChanged).val(lastValue);
  };
  
  var languageFilterBox = null;
  var getFilter = function() {
    if (!languageFilterBox) {
      languageFilterBox = $('#search-filter-field');
    }
    
    return languageFilterBox.val();
  };
  
  var searchFilterChanged = function() {
    LANGCookies.create('filter-field-value', $(this).val());
    performSearch({ term: $('.word').val() });
  };
  
  var performSearch = function(request, response) {
    if (/^[\s\t\/\\]*$/.test(request['term'])) {
      return;
    }
  
    request['language-filter'] = getFilter();
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

  return {
    init: function() {
      hook();
      $('.word').autocomplete({
        minLength: 1,
        source: performSearch
      }).focus();
    },
    set: function(data) {
      var cols = [];
      var mobile = /iphone|android|windows\sphone/i.test(navigator.appVersion);
      var columns = mobile ? 3 : 5;
      
      for (var i = 0; i < data.words.length; i += columns) {
        var rows = [];
        for (var j = i; j < i + columns; ++j) {
          if (data.words[j] === undefined) {
            break;
          }
          rows.push('<div class="search-suggestion"><a href="#' + encodeURIComponent(data.words[j]) + '" tabindex="' + (j + 2) + '">' + data.words[j] + '</a></div>');
        }
        
        cols.push(rows.join(''));
      }
      
      var $result = $('#search-result');
      
      $result.html('<table id="result-table"><tbody><tr><td>' + cols.join('</td><td>') + '</td></tr></tbody></table>');
      
      if (!mobile) {
        var blocks = $('#search-description').show().find('span');
        
        if (blocks.length >= 3) {
          $(blocks[0]).html(data.words.length);
          $(blocks[1]).html(data.matches);
          $(blocks[2]).html(Math.round(data.time * 100) / 100);
        }
      }
      
      // this is necessary for IE, lest it's height will fail miserabily
      $result.css('height', $('#result-table').outerHeight() + 'px');
      
      // Apply the scroll view
      if (/ipad|iphone/i.test(navigator.appVersion)) {
        new iScroll($result.attr('id'));
      }
      
      $result.css('overflow-x', 'scroll');
    }
  };
}();

var LANGAnim = function() {
  return {
    scroll: function(offset) {
      $('html, body').animate({ scrollTop: offset }, 500);
    }
  };
}();

$(function() {
  LANGDict.init();
  LANGSearch.init();
});