define(['exports'], function (exports) {
  
  var CEditableInlineElement = function (rootElement, callback) {
    this.originalElement = rootElement[0];
    this.type = this.originalElement.getAttribute('data-editing-type') || 'text';
    this.editElement = null;
    this.callback = callback || null;
  };
  
  CEditableInlineElement.install = function (element, callback) {
    var editableElement = new CEditableInlineElement($(element), callback);
    editableElement.activate();
  }
  
  CEditableInlineElement.prototype.activate = function () {      
    var _this = this;
    $(this.originalElement).on('click', function () {
      _this.beginEditing();
    });
  }
  
  CEditableInlineElement.prototype.beginEditing = function () {
    var editor = this.makeEditor();
    
    this.originalElement.style.display = 'none';
    this.originalElement.parentElement.insertBefore(editor, this.originalElement);
    this.editElement = editor;
    
    this.editing();
  }
  
  CEditableInlineElement.prototype.editing = function () {
    var _this = this;
    
    this.editElement.focus();
    
    $(this.editElement).on('blur', function () {
      _this.endEditing();
    });
  }
  
  CEditableInlineElement.prototype.endEditing = function () {
    var value = this.getValue();
    
    this.editElement.parentElement.removeChild(this.editElement);
    this.editElement = null;
    
    this.setValue(value);
    this.originalElement.style.display = '';
  }
  
  CEditableInlineElement.prototype.makeEditor = function () {
    var editElement = null;
    switch (this.type) {
      case 'text':
      case 'number':
        editElement = document.createElement('input');
        editElement.type = this.type;
        
        break;
      case 'textarea':
        editElement = document.createElement('textarea');
        break;
    }
    
    editElement.value = this.getValue();
    editElement.className = 'editing';
    
    var additionalClassNames = this.originalElement.getAttribute('data-editing-class');
    if (! /^\s*$/.test(additionalClassNames)) {
      editElement.className += ' ' + additionalClassNames;
    }
    
    return editElement;
  }
  
  CEditableInlineElement.prototype.getValue = function () {
    if (this.editElement) {
      return this.editElement.value;
    } else {
      return this.originalElement.getAttribute('data-editing-value') || $(this.originalElement).text() || '';
    }
  }
  
  CEditableInlineElement.prototype.setValue = function (value) {
    if (this.editElement) {
      this.editElement.value = value;
    } else {
      this.originalElement.setAttribute('data-editing-value', value);
      $(this.originalElement).text(value);
      
      if (typeof this.callback === 'function') {
        this.callback.call(this.originalElement, value);
      }
    }
  }
  
  return CEditableInlineElement;
  
});
