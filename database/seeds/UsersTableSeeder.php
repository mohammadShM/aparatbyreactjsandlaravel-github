<?php

use App\channel;
use App\User;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (User::count()) {
            // remove seeder and create seeder fake data
            User::truncate();
            Channel::truncate();
        }
        $this->createAdminUser();
        for ($i = 1; $i <= 9; $i++) {
            $this->createUser($i);
        }
    }

    private function createAdminUser()
    {
        $user = factory(User::class)->make([
            'type' => User::TYPE_ADMIN,
            'name' => 'مدیر اصلی',
            'email' => 'admin@aparat.me',
            'mobile' => '+989000000000'
        ]);
        $user->save();
        $this->command->info('create admin user');
    }

    private function createUser($num = 1)
    {
        $user = factory(User::class)->make([
            'name' => 'کاربر' . ' ' . $num,
            'email' => 'user' . $num . '@aparat.me',
            'mobile' => '+989' . str_repeat($num, 9)
        ]);
        $user->save();
        $this->command->info('create general user' . $num);
    }
}
