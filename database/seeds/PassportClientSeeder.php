<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PassportClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createPersonalClient();
        $this->createPasswordClient();
    }

    private function createPersonalClient()
    {
        DB::table('oauth_clients')->insert([
            'user_id' => null,
            'name' => 'aparatbyreactjsandlaravel Personal Grant Client',
            'redirect' => env('APP_URL'),
            'secret' => 'yJJLSAmqoWMOF9wKmteGtIruTHTcKh6N2VVKq03Q',
            'personal_access_client' => 1,
            'password_client' => 0,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        DB::table('oauth_personal_access_clients')->insert([
            'client_id' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function createPasswordClient()
    {
        DB::table('oauth_clients')->insert([
            'user_id' => null,
            'name' => 'aparatbyreactjsandlaravel Password Grant Client',
            'redirect' => env('APP_URL'),
            'secret' => 'e2PR6wgAy963dppFdp9gfYpeQ9x4tC3F6zK7Svrk',
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

}
