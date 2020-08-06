
Codalia.Field = class {
  constructor(props) {
    // Sets the field properties.
    this.name = props.name;
    this.code = props.code;
    this.type = props.type;
    this.required = props.required;
    this.defaultValue = props.default_value;

    if(this.type == 'list' || this.type == 'radio' || this.type == 'checkbox') {
      this.options = props.options;
    }

    this.parentDivId = 'Form-field-ExtraField-'+this.code+'-group';

    let attribs = {'data-field-name':this.code, 'id':this.parentDivId, 'class':'form-group span-left'};
    document.getElementById('field').appendChild(this.createElement('div', attribs)); 

    attribs = {'for':this.parentDivId, 'id':'label-'+this.code};
    document.getElementById(this.parentDivId).appendChild(this.createElement('label', attribs)); 
    document.getElementById('label-'+this.code).textContent = this.name;

    if(this.type == 'date' || this.type == 'datetime') {
      attribs = {'id':'datetime-row-'+this.code, 'class':'row'};
      document.getElementById(this.parentDivId).appendChild(this.createElement('div', attribs)); 
      
      this.parentDivId = 'datetime-row-'+this.code;
    }

    return this;
  }

  text = function() {
    let attribs = {'type':'text', 'name':'xtrf_'+this.code, 'id':'xtrf-'+this.code, 'class':'form-control'};
    document.getElementById(this.parentDivId).appendChild(this.createElement('input', attribs)); 
  }

  textarea = function() {
    let attribs = {'name':'xtrf_'+this.code, 'id':'xtrf-'+this.code, 'class':'form-control field-textarea'};
    document.getElementById(this.parentDivId).appendChild(this.createElement('textarea', attribs)); 
  }

  list = function() {
    let attribs = {'name':'xtrf_'+this.code, 'id':'xtrf-'+this.code, 'class':'form-control custom-select'};
    let element = this.createElement('select', attribs);
    let options = '';

    for(let i = 0; i < this.options.length; i++) {
      let selected = '';
      options += '<option value="'+this.options[i].value+'" '+selected+'>'+this.options[i].text+'</option>';
    }

    element.insertAdjacentHTML('afterbegin', options);
    document.getElementById(this.parentDivId).appendChild(element); 
    $('#xtrf-'+this.code).select2();
  }

  radio = function() {
    this.multiValueField('radio');
  }

  checkbox = function() {
    this.multiValueField('checkbox');
  }

  multiValueField = function(type) {
    for(let i = 0; i < this.options.length; i++) {
      let value = this.options[i].value;
      let attribs = {'class':type, 'id':this.code+'-'+value};
      document.getElementById(this.parentDivId).appendChild(this.createElement('div', attribs)); 

      attribs = {'type':type, 'name':'xtrf_'+this.code, 'id':'xtrf-'+this.code+'-'+value, 'value':value};
      document.getElementById(this.code+'-'+value).appendChild(this.createElement('input', attribs)); 

      attribs = {'for':this.code+'-'+value, 'id':'label-'+this.code+'-'+value};
      document.getElementById(this.code+'-'+value).appendChild(this.createElement('label', attribs)); 
      document.getElementById('label-'+this.code+'-'+value).textContent = this.options[i].text;
    }
  }

  date = function() {
    this.dateField(false);
  }

  datetime = function() {
    this.dateField(true);
  }

  /**
   * Creates a date and time fields into a given location.
   *
   * @param   boolean   time		If true, displays the time field.
   *
   * @return  void
  */
  dateField = function(time) {
    let attribs = {'class':'col-md-6', 'id':'date-wrapper-'+this.code};
    document.getElementById(this.parentDivId).appendChild(this.createElement('div', attribs));

    attribs = {'class':'field-datepicker', 'data-control':'datepicker', 'data-mode':'datetime', 'id':'datepicker-'+this.code};
    document.getElementById('date-wrapper-'+this.code).appendChild(this.createElement('div', attribs));

    attribs = {'class':'input-with-icon right-align datetime-field', 'id':'div-date-'+this.code};
    document.getElementById('datepicker-'+this.code).appendChild(this.createElement('div', attribs));

    attribs = {'class':'icon icon-calendar-o'};
    document.getElementById('div-date-'+this.code).appendChild(this.createElement('i', attribs));

    attribs = {'type':'text', 'id':'xtrf-date-'+this.code, 'class':'form-control', 'autocomplete':'off', 'data-datepicker':''};
    document.getElementById('div-date-'+this.code).appendChild(this.createElement('input', attribs));

    if(time) {
      attribs = {'class':'col-md-6', 'id':'datetime-wrapper-'+this.code};
      document.getElementById(this.parentDivId).appendChild(this.createElement('div', attribs));

      attribs = {'class':'input-with-icon right-align datetime-field', 'id':'div-time-'+this.code};
      document.getElementById('datetime-wrapper-'+this.code).appendChild(this.createElement('div', attribs));

      attribs = {'class':'icon icon-clock-o'};
      document.getElementById('div-time-'+this.code).appendChild(this.createElement('i', attribs));

      attribs = {'type':'text', 'id':'time-'+this.code, 'class':'form-control', 'autocomplete':'off', 'data-timepicker':''};
      document.getElementById('div-time-'+this.code).appendChild(this.createElement('input', attribs));
    }

    if(this.value == null) {
      this.value = '';
    }

    attribs = {'type':'hidden', 'name':'xtrf_'+this.code, 'id':'xtrf-'+this.code, 'value':this.value, 'data-datetime-value':''};
    document.getElementById('datepicker-'+this.code).appendChild(this.createElement('input', attribs));

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
}
