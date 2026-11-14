<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateInitialAdmin extends Command
{
    protected $signature = 'sgpe:admin:create';

    protected $description = 'Create the first national administrator';

    public function handle(): int
    {
        if (User::where('role', UserRole::Admin->value)
            ->where('is_active', true)
            ->exists()) {
            $this->error('Un administrateur existe déjà.');

            return self::FAILURE;
        }

        $name = $this->ask('Nom complet');
        $email = $this->ask('Adresse e-mail');
        $password = $this->secret('Mot de passe');
        $passwordConfirmation = $this->secret('Confirmer le mot de passe');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::Admin,
            'service_id' => null,
            'is_active' => true,
        ]);

        $this->info('Administrateur créé.');

        return self::SUCCESS;
    }
}
