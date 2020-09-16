<?php

namespace Walkwizus\Probance\Model\Flow\Type\Factory;

use Walkwizus\Probance\Helper\Data;
use Walkwizus\Probance\Model\Flow\Type\TypeInterface;

class Price implements TypeInterface
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
     * Render price field
     *
     * @param $value
     * @param bool $limit
     * @return bool|string
     */
    public function render($value, $limit = false)
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $decimal = $this->data->getFlowFormatValue('numeric_format_number_decimal');
        $decPoint = $this->data->getFlowFormatValue('numeric_format_dec_point');
        $thousandSeparator = $this->data->getFlowFormatValue('numeric_format_thousand_separator');

        return number_format($value, $decimal, $decPoint, $thousandSeparator);
    }
}