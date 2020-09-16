<?php

namespace Walkwizus\Probance\Model\Flow\Type\Factory;

use Walkwizus\Probance\Model\Flow\Type\TypeInterface;

class Text implements TypeInterface
{
    /**
     * Render field text
     *
     * @param $value
     * @param bool $limit
     * @return bool|string
     */
    public function render($value, $limit = false)
    {
        if ($limit != false) {
            $value = substr($value, 0, $limit);
        }

        return $value;
    }
}