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

  function assertInternal(params, typeOrCallback) {
    var i = 0, actualType;
    
    // The typeOrCallback parameter is internal, so rigorous testing isn't
    // necessary.
    var isFunction = typeof typeOrCallback === 'function'; 
    
    for (; i < params.length; i += 1) {
      // Perform rigorous type testing on the param parameter
      actualType = $.type(params[i]);
      
      if ((isFunction && !typeOrCallback(params[i])) || 
          (!isFunction && actualType !== typeOrCallback)) {
        throw 'Parameter "' + params[i] + '" is a ' + actualType + '. Expecting ' + 
          typeOrCallback + '.';
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
