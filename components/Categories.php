<?php namespace Codalia\Journal\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Codalia\Journal\Models\Category as ArticleCategory;


class Categories extends ComponentBase
{

    /**
     * @var Collection A collection of categories to display
     */
    public $categories;

    /**
     * @var string Reference to the current category slug.
     */
    public $currentCategorySlug;


    public function componentDetails()
    {
        return [
            'name'        => 'codalia.journal::lang.settings.category_title',
            'description' => 'codalia.journal::lang.settings.category_description'
        ];
    }

    public function defineProperties()
    {
      return [
	    'slug' => [
                'title'       => 'codalia.journal::lang.settings.category_slug',
                'description' => 'codalia.journal::lang.settings.category_slug_description',
                'default'     => '{{ :slug }}',
                'type'        => 'string',
            ],
            'displayEmpty' => [
                'title'       => 'codalia.journal::lang.settings.category_display_empty',
                'description' => 'codalia.journal::lang.settings.category_display_empty_description',
                'type'        => 'checkbox',
                'default'     => 0,
            ],
            'displayAsMenu' => [
                'title'       => 'codalia.journal::lang.settings.category_display_as_menu',
                'description' => 'codalia.journal::lang.settings.category_display_as_menu_description',
                'type'        => 'checkbox',
                'default'     => 0,
            ],
      ];
    }


    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }


    public function onRun()
    {
	$this->currentCategorySlug = $this->page['currentCategorySlug'] = $this->property('slug');
	$this->categories = $this->page['categories'] = $this->loadCategories();
	$this->page['displayAsMenu'] = $this->property('displayAsMenu');
    }

    /**
     * Load all published categories or, depending on the <displayEmpty> option, only those that have articles
     * @return mixed
     */
    protected function loadCategories()
    {
        $categories = ArticleCategory::where('status', 'published')->with('articles_count')->getNested();

        if (!$this->property('displayEmpty')) {
            $iterator = function ($categories) use (&$iterator) {
                return $categories->reject(function ($category) use (&$iterator) {
                    if ($category->getNestedArticleCount() == 0) {
                        return true;
                    }
                    if ($category->children) {
                        $category->children = $iterator($category->children);
                    }
                    return false;
                });
            };

            $categories = $iterator($categories);
        }

        /*
         * Add a "url" helper attribute for linking to each category
         */
        return $this->linkCategories($categories);
    }

    /**
     * Sets the URL on each category according to the defined category page
     * @return void
     */
    protected function linkCategories($categories)
    {
        return $categories->each(function ($category) {
            $category->setUrl($this->controller);

            if ($category->children) {
                $this->linkCategories($category->children);
            }
        });
    }
}
