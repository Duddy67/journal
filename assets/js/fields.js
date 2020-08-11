(function($) {

  var required = [];

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    $('#Form-field-Article-field_group').change( function() { $.fn.setFields($('#Form-field-Article-field_group').val()); });
    $.fn.setFields($('#Form-field-Article-field_group').val());
  });

  $.fn.setFields = function(groupId) {

    $('#field').empty();

    if(groupId == '') {
      //alert('group id '+id);
      return;
    }

    required = [];

    // Sets the validating function.
    $('[id^="on-save"]').click( function(e) { validateFields(e); });

    // The input element containing the root location.
    let rootLocation = $('#root-location').val();

    let articleId = $('#Form-field-Article-id').val();
    let token = $('input[name="_token"]').val();

    // Prepares then run the Ajax query.
    const ajax = new Codalia.Ajax();
    let url = rootLocation+'/backend/codalia/journal/articles/json/'+articleId+'/'+groupId+'/'+token;
//alert(url);
    let params = {'method':'GET', 'url':url, 'dataType':'json', 'async':true};
    ajax.prepare(params);
    ajax.process(getAjaxResult);
  }

  validateFields = function(e) {
    for(let i = 0; i < required.length; i++) {
      Codalia.checkRequiredField(required[i]);
      //alert(required[i].code);
    }
  }

  /** Callback functions **/

  getAjaxResult = function(result) {
    if(result.success === true) {
      $.each(result.data, function(i, field) {
	  elem = new Codalia.Field(field);
	  elem[field.type]();

	  if(field.required) {
	    required.push(field);
	  }
      });
    }
    else {
      alert('Error: '+result.message);
    }
  }

})(jQuery);
