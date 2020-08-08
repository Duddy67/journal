<?php namespace Codalia\Journal\Models;

use Model;
use October\Rain\Support\Str;
use Lang;

/**
 * Field Model
 */
class Field extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_journal_fields';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Validation rules for attributes
     */
    public $rules = [];

    /**
     * @var array Attributes to be cast to native types
     */
    protected $casts = [];

    /**
     * @var array Attributes to be cast to JSON
     */
    protected $jsonable = [];

    /**
     * @var array Attributes to be appended to the API representation of the model (ex. toArray())
     */
    protected $appends = [];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'options' => ['Codalia\Journal\Models\Option'],
        'values' => ['Codalia\Journal\Models\FieldValue']
    ];
    public $belongsTo = [];
    public $belongsToMany = [
        'groups' => [
            'Codalia\Journal\Models\Group',
            'table' => 'codalia_journal_fields_groups',
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];


    public function getStatusOptions()
    {
      return array('unpublished' => 'codalia.journal::lang.status.unpublished',
		   'published' => 'codalia.journal::lang.status.published');
    }

    public function getTypeOptions()
    {
      return array('text' => 'codalia.journal::lang.field.text',
		   'textarea' => 'codalia.journal::lang.field.textarea',
		   'list' => 'codalia.journal::lang.field.list',
		   'radio' => 'codalia.journal::lang.field.radio',
		   'checkbox' => 'codalia.journal::lang.field.checkbox',
		   'date' => 'codalia.journal::lang.field.date',
		   'datetime' => 'codalia.journal::lang.field.datetime');
    }

    public function beforeSave()
    {
        $this->code = Str::slug($this->code);
	$this->code = preg_replace('#\-#', '_', $this->code); 
    }

    public function afterSave()
    {
	$this->setOptions();
    }

    public function afterDelete()
    {
        // Deletes relationship rows linked to the deleted book.
        $this->options()->where('field_id', $this->id)->delete();
    }

    /**
     * Returns the options of a given extra field.
     * @param integer $recordId
     *
     * @return array
     */
    public static function getOptions($recordId)
    {
        if (!ctype_digit($recordId)) {
	    return [];
	}

	$field = Field::with(['options' => function ($query){
	    $query->orderBy('ordering');
	}])->where('id', $recordId)->first();

	$options = [];

	foreach ($field->options as $option) {
	    $options[] = $option->attributes;
	}

	return $options;
    }

    /**
     * Parses the option fields and stores their values.
     *
     * @return void
     */
    public function setOptions()
    {
        // First resets the multi value set.
        $this->options()->delete();
        $input = \Input::all();

	foreach ($input as $key => $value) {
	    if(preg_match('#^option_value_([0-9]+)$#', $key, $matches)) {
		$idNb = $matches[1];
		$option = new \Codalia\Journal\Models\Option;

		$option->id = $idNb;
		$option->field_id = $this->id;
		$option->value = $input['option_value_'.$idNb];
		$option->text = $input['option_text_'.$idNb];
		$option->ordering = $input['option_ordering_'.$idNb];

		$option->save();
	    }
	}
    }

    /**
     * Switch visibility of some fields.
     *
     * @param       $fields
     * @param  null $context
     * @return void
     */
    public function filterFields($fields, $context = null)
    {
        if ($context == 'create') {
	    // The item is about to be created. These field values are not known yet.
	    //$fields->created_at->hidden = true;
	    //$fields->updated_at->hidden = true;
	    $fields->id->hidden = true;
	}
    }
}
