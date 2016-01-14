<?php
namespace Gemtoo\Blog\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\State;
use Gemtoo\Blog\Model\ArticleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Router implements RouterInterface
{
    const MODULE_NAME = 'gemtoo_blog';
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Article factory
     * @var \Gemtoo\Blog\Model\ArticleFactory
     */
    protected $articleFactory;

    /**
     * Config primary
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Url
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * Response
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var bool
     */
    protected $dispatched;

    /**
     * @param ActionFactory $actionFactory
     * @param ManagerInterface $eventManager
     * @param UrlInterface $url
     * @param State $appState
     * @param ArticleFactory $articleFactory
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        UrlInterface $url,
        State $appState,
        ArticleFactory $articleFactory,
        StoreManagerInterface $storeManager,
        ResponseInterface $response,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->url = $url;
        $this->appState = $appState;
        $this->articleFactory = $articleFactory;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param RequestInterface $request
     * @return null
     */
    public function match(RequestInterface $request)
    {

        if (!$this->dispatched) {
            $urlKey = trim($request->getPathInfo(), '/');
            $origUrlKey = $urlKey;

            $condition = new DataObject(
                [
                    'url_key' => $urlKey,
                    'continue' => true
                ]
            );
            $this->eventManager->dispatch(
                self::MODULE_NAME .'_controller_router_match_before',
                [
                    'router' => $this,
                    'condition' => $condition
                ]
            );
            $urlKey = $condition->getUrlKey();
            if ($condition->getRedirectUrl()) {
                $this->response->setRedirect($condition->getRedirectUrl());
                $request->setDispatched(true);
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Redirect',
                    ['request' => $request]
                );
            }
            if (!$condition->getContinue()) {
                return null;
            }
            $entities = [
                'article' => [
                    'prefix'        => $this->scopeConfig->getValue(
                        self::MODULE_NAME .'/article/url_prefix',
                        ScopeInterface::SCOPE_STORES
                    ),
                    'suffix'        => $this->scopeConfig->getValue(
                        self::MODULE_NAME .'/article/url_suffix',
                        ScopeInterface::SCOPE_STORES
                    ),
                    'list_key'      => $this->scopeConfig->getValue(
                        self::MODULE_NAME .'/article/list_url',
                        ScopeInterface::SCOPE_STORES
                    ),
                    'list_action'   => 'index',
                    'factory'       => $this->articleFactory,
                    'controller'    => 'article',
                    'action'        => 'view',
                    'param'         => 'id',
                ]
            ];

            foreach ($entities as $entity => $settings) {
                if ($settings['list_key']) {
                    if ($urlKey == $settings['list_key']) {
                        $request->setModuleName(self::MODULE_NAME)
                            ->setControllerName($settings['controller'])
                            ->setActionName($settings['list_action']);
                        $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $urlKey);
                        $this->dispatched = true;
                        return $this->actionFactory->create(
                            'Magento\Framework\App\Action\Forward',
                            ['request' => $request]
                        );
                    }
                }
                if ($settings['prefix']) {
                    $parts = explode('/', $urlKey);
                    if ($parts[0] != $settings['prefix'] || count($parts) != 2) {
                        continue;
                    }
                    $urlKey = $parts[1];
                }
                if ($settings['suffix']) {
                    $suffix = substr($urlKey, -strlen($settings['suffix']) - 1);
                    if ($suffix != '.'.$settings['suffix']) {
                        continue;
                    }
                    $urlKey = substr($urlKey, 0, -strlen($settings['suffix']) - 1);
                }

                $instance = $settings['factory']->create();
                $id = $instance->checkUrlKey($urlKey, $this->storeManager->getStore()->getId());
                if (!$id) {
                    return null;
                }
                $request->setModuleName(self::MODULE_NAME)
                    ->setControllerName('article')
                    ->setActionName('view')
                    ->setParam('id', $id);
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $origUrlKey);
                $request->setDispatched(true);
                $this->dispatched = true;
                return $this->actionFactory->create(
                    'Magento\Framework\App\Action\Forward',
                    ['request' => $request]
                );
            }
        }
        return null;
    }
}
