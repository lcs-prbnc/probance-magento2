<?php

namespace Walkwizus\Probance\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Walkwizus\Probance\Model\Config\Source\Attribute\Article;
use Walkwizus\Probance\Model\Config\Source\Attribute\Customer;
use Walkwizus\Probance\Model\Config\Source\Attribute\Order;
use Walkwizus\Probance\Model\Config\Source\Attribute\Product;
use Symfony\Component\Console\Helper\Table;

class AttributeListCommand extends Command
{
    const NAME = 'entity';

    /**
     * @var State
     */
    private $state;

    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var Article
     */
    private $article;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Product
     */
    private $product;

    /**
     * AttributeListCommand constructor.
     *
     * @param State $state
     * @param Customer $customer
     * @param Article $article
     * @param Order $order
     * @param Product $product
     */
    public function __construct(
        State $state,
        Customer $customer,
        Article $article,
        Order $order,
        Product $product
    )
    {
        $this->state = $state;
        $this->customer = $customer;
        $this->article = $article;
        $this->order = $order;
        $this->product = $product;

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
                'Entity'
            )
        ];

        $this->setName('probance:attribute:list');
        $this->setDescription('Show attribute list for an entity');
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
            $entityName = $input->getOption('entity');

            if (property_exists($this, $entityName)) {
                $table = new Table($output);
                $table->setHeaders(['Label', 'Value']);
                $table->setRows($this->$entityName->toOptionArray());
                $table->render();
            } else {
                $output->writeln("This entity $entityName does not exists.");
            }
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }
}