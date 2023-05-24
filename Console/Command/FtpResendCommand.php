<?php

namespace Probance\M2connector\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Driver\File;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Probance\M2connector\Model\Ftp;
use Probance\M2connector\Model\Export\AbstractFlow;

class FtpResendCommand extends Command
{
    const NAME = 'filename';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * AttributeListCommand constructor.
     *
     * @param State $state
     * @param Ftp $ftp
     * @param DirectoryList $directoryList
     * @param File $file
     */
    public function __construct(
        State $state,
        Ftp $ftp,
        DirectoryList $directoryList,
        File $file
    )
    {
        $this->state = $state;
        $this->ftp = $ftp;
        $this->directoryList = $directoryList;
        $this->file = $file;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Filename'
            )
        ];

        $this->setName('probance:ftp:resend');
        $this->setDescription('Resend file from Probance export folder to Probance defined FTP');
        $this->setDefinition($options);

        parent::configure();
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws LocalizedException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_CRONTAB);

        try {
            $directory = $this->directoryList->getPath('var') . DIRECTORY_SEPARATOR . AbstractFlow::EXPORT_DIRECTORY;
            $filename = $input->getOption(self::NAME);
            $filepath = $directory . DIRECTORY_SEPARATOR . $filename;

            if ($this->file->isExists($filepath)) {
                $output->writeln("<info>Sending file over FTP</info>");
                $this->ftp->sendFile($filename, $filepath);
            } else {
                $output->writeln('<error>Given file not exists : '.$filepath.'</error>');
            }

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
	}
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
