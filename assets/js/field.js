(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    // Disables both top and left panels of the editing form.
    $('#layout-mainmenu').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
    $('#layout-sidenav').prepend('<div class="disable-panel">&nbsp;</div>');
    $('.control-toolbar').attr('style', 'table-layout: auto !important');

    $('#Form-field-Field-type').change( function() { $.fn.setFieldType($('#Form-field-Field-type').val()); });
    $.fn.setFieldType($('#Form-field-Field-type').val());

    if ($('#Form-field-Field-id').val() != undefined) {
      $.fn.setInitialGroups();
      $('[id^="checkbox_Form-field-Field-groups_"]').click( function() { $.fn.checkUnselectedGroups(this); });
    }
  });

  $.fn.setFieldType = function(type) {
    if (type == 'list' || type == 'checkbox' || type == 'radio') {
      $('#option').css({'visibility':'visible','display':'block'});
    }
    else {
      $('#option').css({'visibility':'hidden','display':'none'});
    }
  }

  // Stores the initial group ids into a json array.
  $.fn.setInitialGroups = function() {
    var groups = [];

    $('[id^="checkbox_Form-field-Field-groups_"]').each(function() {
      if($(this).is(':checked')) {
	groups.push($(this).val());
      }
    });

    $('#initial-groups').val(JSON.stringify(groups));
  }

  $.fn.checkUnselectedGroups = function(checkbox) {
    let groups = $('#initial-groups').val(); 
    groups = JSON.parse(groups);

    if(groups.indexOf(checkbox.value) != -1 && checkbox.checked == false) {
      alert('check '+checkbox.checked);
    }
  }
})(jQuery);
