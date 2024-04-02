<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Filesystem\Io\Sftp;
use Psr\Log\LoggerInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Api\LogRepositoryInterface;

class Ftp
{
    /**
     * @var Sftp
     */
    protected $sftp;

    /**
     * @var Data
     */
    protected $probanceHelper;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var LogRepositoryInterface
     */
    protected $logRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Ftp constructor.
     *
     * @param Sftp $sftp
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Sftp $sftp,
        ProbanceHelper $probanceHelper,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository
    )
    {
        $this->logger = $logger;
        $this->sftp = $sftp;
        $this->probanceHelper = $probanceHelper;
        $this->logFactory = $logFactory;
        $this->logRepository = $logRepository;
    }

    /**
     * Send file on Probance FTP server
     *
     * @param $storeId
     * @param $filename
     * @param $file
     * @return $this
     * @throws \Exception
     */
    public function sendFile($storeId, $filename, $file)
    {
        try {
            $this->sftp->open([
                'host' => $this->probanceHelper->getFtpValue('host',$storeId),
                'port' => 22,
                'username' => $this->probanceHelper->getFtpValue('username',$storeId),
                'password' => $this->probanceHelper->getFtpValue('password',$storeId),
                'passive' => true,
            ]);
            $this->sftp->write('/upload/' . $filename, $file);
            $this->sftp->close();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $log = $this->logFactory->create();
            $log->setFilename('ftp::'.$filename);
            $log->setErrors(serialize($e->getMessage()));
            $log->setCreatedAt(date('Y-m-d H:i:s'));
            $this->logRepository->save($log);
            throw $e;
        }
        return $this;
    }
}
