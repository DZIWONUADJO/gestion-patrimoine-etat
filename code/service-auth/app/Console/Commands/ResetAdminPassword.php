<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResetAdminPassword extends Command
{
    protected $signature = 'sgpe:admin:password-reset';

    protected $description = 'Reset the password of an active administrator';

    public function handle(): int
    {
        $email = trim((string) $this->ask('Adresse e-mail de l’administrateur'));

        $emailValidator = Validator::make(['email' => $email], [
            'email' => ['required', 'email'],
        ]);

        if ($emailValidator->fails()) {
            $this->error($emailValidator->errors()->first());

            return self::FAILURE;
        }

        $admin = User::query()
            ->where('email', $email)
            ->where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->first();

        if ($admin === null) {
            $this->error('Aucun administrateur actif trouvé pour cette adresse.');

            return self::FAILURE;
        }

        $password = $this->secret('Nouveau mot de passe');
        $passwordConfirmation = $this->secret('Confirmer le nouveau mot de passe');

        $passwordValidator = Validator::make([
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($passwordValidator->fails()) {
            $this->error($passwordValidator->errors()->first());

            return self::FAILURE;
        }

        DB::transaction(function () use ($admin, $password): void {
            $admin->password = $password;
            $admin->save();
            $admin->tokens()->delete();
        });

        $this->info('Mot de passe administrateur réinitialisé.');

        return self::SUCCESS;
    }
}
