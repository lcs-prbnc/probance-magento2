<?php

namespace Probance\M2connector\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Framework\Console\CommandListInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Probance\M2connector\Console\Output\StreamedOutput;

class Manual extends Action
{
    protected $commandList;

    /**
     * Index constructor.
     *
     * @param Action\Context $context
     * @param CommandListInterface $commandList
     */
    public function __construct(
        Action\Context $context,
        CommandListInterface $commandList
    )
    {
        parent::__construct($context);
        $this->commandList = $commandList;
    }

    /**
     * @return Page
     */
    public function execute()
    {
        //ExportCatalogCommand
        $entity = $this->getRequest()->getParam('entity');

        $commands = $this->commandList->getCommands();
        $command = isset($commands[$entity]) && $commands[$entity]->isEnabled() ? $commands[$entity] : false;
        if ($command) {
            $command->setMinSecondsBetweenRedraws(5);
            // create inputs
            // from & to 
            $fromDate = new \DateTime($this->getRequest()->getParam('from'));
            $from = $fromDate->format('Y-m-d H:i:s');
            $toDate = new \DateTime($this->getRequest()->getParam('to'));
            $to = $toDate->format('Y-m-d H:i:s');
            $arrayInput = [
                '--from' => $from,
                '--to' => $to
            ];
            // store_id
            $storeId = $this->getRequest()->getParam('store_id');
            if ($storeId !== '') $arrayInput['--store_id'] = $storeId;
            // entity id
            $entityId = $this->getRequest()->getParam('id');
            if ($entityId !== '') $arrayInput['--id'] = $entityId;

            $input = new ArrayInput($arrayInput);
            $output = new StreamedOutput(fopen('php://stdout', 'w'),StreamedOutput::VERBOSITY_NORMAL, true);
            // command run
            ob_start();
            $command->run($input, $output);
            ob_end_clean();
        }
        return '';
    }

    /**
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Probance_M2connector::probance_manual_export');
    }
}
