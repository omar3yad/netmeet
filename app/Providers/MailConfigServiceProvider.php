<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
public function boot()
{
    if (file_exists(storage_path('installed'))) {
        config([
            'mail.mailers.smtp.host' => getSetting('MAIL_HOST'),
            'mail.mailers.smtp.port' => getSetting('MAIL_PORT'),
            'mail.mailers.smtp.username' => getSetting('MAIL_USERNAME'),
            'mail.mailers.smtp.password' => getSetting('MAIL_PASSWORD'),
            'mail.mailers.smtp.encryption' => getSetting('MAIL_ENCRYPTION'),
            'mail.mailers.smtp.stream' => [
                'ssl' => [
                    'allow_self_signed' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ],
            'mail.from.address' => getSetting('MAIL_FROM_ADDRESS'),
            'mail.from.name' => getSetting('APPLICATION_NAME'),
        ]);
    }
}
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
