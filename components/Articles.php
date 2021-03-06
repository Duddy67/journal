<?php namespace Codalia\Journal\Components;

use Lang;
use BackendAuth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Codalia\Journal\Models\Article;
use Codalia\Journal\Models\Category as ArticleCategory;
use Codalia\Journal\Models\Settings;
use Cms\Classes\Theme;
use Auth;
use Event;


class Articles extends ComponentBase
{
    /**
     * A collection of articles to display
     *
     * @var Collection
     */
    public $articles;

    /**
     * Parameter to use for the page number
     *
     * @var string
     */
    public $pageParam;

    /**
     * If the article list should be filtered by a category, the model to use
     *
     * @var Model
     */
    public $category;

    /**
     * Message to display when there are no messages
     *
     * @var string
     */
    public $noArticlesMessage;

    /**
     * Reference to the page name for linking to articles
     *
     * @var string
     */
    public $articlePage;

    /**
     * If the article list should be ordered by another attribute
     *
     * @var string
     */
    public $sortOrder;


    public function componentDetails()
    {
        return [
            'name'        => 'codalia.journal::lang.settings.articles_title',
            'description' => 'codalia.journal::lang.settings.articles_description'
        ];
    }

    public function defineProperties()
    {
	return [
            'pageNumber' => [
                'title'       => 'codalia.journal::lang.settings.articles_pagination',
                'description' => 'codalia.journal::lang.settings.articles_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}'
            ],
            'categoryFilter' => [
                'title'       => 'codalia.journal::lang.settings.articles_filter',
                'description' => 'codalia.journal::lang.settings.articles_filter_description',
                'type'        => 'string',
                'default'     => '{{ :slug }}',
            ],
            'articlesPerPage' => [
                'title'             => 'codalia.journal::lang.settings.articles_per_page',
                'default'           => 5,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'codalia.journal::lang.settings.articles_per_page_validation',
                'showExternalParam' => false
            ],
            'noArticlesMessage' => [
                'title'             => 'codalia.journal::lang.settings.articles_no_articles',
                'description'       => 'codalia.journal::lang.settings.articles_no_articles_description',
                'type'              => 'string',
                'default'           => Lang::get('codalia.journal::lang.settings.articles_no_articles_default'),
                'showExternalParam' => false
            ],
            'sortOrder' => [
                'title'       => 'codalia.journal::lang.settings.articles_order',
                'description' => 'codalia.journal::lang.settings.articles_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc'
            ],
            'articlePage' => [
                'title'       => 'codalia.journal::lang.settings.articles_article',
                'description' => 'codalia.journal::lang.settings.articles_article_description',
                'type'        => 'dropdown',
                'group'       => 'codalia.journal::lang.settings.group_links'
            ],
            'exceptArticle' => [
                'title'             => 'codalia.journal::lang.settings.articles_except_article',
                'description'       => 'codalia.journal::lang.settings.articles_except_article_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'codalia.journal::lang.settings.articles_except_article_validation',
                'group'             => 'codalia.journal::lang.settings.group_exceptions'
            ],
            'exceptCategories' => [
                'title'             => 'codalia.journal::lang.settings.articles_except_categories',
                'description'       => 'codalia.journal::lang.settings.articles_except_categories_description',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'codalia.journal::lang.settings.articles_except_categories_validation',
                'group'             => 'codalia.journal::lang.settings.group_exceptions'
            ]
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getArticlePageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        $options = Article::$allowedSortingOptions;

        foreach ($options as $key => $value) {
            $options[$key] = Lang::get($value);
        }

        return $options;
    }

    public static function getUserGroupIds()
    {
        $ids = [];

	if (\System\Classes\PluginManager::instance()->exists('RainLab.User') && Auth::check()) {
	    $userGroups = Auth::getUser()->getGroups();

	    foreach ($userGroups as $userGroup) {
	        $ids[] = $userGroup->id;
	    }
	}

	return $ids;
    }

    public function init()
    {
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            foreach ($params as $paramName => $paramValue) {
		if ($paramName == 'page') {
		    continue;
		}

		$realParamName = $paramName;

	        if (preg_match('#^parent-[0-9]+#', $paramName)) {
		    $realParamName = 'slug';
		}

		$records = ArticleCategory::transWhere($realParamName, $paramValue, $oldLocale)->first();

		if ($records) {
		    $records->translateContext($newLocale);
		    $newParams[$paramName] = $records[$realParamName];
		}
            }

            return $newParams;
        });
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->category = $this->page['category'] = $this->loadCategory();

	if ($this->category === null) {
	    return \Redirect::to(404);
	}

        $this->articles = $this->page['articles'] = $this->listArticles();
	$this->addCss(url('plugins/codalia/journal/assets/css/breadcrumb.css'));

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->articles->lastPage()) && $currentPage > 1) {
                return \Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    protected function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->noArticlesMessage = $this->page['noArticlesMessage'] = $this->property('noArticlesMessage');

        /*
         * Page link
         */
        $this->articlePage = $this->page['articlePage'] = $this->property('articlePage');
    }

    protected function listArticles()
    {
        $category = $this->category ? $this->category->id : null;

	// Removes the colon before the page number.
	if ($this->property('pageNumber') && preg_match('#^:([0-9]+)$#', $this->property('pageNumber'), $matches) === 1) {
	    $this->setProperty('pageNumber', $matches[1]);
	}

        /*
         * List all the articles, eager load their categories
         */

	$articles = Article::whereHas('category', function ($query) {
	        // Articles must have their main category published.
		$query->where('status', 'published');
	})->where(function($query) { 
	        // Gets articles which match the groups of the current user.
		$query->whereIn('access_id', self::getUserGroupIds()) 
		      ->orWhereNull('access_id');
        })->with(['categories' => function ($query) {
	        // Gets published categories only.
		$query->where('status', 'published');
	}])->listFrontEnd([
            'page'             => $this->property('pageNumber'),
            'sort'             => $this->property('sortOrder'),
            'perPage'          => $this->property('articlesPerPage'),
            'search'           => trim(input('search')),
            'category'         => $category,
            'exceptArticle'       => is_array($this->property('exceptArticle'))
                ? $this->property('exceptArticle')
                : preg_split('/,\s*/', $this->property('exceptArticle'), -1, PREG_SPLIT_NO_EMPTY),
            'exceptCategories' => is_array($this->property('exceptCategories'))
                ? $this->property('exceptCategories')
                : preg_split('/,\s*/', $this->property('exceptCategories'), -1, PREG_SPLIT_NO_EMPTY),
        ]);

        /*
         * Add a "url" helper attribute for linking to each article and category
         */
        $articles->each(function($article, $key) {
	    $article->setUrl($this->articlePage, $this->controller, $this->category);

	    $article->categories->each(function($category, $key) {
		$category->setUrl($this->controller);
	    });
        });

	if (isset($this->category) && Settings::get('show_breadcrumb')) {
	    $this->category->breadcrumb = \Codalia\Journal\Helpers\JournalHelper::instance()->getBreadcrumb($this->category);
	}

        return $articles;
    }

    protected function loadCategory()
    {
        if (!$slug = $this->property('categoryFilter')) {
            return null;
        }

        $category = new ArticleCategory;

        $category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
	    : $category->where('slug', $slug);

	if (($category = $category->first()) === null) {
            return null;
        }

        $path = [];
        $i = 1;

        // Builds the category path (if any).
        while ($this->param('parent-'.$i)) {
            $path[] = $this->param('parent-'.$i);
            $i++;
        }

        if (!empty($path)) {
            $path = array_reverse($path);
            $parent = $category;

            // Goes up to the root parent.
            foreach ($path as $segment) {
                $parent = $parent->getParent()->first();
                // Checks against the given path segment.
                if ($parent->slug != $segment) {
                    return null;
                }
            }
        }

        return $category;
    }
}
