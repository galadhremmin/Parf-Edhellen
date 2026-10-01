<?php

namespace App\Console\Commands;

use App\Models\AuthorizationProvider;
use Illuminate\Console\Command;

class TestIdentityProviderCommand extends Command
{
    protected $signature = 'ed:test-identity-provider';

    protected $description = 'Adds the local test identity provider to the login page. Refuses outside APP_ENV=local.';

    public function handle(): int
    {
        if (! app()->isLocal()) {
            $this->error('The test identity provider lets anyone be anyone. It only runs with APP_ENV=local.');

            return Command::FAILURE;
        }

        $provider = AuthorizationProvider::withTrashed()->firstOrNew(['name_identifier' => 'test']);
        $provider->name = 'Test';
        $provider->logo_file_name = 'test.svg';
        $provider->save();
        $provider->restore();

        $this->info(sprintf('The test identity provider is in place (id %d).', $provider->id));

        if (! config('ed.identity_providers.test')) {
            $this->warn('Set ED_TEST_IDENTITY_PROVIDER=true in .env to switch it on.');
        }

        return Command::SUCCESS;
    }
}
