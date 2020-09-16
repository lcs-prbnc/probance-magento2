<?php

namespace Walkwizus\Probance\Helper;

class Logo
{
    /**
     * Get Probance logo
     *
     * @return string
     */
    public function getLogo()
    {
        $logo = <<<LOGO
  _____                   _                                  
 |  __ \                 | |                                  
 | |__) |  _ __    ___   | |__     __ _   _ __     ___    ___ 
 |  ___/  | '__|  / _ \  | '_ \   / _` | | '_ \   / __|  / _ \
 | |      | |    | (_) | | |_) | | (_| | | | | | | (__  |  __/
 |_|      |_|     \___/  |_.__/   \__,_| |_| |_|  \___|  \___|
LOGO;
        return $logo;
    }
}