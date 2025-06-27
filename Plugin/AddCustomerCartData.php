<?php

namespace Probance\M2connector\Plugin;

class AddCustomerCartData
{
    public function __construct(
        protected \Magento\Checkout\Model\Session $checkoutSession,
    )
    {
    }

    /**
     * @param Customer $subject
     * @param          $result
     *
     * @return mixed
     */
    public function afterGetSectionData(
        \Magento\Checkout\CustomerData\Cart $subject,
                                            $result
    )
    {
        $result['quoteId'] = $this->getQuoteId();


        return $result;
    }

    /**
     * @return string
     */
    public function getQuoteId()
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote) {
            return $quote->getId() ?? 0;
        }

        return 0;
    }
}
