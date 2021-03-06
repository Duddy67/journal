<?php namespace Codalia\Journal\Controllers;

use BackendMenu;
use Flash;
use Lang;
use Backend\Classes\Controller;
use Backend\Behaviors\ReorderController;
use Codalia\Journal\Controllers\Articles;
use Codalia\Journal\Helpers\JournalHelper;

/**
 * Orderings Back-end Controller
 */
class Orderings extends Controller
{
    public $implement = [
        'Backend.Behaviors.ReorderController',
    ];

    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.Journal', 'journal', 'orderings');
    }

    public function reorder()
    {
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();
	$this->addCss(url('plugins/codalia/journal/assets/css/extra.css'));

        $this->asExtension('ReorderController')->reorder();
    }

    public function getCurrentFilters($name) {
        // Loops through the session array.
        foreach (\Session::get('widget', []) as $key => $item) {
            if (str_contains($key, 'Filter')) {
                $filters = @unserialize(@base64_decode($item));
                if ($filters) {
		    // Checks for the given filter name.
		    if (array_key_exists('scope-'.$name, $filters)) {
		        $filter = (isset($filters['scope-'.$name])) ? $filters['scope-'.$name] : [];
		        return $filter;
		    }

		    return $filters;
                }
		else {
		    return [];
		}
            }
        }

	return [];
    }

    public function reorderExtendQuery($query)
    {
        $category = $this->getCurrentFilters('category');

        if (count($category) == 1) {
	    $query->where('category_id', array_keys($category)); 
	}
	else {
	    // Cancels the query. No item is returned.
	    $query->whereRaw('1 = 0');
	    Flash::warning(Lang::get('codalia.journal::lang.action.cannot_reorder'));
	}
    }
}
