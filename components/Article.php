<?php namespace Codalia\Journal\Components;

use Event;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Codalia\Journal\Models\Article as ArticleItem;
use Codalia\Journal\Models\Category;
use Codalia\Journal\Models\Settings;
use Codalia\Journal\Components\Articles;


class Article extends ComponentBase
{
    /**
     * @var Codalia\Journal\Models\Article The article model used for display.
     */
    public $article;


    public function componentDetails()
    {
        return [
            'name'        => 'codalia.journal::lang.settings.article_title',
            'description' => 'codalia.journal::lang.settings.article_description'
        ];
    }

    public function defineProperties()
    {
	  return [
            'slug' => [
                'title'       => 'codalia.journal::lang.settings.article_slug',
                'description' => 'codalia.journal::lang.settings.article_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'categoryPath' => [
                'title'       => 'codalia.journal::lang.settings.category_path',
                'description' => 'codalia.journal::lang.settings.category_path_description',
                'default'     => '{{ :category-path }}',
                'type'        => 'string',
            ],
        ];
    }


    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function init()
    {
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            foreach ($params as $paramName => $paramValue) {
	        if ($paramName == 'category-path') {
		    // Breaks down the category path string into slug segments.
		    $slugs = explode('/', $paramValue);
		    $newPath = '';

		    foreach ($slugs as $slug) {
			$records = Category::transWhere('slug', $slug, $oldLocale)->first();

			if ($records) {
			    $records->translateContext($newLocale);
			    $newPath .= $records['slug'].'/';
			}
		    }
                    // Removes the slash from the end of the string.
		    $newPath = substr($newPath, 0, -1);
		    $newParams[$paramName] = $newPath;
		}
		else {
		    $records = ArticleItem::transWhere($paramName, $paramValue, $oldLocale)->first();

		    if ($records) {
			$records->translateContext($newLocale);
			$newParams[$paramName] = $records[$paramName];
		    }
		}
            }

            return $newParams;
        });
    }

    public function onRun()
    {
        $this->article = $this->page['article'] = $this->loadArticle();

	if ($this->article === null || $this->article->category->status != 'published') {
	    return \Redirect::to(404);
	}

	if (!$this->article->canView()) {
	    return \Redirect::to(403);
	}

	$this->addCss(url('plugins/codalia/journal/assets/css/breadcrumb.css'));
    }

    public function onRender()
    {
        if (empty($this->article)) {
            $this->article = $this->page['article'] = $this->loadArticle();
        }
    }

    protected function loadArticle()
    {
        $slug = $this->property('slug');
        $article = new ArticleItem;

        $article = $article->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
		      ? $article->transWhere('slug', $slug)
		      : $article->where('slug', $slug);

        $article->with(['categories' => function ($query) {
	    // Gets only published categories.
	    $query->where('status', 'published');
        }, 'field_group']);

	if (($article = $article->first()) === null) {
	    return null;
	}

        // Add a "url" helper attribute for linking to the main category.
	$article->category->setUrl($this->controller);

        /*
         * Add a "url" helper attribute for linking to each extra category
         */
        if ($article && $article->categories->count()) {
            $article->categories->each(function($category, $key) use($article) {
		$category->setUrl($this->controller);
            });
	}

	// Builds the canonical link to the article based on the main category of the article.
	$path = implode('/', Category::getCategoryPath($article->category));
	$articlePage = $this->getPage()->getBaseFileName();
	$params = ['id' => $article->id, 'slug' => $article->slug, 'category' => $path];
	$article->canonical = $this->controller->pageUrl($articlePage, $params);

	if (Settings::get('show_breadcrumb')) {
	    $article->breadcrumb = $this->getBreadcrumb($article);
	}

	if ($article->field_group) {
	    // Loops through the extra fields.
	    foreach ($article->field_group->fields as $key => $field) {
	      // N.B: Use '_value' instead of 'value' or a conflict with the Eloquent value() method will occur. 
	      $field->_value = $field->values->where('article_id', $article->id)->pluck('value')->first();

	      // Sets the value as the text value of the selected option(s).
	      if ($field->type == 'radio' || $field->type == 'checkbox' || $field->type == 'list') {
		  $values = '';

		  foreach ($field->options as $option) {
		      if ($field->type != 'checkbox' && $option->attributes['value'] == $field->_value) {
			  $field->_value = $option->attributes['text'];
		      }
		      elseif ($field->type == 'checkbox' && preg_match('#'.$option->attributes['value'].'#', $field->_value)) {
			  // Concatenates the checkbox text values.
			  $values .= $option->attributes['text'].', ';
		      }
		  }

		  if($field->type == 'checkbox') {
		      // Removes comma and space from the end of the string.
		      $values = substr($values, 0, -2);
		      $field->_value = $values;
		  }
	      }
	    }
	}

        return $article;
    }

    /**
     * Returns the breadcrumb path to a given article.
     *
     * @param object $article
     *
     * @return array
     */
    public function getBreadcrumb($article)
    {
        preg_match('#/([a-z0-9-]+)/'.$article->slug.'$#', $this->currentPageUrl(), $matches);
        $slug = $matches[1];
        $category = new Category;

        $category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
		      ? $category->transWhere('slug', $slug)
		      : $category->where('slug', $slug);

        try {
            $category = $category->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

	return \Codalia\Journal\Helpers\JournalHelper::instance()->getBreadcrumb($category, $article);
    }

    public function previousArticle()
    {
        return $this->getArticleSibling(-1);
    }

    public function nextArticle()
    {
        return $this->getArticleSibling(1);
    }

    protected function getArticleSibling($direction = 1)
    {
        if (!$this->article) {
            return;
        }

        $method = $direction === -1 ? 'previousArticle' : 'nextArticle';

        if (!$article = $this->article->$method()) {
            return;
        }

        $articlePage = $this->getPage()->getBaseFileName();

        $article->setUrl($articlePage, $this->controller);

        $article->categories->each(function($category) {
            $category->setUrl($this->controller);
        });

        return $article;
    }
}
