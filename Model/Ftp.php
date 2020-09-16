<?php

namespace Walkwizus\Probance\Model;

use Magento\Framework\Filesystem\Io\Sftp;
use Walkwizus\Probance\Helper\Data as ProbanceHelper;

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
     * Ftp constructor.
     *
     * @param Sftp $sftp
     * @param ProbanceHelper $probanceHelper
     */
    public function __construct(
        Sftp $sftp,
        ProbanceHelper $probanceHelper
    )
    {
        $this->sftp = $sftp;
        $this->probanceHelper = $probanceHelper;
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
        $this->sftp->open([
            'host' => $this->probanceHelper->getFtpValue('host'),
            'port' => 22,
            'username' => $this->probanceHelper->getFtpValue('username'),
            'password' => $this->probanceHelper->getFtpValue('password'),
            'passive' => true,
        ]);

        $content = file_get_contents($file);
        $this->sftp->write('/upload/' . $filename, $content);
        $this->sftp->close();

        return $this;
    }
}