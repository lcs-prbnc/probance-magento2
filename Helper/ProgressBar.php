<?php

namespace Probance\M2connector\Helper;

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProgressBar
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Logo
     */
    private $logo;

    /**
     * ProgressBarTrait constructor.
     * @param ObjectManagerInterface $objectManager
     * @param Logo $logo
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Logo $logo
    )
    {
        $this->objectManager = $objectManager;
        $this->logo = $logo;
    }

    /**
     * @param OutputInterface $output
     * @return mixed
     */
    public function getProgressBar(OutputInterface $output)
    {
        $progressBar = $this->objectManager->create(\Symfony\Component\Console\Helper\ProgressBar::class, ['output' => $output]);
        $progressBar->setBarCharacter('<fg=green>⚬</>');
        $progressBar->setEmptyBarCharacter("<fg=red>⚬</>");
        $progressBar->setProgressCharacter("<fg=green>➤</>");
        $progressBar->setFormat(
            "<fg=black;bg=yellow> %warn:-45s%</>\n<fg=black;bg=cyan> %status:-45s%</>\n%current%/%max% [%bar%] %percent:3s%%\n Elapsed: %elapsed:6s%\n Estimated: %estimated:-6s%\n Memory: %memory:6s%"
        );

        return $progressBar;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo->getLogo();
    }
}
