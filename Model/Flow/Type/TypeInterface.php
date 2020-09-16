<?php

namespace Walkwizus\Probance\Model\Flow\Type;

interface TypeInterface
{
    public function render($value, $limit = false);
}