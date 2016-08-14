<?php

use Orchestra\Testbench\TestCase;

class TestBase extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->resetDatabase();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => ''
        ]);

        $app['config']->set('chronicle.user_model', 'User');
    }

    protected function getPackageProviders($app)
    {
        return ['Kenarkose\Chronicle\ChronicleServiceProvider'];
    }

    protected function resetDatabase()
    {
        // Relative to the testbench app folder: vendors/orchestra/testbench/src/fixture
        $migrationsPath = 'tests/_migrations';
        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

        // Migrate
        $artisan->call('migrate', [
            '--database' => 'sqlite',
            '--realpath'     => $migrationsPath,
        ]);
    }
}