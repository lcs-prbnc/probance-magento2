<?php

namespace Probance\M2connector\Model\Flow\Type\Factory;

class Datetime extends Probance\M2connector\Model\Flow\Type\Factory\Date
{
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
        if ($value) {
	    $datetime = $this->timezone->date(strtotime($value),null, false);
	    return $datetime->format($this->data->getFlowFormatValue('datetime_format'));
	}
	return '';
    }
}
