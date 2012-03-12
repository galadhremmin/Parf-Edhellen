var TengwarImage = {
  generate: function(text) {
    var code = Base64.encode(text);
    var url = 'http://elfdict.com/api/drawtengwar/render?code=' + encodeURIComponent(code);
    
    jQuery('#tengwar-result').html(
      '<a href="' + url + '" target="_blank"><img src="' + url + '" alt="Message in tengwar" border="0" /></a>'
    );
    
    return false;
  }
};