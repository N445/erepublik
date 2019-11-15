<?php

namespace App\Utils;

class MondayHelper
{
    const PREV_MONDAY = 'prev';
    const NEXT_MONDAY = 'next';

    /**
     * @param $semaine
     * @return \DateTime
     * @throws \Exception
     */
    public static function getSemaineDateTime($semaine)
    {
        return (new \DateTime())->setTimestamp(strtotime(self::getSemaineString($semaine), (new \DateTime("NOW"))->getTimestamp()));
    }

    /**
     * @param $semaine
     * @return string
     */
    private static function getSemaineString($semaine)
    {
        if (self::PREV_MONDAY === $semaine) {
            return 'previous monday';
        }
        return 'next monday';
    }

    public static function getErepublikSemaine($semaine)
    {
        if (self::PREV_MONDAY === $semaine) {
            return 1;
        }
        return 0;
    }
}