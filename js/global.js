var LANGDict = {
  messages: {
    'q-wordID': 'This is entry order, according to the function and the sense of the word (word.1, word.2, word.3 etc.).\n\nUsually you can leave this to its default value, but when a word might have multiple meanings such as Sindarin _a_, order the word with dots: a.1, a.2 etc.'
  },
  Loader: {
    inst: 0,
    inc: function() {
      ++this.inst;
      
      var targ = $('#loading');
      if (targ.is(':hidden') && this.inst > 0) {
        targ.show(1000);
      }
    },
    dec: function() {
      --this.inst;
      
      if (this.inst < 1) {
        this.inst = 0;
        $('#loading').hide(1000);
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
    
    this.hashChanged();
  },
  contentLoaded: function() {
    // invoke upon hash change
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
    }
  },
  load: function(item) {
    $('#result').load(
      'translate.php?term=' + encodeURIComponent(item),
      null,
      function(responseText, textStatus, XMLHttpRequest) {
        LANGDict.contentLoaded();
      }
    );
  },
  showForm: function(id) {
    var values = {};
    if (id) {
      // load values here
    }
    
    $('#extend-form').slideToggle(1000, function() {
      var word = new String(window.location.hash);
      if (word.length > 0) {
        word = decodeURIComponent(word.substr(1));
      }
      $('#word-input').val(word).keyup().focus();
    }); // 1 second
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
          $('#extend-form').slideUp(1000);
        } else {
          alert('Unfortunately, an error occurred that prevented this record from being saved. '+
                '\n\nPlease refresh the page and try again.');
        }
        LANGDict.Loader.dec();
      }
    });
    return false;
  },
  hideTranslationForm: function() {
    $('#translation-form').slideUp();
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
      this.Loader.inc();
      
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
          LANGDict.Loader.dec();
        }
      });
      
      return false; /* this method will be invoked again once data has been acquired */
    }
    
    var targ = $('#translation-form');
    if (targ.is(':hidden')) {
      targ.slideDown();
    }
    
    $('#translation-form span[rel=function]').html(preserveForm ? 'Edit' : 'Add');
    return false;
  },
  saveTranslation: function(_form) {
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
  }
};

$(function() {
  LANGDict.init();
  $('.word').autocomplete({
    source: function(request, response) {
      $.ajax({
        url: 'api/word/search',
        data: request,
        dataType: 'json',
        type: 'post',
        success: function(data) {
          response(data.response);
        }
      });
    },
    minLength: 1,
    select: function(e, sender) {
      LANGDict.submit(sender.item.value);
    }
  }).focus();
});