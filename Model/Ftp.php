<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Filesystem\Io\Sftp;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;
use Walkwizus\Probance\Model\LogFactory;
use Walkwizus\Probance\Api\LogRepositoryInterface;

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
    private $logFactory;

    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    /**
     * Ftp constructor.
     *
     * @param Sftp $sftp
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        Sftp $sftp,
        ProbanceHelper $probanceHelper,
        LogFactory $logFactory,
        LogRepositoryInterface $logRepository
    )
    {
        $this->sftp = $sftp;
        $this->probanceHelper = $probanceHelper;
        $this->logFactory = $logFactory;
        $this->logRepository = $logRepository;
    }

    /**
     * Send file on Probance FTP server
     *
     * @param $filename
     * @param $file
     * @return $this
     * @throws \Exception
     */
    public function sendFile($filename, $file)
    {
        try {
            $this->sftp->open([
                'host' => $this->probanceHelper->getFtpValue('host'),
                'port' => 22,
                'username' => $this->probanceHelper->getFtpValue('username'),
                'password' => $this->probanceHelper->getFtpValue('password'),
                'passive' => true,
            ]);
            $this->sftp->write('/upload/' . $filename, $file);
            $this->sftp->close();
        } catch (Exception $e) {
            $log = $this->logFactory->create();
            $log->setFilename('ftp::'.$filename);
            $log->setErrors(serialize($e->getMessage()));
            $log->setCreatedAt(date('Y-m-d H:i:s'));
            $this->logRepository->save($log);
        }
        return $this;
    }
}
