<?php

namespace Probance\M2connector\Model;

use Magento\Framework\Filesystem\Io\Sftp as Magento_Sftp;
use Psr\Log\LoggerInterface;
use Probance\M2connector\Model\LogFactory;
use Probance\M2connector\Api\LogRepositoryInterface;
use Probance\M2connector\Helper\Data as ProbanceHelper;

class Ftp extends Magento_Sftp 
{
    const ROOT_FOLDER = '/upload';

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
     * @param LoggerInterface $logger
     * @param LogFactory $logFactory
     * @param LogRepositoryInterface $logRepository
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        LoggerInterface $logger,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository,
        ProbanceHelper $probanceHelper
    )
    {
        $this->logger = $logger;
        $this->logFactory = $logFactory;
        $this->logRepository = $logRepository;
        $this->probanceHelper = $probanceHelper;
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
            if ($this->probanceHelper->getFtpValue('enabled',$storeId)) {
                $this->open([
                    'host' => $this->probanceHelper->getFtpValue('host',$storeId),
                    'port' => 22,
                    'username' => $this->probanceHelper->getFtpValue('username',$storeId),
                    'password' => $this->probanceHelper->getFtpValue('password',$storeId),
                    'passive' => true,
                ]);
                $folder = self::ROOT_FOLDER;
                $subFolder = $this->probanceHelper->getFtpValue('folder',$storeId);
                if ($subFolder) {
                    $folder .= '/'.trim(trim($subFolder),'/');
                    if (!$this->_connection->is_dir($folder)) {
                        if (!$this->mkdir($folder)) {
                            throw new \Exception($folder.' is impossible to create.');
                        }
                    }
                }
                $this->write($folder .'/'. $filename, $file);
                $this->close();
            } else {
                $this->logger->warning('FTP is disabled');
            } 
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
