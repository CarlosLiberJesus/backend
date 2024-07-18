<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserRoles;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar as Avatar;
use Illuminate\Support\Facades\Date;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $this->call('UsersTableSeeder');
        $avatarPath = './public/avatars/';

        $user = User::create(['uuid' => 'b859b662-1ae3-416a-b7c4-83451c30a4ad',
        'app_id' => 1, 'name' => "Carlos Emanuel Gonçalves de Jesus",'email' => "bewhy.org@gmail.com",
        'password' => Hash::make('qwerty'), 'email_verified_at' => Date::now()]);
        //(new Avatar(include './config/laravolt/avatar.php'))->create('Carlos Jesus')->save($avatarPath.'b859b662-1ae3-416a-b7c4-83451c30a4ad.png');
        $fileName = 'b859b662-1ae3-416a-b7c4-83451c30a4ad.png';
        UserProfile::create(['user_id' => $user->id, 'status_id'=> 1, 'freguesia_id' => 1304, 'avatar' => $fileName]);
        UserRoles::create(['user_id' => $user->id, 'role_id' => 1]);
        UserRoles::create(['user_id' => $user->id, 'role_id' => 6]);


        $user = User::create(['uuid' => 'ef8c398b-e3e5-41ca-b417-e76d4e6f5f1e',
            'app_id' => 1, 'name' => "Carlos Emanuel Gonçalves de Jesus",
            'email' => "cejesus@gmail.com", 'password' => Hash::make('qwerty'), 'email_verified_at' => Date::now()]);
        //(new Avatar(include './config/laravolt/avatar.php'))->create('Carlos Jesus')->save($avatarPath.'ef8c398b-e3e5-41ca-b417-e76d4e6f5f1e.png');
        $fileName = 'ef8c398b-e3e5-41ca-b417-e76d4e6f5f1e.png';
        UserProfile::create(['user_id' => $user->id, 'status_id'=> 2, 'freguesia_id' => 1304, 'avatar' => $fileName]);

         /*
        $faker = Faker::create();
        for ($i = 0; $i < 5; $i++) {
            $name = $faker->name;
            $uuid = Str::uuid();
            $user = User::create(['uuid' => $uuid, 'name' => $name, 'email' => $faker->unique()->safeEmail, 'password' => Hash::make('qwerty'), 'email_verified_at' => Date::now()]);
            $fileName = $uuid.'.png';
            (new Avatar(include './config/laravolt/avatar.php'))->create($name)->save($avatarPath.$fileName);
            //UserProfile::create(['user_id' => $user->id, 'app_id'=> $application->id, 'role_id'=> $role_user->id, 'status_id'=> $status->id, 'avatar' => $fileName]);
        }
        */
    }
}
