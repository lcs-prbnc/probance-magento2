<?php

namespace Probance\M2connector\Model\Flow\Type\Factory;

use Probance\M2connector\Helper\Data;
use Probance\M2connector\Model\Flow\Type\TypeInterface;

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
     * @param array $escaper
     * @return bool|string
     */
    public function render($value, $limit = false, $escaper = [])
    {
        return $value ? date($this->data->getFlowFormatValue('date_format'), strtotime($value)) : '';
    }
}
