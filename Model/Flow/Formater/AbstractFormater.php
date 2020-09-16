<?php

namespace Walkwizus\Probance\Model\Flow\Formater;

abstract class AbstractFormater
{
    /**
     * Convert attribute code to camel case method
     *
     * @param $value
     * @return mixed|string
     */
    public function convertToCamelCase($value)
    {
        $value = ucwords(str_replace('_', ' ', $value));
        $value = str_replace(' ', '', $value);

        return $value;
    }

    /**
     * Retrieve empty field
     *
     * @return string
     */
    public function getEmptyField()
    {
        return "";
    }
}