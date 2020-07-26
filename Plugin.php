<?php namespace Codalia\Journal;

use Backend;
use System\Classes\PluginBase;
use Backend\Models\User as BackendUserModel;
use RainLab\User\Models\User as UserModel;
use RainLab\User\Controllers\Users as UsersController;
use RainLab\User\Models\UserGroup;
use Codalia\Journal\Models\Article;
use Backend\FormWidgets\Relation;
use Event;
use Db;

/**
 * journal Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Journal',
            'description' => 'A simple plugin used to managed articles.',
            'author'      => 'codalia',
            'icon'        => 'icon-newspaper-o'
        ];
    }

    /**
     * Register method, called when the plugin is first registered.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Boot method, called right before the request route.
     *
     * @return array
     */
    public function boot()
    {
	BackendUserModel::extend(function ($model) {
	    $model->hasMany['articles'] = ['Codalia\Journal\Models\Article', 'key' => 'created_by'];
	});

	// Ensures first that the RainLab User plugin is installed and activated.
	if (\System\Classes\PluginManager::instance()->exists('RainLab.User')) {
	    UserGroup::extend(function ($model) {
		$model->hasMany['articles'] = ['Codalia\Journal\Models\Article', 'key' => 'access_id'];
	    });
	}

	// Extends the partial files used for the relation type fields.
	Relation::extend(function ($widget) {
	    $widget->addViewPath(['$/codalia/journal/models/article']);
	});

	\Cms\Controllers\Index::extend(function ($controller) {
	    $controller->bindEvent('template.processSettingsBeforeSave', function ($dataHolder) {
	        $data = post();  
		// Ensures the page file names for categories fit the correct pattern.
		if ($data['templateType'] == 'page' &&
		    isset($data['component_names']) && in_array('articleList', $data['component_names']) &&
		    !preg_match('#^category-level-[0-9]+\.htm$#', $data['fileName'])) {
		    throw new \ApplicationException(\Lang::get('codalia.journal::lang.settings.invalid_file_name'));
		}
	    });
	});
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Codalia\Journal\Components\Article' => 'article',
            'Codalia\Journal\Components\Articles' => 'articleList',
            'Codalia\Journal\Components\Categories' => 'articleCategories',
            'Codalia\Journal\Components\Featured' => 'featuredArticles',
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'codalia.journal.manage_settings' => [
                'tab' => 'codalia.journal::lang.journal.tab',
                'label' => 'codalia.journal::lang.journal.manage_settings',
		'order' => 200
	      ],
            'codalia.journal.access_articles' => [
                'tab' => 'codalia.journal::lang.journal.tab',
                'label' => 'codalia.journal::lang.journal.access_articles',
		'order' => 201
            ],
            'codalia.journal.access_categories' => [
                'tab' => 'codalia.journal::lang.journal.tab',
                'label' => 'codalia.journal::lang.journal.access_categories',
		'order' => 202
            ],
            'codalia.journal.access_publish' => [
                'tab' => 'codalia.journal::lang.journal.tab',
                'label' => 'codalia.journal::lang.journal.access_publish'
            ],
            'codalia.journal.access_delete' => [
                'tab' => 'codalia.journal::lang.journal.tab',
                'label' => 'codalia.journal::lang.journal.access_delete'
            ],
            'codalia.journal.access_other_articles' => [
                'tab' => 'codalia.journal::lang.journal.tab',
                'label' => 'codalia.journal::lang.journal.access_other_articles'
            ],
            'codalia.journal.access_check_in' => [
                'tab' => 'codalia.journal::lang.journal.tab',
                'label' => 'codalia.journal::lang.journal.access_check_in'
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'journal' => [
                'label'       => 'Journal',
                'url'         => Backend::url('codalia/journal/articles'),
                'icon'        => 'icon-newspaper-o',
                'permissions' => ['codalia.journal.*'],
                'order'       => 500,
	    'sideMenu' => [
		'new_article' => [
		    'label'       => 'codalia.journal::lang.articles.new_article',
		    'icon'        => 'icon-plus',
		    'url'         => Backend::url('codalia/journal/articles/create'),
		    'permissions' => ['codalia.journal.access_articles']
		],
		'articles' => [
		    'label'       => 'codalia.journal::lang.journal.articles',
		    'icon'        => 'icon-file-text-o',
		    'url'         => Backend::url('codalia/journal/articles'),
		    'permissions' => ['codalia.journal.access_articles']
		],
		'categories' => [
		    'label'       => 'codalia.journal::lang.journal.categories',
		    'icon'        => 'icon-sitemap',
		    'url'         => Backend::url('codalia/journal/categories'),
		    'permissions' => ['codalia.journal.access_categories']
		],
		'extra_fields' => [
		    'label'       => 'codalia.journal::lang.journal.extra_fields',
		    'icon'        => 'icon-plus-square',
		    'url'         => Backend::url('codalia/journal/extrafields'),
		    'permissions' => ['codalia.journal.access_categories']
		]
	      ]
            ],
        ];
    }


    public function registerSettings()
    {
	return [
	    'journal' => [
		'label'       => 'Journal',
		'description' => 'A simple plugin to manage articles.',
		'category'    => 'Journal',
		'icon'        => 'icon-newspaper-o',
		'class' => 'Codalia\Journal\Models\Settings',
		'order'       => 500,
		'keywords'    => 'geography place placement',
		'permissions' => ['codalia.journal.manage_settings']
	    ]
	];
    }
}
