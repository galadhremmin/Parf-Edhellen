<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Security\AccountManager;
use App\Security\RoleConstants;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProvisionRootAccountCommand extends Command
{
    protected $signature = 'ed:provision-root
        {email : E-mail address of the new root account}
        {--name= : Display name for the new root account}
        {--password= : Password for the new root account (you will be prompted securely if omitted)}
        {--force : Provision even though accounts already exist}';

    protected $description = 'Provision the initial Root account. Refuses to run if a Root account already exists.';

    public function handle(AccountManager $accountManager): int
    {
        // Fail closed: there must never be a second path to a Root account.
        if ($accountManager->getRootAccount() !== null) {
            $this->error('A root account already exists. Refusing to provision another one.');

            return Command::FAILURE;
        }

        if (Account::count() > 0 && ! $this->option('force')) {
            $this->error('Accounts already exist. If you are sure, re-run with --force.');

            return Command::FAILURE;
        }

        if ($this->option('force')
            && ! $this->confirm('Accounts already exist but none is root. Provision a new root account anyway?', false)) {
            $this->warn('Aborted.');

            return Command::FAILURE;
        }

        $email = trim((string) $this->argument('email'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('The e-mail address is invalid.');

            return Command::FAILURE;
        }

        if ($accountManager->getMasterAccountByEmail($email) !== null || Account::where('email', $email)->exists()) {
            $this->error('An account with that e-mail address already exists.');

            return Command::FAILURE;
        }

        $password = $this->option('password');
        if (empty($password)) {
            $password = $this->promptForPassword();
            if ($password === null) {
                return Command::FAILURE;
            }
        } elseif (strlen($password) < 8) {
            $this->error('The password must be at least 8 characters.');

            return Command::FAILURE;
        }

        $account = $accountManager->createAccount(
            $email,
            null,
            null,
            $password,
            $this->option('name') ?: null
        );
        $account->addMembershipTo(RoleConstants::Root);

        // The operator asserted this e-mail address, so the account starts verified;
        // admin surfaces require a verified e-mail.
        $account->email_verified_at = Carbon::now();
        $account->save();

        $this->info(sprintf('Root account provisioned: %s (id %d).', $account->email, $account->id));

        return Command::SUCCESS;
    }

    private function promptForPassword(): ?string
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $password = $this->secret('Password for the new root account (min 8 characters)');
            $confirm = $this->secret('Confirm password');

            if (strlen($password) < 8) {
                $this->error('The password must be at least 8 characters.');
                continue;
            }

            if ($password !== $confirm) {
                $this->error('The passwords do not match.');
                continue;
            }

            return $password;
        }

        $this->error('Too many attempts. Aborted.');

        return null;
    }
}
