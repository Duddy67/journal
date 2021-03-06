(function($) {
  // A global variable to store then access the dynamical item objects.
  const GETTER = {};

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    // The input element containing the root location.
    let rootLocation = $('#root-location').val();
    // Sets the dynamic item properties.
    let props = {'vendor':'codalia', 'plugin':'journal', 'item':'option', 'ordering':true, 'rootLocation':rootLocation, 'rowsCells':[4], 'Select2':true};

    // Stores the newly created object.
    GETTER.option = new Codalia.DynamicItem(props);
    // Sets the validating function.
    $('[id^="on-save"]').click( function(e) { validateFields(e); });

    let fieldId = $('#Form-field-Field-id').val();
    let token = $('input[name="_token"]').val();

    // Prepares then run the Ajax query.
    const ajax = new Codalia.Ajax();
    let url = rootLocation+'/backend/codalia/journal/fields/json/'+fieldId+'/'+token;
    let params = {'method':'GET', 'url':url, 'dataType':'json', 'async':true};
    ajax.prepare(params);
    ajax.process(getAjaxResult);
  });

  validateFields = function(e) {
    let type = $('#Form-field-Field-type').val();

    if(type != 'list' && type != 'checkbox' && type != 'radio') {
      return true;
    }

    let count = $('#option-container').find('[id^="option-item-"]').length;
    let fields = {'value':'snake_case', 'text':''};

    if(!GETTER.option.validateFields(fields) || count == 0) {
      // Shows the dynamic item tab.
      //$('.nav-tabs a[href="#secondarytab-4"]').tab('show');
      if(count == 0) {
	alert(CodaliaLang.message.alert_missing_value);
      }

      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  }

  /** Callback functions **/

  getAjaxResult = function(result) {
    if(result.success === true) {
      $.each(result.data, function(i, item) { GETTER.option.createItem(item); });
    }
    else {
      alert('Error: '+result.message);
    }
  }

  populateOptionItem = function(idNb, data) {
    // Defines the default field values.
    if(data === undefined) {
      data = {'id':'', 'value':'', 'text':''};
    }

    // Element label.
    let attribs = {'title':CodaliaLang.option.value_desc, 'class':'item-label', 'id':'option-value-label-'+idNb};
    $('#option-row-1-cell-1-'+idNb).append(GETTER.option.createElement('span', attribs));
    $('#option-value-label-'+idNb).text(CodaliaLang.option.value_label);

    // Text input tag:
    attribs = {'type':'text', 'name':'option_value_'+idNb, 'id':'option-value-'+idNb, 'class':'form-control', 'value':data.value};
    $('#option-row-1-cell-1-'+idNb).append(GETTER.option.createElement('input', attribs));

    // Element label.
    attribs = {'title':CodaliaLang.option.text_desc, 'class':'item-label', 'id':'option-text-label-'+idNb};
    $('#option-row-1-cell-2-'+idNb).append(GETTER.option.createElement('span', attribs));
    $('#option-text-label-'+idNb).text(CodaliaLang.option.text_label);

    // Text input tag:
    attribs = {'type':'text', 'name':'option_text_'+idNb, 'id':'option-text-'+idNb, 'class':'form-control', 'value':data.text};
    $('#option-row-1-cell-2-'+idNb).append(GETTER.option.createElement('input', attribs));
  }

  reverseOrder = function(direction, idNb, dynamicItemType) {
    // Calls the parent function from the corresponding instance.
    GETTER[dynamicItemType].reverseOrder(direction, idNb);
  }

  beforeRemoveItem = function(idNb, dynamicItemType) {
    // Execute here possible tasks before the item deletion.
  }

  afterRemoveItem = function(idNb, dynamicItemType) {
    // Execute here possible tasks after the item deletion.
  }

})(jQuery);
