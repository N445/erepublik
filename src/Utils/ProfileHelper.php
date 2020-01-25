<?php

namespace App\Utils;

class ProfileHelper
{
    const MAX_PLANE_LEVEL = 44;
    const ACTIVE          = 'active';
    const DEAD            = 'dead';
    const LEVELMAX        = 'levelmax';
    const INACTIVE        = 'inactive';
    const DESACTIVE      = 'desactive';

    public static function getStatusForm()
    {
        return self::getStatus();
    }

    public static function getStatusLabelByKey($key)
    {
        if (array_key_exists($key, array_flip(self::getStatus()))) {
            return array_flip(self::getStatus())[$key];
        }
        return $key;
    }

    public static function getStatusLabels()
    {
        return array_flip(self::getStatus());
    }

    private function getStatus()
    {
        return [
            'Actif'             => self::ACTIVE,
            'Inactif'           => self::INACTIVE,
            'Mort'              => self::DEAD,
            'Level max atteint' => self::LEVELMAX,
            'Désactivé'        => self::DESACTIVE,
        ];
    }
}
