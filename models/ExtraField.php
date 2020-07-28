<?php namespace Codalia\Journal\Models;

use Model;
use October\Rain\Support\Str;
use Lang;

/**
 * ExtraField Model
 */
class ExtraField extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_journal_extra_fields';

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
        'multi_values' => ['Codalia\Journal\Models\MultiValue']
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
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
      return array('text' => 'codalia.journal::lang.extra_field.text',
		   'textarea' => 'codalia.journal::lang.extra_field.textarea',
		   'list' => 'codalia.journal::lang.extra_field.list',
		   'radio' => 'codalia.journal::lang.extra_field.radio',
		   'checkbox' => 'codalia.journal::lang.extra_field.checkbox',
		   'date' => 'codalia.journal::lang.extra_field.date',
		   'datetime' => 'codalia.journal::lang.extra_field.datetime');
    }

    public function beforeSave()
    {
        $this->code = Str::slug($this->code);
	$this->code = preg_replace('#\-#', '_', $this->code); 
    }

    public function afterSave()
    {
	$this->setMultiValues();
	file_put_contents('debog_file.txt', print_r($this->groups, true)); 
    }

    public function afterDelete()
    {
        // Deletes relationship rows linked to the deleted book.
        $this->multi_values()->where('extra_field_id', $this->id)->delete();
    }

    /**
     * Returns the multi values of a given extra field.
     * @param integer $recordId
     *
     * @return array
     */
    public static function getMultiValues($recordId)
    {
        if (!ctype_digit($recordId)) {
	    return [];
	}

	$extraField = ExtraField::with(['multi_values' => function ($query){
	    $query->orderBy('ordering');
	}])->where('id', $recordId)->first();

	$multiValues = [];

	foreach ($extraField->multi_values as $multiValue) {
	    $multiValues[] = $multiValue->attributes;
	}

	return $multiValues;
    }

    /**
     * Parses the multi value fields and stores their values.
     *
     * @return void
     */
    public function setMultiValues()
    {
        // First resets the multi value set.
        $this->multi_values()->delete();
        $input = \Input::all();

	foreach ($input as $key => $value) {
	    if(preg_match('#^multi_value_value_([0-9]+)$#', $key, $matches)) {
		$idNb = $matches[1];
		$multiValue = new \Codalia\Journal\Models\MultiValue;

		$multiValue->id = $idNb;
		$multiValue->extra_field_id = $this->id;
		$multiValue->value = $input['multi_value_value_'.$idNb];
		$multiValue->text = $input['multi_value_text_'.$idNb];
		$multiValue->ordering = $input['multi_value_ordering_'.$idNb];

		$multiValue->save();
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
