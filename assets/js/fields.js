(function($) {
  // A global variable to store then access the field objects.
  const GETTER = {};

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

    // Stores the newly created object.
    //GETTER.fields = new Codalia.Fields(props);

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

  createField = function(field) {
    let id = 'Form-field-ExtraField-'+field.code+'-group';
    let attribs = {'data-field-name':field.code, 'id':id, 'class':'form-group span-left'};
    $('#field').append(createElement('div', attribs));
    attribs = {'for':id};
    $('#'+id).append(createElement('label', attribs));
    $('#'+id+' > label').text(field.name);

    if(field.type == 'checkbox') {
      attribs = {'id':'field-checkboxlist-'+field.code, 'class':'field-checkboxlist-inner'};
      $('#'+id).append(createElement('div', attribs));
      id = 'field-checkboxlist-'+field.code;
    }

    let type = field.type.charAt(0).toUpperCase() + field.type.slice(1);
    var create = window['create'+type];
    create(field, id);
  }

  createList = function(field, id) {
    let attribs = {'name':'xtrf_'+field.code, 'id':'xtrf-'+field.code, 'class':'form-control custom-select'};
    let element = createElement('select', attribs);
    let options = '';

    for(let i = 0; i < field.options.length; i++) {
      let selected = '';
      options += '<option value="'+field.options[i].value+'" '+selected+'>'+field.options[i].text+'</option>';
    }

    $('#'+id).append(element);
    $('#xtrf-'+field.code).html(options);
    $('#xtrf-'+field.code).select2();
  }

  createRadio = function(field, id) {
    for(let i = 0; i < field.options.length; i++) {
      let value = field.options[i].value;
      let attribs = {'class':'radio', 'id':field.code+'-'+value};
      $('#'+id).append(createElement('div', attribs));

      attribs = {'type':'radio', 'name':'xtrf_'+field.code, 'id':'xtrf-'+field.code+'-'+value, 'value':value};
      $('#'+field.code+'-'+value).append(createElement('input', attribs));

      attribs = {'for':field.code+'-'+value};
      $('#'+field.code+'-'+value).append(createElement('label', attribs));
      $('#'+field.code+'-'+value+' > label').text(field.options[i].text);
    }
  }

  createCheckbox = function(field, id) {
    for(let i = 0; i < field.options.length; i++) {
      let value = field.options[i].value;
      let attribs = {'class':'checkbox', 'id':field.code+'-'+value};
      $('#'+id).append(createElement('div', attribs));

      attribs = {'type':'checkbox', 'name':'xtrf_'+field.code+'_'+value, 'id':'xtrf-'+field.code+'-'+value, 'value':value};
      $('#'+field.code+'-'+value).append(createElement('input', attribs));

      attribs = {'for':field.code+'-'+value};
      $('#'+field.code+'-'+value).append(createElement('label', attribs));
      $('#'+field.code+'-'+value+' > label').text(field.options[i].text);
    }
  }

  createText = function(field, id) {
    let attribs = {'type':'text', 'name':'xtrf_'+field.code, 'id':'xtrf-'+field.code, 'class':'form-control'};
    $('#'+id).append(createElement('input', attribs));
  }

  createTextarea = function(field, id) {
    let attribs = {'name':'xtrf_'+field.code, 'id':'xtrf-'+field.code, 'class':'form-control field-textarea'};
    $('#'+id).append(createElement('textarea', attribs));
  }

  createDatetime = function(field, id) {
    let attribs = {'name':'xtrf_'+field.code, 'id':'xtrf-'+field.code, 'class':'form-control field-textarea'};
    $('#'+id).append(createElement('textarea', attribs));
  }

  /**
   * Creates a date and time fields into a given location.
   *
   * @param   string    name		The name of the date time field.
   * @param   integer   idNb		The item id number.
   * @param   string    rowCellId	The location where the date time field is created.
   * @param   string    value		The datetime value.
   * @param   boolean   time		If true, displays the time field.
   *
   * @return  void
  */
  createDate = function(field, id) {
    let attribs = {'class':'field-datepicker', 'data-control':'datepicker', 'data-mode':'datetime', 'id':'datepicker-'+field.code};
    document.getElementById(id).appendChild(this.createElement('div', attribs));

    attribs = {'class':'input-with-icon right-align datetime-field', 'id':'div-date-'+field.code};
    document.getElementById('datepicker-'+field.code).appendChild(this.createElement('div', attribs));

    attribs = {'class':'icon icon-calendar-o'};
    document.getElementById('div-date-'+field.code).appendChild(this.createElement('i', attribs));

    attribs = {'type':'text', 'id':'xtrf-date-'+field.code, 'class':'form-control', 'autocomplete':'off', 'data-datepicker':''};
    document.getElementById('div-date-'+field.code).appendChild(this.createElement('input', attribs));

    /*if(time) {
      attribs = {'class':'input-with-icon right-align datetime-field', 'id':'div-time-'+name+'-'+idNb};
      document.getElementById('datepicker-'+name+'-'+idNb).appendChild(this.createElement('div', attribs));

      attribs = {'class':'icon icon-clock-o'};
      document.getElementById('div-time-'+name+'-'+idNb).appendChild(this.createElement('i', attribs));

      attribs = {'type':'text', 'id':this.itemType+'-time-'+name+'-'+idNb, 'class':'form-control', 'autocomplete':'off', 'data-timepicker':''};
      document.getElementById('div-time-'+name+'-'+idNb).appendChild(this.createElement('input', attribs));
    }*/

    if(field.value == null) {
      field.value = '';
    }

    attribs = {'type':'hidden', 'name':'xtrf_'+field.code, 'id':'xtrf-'+field.code, 'value':field.value, 'data-datetime-value':''};
    document.getElementById('datepicker-'+field.code).appendChild(this.createElement('input', attribs));

    $('[data-control="datepicker"]').datePicker();
  }

  /**
   * Creates an HTML element of the given type.
   *
   * @param   string   type        The type of the element.
   * @param   object   attributes  The element attributes.
   *
   * @return  object   The HTML element.
  */
  createElement = function(type, attributes) {
    let element = document.createElement(type);
    // Sets the element attributes (if any).
    if(attributes !== undefined) {
      for(let key in attributes) {
	// Ensures that key is not a method/function.
	if(typeof attributes[key] !== 'function') {
	  element.setAttribute(key, attributes[key]);
	}
      }
    }

    return element;
  }

  /** Callback functions **/

  getAjaxResult = function(result) {
    if(result.success === true) {
      $.each(result.data, function(i, field) {
	  elem = new Codalia.Field(field);
	  elem[field.type]();
      });
    }
    else {
      alert('Error: '+result.message);
    }
  }

})(jQuery);
