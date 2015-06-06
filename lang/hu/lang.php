<?php

return [
    'plugin_name' => 'Levelezés',
    'plugin_description' => 'E-mail statisztika és naplózás.',

    'formwidget' => [
        'title' => 'Levél rács',
        'description' => 'Rácsok létrehozás a levelekhez',
    ],

    'permission' => [
        'template' => 'Téma statisztika megtekintése',
        'mail' => 'Levél statisztika megtekintése',
    ],

    'controllers' => [
        'mail' => [
            'title' => 'Levél naplózás',
            'mails_sent' => 'Levelek',
            'preview' => 'View mail information',
            'return' => 'Vissza a levél listázáshoz',

            'stats_sent' => 'Elküldött',
            'stats_bounced' => 'Visszapattanó',
            'stats_total_sent' => 'Összes elküldött',
            'stats_total_opens' => 'Összes megnyitott',
        ],
        'template' => [
            'title' => 'Témák',
            'stats_title' => 'Statisztika',
            'opens' => 'Megnyitott levelek',
            'opens_desc' => 'Az adott témával rendelkező levelek száma, amiket az elmúlt 7 napban nyitottak meg.',
        ],
    ],

    'models' => [
        'email' => [
            'tab_emails' => 'Levelek',
            'tab_opens' => 'Megnyitott',
            'tab_response' => 'Válasz',

            'id' => 'Azonosító',
            'code' => 'Kód',
            'to_email' => 'Címzett',
            'cc_emails' => 'Másolat',
            'bcc_emails' => 'Titkos másolat',
            'subject' => 'Tárgy',
            'body' => 'Törzs',
            'sender' => 'Küldő',
            'reply_to' => 'Válasz neki',
            'date' => 'Elküldés dátuma',
            'response' => 'Válasz',
            'sent' => 'Küldés',
            'times_opened' => 'Megnyitások száma',
            'times_opened_desc' => 'Az ügyfél által',
            'last_opened' => 'Utolsó megnyitás',
            'created_at' => 'Létrehozva',
            'updated_at' => 'Módosítva',

            'show_bounced' => 'Csak a visszapattanók mutatása',
        ],

        'emailopens' => [
            'id' => 'Azonosító',
            'ip' => 'IP cím',
            'created_at' => 'Dátum és idő',
        ],

        'template' => [
            'id' => 'Azonosító',
            'code' => 'Kód',
            'sent' => 'Elküldött levelek',
            'opens' => 'Megnyitások száma',
            'last_send' => 'Utoljára elküldés ideje',
            'last_open' => 'Utoljára megnyitás ideje',
        ],
    ],
];
