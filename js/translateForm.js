define(['exports', 'utilities', 'widgets/editableInlineElement'], function (exports, util, CEditableInlineElement) {
  
  var CTranslateForm = function () {
    this.parentElement = null;
    this.listElement   = null;
    this.dataSource    = null;
    this.editElement   = null;
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
    
    this.parentElement = element;
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
  }

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
  }
  
  CTranslateForm.prototype.getTags = function () {
    var tags = [];
    
    try {
      tags = JSON.parse( this.dataSource.val() );
    } catch (ex) {
    }
    
    return tags;
  }
  
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
  }
  
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
      _this.removeTag($(this).parent('li').index());
    });
  }
  
  return new CTranslateForm();
});
