<?php namespace Codalia\Journal\Controllers;

use Flash;
use Lang;
use BackendMenu;
use Backend\Classes\Controller;
use Codalia\Journal\Models\ExtraField;
use BackendAuth;
use Codalia\Journal\Helpers\JournalHelper;

/**
 * Extra Fields Back-end Controller
 */
class ExtraFields extends Controller
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

        BackendMenu::setContext('Codalia.Journal', 'journal', 'extrafields');
    }


    public function index()
    {
	$this->vars['statusIcons'] = JournalHelper::instance()->getStatusIcons();
	$this->addCss(url('plugins/codalia/journal/assets/css/extra.css'));
	// Unlocks the checked out items of this user (if any).  
	JournalHelper::instance()->checkIn((new ExtraField)->getTable(), BackendAuth::getUser());
	// Calls the parent method as an extension.
        $this->asExtension('ListController')->index();
    }

    public function update($recordId = null, $context = null)
    {
	$extraField = ExtraField::find($recordId);
	$user = BackendAuth::getUser();

	// Checks for check out matching.
	if ($extraField->checked_out && $user->id != $extraField->checked_out) {
	    Flash::error(Lang::get('codalia.journal::lang.action.check_out_do_not_match'));
	    return redirect('backend/codalia/journal/extrafields');
	}

        if ($context == 'edit') {
	    // Locks the item for this user.
	    JournalHelper::instance()->checkOut((new ExtraField)->getTable(), $user, $recordId);
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
	        // Checks that extra field does exist.
                if (!$extraField = ExtraField::find($recordId)) {
                    continue;
                }

		if ($extraField->checked_out) {
		    Flash::warning(Lang::get('codalia.journal::lang.action.checked_out_item', ['name' => $extraField->name]));
		    return;
		}

                $extraField->delete();

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
	      $extraField = ExtraField::find($recordId);

	      if ($extraField->checked_out) {
		  Flash::error(Lang::get('codalia.journal::lang.action.checked_out_item', ['name' => $extraField->name]));
		  return $this->listRefresh();
	      }

	      $extraField->status = $status;
	      // Important: Do not use the save() or update() methods here as the events (afterSave etc...) will be 
	      //            triggered as well and may have unexpected behaviors.
	      \Db::table('codalia_journal_extra_fields')->where('id', $recordId)->update(['status' => $status]);

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
	      JournalHelper::instance()->checkIn((new ExtraField)->getTable(), null, $recordId);
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
	$this->addJs('/plugins/codalia/journal/assets/js/extrafield.js');
	$this->addJs('/plugins/codalia/journal/assets/js/codalia-ajax.js');
	$this->addJs('/plugins/codalia/journal/assets/js/codalia-dynamic-item.js');
	$this->addJs('/plugins/codalia/journal/assets/js/multivalue.js');
	$this->addCss(url('plugins/codalia/journal/assets/css/extra.css'));
    }
}
