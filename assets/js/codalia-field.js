
Codalia.Field = class {
  constructor(props) {
    // Sets the field properties.
    this.id = props.id;
    this.name = props.name;
    this.code = props.code;
    this.type = props.type;
    this.required = props.required;
    this.defaultValue = props.default_value;
    this.value = props.value;
    this.value = this.value == '' ? this.defaultValue : this.value;

    if(this.type == 'list' || this.type == 'radio' || this.type == 'checkbox') {
      this.options = props.options;
    }

    this.parentDivId = 'Form-field-ExtraField-'+this.code+'-group';
    let isRequired = (this.required) ? ' is-required': '';

    let attribs = {'data-field-name':this.code, 'id':this.parentDivId, 'class':'form-group span-left'+isRequired};
    document.getElementById('field').appendChild(this.createElement('div', attribs)); 

    attribs = {'for':this.parentDivId, 'id':'label-'+this.code};
    document.getElementById(this.parentDivId).appendChild(this.createElement('label', attribs)); 
    document.getElementById('label-'+this.code).textContent = this.name;

    if(this.type == 'date' || this.type == 'datetime') {
      attribs = {'class':'field-datepicker row', 'data-control':'datepicker', 'data-mode':'datetime', 'id':'datepicker-'+this.code};
      document.getElementById(this.parentDivId).appendChild(this.createElement('div', attribs)); 
      
      this.parentDivId = 'datepicker-'+this.code;
    }

    return this;
  }

  text = function() {
    let attribs = {'type':'text', 'name':'xtrf_'+this.id+'_'+this.type+'_'+this.code, 'id':'xtrf-'+this.code, 'class':'form-control', 'value':this.value};
    document.getElementById(this.parentDivId).appendChild(this.createElement('input', attribs)); 
  }

  textarea = function() {
    let attribs = {'name':'xtrf_'+this.id+'_'+this.type+'_'+this.code, 'id':'xtrf-'+this.code, 'class':'form-control field-textarea'};
    document.getElementById(this.parentDivId).appendChild(this.createElement('textarea', attribs)); 
    document.getElementById('xtrf-'+this.code).textContent = this.value;
  }

  list = function() {
    let attribs = {'name':'xtrf_'+this.id+'_'+this.type+'_'+this.code, 'id':'xtrf-'+this.code, 'class':'form-control custom-select'};
    let element = this.createElement('select', attribs);
    let options = '';

    for(let i = 0; i < this.options.length; i++) {
      let selected = '';

      if(this.options[i].value == this.value) {
	selected = 'selected="selected"';
      }

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

      let extraId = type == 'checkbox' ? '_'+value : '';
      attribs = {'type':type, 'name':'xtrf_'+this.id+'_'+this.type+'_'+this.code+extraId, 'id':'xtrf-'+this.code+'-'+value, 'value':value};

      let regex = new RegExp(value);
      if(regex.test(this.value)) {
	attribs.checked = 'checked';
      }

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
    let attribs = {'class':'col-md-6', 'id':'date-picker-'+this.code};
    document.getElementById(this.parentDivId).appendChild(this.createElement('div', attribs));

    attribs = {'class':'input-with-icon right-align datetime-field', 'id':'div-date-'+this.code};
    document.getElementById('date-picker-'+this.code).appendChild(this.createElement('div', attribs));

    attribs = {'class':'icon icon-calendar-o'};
    document.getElementById('div-date-'+this.code).appendChild(this.createElement('i', attribs));

    attribs = {'type':'text', 'id':'xtrf-date-'+this.code, 'class':'form-control', 'autocomplete':'off', 'data-datepicker':''};
    document.getElementById('div-date-'+this.code).appendChild(this.createElement('input', attribs));

    if(time) {
      attribs = {'class':'col-md-6', 'id':'time-picker-'+this.code};
      document.getElementById(this.parentDivId).appendChild(this.createElement('div', attribs));

      attribs = {'class':'input-with-icon right-align datetime-field', 'id':'div-time-'+this.code};
      document.getElementById('time-picker-'+this.code).appendChild(this.createElement('div', attribs));

      attribs = {'class':'icon icon-clock-o'};
      document.getElementById('div-time-'+this.code).appendChild(this.createElement('i', attribs));

      attribs = {'type':'text', 'id':'xtrf-time-'+this.code, 'class':'form-control', 'autocomplete':'off', 'data-timepicker':''};
      document.getElementById('div-time-'+this.code).appendChild(this.createElement('input', attribs));
    }

    attribs = {'type':'hidden', 'name':'xtrf_'+this.id+'_'+this.type+'_'+this.code, 'id':'xtrf-'+this.code, 'value':this.value, 'data-datetime-value':''};
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
