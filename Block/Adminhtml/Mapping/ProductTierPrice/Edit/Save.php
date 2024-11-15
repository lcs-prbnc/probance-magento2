<?php

namespace Probance\M2connector\Block\Adminhtml\Mapping\ProductTierPrice\Edit;

use Magento\CatalogRule\Block\Adminhtml\Edit\GenericButton;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class Save extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $url = $this->getUrl('probance/mapping_productTierPrice/save');

        return [
            'label' => __('Save Rows'),
            'class' => 'save primary',
            'on_click' => "setLocation('". $url ."')",
            'sort_order' => 90,
        ];
    }
}
