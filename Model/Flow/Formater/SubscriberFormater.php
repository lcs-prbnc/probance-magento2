<?php

namespace Walkwizus\Probance\Model\Flow\Formater;

use Magento\Newsletter\Model\Subscriber;

class SubscriberFormater extends AbstractFormater
{
    /**
     * Format change_status_at attribute
     *
     * @param Subscriber $subscriber
     * @return string
     * @throws \Exception
     */
    public function getCreatedAt(Subscriber $subscriber)
    {
        $datetime = new \DateTime($subscriber->getChangeStatusAt());
        return $datetime->format('Y-m-d');
    }

    /**
     * Get is optin flag
     *
     * @param Subscriber $subscriber
     * @return int
     */
    public function getOptinFlag(Subscriber $subscriber)
    {
        if ($subscriber->isSubscribed()) {
            return 1;
        }

        return 0;
    }
}