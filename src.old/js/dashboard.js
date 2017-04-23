define(['exports', 'utilities'], function (exports, util) {
  
  var CDashboardManager = function () {
    this.parentElement = null;
  };
  
  /**
   * Assigns the specified element as the root element. The element will
   * be used in the load method.
   *
   * @method setElement
   * @param {jQuery} element  Wrapped parent element.
   */
  CDashboardManager.prototype.setElement = function (element)  {
    util.CAssert.jQuery(element);
    
    this.parentElement = element;
  };

  /**
   * Select, initializes and runs the profile manager for this context.
   *
   * @method load
   */
  CDashboardManager.prototype.load = function () {
    var _this = this;
    this.parentElement.find('a.favourite-delete').on('click', function (ev) {
      ev.preventDefault();
      _this.deleteFavourite(this);
    });
  };
  
  CDashboardManager.prototype.deleteFavourite = function (element) {
    var id = parseInt( element.getAttribute('data-favourite-id') );
    if (!id) {
      return;
    }
    
    $.ajax({
      url: '/api/profile/favourite',
      data: { translationID: id, add: false }
    }).done(function (data) {
      // remove the row containing the item
      var row = document.getElementById('favourite-' + id);
      if (row) {
        row.parentNode.removeChild(row);
      }
    });
  };
  
  return new CDashboardManager();
});
