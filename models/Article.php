<?php namespace Codalia\Journal\Models;

use Lang;
use Html;
use Model;
use Auth;
use Db;
use BackendAuth;
use Backend\Models\User;
use October\Rain\Support\Str;
use October\Rain\Database\Traits\Validation;
use Carbon\Carbon;
use Codalia\Journal\Models\Category as ArticleCategory;
use Codalia\Journal\Models\Settings;
use Codalia\Journal\Models\Group;
use Codalia\Journal\Components\Articles;


/**
 * Article Model
 */
class Article extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'codalia_journal_articles';

    public $implement = ['@RainLab.Translate.Behaviors.TranslatableModel'];

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
    public $rules = ['title' => 'required'];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'description',
        ['slug', 'index' => true]
    ];

    /**
     * @var array Custom validation messages
     */
    public $customMessages = [
        'title.required' => 'codalia.journal::lang.messages.required_field'
      ];

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
    protected $appends = ['summary'];

    /**
     * @var array Attributes to be removed from the API representation of the model (ex. toArray())
     */
    protected $hidden = [];

    /**
     * @var array Attributes to be cast to Argon (Carbon) instances
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'published_up',
        'published_down'
    ];

    /**
     * The attributes on which the article list can be ordered.
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc'         => 'codalia.journal::lang.sorting.title_asc',
        'title desc'        => 'codalia.journal::lang.sorting.title_desc',
        'created_at asc'    => 'codalia.journal::lang.sorting.created_asc',
        'created_at desc'   => 'codalia.journal::lang.sorting.created_desc',
        'updated_at asc'    => 'codalia.journal::lang.sorting.updated_asc',
        'updated_at desc'   => 'codalia.journal::lang.sorting.updated_desc',
        'published_up asc'  => 'codalia.journal::lang.sorting.published_asc',
        'published_up desc' => 'codalia.journal::lang.sorting.published_desc',
        'sort_order asc'  => 'codalia.journal::lang.sorting.order_asc',
        'sort_order desc'  => 'codalia.journal::lang.sorting.order_desc',
        'random'            => 'codalia.journal::lang.sorting.random'
    ];

    /**
     * @var array Relations
     */
    public $hasOne = [
    ];
    public $hasMany = [
        'orderings' => ['Codalia\Journal\Models\Ordering']
    ];
    public $belongsTo = [
        'user' => ['Backend\Models\User', 'key' => 'created_by'],
        'category' => ['Codalia\Journal\Models\Category', 'key' => 'category_id'],
        'field_group' => ['Codalia\Journal\Models\Group', 'key' => 'field_group_id'],
    ];
    public $belongsToMany = [
        'categories' => [
            'Codalia\Journal\Models\Category',
            'table' => 'codalia_journal_cat_articles',
            'order' => 'name'
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];


    public function __construct($attributes = array())
    {
	// Ensures first that the RainLab User plugin is installed and activated.
	if (\System\Classes\PluginManager::instance()->exists('RainLab.User')) {
	    $this->belongsTo['usergroup'] = ['RainLab\User\Models\UserGroup', 'key' => 'access_id'];
	}
	else {
	    // Links to the administrator's user goup by default to prevent an error. 
	    // However, this relation will not be used.
	    $this->belongsTo['usergroup'] = ['Backend\Models\UserGroup', 'key' => 'access_id'];
	}

        parent::__construct($attributes);
    }


    public function getStatusOptions()
    {
      return array('unpublished' => 'codalia.journal::lang.status.unpublished',
		   'published' => 'codalia.journal::lang.status.published',
		   'archived' => 'codalia.journal::lang.status.archived');
    }

    public function getUserRoleOptions()
    {
        $results = Db::table('backend_user_roles')->select('code', 'name')->where('code', '!=', '')->get();

        $options = array();

	foreach ($results as $option) {
	    $options[$option->code] = $option->name;
	}

	return $options;
    }

    public function getUpdatedByFieldAttribute()
    {
	$names = '';

	if($this->updated_by) {
	    $user = BackendAuth::findUserById($this->updated_by);
	    $names = $user->first_name.' '.$user->last_name;
	}

	return $names;
    }

    public function getCreatedByFieldAttribute()
    {
	$names = '';

        if ($this->created_by) {
	    $user = BackendAuth::findUserById($this->created_by);
	    $names = $user->first_name.' '.$user->last_name;
	}

	return $names;
    }

    public function getStatusFieldAttribute()
    {
	$statuses = $this->getStatusOptions();
	$status = (isset($this->status)) ? $this->status : 'unpublished';

	return Lang::get($statuses[$status]);
    }

    public static function getFields($groupId, $id)
    {
        // Gets the fields linked to the group.
        $fields = Group::find($groupId)->fields;
	$data = [];
	foreach ($fields as $field) {
	    $data[] = ['name' => $field->name, 'code' => $field->code, 'type' => $field->type];
	}
//file_put_contents('debog_file.txt', print_r($fields, true));
	return $data;
    }

    public function beforeCreate()
    {
	if(empty($this->slug)) {
	    $this->slug = Str::slug($this->title);
	}

	$this->published_up = self::setPublishingDate($this);

	$user = BackendAuth::getUser();
	// For whatever reason the user object is null when refreshing the plugin. 
	$this->created_by = ($user !== null) ? $user->id : 1;
    }

    public function beforeUpdate()
    {
	$this->published_up = self::setPublishingDate($this);
	$user = BackendAuth::getUser();
	$this->updated_by = $user->id;
    }

    public function afterSave()
    {
        $this->setOrderings();
	$this->reorderByCategory();
    }

    public function afterDelete()
    {
        // Deletes ordering rows linked to the deleted article.
        $this->orderings()->where('article_id', $this->id)->delete();
    }

    public function setOrderings()
    {
        // Gets the category ids.
	$newCatIds = $this->categories()->pluck('category_id')->all();
	$oldCatIds = $this->orderings()->where('article_id', $this->id)->pluck('category_id')->all();

	// Loop through the currently selected categories.
	foreach ($newCatIds as $newCatId) {
	    if (!in_array($newCatId, $oldCatIds)) {
		// Stores the new selected category in a new ordering row.
		$this->orderings()->insert(['id' => $newCatId.'_'.$this->id,
					    'category_id' => $newCatId,
					    'article_id' => $this->id,
					    'title' => $this->title]);
	    }
	    else {
		// In case the article title has been modified.
		$this->orderings()->where('id', $newCatId.'_'.$this->id)->update(['title' => $this->title]);

		// Removes the ids of the categories which are still selected.
		if (($key = array_search($newCatId, $oldCatIds)) !== false) {
		    unset($oldCatIds[$key]);
		}
	    }
	}

	// Deletes the unselected categories.
	foreach ($oldCatIds as $oldCatId) {
	    $this->orderings()->where('id', $oldCatId.'_'.$this->id)->delete();
	}
    }

    public function reorderByCategory()
    {
        // Gets the orderings for each category.
        foreach ($this->categories as $category) {
	    // N.B: The orderings with null values are placed at the end of the array: (-sort_order DESC).
	    $orderings = $category->orderings()->orderByRaw('-sort_order DESC')->pluck('sort_order', 'id')->all();
	    $order = 1;

	    foreach ($orderings as $id => $sortOrder) {
	        // A new category has been added.
	        if ($sortOrder === null) {
		    $category->orderings()->where('id', $id)->update(['sort_order' => $order]);
		}
		else {
		    $order = $sortOrder;
		}

		$order++;
	    }
	}
    }

    /**
     * Sets the "url" attribute with a URL to this object.
     * @param string $pageName
     * @param Controller $controller
     * @param Object $category          The current category the articles are showed in. (optional)
     *
     * @return string
     */
    public function setUrl($pageName, $controller, $category = null)
    {
        $params = [
            'id'   => $this->id,
            'slug' => $this->slug,
            'category-path' => ''
        ];

	// If no (current) category is given, the main category of the article is set.
        $category = ($category === null) ? $this->category : $category;
	// Sets the category path to the article.
	$params['category-path'] = implode('/', ArticleCategory::getCategoryPath($category));
	// Don't use the homepage (home.htm) to get the article url. Use the article page instead.
	$pageName = ($pageName == 'home') ? 'article.htm' : $pageName;

        // Expose published year, month and day as URL parameters.
        if ($this->published_up) {
            $params['year']  = $this->published_up->format('Y');
            $params['month'] = $this->published_up->format('m');
            $params['day']   = $this->published_up->format('d');
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    /**
     * Switch visibility of some fields according to the current user accesses.
     *
     * @param       $fields
     * @param  null $context
     * @return void
     */
    public function filterFields($fields, $context = null)
    {
	if (!\System\Classes\PluginManager::instance()->exists('RainLab.User')) {
	    // Doesn't manage the access on front-end.
	    $fields->usergroup->hidden = true;
	}

        if ($context == 'create') {
	    // The item is about to be created. These field values are not known yet.
	    $fields->created_at->hidden = true;
	    $fields->updated_at->hidden = true;
	    $fields->_updated_by_field->hidden = true;
	    $fields->id->hidden = true;
	}

        if ($context == 'update') {
	  // The item has just been created. Don't display the updating fields. 
	  if (strcmp($fields->created_at->value->toDateTimeString(), $fields->updated_at->value->toDateTimeString()) === 0) {
	      $fields->updated_at->cssClass = 'hidden';
	      $fields->_updated_by_field->cssClass = 'hidden';
	  }
	}

        if (!isset($fields->_status_field)) {
            return;
	}

        $user = BackendAuth::getUser();

        if($user->hasAccess('codalia.journal.access_publish')) {
            $fields->_status_field->cssClass = 'hidden';
        }

	if (isset($fields->_created_by_field) && $user->hasAccess('codalia.journal.access_other_articles')) {
            $fields->_created_by_field->cssClass = 'hidden';
        }
    }

    public static function setPublishingDate($article)
    {
	// Sets to the current date time in case the record has never been published before. 
	return ($article->status == 'published' && is_null($article->published_up)) ? Carbon::now() : $article->published_up;
    }

    /**
     * Used to test if a certain user has permission to edit article,
     * returns TRUE if the user is the owner or has other articles access.
     * @param  User $user
     * @return bool
     */
    public function canEdit(User $user)
    {
        return ($this->created_by == $user->id) || $user->hasAnyAccess(['codalia.journal.access_other_articles']);
    }

    public function canView()
    {
	if ($this->access_id === null) {
	    return true;
	}

	if (\System\Classes\PluginManager::instance()->exists('RainLab.User') && Auth::check()) {
	    $userGroups = Auth::getUser()->getGroups();

	    foreach ($userGroups as $userGroup) {
	      if ($userGroup->id == $this->access_id) {
		  return true;
	      }
	    }
	}

	return false;
    }

    /**
     * Returns the HTML content before the <!-- more --> tag or a limited 600
     * character version.
     *
     * @return string
     */
    public function getSummaryAttribute()
    {
        $more = '<!-- more -->';

        if (strpos($this->description, $more) !== false) {
            $parts = explode($more, $this->description);

            return array_get($parts, 0);
        }

        return Html::limit($this->description, Settings::get('max_characters', 600));
    }

    //
    // Scopes
    //

    public function scopeArticleCount($query)
    {
	// Ensures the article is published and access matches the groups of the current user.
	return $query->where('status', 'published')
		     ->where(function($query) { 
			  $query->whereIn('access_id', Articles::getUserGroupIds()) 
				->orWhereNull('access_id');
		      });
    }

    /**
     * Allows filtering for specific categories.
     * @param  Illuminate\Query\Builder  $query      QueryBuilder
     * @param  array                     $categories List of category ids
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories);
        });
    }

    public function scopeIsPublished($query)
    {
        return $query->whereNotNull('status')
		     ->where('status', 'published')
		     ->whereNotNull('published_up')
		     ->where('published_up', '<', Carbon::now())
		     // Groups constraints within parenthesis.
		     ->where(function ($orWhere) {
			   $orWhere->whereNull('published_down')->orWhereColumn('published_down', '<', 'published_up');
		     });
    }

    /**
     * Apply a constraint to the query to find the nearest sibling
     *
     *     // Get the next article
     *     Article::applySibling()->first();
     *
     *     // Get the previous article
     *     Article::applySibling(-1)->first();
     *
     *     // Get the previous article, ordered by the ID attribute instead
     *     Article::applySibling(['direction' => -1, 'attribute' => 'id'])->first();
     *
     * @param       $query
     * @param array $options
     * @return
     */
    public function scopeApplySibling($query, $options = [])
    {
        if (!is_array($options)) {
            $options = ['direction' => $options];
        }

        extract(array_merge([
            'direction' => 'next',
            'attribute' => 'title'
        ], $options));

        $isPrevious = in_array($direction, ['previous', -1]);
        $directionOrder = $isPrevious ? 'desc' : 'asc';
        $directionOperator = $isPrevious ? '<' : '>';

        $query->where('id', '<>', $this->id);

        if (!is_null($this->$attribute)) {
            $query->where($attribute, $directionOperator, $this->$attribute);
	}

        $query->orderBy($attribute, $directionOrder);

        return $query;
    }

    /**
     * Returns the next article, if available.
     * @return self
     */
    public function nextArticle()
    {
        return self::isPublished()->applySibling()->first();
    }

    /**
     * Returns the previous article, if available.
     * @return self
     */
    public function previousArticle()
    {
        return self::isPublished()->applySibling(-1)->first();
    }

    /**
     * Lists articles for the frontend
     *
     * @param        $query
     * @param  array $options Display options
     * @return Article
     */
    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page'             => 1,
            'perPage'          => 30,
            'sort'             => 'created_at',
            'categories'       => null,
            'exceptCategories' => null,
            'category'         => null,
            'search'           => '',
            'exceptArticle'       => null
        ], $options));

        $searchableFields = ['title', 'slug', 'description'];

	// Shows only published articles.
	$query->isPublished();

        /*
         * Except article(s)
         */
        if ($exceptArticle) {
            $exceptArticles = (is_array($exceptArticle)) ? $exceptArticle : [$exceptArticle];
            $exceptArticleIds = [];
            $exceptArticleSlugs = [];

            foreach ($exceptArticles as $exceptArticle) {
                $exceptArticle = trim($exceptArticle);

                if (is_numeric($exceptArticle)) {
                    $exceptArticleIds[] = $exceptArticle;
                } else {
                    $exceptArticleSlugs[] = $exceptArticle;
                }
            }

            if (count($exceptArticleIds)) {
                $query->whereNotIn('codalia_journal_articles.id', $exceptArticleIds);
            }
            if (count($exceptArticleSlugs)) {
                $query->whereNotIn('slug', $exceptArticleSlugs);
            }
        }

        /*
         * Sorting
         */
        if (in_array($sort, array_keys(static::$allowedSortingOptions))) {
            if ($sort == 'random' || (substr($sort, 0, 10) === 'sort_order' && $category === null)) {
                $query->inRandomOrder();
            } else {
                @list($sortField, $sortDirection) = explode(' ', $sort);

                if (is_null($sortDirection)) {
                    $sortDirection = "desc";
                }

		if ($sortField == 'sort_order') {
		  // Important: Exclude the ordering columns from the result or article
		  //            categories won't match.
		  $query->select('codalia_journal_articles.*')
			// Joins over the ordering model.
		        ->join('codalia_journal_orderings AS o', function($join) use($category) {
			    $join->on('o.article_id', '=', 'codalia_journal_articles.id')
				 ->where('o.category_id', '=', $category);
			});
		}

		$query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, $searchableFields);
        }

        /*
         * Categories
         */
        if ($categories !== null) {
            $categories = is_array($categories) ? $categories : [$categories];
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        /*
         * Except Categories
         */
        if (!empty($exceptCategories)) {
            $exceptCategories = is_array($exceptCategories) ? $exceptCategories : [$exceptCategories];
            array_walk($exceptCategories, 'trim');

            $query->whereDoesntHave('categories', function ($q) use ($exceptCategories) {
                $q->whereIn('slug', $exceptCategories);
            });
        }

        /*
         * Gets articles which are in the current category.
         */
        if ($category !== null) {
            $query->whereHas('categories', function($q) use ($category) {
                $q->where('id', $category);
            });
        }

        return $query->paginate($perPage, $page);
    }
}
