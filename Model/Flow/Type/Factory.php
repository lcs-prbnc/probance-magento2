<?php

namespace Probance\M2connector\Model\Flow\Type;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    protected $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param $type
     * @return \Probance\M2connector\Model\Flow\Type\TypeInterface
     */
    public function getInstance($type)
    {
        return $this->objectManager->get(self::class . '\\' . ucfirst($type));
    }
}
