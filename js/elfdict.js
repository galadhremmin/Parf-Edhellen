requirejs.config({
  baseUrl: '/js'
});

require(['require', 'navigation'], function(require, nav) {
  'use strict';
    
  var navigator = new nav.CNavigator('result');
  navigator.listen();
  
  var searchNavigator = new nav.CSearchNavigator('search-query-field', 'search-result', 'search-reverse-box', 'search-language-select');
  searchNavigator.listen();
  
});
