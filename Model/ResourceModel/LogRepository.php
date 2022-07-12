<?php

namespace Probance\M2connector\Model\ResourceModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Api\Data\LogInterface;
use Probance\M2connector\Model\LogFactory;

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
        $log->save($log);
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
        $log->load($log, $id);

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
        $this->deleteById($log->getId());
        return $this;
    }

    public function deleteById($id)
    {
        $log = $this->logFactory->create();
        $log->load($log, $id);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Unable to load log with ID "%1"', $id));
        }
        $log->delete();
        return $this;
    }
}
