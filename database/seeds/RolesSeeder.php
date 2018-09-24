<?php

use Illuminate\Database\Seeder;

use App\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = new Role;
        $roles->name = 'Посетитель';
        $roles->save();

        $roles = new Role;
        $roles->name = 'Админ';
        $roles->save();

        $roles = new Role;
        $roles->name = 'Модератор';
        $roles->save();


    }
}
