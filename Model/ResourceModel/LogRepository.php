<?php

namespace Probance\M2connector\Model\ResourceModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Api\Data\LogInterface;

class LogRepository implements LogRepositoryInterface
{
    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * LogRepository constructor.
     *
     * @param LogFactory $logFactory
     */
    public function __construct(LogFactory $logFactory)
    {
        $this->logFactory = $logFactory;
    }

    /**
     * @param LogInterface $log
     * @return LogInterface
     */
    public function save(LogInterface $log)
    {
        $log->getResource()->save($log);
        return $log;
    }

    /**
     * @param $id
     * @return Log
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $log = $this->logFactory->create();
        $log->getResource()->load($log, $id);

        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Unable to load log with ID "%1"', $id));
        }

        return $log;
    }

    /**
     * @param LogInterface $log
     * @return LogInterface
     */
    public function delete(LogInterface $log)
    {
        $log->getResource()->delete($log);
        return $log;
    }

    public function deleteById($id)
    {

    }
}