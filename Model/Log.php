<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Model\AbstractModel;
use Probance\M2connector\Api\Data\LogInterface;

class Log extends AbstractModel implements LogInterface
{
    protected function _construct()
    {
        $this->_init(ResourceModel\Log::class);
    }

    public function getFilename()
    {
        return $this->getData('filename');
    }

    public function setFilename($filename)
    {
        $this->setData('filename', $filename);
        return $this;
    }

    public function getErrors()
    {
        return $this->getData('errors');
    }

    public function setErrors($errors)
    {
        $this->setData('errors', $errors);
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function setCreatedAt($date)
    {
        $this->setData('created_at', $date);
        return $this;
    }
}