<?php

namespace App\Utils\KillStats;

use App\Model\KillsStats\Profile;
use Faker\Factory;

class FakeProfile
{
    /**
     * @return Profile[]
     */
    public static function getProfiles()
    {
        $faker = Factory::create('fr');
        return array_map(function () use ($faker) {
            $profile = new Profile();
            $profile->setName($faker->name)
                    ->setId(rand(10000, 999999))
            ;
            return $profile;
        }, range(1, 6));
    }
}