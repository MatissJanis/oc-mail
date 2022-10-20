<?php namespace Mja\Mail;

use Backend;
use Event;
use Mail;
use Mja\Mail\Controllers\Mail as MailController;
use Mja\Mail\Controllers\Template as TemplateController;
use Mja\Mail\Models\Email;
use Mja\Mail\Models\Settings;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use System\Models\MailTemplate;

/**
 * Plugin Information File
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
            'name'        => 'mja.mail::lang.plugin_name',
            'description' => 'mja.mail::lang.plugin_description',
            'author'      => 'Matiss Janis Aboltins',
            'homepage'    => 'http://mja.lv/',
            'icon'        => 'icon-envelope',
        ];
    }

    public function registerNavigation()
    {
        return [
            'mail' => [
                'label'       => 'mja.mail::lang.controllers.mail.title',
                'url'         => Backend::url('mja/mail/mail'),
                'icon'        => 'icon-paper-plane-o',
                'permissions' => ['mja.mail.*'],
                'order'       => 500,

                'sideMenu'    => [
                    'template' => [
                        'label'       => 'mja.mail::lang.controllers.template.title',
                        'icon'        => 'icon-database',
                        'url'         => Backend::url('mja/mail/template'),
                        'permissions' => ['mja.mail.template'],
                    ],
                    'mail'     => [
                        'label'       => 'mja.mail::lang.controllers.mail.mails_sent',
                        'icon'        => 'icon-paper-plane',
                        'url'         => Backend::url('mja/mail/mail'),
                        'permissions' => ['mja.mail.mail'],
                    ],
                    'settings' => [
                        'label' => 'mja.mail::lang.controllers.settings.title',
                        'icon'  => 'icon-cog',
                        'url'   => Backend::url('system/settings/update/mja/mail/settings'),
                    ],
                ],
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'mja.mail::lang.controllers.settings.settings_title',
                'description' => 'mja.mail::lang.controllers.settings.description',
                'category'    => SettingsManager::CATEGORY_MAIL,
                'icon'        => 'icon-paper-plane-o',
                'class'       => Settings::class,
                'keywords'    => 'email logger log stats statistics',
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'Mja\Mail\FormWidgets\EmailGrid' => [
                'label' => 'mja.mail::lang.formwidget.title',
                'code'  => 'emailgrid',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'mja.mail.template' => ['tab' => 'mja.mail::lang.controllers.mail.title', 'label' => 'mja.mail::lang.permission.template'],
            'mja.mail.mail'     => ['tab' => 'mja.mail::lang.controllers.mail.title', 'label' => 'mja.mail::lang.permission.mail'],
        ];
    }

    /**
     * Attach event listeners on boot.
     * @return void
     */
    public function boot()
    {
        $logEmailOpens = (bool) Settings::get('log_email_opens', true);

        // Before send: attach blank image that will track mail opens
        Event::listen('mailer.prepareSend', function ($self, $view, $message) use ($logEmailOpens) {
            $symfony = $message->getSymfonyMessage();

            $mail = Email::create([
                'code'     => $view ? substr($view, 0, 255) : null,
                'to'       => $symfony->getTo(),
                'cc'       => $symfony->getCc(),
                'bcc'      => $symfony->getBcc(),
                'subject'  => $symfony->getSubject(),
                'body'     => $symfony->getHtmlBody(),
                'sender'   => $symfony->getSender(),
                'reply_to' => $symfony->getReplyTo(),
                'date'     => $symfony->getDate()
            ]);

            if ($logEmailOpens) {
                $url = Backend::url('mja/mail/image/image', [
                    'id'   => $mail->id,
                    'hash' => $mail->hash . '.png',
                ]);

                $symfony->setBody($symfony->html($symfony->getHtmlBody() . '<img src="'. $url .'" style="display:none;width:0;height:0;" />')->getBody());
            }
        });

        // After send: log the result
        Event::listen('mailer.send', function ($self, $view, $message) {
            $symfony = $message->getSymfonyMessage();

            $mail = Email::where('code', $view)
                ->get()
                ->last();

            if ($mail === null) {
                return;
            }

            $mail->sent = true;
            $mail->save();
        });

        // Use for the mails sent list filter
        Event::listen('backend.filter.extendScopesBefore', function ($filter) {
            if (!($filter->getController() instanceof MailController)) {
                return;
            }

            $filter->scopes['views']['options'] = [];

            $templates = MailTemplate::get();
            foreach ($templates as $template) {
                $filter->scopes['views']['options'][$template->code] = $template->code;
            }
        });

        if ($logEmailOpens === false) {
            MailController::extendFormFields(function ($controller) {
                $controller->removeField('opens@preview');
            });

            MailController::extendListColumns(function ($controller) {
                $controller->removeColumn('times_opened');
            });

            TemplateController::extendListColumns(function ($controller) {
                $controller->removeColumn('times_opened');
                $controller->removeColumn('last_open');
            });
        }

        // Extend the mail template so that we could have the number of sent emails
        // in the template list of this plugin.
        MailTemplate::extend(function ($model) {

            // Email relation
            $model->addDynamicMethod('emails', function () use ($model) {
                return $model->hasMany('Mja\Mail\Models\Email', 'code', 'code');
            });

            // Emails sent
            $model->addDynamicMethod('getTimesSentAttribute', function () use ($model) {
                return (int) $model->emails()->count();
            });

            // Email opens
            $model->addDynamicMethod('getTimesOpenedAttribute', function () use ($model) {
                return (int) $model->opens()->count();
            });

            // Last time sent
            $model->addDynamicMethod('getLastSentAttribute', function () use ($model) {
                $email = $model->emails()->orderBy('id', 'desc')->first();
                return $email ? $email->created_at : null;
            });

            // Last time opened
            $model->addDynamicMethod('getLastOpenAttribute', function () use ($model) {
                $emails    = $model->emails()->get();
                $last_open = null;

                foreach ($emails as $email) {
                    $lo = $email->opens()->orderBy('id', 'desc')->first();
                    if ($lo && ($lo->created_at < $last_open || $last_open === null)) {
                        $last_open = $lo->created_at;
                    }

                }

                return $last_open;
            });

            // Get the email opens by template
            $model->addDynamicMethod('opens', function () use ($model) {
                return $model->hasManyThrough('Mja\Mail\Models\EmailOpens', 'Mja\Mail\Models\Email', 'code', 'email_id', 'code');
            });
        });
    }
}
