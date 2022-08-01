<?php

namespace Probance\M2connector\Model\Flow\Type\Factory;

use Probance\M2connector\Helper\Data;
use Probance\M2connector\Model\Flow\Type\TypeInterface;

class Datetime implements TypeInterface
{
    /**
     * @var Data
     */
    protected $data;

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
     * Render datetime field
     *
     * @param $value
     * @param bool $limit
     * @param array $escaper
     * @return bool|string
     */
    public function render($value, $limit = false, $escaper = [])
    {
        return $value ? date($this->data->getFlowFormatValue('datetime_format'), strtotime($value)) : '';
    }
}
