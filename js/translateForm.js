define(['exports', 'utilities', 'widgets/editableInlineElement'], function (exports, util, CEditableInlineElement) {
  
  var CTranslateForm = function () {
    this.parentElement = null;
    this.listElement   = null;
    this.dataSource    = null;
    this.editElement   = null;
    this.loading       = false;
  };
  
  /**
   * Assigns the specified element as the root element. The element will
   * be used in the load method.
   *
   * @method setElement
   * @param {jQuery} element  Wrapped parent element.
   */
  CTranslateForm.prototype.setElement = function (element)  {
    util.CAssert.jQuery(element);
    
    this.parentElement = element.get(0);
    this.listElement   = element.find('#ed-translate-indexes-rendered');
    this.dataSource    = element.find('#ed-translate-indexes');
    this.editElement   = element.find('#ed-translate-index');
    
    var _this = this;
    element.find('#ed-translate-index-add').on('click', function (ev) {
      ev.preventDefault();
      // commit the tag to the collection
      _this.addTag( _this.editElement.val() );
      
      // empty current value and restore focus
      _this.editElement.val('');
      _this.editElement.focus();
    });
    
    element.find('#ed-translate-submit').on('click', function (ev) {
      ev.preventDefault();

      switch ($(this).data('type')) {
        case 'translation':
          _this.saveTranslation();
          break;
        case 'review':
          _this.saveReview(true);
          break;
        case 'review-update':
          _this.saveReview();
          break;
      }
    });

    element.find('#ed-translate-reject').on('click', function (ev) {
      ev.preventDefault();
      var justification = '';
      while (/^\s*$/.test(justification)) {
        justification = prompt('Please justify your decision to reject this contribution:', '');

        if (!justification) {
          return;
        }
      }

      if (confirm('You reject this contribution because:\n'+justification+'\n\nIs this OK?')) {
        _this.saveReview(false, justification);
      }
    });

    element.find('#ed-translate-delete').on('click', function (ev) {
      ev.preventDefault();
      _this.deleteReview();
    });
    
    element.find('.btn-cancel').on('click', function (ev) {
      window.location.href = '/dashboard.page';
    });
    
    this.editElement.on('keydown', function (ev) {
      // enter key?
      if (ev.which === 13) {
        ev.preventDefault();
        // commit the tag to the collection
        _this.addTag( _this.editElement.val() );
        
        // empty current value
        _this.editElement.val('');
      }
    });
  };

  /**
   * Select, initializes and runs the form for this context.
   *
   * @method load
   */
  CTranslateForm.prototype.load = function () {
    if (this.listElement.length < 1) {
      return;
    }
    
    this.renderTags();
  };

  CTranslateForm.prototype.getAndValidateData = function () {
    // Retrieve all information from the form.
    var sucker = new util.CFormSucker(this.parentElement, 'ed-translate-');
    var data = sucker.suck();

    // Remove unused properties
    delete data.index;
    delete data.submit;

    if (data.reject !== undefined) {
      delete data.reject;
    }

    // Perform validation on the data input.
    var errors = [];

    if (! data.language) {
      errors.push('language');
    }

    if (! data.word || data.word.length < 1) {
      errors.push('word');
    }

    if (! data.translation || data.translation.length < 1) {
      errors.push('translation');
    }

    if (! data.source || data.source.length < 3) {
      errors.push('source');
    }

    for (var key in data) {
      var group = $('#ed-translate-' + key).parents('.form-group');

      if ($.inArray(key, errors) > -1) {
        // Display error messages next to the fields.
        group.addClass('has-error');
      } else {
        // Remove possible errors
        group.removeClass('has-error');
      }
    }

    if (errors.length) {
      // Display the error alert, and scroll to it.
      var y, alert = $('#ed-translate-error-alert');
      alert.removeClass('hidden');

      y = alert.offset().top - alert.outerHeight(true);
      window.scrollTo(0, y);

      return null;
    }

    // Make sure to pass zero to the service
    if (! data.id) {
      data.id = 0;
    }

    if (! data.senseID) {
      data.senseID = 0;
    }

    return data;
  };
  
  CTranslateForm.prototype.saveTranslation = function () {
    // Kill repetitive button clicks..
    if (this.loading) {
      return;
    }

    // Retrieve and validate the user input
    var data = this.getAndValidateData();
    if (!data) {
      return;
    }

    var _this = this;
    _this.loading = true;

    // Pass the values to the web service for persistance.
    $.ajax({
      url: '/api/translation/save',
      data: data,
      method: 'post'
    }).done(function (data) {
      if (! data.succeeded) {
        _this.loading = false;
        return;
      }
      
      window.location.href = '/dashboard.page?highlight=translation-' + data.response.id + '&message=review-created';
    }).fail(function () {
      console.log('CTranslateForm: failed to save ' + JSON.stringify(data) + '.');
      _this.loading = false;
    });
  };

  CTranslateForm.prototype.saveReview = function (approved, justification) {
    if (this.loading) {
      return;
    }

    // Retrieve and validate the user input
    var data = this.getAndValidateData();
    if (!data) {
      return;
    }

    var approval = (approved === true || approved === false);
    if (approval) {

      if (! approved && (!justification || /^\s*$/.test(justification))) {
        throw 'Please justify your rejection';
      }

      // approve of reject the review item
      data.reviewApproved = approved ? 'true' : 'false';
      data.reviewJustification = justification;
    } else {
      // just update the information of the review item
      data.reviewUpdate = true;
    }

    var _this = this;
    _this.loading = true;

    // Pass the values to the web service for persistance.
    $.ajax({
      url: '/api/translation/saveReview',
      data: data,
      method: 'post'
    }).done(function (data) {
      if (! data.succeeded) {
        _this.loading = false;
        return;
      }

      if (approval) {
        window.location.href = '/dashboard.page?message=review-' + (approved ? 'approved' : 'rejected');
      } else {
        window.location.href = '/dashboard.page?message=review-updated';
      }
    }).fail(function () {
      console.log('CTranslateForm: failed to save review ' + JSON.stringify(data) + '.');
      _this.loading = false;
    });
  };

  CTranslateForm.prototype.deleteReview = function () {
    var data = this.getAndValidateData();

    if (isNaN(data.reviewID)) {
      console.error('Review ID doesn\'t exist?!');
      return;
    }

    if (!confirm('Would you really like to delete review item ' + data.reviewID + '?\n\nThis action is irreversible.')) {
      return;
    }

    $.ajax({
      url: '/api/translation/deleteReview',
      data: { reviewID: data.reviewID },
      method: 'post'
    }).done(function () {
      window.location.href = '/dashboard.page?message=review-deleted';
    });
  }
  
  CTranslateForm.prototype.getTags = function () {
    var tags = [];
    
    try {
      tags = JSON.parse( this.dataSource.val() );
    } catch (ex) {
      tags = [];
    }
    
    return tags;
  };
  
  CTranslateForm.prototype.addTag = function (tag) {
    util.CAssert.string(tag);
    
    // always register tags as lower case
    tag = tag.toLocaleLowerCase();
    
    // ensure that it's not just whitespace
    if (/^\s*$/.test(tag)) {
      return;
    }
    
    // ensure uniqueness
    var tags = this.getTags();
    for (var i = 0; i < tags.length; i += 1) {
      if (tags[i] === tag) {
        return;
      }
    }
    
    // add the tag to the collection and save the collection anew
    tags.push(tag);
    tags.sort();
    
    this.dataSource.val( JSON.stringify(tags) );
    
    // re-render the list of tags available
    this.renderTags();
  };
  
  CTranslateForm.prototype.removeTag = function (index) {
    util.CAssert.number(index);
    
    var tags = this.getTags();
    
    if (tags.length <= index || index < 0) {
      return;
    }
    
    // remove the element
    tags.splice(index, 1);
    
    // commit the changes 
    this.dataSource.val( JSON.stringify(tags) );
    this.renderTags();
  }
  
  CTranslateForm.prototype.renderTags = function () {
    var tags = this.getTags();
    var html = [];
    
    for (var i = 0; i < tags.length; i += 1) {
      var tag = tags[i];
      
      html.push('<li class="list-group-item">');
      html.push(tag);
      html.push(' <button type="button" class="btn btn-default btn-xs" style="float:right"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>');
      html.push('</li>');
    }
    
    this.listElement.html(html.join(''));
    
    var _this = this;
    this.listElement.find('button').on('click', function () {
      _this.removeTag( $(this).parent('li').index() );
    });
  };
  
  return new CTranslateForm();
});
