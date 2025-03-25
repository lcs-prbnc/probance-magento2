<?php

namespace Probance\M2connector\Model\Flow\Type\Factory;

use Probance\M2connector\Helper\Data;
use Probance\M2connector\Model\Flow\Type\TypeInterface;

class Price implements TypeInterface
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
     * Render price field
     *
     * @param $value
     * @param bool $limit
     * @param array $escaper
     * @return bool|string
     */
    public function render($value, $limit = false, $escaper = [])
    {
        if (!is_numeric($value)) {
            return $value;
        }

        $decimal = $this->data->getFlowFormatValue('numeric_format_number_decimal') ?? 2;
        $decPoint = $this->data->getFlowFormatValue('numeric_format_dec_point') ?? '';
        $thousandSeparator = $this->data->getFlowFormatValue('numeric_format_thousand_separator') ?? '';

        return number_format($value, $decimal, $decPoint, $thousandSeparator);
    }
}
