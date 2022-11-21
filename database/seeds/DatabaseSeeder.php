<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call('RoleDatabaseSeeder');
        $this->call('CompanyDatabaseSeeder');
        $this->call('UserDatabaseSeeder');
        $this->call('CategorySeeder');
    }
}

class RoleDatabaseSeeder extends Seeder {
    public function run()
    {
        DB::table('role')->insert([
            ['id' => 1, 'name' => 'Admin', 'description' => 'Administrator', 'created_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['id' => 2, 'name' => 'Manager', 'description' => 'Company Manager', 'created_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['id' => 3, 'name' => 'Member', 'description' => 'Member', 'created_at' => Carbon::now()->format('Y-m-d H:i:s')]
        ]);
    }
}

class UserDatabaseSeeder extends Seeder {
    public function run()
    { 
        DB::table('user')->insert([
            ['id' => 1, 'name' => 'Admin', 'email' => 'admin@gmail.com', 'gender' => 'male', 'role_id' => 1 ,'password' => '$2y$10$50Pbf7uNj.diNmacjGlNVOlN3ikZ.MJdu5tPdFmHPoHwEg.kVG35q', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'verified' => User::VERIFIED_USER, 'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['id' => 2, 'name' => 'Member1', 'email' => 'member1@gmail.com', 'gender' => 'male', 'role_id' => 3 ,'password' => '$2y$10$50Pbf7uNj.diNmacjGlNVOlN3ikZ.MJdu5tPdFmHPoHwEg.kVG35q', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'verified' => User::VERIFIED_USER, 'email_verified_at' => Carbon::now()->format('Y-m-d H:i:s')]
        ]);
    }
}

class CompanyDatabaseSeeder extends Seeder {
    public function run()
    {
        DB::table('company')->insert([
            ['id' => 1, 'name' => 'System', 'address' => 'DaNang', 'phone' => '0123456789']
        ]);
    }
}

class CategorySeeder extends Seeder {
    public function run()
    {
        DB::table('category')->insert([
            ['id' => 1, 'name' => 'TIPS', 'description' => 'Tips for English', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['id' => 2, 'name' => 'NOTIFICATIONS', 'description' => 'Notifications', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
            ['id' => 3, 'name' => 'NEWS', 'description' => 'News', 'created_at' => Carbon::now()->format('Y-m-d H:i:s'), 'updated_at' => Carbon::now()->format('Y-m-d H:i:s')],
        ]);
    }
}


