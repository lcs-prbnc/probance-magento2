<?php

namespace Probance\M2connector\Model\ResourceModel;

use Probance\M2connector\Model\ResourceModel\Log as LogResource;
use Magento\Framework\Exception\NoSuchEntityException;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Api\Data\LogInterface;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Model\Log as LogModel;

class LogRepository implements LogRepositoryInterface
{
    /** @var LogResource */
    protected $logResource;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * LogRepository constructor.
     *
     * @param LogFactory $logFactory
     * @param LogResource $logResource
     */
    public function __construct(
        LogFactory $logFactory,
        LogResource $logResource
    ) {
        $this->logResource = $logResource;
        $this->logFactory = $logFactory;
    }

    /**
     * @param LogInterface $log
     * @return LogInterface
     */
    public function save(LogInterface $log)
    {
        $this->logResource->save($log);
        return $log;
    }

    /**
     * @param $id
     * @return LogModel
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $log = $this->logFactory->create();
        $this->logResource->load($log, $id);

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
        $this->logResource->load($log, $id);
        if (!$log->getId()) {
            throw new NoSuchEntityException(__('Unable to load log with ID "%1"', $id));
        }
        $this->logResource->delete($log);
        return $this;
    }
}
