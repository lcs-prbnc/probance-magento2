<?php

namespace Probance\M2connector\Console\Command;

class InitOrderCommand extends ExportOrderCommand
{
    /**
     * @var Boolean
     */
    protected $can_use_range = false;

    /**
     * @var Boolean
     */
    protected $is_init = true;

    /**
     * @var string
     */
    protected $command_line = 'probance:init:order';
}
