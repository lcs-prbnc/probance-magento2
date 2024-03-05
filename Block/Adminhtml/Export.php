<?php

namespace Probance\M2connector\Block\Adminhtml;

class Export extends \Magento\Framework\View\Element\Template
{
    /** @var array */
    public $export_entities;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $export_entities = [], 
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->export_entities = $export_entities;
    }

    public function getEntities() 
    {
        return $this->export_entities;
    }

    public function getExportUrl()
    {
        return $this->getUrl('probance/export/manual');
    }
}
