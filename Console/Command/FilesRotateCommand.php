<?php

namespace Probance\M2connector\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Probance\M2connector\Cron\Files as CronFiles;

class FilesRotateCommand extends Command
{
    const NAME = 'fileRotate';
    const NBDAY = 'nbday';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var CronFiles
     */
    protected $cronFiles;

    /**
     * AttributeListCommand constructor.
     *
     * @param State $state
     * @param CronFiles $cronFiles
     */
    public function __construct(
        State $state,
        CronFiles $cronFiles
    )
    {
        $this->state = $state;
        $this->cronFiles = $cronFiles;

        parent::__construct();
    }

    /**
     * Configure command
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::NBDAY,
                null,
                InputOption::VALUE_REQUIRED,
                'Nb day'
            )
        ];

        $this->setName('probance:files:rotate');
        $this->setDescription('Force files rotate according to nb day given');
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
            $nbDay = $input->getOption(self::NBDAY);
            $output->writeln("<info>Force files rotate on ".$nbDay." days</info>");

            $this->cronFiles->doRotate($nbDay);

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
	    }
        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
