<?php

namespace Probance\M2connector\Model\Flow\Type\Factory;

use Probance\M2connector\Model\Flow\Type\TypeInterface;

class Text implements TypeInterface
{
    /**
     * Render field text
     *
     * @param $value
     * @param bool $limit
     * @param array $escaper
     * @return bool|string
     */
    public function render($value, $limit = false, $escaper = [])
    {
        // always remove \r and \n from value
        $value = str_ireplace(array("\r","\n"),'', $value);
        if ($limit != false) {
            $value = substr($value, 0, $limit);
        }
        if (!empty($escaper)) {
            $value = preg_replace(array_keys($escaper),array_values($escaper),$value);
        }

        return $value;
    }
}
