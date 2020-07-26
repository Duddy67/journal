(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    // Disables both top and left panels of the editing form.
    $('#layout-mainmenu').prepend('<div class="disable-panel top-panel">&nbsp;</div>');
    $('#layout-sidenav').prepend('<div class="disable-panel">&nbsp;</div>');
    $('.control-toolbar').attr('style', 'table-layout: auto !important');

    $('#Form-field-Article-category').change( function() { $.fn.setMainCategory(); });

    $.fn.setMainCategory();

    // Triggered before the request is formed.
    $(document).on('ajaxSetup', function(event, context, data) {
      // Enables the checkbox to get its value taken into account when saving.
      $('input:checkbox.main-category').prop('disabled', false);
    });

    // Triggered finally if the AJAX request was successful.
    $(document).on('ajaxDone', function() {
      // Disables the checkbox again.
      $('input:checkbox.main-category').prop('disabled', true);
    });
  });

  $(document).ready(function() {
      // Shows the active tab on page reload.
      $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
	  localStorage.setItem('activeTab', $(e.target).attr('href'));
      });

      var activeTab = localStorage.getItem('activeTab');

      if(activeTab){
	  $('.nav-tabs a[href="' + activeTab + '"]').tab('show');
      }
  });

  $.fn.setMainCategory = function() {
    let mainCategoryId = $('#Form-field-Article-category').val();
    // Loops through the checkbox inputs.
    $('.custom-checkbox').children('input').each(function(i, input) {
      if($(input).val() == mainCategoryId) {
	// Forces the main category to be checked.
	$(input).prop('checked', true);
	$(input).prop('disabled', true);
	$(input).addClass('main-category');
      }
      // Checks for the main category previously selected (if any).
      else if($(input).hasClass('main-category')) {
	// Enables then unchecks the checkbox.
	$(input).prop('disabled', false);
	$(input).prop('checked', false);
	$(input).removeClass('main-category');
      }
    });
  }

})(jQuery);
