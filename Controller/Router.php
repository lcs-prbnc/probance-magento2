<?php 

namespace Probance\M2connector\Controller;

use Magento\Framework\App\Route\ConfigInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ActionFactory;

use Probance\M2connector\Helper\Data;

class Router implements RouterInterface
{
    /** @var ActionFactory **/
    protected $actionFactory;
    /** @var Data **/
    protected $helper;

    public function __construct(
        ActionFactory $actionFactory,
        ResponseInterface $response,
        ConfigInterface $routerConfig,
        Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->routerConfig = $routerConfig;
        $this->helper = $helper;
    }

    public function match(RequestInterface $request)
    {
        $path = trim($request->getPathInfo(), '/');
        if ($this->helper->getRecoveryCartPath() === $path) 
        {
            $request->setControllerName('probance');
            $request->setActionName('cart');
            $request->setRouteName('recovery');
            $action = $this->actionFactory->create(\Probance\M2connector\Controller\Cart\Recovery::class);
            return $action;
        }
    }
}
