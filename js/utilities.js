define(['require', 'exports'], function (require, exports) {
  'use strict';
  
  var CAssert = {};

  CAssert.string = function () {
    assertInternal(arguments, 'string');
  }

  CAssert.number = function () {
    assertInternal(arguments, 'number');
  }

  CAssert.boolean = function () {
    assertInternal(arguments, 'boolean');
  }

  CAssert.array = function () {
    assertInternal(arguments, 'array');
  }
  
  CAssert.event = function () {
    assertInternal(arguments, function (param) {
      return param instanceof jQuery.Event;
    });
  }
  
  CAssert.jQuery = function () {
    assertInternal(arguments, function (param) {
      return param instanceof jQuery;
    });
  }

  CAssert.element = function () {
    assertInternal(arguments, function (param) {
      return param && param instanceof Element;
    });
  }

  function assertInternal(params, typeOrFunction) {
    var i = 0, actualType;
    
    // The typeOrCallback parameter is internal, so rigorous testing isn't
    // necessary.
    var isFunction = typeof typeOrFunction === 'function'; 
    
    for (; i < params.length; i += 1) {
      // Perform rigorous type testing on the param parameter
      actualType = $.type(params[i]);
      
      if ((isFunction && !typeOrFunction(params[i])) || 
          (!isFunction && actualType !== typeOrFunction)) {
        throw 'Parameter "' + params[i] + '" is a ' + actualType + '. Expecting ' + 
          (isFunction ? typeOrFunction.toString() : typeOrFunction) + '.';
      }
    }
  }
  
  exports.CAssert = CAssert;
  
  /**
   * Creates a new loading indicator, which uses CSS3 to perform the animation.
   *
   * @class CLoadingIndicator
   * @constructor
   * @param {Element} element
   */
  var CLoadingIndicator = function (element) {
    CAssert.element(element);
  
    this.element      = element;
    this.isLoading    = false;
    this.loadingCount = 0;
  }
  
  /**
   * Retrieves a shared loader for the specified element.
   *
   * @public
   * @static
   * @method shared
   * @param {string} elementId
   */
  CLoadingIndicator.shared = function (elementId) {
    if (!this.sharedRefs) {
      this.sharedRefs = {};
    }
    
    if (this.sharedRefs[elementId]) {
      return this.sharedRefs[elementId];
    }
    
    return (this.sharedRefs[elementId] = new CLoadingIndicator(document.getElementById(elementId)));
  }
  

  /**
   * Instructs the loader that a loading operation has begun.
   *
   * @public
   * @method loading
   */
  CLoadingIndicator.prototype.loading = function () {
    this.loadingCount += 1;
    this.trigger();
  }
  

  /**
   * Instructs the loader that a loading operation has just completed.
   *
   * @public
   * @method loaded
   */
  CLoadingIndicator.prototype.loaded = function () {
    this.loadingCount = Math.max(this.loadingCount - 1, 0);
    this.trigger();
  }
  
  /**
   * Triggers the loading animation based on the state of the loader.
   *
   * @private
   * @method trigger
   */
  CLoadingIndicator.prototype.trigger = function () {
    if (this.loadingCount < 1) {
      if (this.isLoading) {
        this.element.className = this.originalClassName;
        this.isLoading = false; 
      }
    } else {
     if (!this.isLoading) {
      this.originalClassName = this.element.className;
      this.element.className = this.element.getAttribute('data-loading-class') || 'loading';
      this.isLoading = true;
     } 
    }
  }
  
  exports.CLoadingIndicator = CLoadingIndicator;
  
  var CFormSucker = function (formElement, prefix) {
    this.formElement = formElement;
    this.prefix      = prefix || null;
  }
  
  /**
   * Retrieves ("sucks") the information out from all child elements.
   *
   * @public
   * @method suck
   */
  CFormSucker.prototype.suck = function () {
    var elements = this.formElement.querySelectorAll('input,textarea,select');
    var data = {};
    var element;
    var value;
    var name;
    
    for (var i = 0; i < elements.length; i += 1) {
      element = elements[i];
      name    = element.id || element.name || 0;
      
      if (! name) {
        continue;
      }

      // Checkboxes and radio boxes must be checked
      if (/^radio|checkbox$/i.test(element.type) && true !== element.checked) {
        continue;
      }

      // If a prefix is defined, remove it from the name of the element.
      if (this.prefix && name.length > this.prefix.length && 
          name.substr(0, this.prefix.length) === this.prefix) {
        name = name.substr(this.prefix.length);
      }
      
      // Retrieve the value for the selected option, or retrieve the element's
      // value, if it's not a select element. 
      value = element.options !== undefined ? 
        element.options[element.selectedIndex].value : element.value;
      
      if (value.length && ! isNaN(value) && isFinite(value)) {
        value = parseInt(value);
      } 
      
      data[name] = value;
    }
    
    return data;
  }
  
  exports.CFormSucker = CFormSucker;
  
  /**
   * Checks whether the element is within the viewport. 
   * Inspired by Dan @ StackOverflow (http://stackoverflow.com/questions/123999)
   *
   * @method isElementInViewport
   * @param {String} data HTML element or JQuery element.
   */
  exports.isElementInViewport = function (el) {
    if (el instanceof jQuery) {
        el = el[0];
    }

    var rect = el.getBoundingClientRect(),
        height = $(window).height() * 0.7, 
        width = $(window).width();
    
    if (rect.top >= height) {
      return false;
    }
    
    if (rect.top < -$(el).height()) {
      return false;
    }
    
    return true;
  }
  
  if (String.prototype.hashCode === undefined) {
    String.prototype.hashCode = function() {
      var n = 0, t = 0;
      for (; t < this.length; t += 1) {
        n = (n << 5) - n + this.charCodeAt(t);
        n = n & n;
      }
      
      return n;
    };
  }
});
