<?php

namespace Walkwizus\Probance\Model\Flow\Type\Factory;

use Walkwizus\Probance\Helper\Data;
use Walkwizus\Probance\Model\Flow\Type\TypeInterface;

class Date implements TypeInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * Date constructor.
     *
     * @param Data $data
     */
    public function __construct(Data $data)
    {
        $this->data = $data;
    }

    /**
     * Render date field
     *
     * @param $value
     * @param bool $limit
     * @return bool|string
     */
    public function render($value, $limit = false)
    {
        return $value ? date($this->data->getFlowFormatValue('date_format'), strtotime($value)) : '';
    }
}