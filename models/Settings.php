<?php namespace Mja\Mail\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode   = 'mja_mail_settings';
    public $settingsFields = 'fields.yaml';
}
