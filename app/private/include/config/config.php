<?php

if (file_exists(INCLUDE_PATH . 'config/dictionaries')) {
    include_once_full_directory(INCLUDE_PATH . 'config/dictionaries');
}

// GestyMVC::addModule('module-name');

GestyMVC::setConfig('langs', ['es']);
GestyMVC::setConfig('default_lang', 'es');
GestyMVC::setConfig('use_prefix_for_default_lang', false);
GestyMVC::setConfig('force_https', GestyMVC::env('FORCE_HTTPS'));
GestyMVC::setConfig('time_zone', 'Europe/Madrid');

GestyMVC::setConfig('disable_https_on_background_tasks', true);
GestyMVC::setConfig('session_digest_token', GestyMVC::env('SESSION_DIGEST_TOKEN'));
GestyMVC::setConfig('form_helper_token', GestyMVC::env('FORM_HELPER_TOKEN'));
GestyMVC::setConfig('mysql_log_all_queries_on_debug', GestyMVC::env('MYSQL_LOG_ALL_QUERIES_ON_DEBUG'));
GestyMVC::setConfig('mysql_collation', GestyMVC::env('MYSQL_COLLATION'));
GestyMVC::setConfig('no_session_host_name', GestyMVC::env('NO_SESSION_HOST_NAME'));

GestyMVC::setConfig('user_digest_token', GestyMVC::env('USER_DIGEST_TOKEN'));
GestyMVC::setConfig('password_valid_for_minutes', 1296000); // 900 days
GestyMVC::setConfig('password_history_days', 90);
GestyMVC::setConfig('password_reset_validity_minutes', 30);
GestyMVC::setConfig('password_min_length', 8);
GestyMVC::setConfig('password_requires_letters', true);
GestyMVC::setConfig('password_requires_numbers', true);
GestyMVC::setConfig('password_requires_both_cases', true);
GestyMVC::setConfig('password_reset_min_interval_minutes', 60);
GestyMVC::setConfig('max_login_failed_attempts', 3);

GestyMVC::setConfig('x_frame_options', 'SAMEORIGIN');

GestyMVC::setConfig('db', [
    'host' => GestyMVC::env('MYSQL_SERVER'),
    'user' => GestyMVC::env('MYSQL_USER'),
    'password' => GestyMVC::env('MYSQL_PASSWORD'),
    'schema' => GestyMVC::env('MYSQL_DATABASE'),
    'port' => GestyMVC::env('MYSQL_PORT'),
    'socket' => GestyMVC::env('MYSQL_SOCKET'),
    'table_prefix' => GestyMVC::env('MYSQL_TABLE_PREFIX'),
]);

GestyMVC::setConfig('mail', [
    'mail_function' => GestyMVC::env('SMTP_MAIL_FUNCTION'),
    'host' => GestyMVC::env('SMTP_HOST'),
    'user' => GestyMVC::env('SMTP_USER'),
    'password' => GestyMVC::env('SMTP_PASSWORD'),
    'port' => intval(GestyMVC::env('SMTP_PORT')),
    'ssl' => GestyMVC::env('SMTP_SSL'),
    'SMTPAutoTLS' => GestyMVC::env('SMTP_AUTO_TLS'),
    'timeout' => intval(GestyMVC::env('SMTP_TIMEOUT')),
    'email' => GestyMVC::env('SMTP_EMAIL'),
    'display' => GestyMVC::env('SMTP_DISPLAY'),
    'auth_type' => GestyMVC::env('SMTP_AUTH_TYPE'),
]);

GestyMVC::setConfig('website_name', GestyMVC::env('WEBSITE_NAME'));

GestyMVC::setConfig('dev_js_folder', 'static/js');
GestyMVC::setConfig('pro_js_folder', 'static/js');
GestyMVC::setConfig('dev_css_folder', 'static/css');
GestyMVC::setConfig('pro_css_folder', 'static/css');
GestyMVC::setConfig('php_exec_path', 'php');;

GestyMVC::setConfig('staticRootFolders', ['content', 'static', 'assets']);
GestyMVC::setConfig('static_cache_ts', floor(time() / 60 / 60 / 24));

GestyMVC::setConfig(
    'cms_tags',
    /** @lang text */
    '<div><br><b><p><u><ul><li><ol><a><strong><em><i><img><h1><h2><h3><h4><h5><h6><span><table><tr><td><th>'
);
GestyMVC::setConfig('pagination_accumulate_orders', false);
GestyMVC::setConfig('accept_64_bit_dates', true);
define('STATIC_VERSION', GestyMVC::env('STATIC_VERSION'));
GestyMVC::setConfig('use_html5_date_inputs', true);
GestyMVC::setConfig('send_email_to_fake_recipient', GestyMVC::env('SEND_EMAIL_TO_FAKE_RECIPIENT'));
GestyMVC::setConfig('send_error_logs_to_recipient', GestyMVC::env('SEND_ERROR_LOGS_TO_RECIPIENT'));
GestyMVC::setConfig('prefix_email_subjects', GestyMVC::env('PREFIX_EMAIL_SUBJECTS'));

GestyMVC::setConfig('log_to_syslog', GestyMVC::env('LOG_TO_SYSLOG'));
GestyMVC::setConfig('syslog_add_prefix', GestyMVC::env('SYSLOG_ADD_PREFIX'));
GestyMVC::setConfig('syslog_app_prefix', GestyMVC::env('SYSLOG_APP_PREFIX'));
GestyMVC::setConfig('syslog_to_file', GestyMVC::env('SYSLOG_TO_FILE'));
GestyMVC::setConfig('save_debug_logs_to_database', GestyMVC::env('SAVE_DEBUG_LOGS_TO_DATABASE'));
//GestyMVC::setConfig('debugLogFoldersExcludedFromDatabase', ['model-instances']);

GestyMVC::setConfig('pagination', [
    'template_prefix' => '<div class="d-flex flex-stack flex-wrap"><div class="fs-6 fw-bold text-gray-700">%SUMMARY%</div><ul class="pagination">',
    'template_previous_current' => '<li class="page-item previous"><a href="%LINK%" class="page-link"><i class="previous"></i></a></li>',
    'template_previous_other' => '<li class="page-item previous"><a href="%LINK%" class="page-link"><i class="previous"></i></a></li>',
    'template_page_current' => '<li class="page-item active"><a href="%LINK%" class="page-link">%TEXT%</a></li>',
    'template_page_other' => '<li class="page-item"><a href="%LINK%" class="page-link">%TEXT%</a></li>',
    'template_next_current' => '<li class="page-item next"><a href="%LINK%" class="page-link"><i class="next"></i></a></li>',
    'template_next_other' => '<li class="page-item next"><a href="%LINK%" class="page-link"><i class="next"></i></a></li>',
    'template_suffix' => '</ul></div>',
]);

GestyMVC::setConfig('autoconfig_on_default_models', true);
GestyMVC::setConfig('autoconfig_on_auto_models', true);
GestyMVC::setConfig('do_not_save_full_path_on_media', true);
GestyMVC::setConfig('storage', [
    'default_mode' => 'local',
    'keys' => [
        'files' => GestyMVC::env('STORAGE_FILE_VIEW_TOKEN'),
        'pictures' => GestyMVC::env('STORAGE_PICTURE_VIEW_TOKEN'),
    ],
    'modes' => [
        'local' => [
            'active' => true,
            'class' => '\GestyMVC\Storage\Local\Storage',
            'files' => [
                'path' => CONTENT_PATH . 'files' . DIRECTORY_SEPARATOR,
                'public' => true,
            ],
            'pictures' => [
                'path' => CONTENT_PATH . 'img' . DIRECTORY_SEPARATOR,
                'public' => true,
            ],
        ],
        //'aws' => [
        //    'active' => true,
        //    'class' => '\GestyMVC\Storage\AWS\Storage',
        //    'any' => [
        //        'bucket' => GestyMVC::env('AMAZON_S3_BUCKET'),
        //        'region' => GestyMVC::env('AMAZON_S3_REGION'),
        //        'key' => GestyMVC::env('AMAZON_S3_KEY'),
        //        'secret' => GestyMVC::env('AMAZON_S3_SECRET'),
        //        'public' => false,
        //    ],
        //],
    ]
]);

GestyMVC::setConfig('allowedHtml5InputTypes', ['date', 'time', 'color']);

include('alias-controllers.php');

GestyMVC::setConfig('use_gravatar_as_default_avatar_url', false);
GestyMVC::setConfig('default_avatar_url', '/static/img/avatars/blank.png');
GestyMVC::setConfig('max_otp_seconds', 60 * 60 * 24 * 30 * 6 /* Six months */);
GestyMVC::setConfig('reflective_root_url', GestyMVC::env('REFLECTIVE_ROOT_URL'));

GestyMVC::setConfig('trust_only_default_host', true); // Ignored if TRUSTED_HTTP_HOSTS is set

// Callbacks
GestyMVC\Model\Callback::register('User', App\Callback\User::class);
GestyMVC\Model\Callback::register('CanonicalUrl', App\Callback\CanonicalUrl::class);
GestyMVC::setConfig('customTwigExtensions', [
    GestyMVC\Twig\ParticipantUrlExtension::class
]);
