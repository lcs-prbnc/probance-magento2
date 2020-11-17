<?php

namespace Probance\M2connector\Model\Flow\Type;

interface TypeInterface
{
    public function render($value, $limit = false);
}