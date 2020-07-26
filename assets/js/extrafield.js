(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    // Disables both top and left panels of the editing form.
    $('#layout-mainmenu').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
    $('#layout-sidenav').prepend('<div class="disable-panel">&nbsp;</div>');
    $('.control-toolbar').attr('style', 'table-layout: auto !important');

    $('#Form-field-ExtraField-type').change( function() { $.fn.setFieldType($('#Form-field-ExtraField-type').val()); });
    $.fn.setFieldType($('#Form-field-ExtraField-type').val());
  });

  $.fn.setFieldType = function(type) {
    if (type == 'list' || type == 'checkbox' || type == 'radio') {
      $('#multi_value').css({'visibility':'visible','display':'block'});
    }
    else {
      $('#multi_value').css({'visibility':'hidden','display':'none'});
    }
  }

})(jQuery);
