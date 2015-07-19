define(['exports', 'utilities', 'widgets/editableInlineElement'], function (exports, util, CEditableInlineElement) {
  
  var CProfileManager = function () {
    this.parentElement = null;
  };
  
  /**
   * Assigns the specified element as the root element. The element will
   * be used in the load method.
   *
   * @method setElement
   * @param {jQuery} element  Wrapped parent element.
   */
  CProfileManager.prototype.setElement = function (element)  {
    util.CAssert.jQuery(element);
    
    this.parentElement = element;
  }

  /**
   * Select, initializes and runs the profile manager for this context.
   *
   * @method load
   */
  CProfileManager.prototype.load = function () {
    var id = this.parentElement.attr('id');
    var manager = null;
    
    switch (id) {
      case 'profile-completion':
        manager = new CProfileCompletionManager(this);
        break;
      case 'profile-page':
        manager = new CProfileDetailsManager(this);
        break;
    }
    
    if (manager) {
      manager.load();
    }
  }
  
  var CProfileCompletionManager = function (rootManager) {
    this.rootManager = rootManager;
  }
  
  CProfileCompletionManager.prototype.load = function () {
    var _this = this;
    this.rootManager.parentElement.on('submit', function(ev) {
      ev.preventDefault();
      _this.attemptSubmit();
    });
  }
  
  CProfileCompletionManager.prototype.attemptSubmit = function () {
    // hide all error messages which were displayed previously.
    var errors = [];
    this.errors(errors);
    
    // Retrieve the preferred nickname, and validate the input.
    var nick = this.rootManager.parentElement.find('#preferred-nickname').val();
    
    if (/^[\s]*$/.test(nick)) {
      errors.push('#profile-error-too-short');
    }
    
    if (nick.replace(/[^a-zA-Z]/g, '').length < 3) {
      errors.push('#profile-error-weird-letters');
    }
    
    if (this.errors(errors)) {
      return;
    }
    
    $.ajax({
      url: '/api/profile/complete',
      data: { nickname: nick }
    }).done(function (data) {
      if (data.succeeded) {
        window.location.href = '/profile.page?message=auth-new';
        return;
      }
      
      alert(data.error);
    });
  }
  
  CProfileCompletionManager.prototype.errors = function (errors) {
    var noErrors = !$.isArray(errors) || errors.length < 1;
    
    if (noErrors) {
      this.rootManager.parentElement.find('.error').hide();
    } else {
      for (var i = 0; i < errors.length; i += 1) {
        this.rootManager.parentElement.find(errors[i]).show();
      }
    }
    
    return ! noErrors;
  }
  
  var CProfileDetailsManager = function (rootManager) {
    this.rootManager = rootManager;
  }

  CProfileDetailsManager.prototype.load = function () {
    var _this = this;
    
    this.rootManager.parentElement.find('.editable').each(function () {
      var element = $(this);
      
      element.addClass('active');
      CEditableInlineElement.install(element, function (value) { 
        _this.saveChanges(this.getAttribute('data-editing-propety'), value);
      });
    });
  }
  
  CProfileDetailsManager.prototype.saveChanges = function(property, value) {
    var data = {};
    data[property] = value;
    
    $.ajax({
      url: '/api/profile/edit',
      data: data
    }).done(function (data) {
      window.location.reload();
    }).fail(function () {
      console.log('CProfileDetailsManager: failed to save ' + property + '.');
    });
  }

  return new CProfileManager();
});
