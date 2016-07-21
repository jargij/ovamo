<?php

use Illuminate\Database\Seeder;

class VacatureSitesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
	public function run()
    {
        $faker = Faker\Factory::create();

        $limit = 5;

        for ($i = 0; $i < $limit; $i++) {
            DB::table('vacature_sites')->insert([ //,
                'name' => $faker->name,
                'url' => $faker->url,
                'content' => $faker->sentence($nbWords = 6, $variableNbWords = true),
                'status' => "200",
                'date_added' => $faker->date($format = 'Y-m-d', $max = 'now'),
                //'error' => $faker->phoneNumber
                'created_at' => $faker->date($format = 'Y-m-d', $max = 'now'),
                'updated_at' => $faker->date($format = 'Y-m-d', $max = 'now')
            ]);
        }
    }
}
