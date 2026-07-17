<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Refresh the application instance.
     *
     * Overrides the parent to ensure phpunit.xml environment variable values take
     * effect. PHPUnit's `<env>` tags use `putenv()`, which does not update
     * `$_ENV` or `$_SERVER`. Laravel's `env()` helper resolves values through
     * the Dotenv immutable repository, which checks `$_SERVER` and `$_ENV`
     * before `getenv()`. If system-level environment variables (e.g. from Herd)
     * are present in those superglobals, they silently override the intended
     * test configuration.
     *
     * This method synchronises the critical phpunit.xml values into `$_SERVER`
     * before the new application instance is bootstrapped.
     */
    protected function refreshApplication(): void
    {
        $overrides = [
            'APP_ENV' => 'testing',
            'APP_MAINTENANCE_DRIVER' => 'file',
            'BCRYPT_ROUNDS' => '4',
            'BROADCAST_CONNECTION' => 'null',
            'CACHE_STORE' => 'array',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'MAIL_MAILER' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
            'PULSE_ENABLED' => 'false',
            'TELESCOPE_ENABLED' => 'false',
            'NIGHTWATCH_ENABLED' => 'false',
        ];

        foreach ($overrides as $key => $value) {
            $_SERVER[$key] = $value;
            $_ENV[$key] = $value;
        }

        parent::refreshApplication();
    }
}
