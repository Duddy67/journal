<?php namespace Codalia\Journal\Models;

use Model;
use October\Rain\Support\Str;
use Lang;
use Codalia\Journal\Models\Article;

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
    public $rules = ['name' => 'required', 'code' => 'required'];

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

        $id = ($this->id) ? $this->id : 0;
	if (Field::where('code', '=', $this->code)->where('id', '!=', $id)->count()) {
	    throw new \ValidationException(['code' => 'Sorry that code is already taken!']);
	} 
    }

    public function afterSave()
    {
	$this->setOptions();
	$this->cleanValues();
    }

    public function afterDelete()
    {
        // Deletes relationship rows linked to the deleted item.
        $this->options()->where('field_id', $this->id)->delete();
        $this->values()->where('field_id', $this->id)->delete();
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
     * Deletes the field values linked to the groups which have been unselected.
     *
     * @return void
     */
    public function cleanValues()
    {
        if (post('Field') === null) {
	    return;
	}

        // Collects the new and old group ids then figures out the old groups which have
	// been unselected.
	$news = post('Field')['groups'];
	// Note: When no group is selected, zero is returned instead of an empty array.
	$news = (is_array($news)) ? $news : [];
	$olds = json_decode(post('initial_groups'));
	$unselectedGroups = array_diff($olds, $news);
	// Gets the id of articles using the unselected groups.
	$articles = Article::where('field_group_id', implode(',', $unselectedGroups))->pluck('id')->toArray();
        // Deletes the related field values.
	$this->values()->where('article_id', implode(',', $articles))->delete();
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
	elseif ($context == 'edit' || $context == 'update') {
	    $fields->type->disabled = true;
	}
    }

    /**
     * Allows filtering for specific groups.
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $groups     List of group ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterGroups($query, $groups)
    {
        return $query->whereHas('groups', function($q) use ($groups) {
            $q->whereIn('id', $groups);
        });
    }

}
