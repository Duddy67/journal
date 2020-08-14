<?php namespace Codalia\Journal\Controllers;

use Flash;
use Lang;
use BackendMenu;
use Backend\Classes\Controller;
use Codalia\Journal\Models\Field;
use BackendAuth;
use Codalia\Journal\Helpers\JournalHelper;

/**
 * Extra Fields Back-end Controller
 */
class Fields extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Codalia.Journal', 'journal', 'fields');
    }


    public function index()
    {
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();
	$this->addCss(url('plugins/codalia/journal/assets/css/extra.css'));
	// Unlocks the checked out items of this user (if any).  
	JournalHelper::instance()->checkIn((new Field)->getTable(), BackendAuth::getUser());
	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
    }

    public function update($recordId = null, $context = null)
    {
	$field = Field::find($recordId);
	$user = BackendAuth::getUser();

	// Checks for check out matching.
	if ($field->checked_out && $user->id != $field->checked_out) {
	    Flash::error(Lang::get('codalia.journal::lang.action.check_out_do_not_match'));
	    return redirect('backend/codalia/journal/fields');
	}

        if ($context == 'edit') {
	    // Locks the item for this user.
	    JournalHelper::instance()->checkOut((new Field)->getTable(), $user, $recordId);
	}

        return $this->asExtension('FormController')->update($recordId, $context);
    }

    public function listOverrideColumnValue($record, $columnName, $definition = null)
    {
        if ($record->checked_out && $columnName == 'name') {
	    return JournalHelper::instance()->getCheckInHtml($record, BackendAuth::findUserById($record->checked_out));
	}
    }

    public function index_onDelete()
    {
	// Needed for the status column partial.
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();

	if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {
            $count = 0;
            foreach ($checkedIds as $recordId) {
	        // Checks that field does exist.
                if (!$field = Field::find($recordId)) {
                    continue;
                }

		if ($field->checked_out) {
		    Flash::warning(Lang::get('codalia.journal::lang.action.checked_out_item', ['name' => $field->name]));
		    return;
		}

                $field->delete();

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
	      $field = Field::find($recordId);

	      if ($field->checked_out) {
		  Flash::error(Lang::get('codalia.journal::lang.action.checked_out_item', ['name' => $field->name]));
		  return $this->listRefresh();
	      }

	      $field->status = $status;
	      // Important: Do not use the save() or update() methods here as the events (afterSave etc...) will be 
	      //            triggered as well and may have unexpected behaviors.
	      \Db::table('codalia_journal_fields')->where('id', $recordId)->update(['status' => $status]);

	      $count++;
	  }

	  Flash::success(Lang::get('codalia.journal::lang.action.'.rtrim($status, 'ed').'_success', ['count' => $count]));
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
	      JournalHelper::instance()->checkIn((new Field)->getTable(), null, $recordId);
	      $count++;
	  }

	  Flash::success(Lang::get('codalia.journal::lang.action.check_in_success', ['count' => $count]));
	}

	return $this->listRefresh();
    }

    public function listInjectRowClass($record, $definition = null)
    {
        $class = '';

        if ($record->checked_out) {
	    $class = 'safe disabled nolink';
	}

	return $class;
    }

    public function loadScripts()
    {
	$preferences = \Backend\Models\UserPreference::forUser()->get('backend::backend.preferences');
	$this->addJs('/plugins/codalia/journal/assets/js/lang/'.$preferences['locale'].'.js');
	$this->addJs('/plugins/codalia/journal/assets/js/field.js');
	$this->addJs('/plugins/codalia/journal/assets/js/codalia-ajax.js');
	$this->addJs('/plugins/codalia/journal/assets/js/codalia-dynamic-item.js');
	$this->addJs('/plugins/codalia/journal/assets/js/options.js');
	$this->addCss(url('plugins/codalia/journal/assets/css/extra.css'));
    }
}
