<?php

namespace Probance\M2connector\Block\Adminhtml;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Export extends Template
{
    /** @var array */
    public $export_entities;

    /** @var StoreManagerInterface */
    public $storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        array $export_entities = [], 
        array $data = []
    ) {
        parent::__construct($context,$data);
        $this->export_entities = $export_entities;
        $this->storeManager = $storeManager;
    }

    public function getEntities() 
    {
        return $this->export_entities;
    }

    public function getStores()
    {
        $storeManagerDataList = $this->storeManager->getStores(true);
        $options = array();
        foreach ($storeManagerDataList as $key => $value) {
            $options[] = ['label' => $value['name'].' - '.$value['code'], 'value' => $key];
        }
        return $options;
    }

    public function getExportUrl()
    {
        return $this->getUrl('probance/export/manual');
    }

}
