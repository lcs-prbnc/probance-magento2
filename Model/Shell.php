<?php
namespace Probance\M2connector\Model;

use Magento\Framework\Shell as NativeShell;
use Magento\Framework\Exception\LocalizedException;
use Probance\M2connector\Helper\Data as ProbanceHelper;

/**
 * Shell command line wrapper encapsulates command execution and arguments escaping
 */
class Shell extends NativeShell
{
    /**
     * @var ProbanceHelper
     */
    protected $probanceHelper;

    /**
     * @param \Psr\Log\LoggerInterface $logger Logger instance to be used to log commands and their output
     */
    public function __construct(
        ProbanceHelper $probanceHelper
    ) {
        $this->probanceHelper = $probanceHelper;
    }

    /**
     * Execute a command through the command line, passing properly escaped arguments, and return its output
     *
     * @param string $command Command with optional argument markers '%s'
     * @param string[] $arguments Argument values to substitute markers with
     * @return self
     * @throws \Magento\Framework\Exception\LocalizedException If a command returns non-zero exit code
     */
    public function execute($command, array $arguments = [], $output = null, $storeId = null)
    {
        $debug = $this->probanceHelper->getDebugMode($storeId);

        if (!empty($arguments)) {
            $arguments = array_map('escapeshellarg', $arguments);
            $command .= implode(' ', $arguments);
        }

        if ($debug) {
            $this->probanceHelper->addLog(__('Command launched for store %1 : %2',$storeId, $command));
        }

        $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
        if (in_array('exec', $disabled)) {
            throw new LocalizedException(__('The exec function is disabled.'));
        }

        // exec() have to be called here
        // phpcs:ignore Magento2.Security.InsecureFunction
        exec($command, $execOutput, $exitCode);
        if ($output) $output->writeln($execOutput);
        if ($debug) {
            $this->probanceHelper->addLog($execOutput);
        }

        if ($exitCode) {
            $commandError = new \Exception($execOutput, $exitCode);
            throw new LocalizedException(
                __("Command returned non-zero exit code:\n`%1`", [$command.'::'.$exitCode]),
                $commandError
            );
        }
        return $this;
    }
}
