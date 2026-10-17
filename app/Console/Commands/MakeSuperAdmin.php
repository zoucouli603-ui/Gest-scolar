<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeSuperAdmin extends Command
{
    protected $signature = 'app:make-super-admin {email} {--force : Skip the confirmation prompt}';

    protected $description = 'Grant the private YELENSCOL super-administrator role to an existing account';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            $this->error('Aucun compte ne correspond à cette adresse.');

            return self::FAILURE;
        }

        if (!$this->option('force') && !$this->confirm('Accorder l’accès super-administrateur à '.$user->email.' ?')) {
            return self::SUCCESS;
        }

        $user->school_id = null;
        $user->role = 'super_admin';
        $user->save();

        $this->info('Le rôle super-administrateur a été accordé. Cet utilisateur sera redirigé vers l’espace privé de contrôle.');

        return self::SUCCESS;
    }
}
