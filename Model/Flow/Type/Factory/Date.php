<?php

namespace Probance\M2connector\Model\Flow\Type\Factory;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Probance\M2connector\Helper\Data;
use Probance\M2connector\Model\Flow\Type\TypeInterface;

class Date implements TypeInterface
{
    /**
     * @var Data
     */
    protected $data;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * Date constructor.
     *
     * @param Data $data
     */
    public function __construct(
        Data $data,
        TimezoneInterface $timezone
    )
    {
        $this->data = $data;
        $this->timezone = $timezone;
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
        if ($value) {
            $datetime = $this->timezone->date(strtotime($value),null, false);
            return $datetime->format($this->data->getFlowFormatValue('date_format'));
        }
	return '';
    }
}
