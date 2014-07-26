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
