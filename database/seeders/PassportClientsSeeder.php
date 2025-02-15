<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;

class PassportClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientRepository = app(ClientRepository::class);

        $personalClient = $clientRepository->createPersonalAccessClient(
            1, 'Personal Access Client', env('APP_URL')
        );

        $passwordClient = $clientRepository->createPasswordGrantClient(
            1, 'Password Grant Client', env('APP_URL')
        );

        if ($personalClient) {
            $secret = $personalClient->secret;
            $this->command->info('Personal Access Client secret: '.$secret);
        } else {
            $this->command->error('Failed to create personal access client.');
        }

        if ($passwordClient) {
            $secret = $passwordClient->secret;
            $this->command->info('Password Grant Client secret: '.$secret);
        } else {
            $this->command->error('Failed to create password grant client.');
        }
    }
}
