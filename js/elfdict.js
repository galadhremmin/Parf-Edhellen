requirejs.config({
  baseUrl: '/js'
});

require(['require', 'navigation'], function(require, nav) {
  'use strict';
    
  var navigator = new nav.CNavigator('result');
  navigator.listen();
  
  var searchNavigator = new nav.CSearchNavigator('search-query-field', 'search-result', 'search-reverse-box', 'search-language-select');
  searchNavigator.listen();
  
  // Look for other modules. These are defined by the data-module attribute on
  // HTML elements. Load all modules, and invoke the load method on them.
  var modules = $('[data-module]');
  if (modules.length) {
    var moduleNames = [];
    var parentElements =[];
    
    modules.each(function () {
      moduleNames.push($(this).data('module'));
      parentElements.push($(this));
    });
    
    require(moduleNames, function () {
      for (var i = 0; i < arguments.length; i += 1) {
        arguments[i].setElement(parentElements[i]);
        arguments[i].load();
      }
    });
  }
});
