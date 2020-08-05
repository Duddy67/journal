<?php namespace Codalia\Journal\Controllers;

use Flash;
use Lang;
use Carbon\Carbon;
use BackendMenu;
use Backend\Classes\Controller;
use Codalia\Journal\Models\Article;
use Backend\Behaviors\FormController;
use BackendAuth;
use Codalia\Journal\Helpers\JournalHelper;


/**
 * Articles Back-end Controller
 */
class Articles extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['codalia.journal.access_articles'];


    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.Journal', 'journal', 'articles');
    }


    public function index()
    {
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();
	$this->addCss(url('plugins/codalia/journal/assets/css/extra.css'));
	// Unlocks the checked out items of this user (if any).  
	JournalHelper::instance()->checkIn((new Article)->getTable(), BackendAuth::getUser());
	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
    }

    public function create()
    {
	BackendMenu::setContextSideMenu('new_article');

	return $this->asExtension('FormController')->create();
    }

    public function update($recordId = null, $context = null)
    {
	$article = Article::find($recordId);
	$user = BackendAuth::getUser();

	// Checks for permissions.
	if (!$article->canEdit($user)) {
	    Flash::error(Lang::get('codalia.journal::lang.action.editing_not_allowed'));
	    return redirect('backend/codalia/journal/articles');
	}

	// Checks for check out matching.
	if ($article->checked_out && $user->id != $article->checked_out) {
	    Flash::error(Lang::get('codalia.journal::lang.action.check_out_do_not_match'));
	    return redirect('backend/codalia/journal/articles');
	}

        if ($context == 'edit') {
	    // Locks the item for this user.
	    JournalHelper::instance()->checkOut((new Article)->getTable(), $user, $recordId);
	}

        return $this->asExtension('FormController')->update($recordId, $context);
    }

    public function listOverrideColumnValue($record, $columnName, $definition = null)
    {
        if ($record->checked_out && $columnName == 'title') {
	    return JournalHelper::instance()->getCheckInHtml($record, BackendAuth::findUserById($record->checked_out));
	}
    }

    public function listExtendQuery($query)
    {
	if (!$this->user->hasAnyAccess(['codalia.journal.access_other_articles'])) {
	    // Shows only the user's articles if they don't have access to other articles.
	    $query->where('created_by', $this->user->id);
	}
    }

    public function listInjectRowClass($record, $definition = null)
    {
        $class = '';

        if ($record->status == 'archived') {
            $class = 'safe disabled';
        }

        if ($record->checked_out) {
	    $class = 'safe disabled nolink';
	}

	return $class;
    }

    public function index_onDelete()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();

	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            $count = 0;
            foreach ($checkedIds as $recordId) {
	        // Checks that article does exist and the current user has the required access levels.
                if (!$article = Article::find($recordId)) {
                    continue;
                }

		if (!$article->canEdit($this->user)) {
		    Flash::error(Lang::get('codalia.journal::lang.action.not_allowed_to_modify_item', ['name' => $article->title]));
		    return;
		}

		if ($article->checked_out) {
		    Flash::warning(Lang::get('codalia.journal::lang.action.checked_out_item', ['name' => $article->title]));
		    return;
		}

                $article->delete();

		$count++;
            }

            Flash::success(Lang::get('codalia.journal::lang.action.delete_success', ['count' => $count]));
         }

        return $this->listRefresh();
    }

    public function index_onSetStatus()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	  $status = post('status');
	  $count = 0;
	  foreach ($checkedIds as $recordId) {
	      $article = Article::find($recordId);

	      if ($article->checked_out) {
		  Flash::error(Lang::get('codalia.journal::lang.action.checked_out_item', ['name' => $article->title]));
		  return $this->listRefresh();
	      }

	      $article->status = $status;
	      $article->published_up = Article::setPublishingDate($article);
	      // Important: Do not use the save() or update() methods here as the events (afterSave etc...) will be 
	      //            triggered as well and may have unexpected behaviors.
	      \Db::table('codalia_journal_articles')->where('id', $recordId)->update(['status' => $status,
										   'published_up' => Article::setPublishingDate($article)]);
	      $count++;
	  }

	  $toRemove = ($status == 'archived') ? 'd' : 'ed';

	  Flash::success(Lang::get('codalia.journal::lang.action.'.rtrim($status, $toRemove).'_success', ['count' => $count]));
	}

	return $this->listRefresh();
    }

    public function index_onCheckIn()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();

	// Ensures one or more items are selected.
	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
	  $count = 0;
	  foreach ($checkedIds as $recordId) {
	      JournalHelper::instance()->checkIn((new Article)->getTable(), null, $recordId);
	      $count++;
	  }

	  Flash::success(Lang::get('codalia.journal::lang.action.check_in_success', ['count' => $count]));
	}

	return $this->listRefresh();
    }

    public function update_onSave($recordId = null, $context = null)
    {
	// Calls the original update_onSave method
	return $this->asExtension('FormController')->update_onSave($recordId, $context);
    }

    public function loadScripts()
    {
	$preferences = \Backend\Models\UserPreference::forUser()->get('backend::backend.preferences');
	$this->addJs('/plugins/codalia/journal/assets/js/lang/'.$preferences['locale'].'.js');
	$this->addJs('/plugins/codalia/journal/assets/js/article.js');
	$this->addJs('/plugins/codalia/journal/assets/js/codalia-ajax.js');
	//$this->addJs('/plugins/codalia/journal/assets/js/codalia-dynamic-item.js');
	$this->addJs('/plugins/codalia/journal/assets/js/codalia-field.js');
	$this->addJs('/plugins/codalia/journal/assets/js/fields.js');
	$this->addCss(url('plugins/codalia/journal/assets/css/extra.css'));
    }
}
