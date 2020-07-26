<?php namespace Codalia\Journal\Models;

use October\Rain\Database\Model;

class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'codalia_journal_settings';

    public $settingsFields = 'fields.yaml';

    public $rules = [
        'show_breadcrumb' => ['boolean'],
    ];
}
