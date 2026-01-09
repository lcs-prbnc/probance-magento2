<?php

namespace Probance\M2connector\Console\Output;
     
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
     
class StreamedOutput extends StreamOutput
{
    protected $converter;

    /**
     * @param mixed|resource                      $stream    A stream resource
     * @param int                           $verbosity The verbosity level (one of the VERBOSITY constants in OutputInterface)
     * @param bool|null                     $decorated Whether to decorate messages (null for auto-guessing)
     * @param OutputFormatterInterface|null $formatter Output formatter instance (null to use default OutputFormatter)
     *
     * @throws InvalidArgumentException When first argument is not a real stream
     */
    public function __construct($stream, int $verbosity = self::VERBOSITY_NORMAL, ?bool $decorated = null, ?OutputFormatterInterface $formatter = null)
    {
        $this->converter = new AnsiToHtmlConverter();

        parent::__construct($stream, $verbosity, $decorated, $formatter);
    }

    protected function doWrite($message, $newline)
    {
        if (
            false === @fwrite($this->getStream(), $message) ||
            (
                $newline &&
                (false === @fwrite($this->getStream(), PHP_EOL))
            )
        ) {
            throw new RuntimeException('Unable to write output.');
        }
     
        $message = $this->converter->convert($message);
        if ($message) echo $message.'<br/>';

        ob_flush();
        flush();
    }
}
