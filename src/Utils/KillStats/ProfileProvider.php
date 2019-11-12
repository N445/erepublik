<?php

namespace App\Utils\KillStats;

use App\Entity\Profile\Profile;

class ProfileProvider
{
    const NAME       = 'name';
    const IDENTIFIER = 'identifier';

    /**
     * @return Profile[]
     */
    public static function getSmaProfiles()
    {
        return self::rawDataToEntity();
    }

    /**
     * @return array
     */
    private static function rawData()
    {
        return [
            [
                self::NAME       => 'juliebiro',
                self::IDENTIFIER => '8618432',
            ],
            [
                self::NAME       => 'antiochus33',
                self::IDENTIFIER => '3637155',
            ],
            [
                self::NAME       => 'Tamashii',
                self::IDENTIFIER => '2308405',
            ],
            [
                self::NAME       => 'NaAaS',
                self::IDENTIFIER => '9541670',
            ],
            [
                self::NAME       => 'Rudak2.1',
                self::IDENTIFIER => '9543015',
            ],
            [
                self::NAME       => 'Claaaa',
                self::IDENTIFIER => '9541668',
            ],
            [
                self::NAME       => 'irios FR',
                self::IDENTIFIER => '9307040',
            ],
            [
                self::NAME       => 'Merlier',
                self::IDENTIFIER => '1438754',
            ],
            [
                self::NAME       => 'Arnwalder',
                self::IDENTIFIER => '8612563',
            ],
            [
                self::NAME       => 'Orbisa',
                self::IDENTIFIER => '9529479',
            ],
            [
                self::NAME       => 'lamperrouge',
                self::IDENTIFIER => '9530017',
            ],
            [
                self::NAME       => 'Gabylll',
                self::IDENTIFIER => '9529555',
            ],
            [
                self::NAME       => 'overpol81',
                self::IDENTIFIER => '9530244',
            ],
            [
                self::NAME       => 'Albert91000',
                self::IDENTIFIER => '9529481',
            ],
            [
                self::NAME       => 'Yoann Crsnr',
                self::IDENTIFIER => '8334433',
            ],
            [
                self::NAME       => 'Skeelu',
                self::IDENTIFIER => '6847367',
            ],
            [
                self::NAME       => 'Virklix',
                self::IDENTIFIER => '9035088',
            ],
            [
                self::NAME       => 'Eporedax',
                self::IDENTIFIER => '2462340',
            ],
            [
                self::NAME       => 'AlexCarter',
                self::IDENTIFIER => '9389609',
            ],
            [
                self::NAME       => 'Theondrus',
                self::IDENTIFIER => '6428270',
            ],
            [
                self::NAME       => 'banana95',
                self::IDENTIFIER => '9474611',
            ],
            [
                self::NAME       => 'Mordgrim',
                self::IDENTIFIER => '9517618',
            ],
            [
                self::NAME       => 'Hugz',
                self::IDENTIFIER => '9530465',
            ],
            [
                self::NAME       => 'miltiados',
                self::IDENTIFIER => '1749124',
            ],
            [
                self::NAME       => 'Lepus Articus',
                self::IDENTIFIER => '9537392',
            ],
            [
                self::NAME       => 'Margougou',
                self::IDENTIFIER => '9530868',
            ],
            [
                self::NAME       => 'Lhinstit',
                self::IDENTIFIER => '9543654',
            ],
            [
                self::NAME       => 'ShivaGanesha',
                self::IDENTIFIER => '2195175',
            ],
            [
                self::NAME       => 'Teddy.',
                self::IDENTIFIER => '9476649',
            ],
            [
                self::NAME       => 'LaMasse',
                self::IDENTIFIER => '9488939',
            ],
            [
                self::NAME       => 'Milanais',
                self::IDENTIFIER => '1754843',
            ],
            [
                self::NAME       => 'Exelles',
                self::IDENTIFIER => '9515666',
            ],
            [
                self::NAME       => 'Tzeench',
                self::IDENTIFIER => '5294976',
            ],
            [
                self::NAME       => 'Plows',
                self::IDENTIFIER => '8309441',
            ],
            [
                self::NAME       => 'Leonardo Grimaldi',
                self::IDENTIFIER => '9538406',
            ],
            [
                self::NAME       => 'Valladaroy',
                self::IDENTIFIER => '9543789',
            ],
            [
                self::NAME       => 'Francis Garnier',
                self::IDENTIFIER => '9537298',
            ],
            [
                self::NAME       => 'Axir4t',
                self::IDENTIFIER => '7886199',
            ],
            [
                self::NAME       => 'ArmagnaX',
                self::IDENTIFIER => '9545874',
            ],
            [
                self::NAME       => 'Sushuki',
                self::IDENTIFIER => '5154443',
            ],
            [
                self::NAME       => 'Patrador92',
                self::IDENTIFIER => '9529604',
            ],
        ];

    }

    /**
     * @return Profile[]
     */
    private static function rawDataToEntity()
    {
        return array_map(function ($data) {
            return new Profile($data[self::IDENTIFIER], $data[self::NAME]);
        }, self::rawData());
    }

}