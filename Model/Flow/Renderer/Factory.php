<?php

namespace Probance\M2connector\Model\Flow\Renderer;

use Magento\Framework\ObjectManagerInterface;

class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Factory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve renderer instance
     *
     * @param $type
     * @return mixed
     */
    public function getInstance($type)
    {
        $className = 'DefaultRenderer';
        if (!empty($type)) {
            $arrType = explode('_', $type);
            $arrTypeFormated = array_map('ucfirst', $arrType);
            $className = implode('\\', $arrTypeFormated) . 'Renderer';
        }

        return $this->objectManager->get(class_exists(self::class . '\\' . $className) ? self::class . '\\' . $className : self::class . '\DefaultRenderer');
    }
}