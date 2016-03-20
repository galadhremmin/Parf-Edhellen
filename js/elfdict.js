requirejs.config({
  baseUrl: '/js'
});

require(['require', 'navigation'], function(require, nav) {
  'use strict';

  function loadModules(parentElement) {
    // Look for other modules. These are defined by the data-module attribute on
    // HTML elements. Load all modules, and invoke the load method on them.
    var modules = parentElement.find('[data-module]');
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
  }
  
  var navigator = new nav.CNavigator('result');
  navigator.onNavigated = function() {
    // load modules on navigation
    loadModules($('#result'));
  }
  navigator.listen();
  
  var searchNavigator = new nav.CSearchNavigator('search-query-field', 'search-result', 'search-reverse-box', 'search-language-select');
  searchNavigator.listen();

  $('#push-down-link').on('click', function (ev) {
    ev.preventDefault();
    $('#site-container').toggleClass('pushed-down');
  });
  
  // Initialize all existing modules
  loadModules($('body'));
});
