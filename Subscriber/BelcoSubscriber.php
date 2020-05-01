<?php 

namespace BelcoConnectorPlugin\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Context\ActivateContext;
use Shopware\Components\Context\UninstallContext;
use Shopware\Components\Plugin\ConfigReader;
use BelcoConnectorPlugin\Components\BelcoConnector;

class BelcoSubscriber implements SubscriberInterface{
    private $belcoConnector;
    private $pluginDirectory;
    private $config;
    
    public function activate(ActivateContext $context) { //Belco::activate() must be an instance of Belco\\ActivateContext, instance of Shopware\\Components\\Plugin\\Context\\ActivateContext
        $context->scheduleClearCache(InstallContext::CACHE_LIST_DEFAULT);
    }

    public function uninstall(UninstallContext $context) {
        if ($context->keepUserData()) {
            return;
        }
    }

    public static $repository = null;

    public static function getSubscribedEvents() {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch'
        ];
    }
/*
    public function onPostDispatch() {
        $this->templateManager->addTemplateDir($this->pluginBaseDirectory . '/Resources/views');
    }
*/
    public function __construct($pluginName, $pluginDirectory, BelcoConnector $belcoConnector, ConfigReader $configReader)
    {
        $this->pluginDirectory = $pluginDirectory;
        $this->belcoConnector = $belcoConnector;

        $this->config = $configReader->getByPluginName($pluginName);
    }

    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Action $controller */
        $controller = $args->get('subject');
        $view = $controller->View();

        $view->addTemplateDir($this->pluginDirectory . '/Resources/views');

        $view->assign('Apikey', $this->config['Apikey']);
        $view->assign('Apisecret', $this->config['Apisecret']);

        /*if (!$this->config['swagSloganContent']) {
            $view->assign('swagSloganContent', $this->sloganPrinter->getSlogan());
        }*/
    }
}
/*

    public function install() {
        $this->subscribeEvent(//kan weg, Call to undefined method BelcoConnectorPlugin\\BelcoConnectorPlugin::subscribeEvent() in 
            'Enlight_Controller_Action_PostDispatchSecure_Frontend',
            'onFrontendPostDispatch'
        );

        $this->createConfig();

        return true;
    }

    private function getCurrency() {
        return $this->get('currency')->getShortName();
    }

    public function onFrontendPostDispatch(Enlight_Event_EventArgs $args) {
        /** @var \Enlight_Controller_Action $controller */ /*
        $controller = $args->get('subject');
        $view = $controller->View();

        $view->addTemplateDir(
            __DIR__ . '/Views'
        );

        $view->assign('belcoConfig', $this->getWidgetConfig());
    }

    public function getCart() {
        $cart = Shopware()->System()->sMODULES['sBasket']->sGetBasketData();

        if (empty($cart['content'])) {
            return null;
        }

        return array(
            'total' => (float) $cart['AmountNumeric'],
            'subtotal' => (float) $cart['AmountNetNumeric'],
            'currency' => $this->getCurrency(),
            'items' => array_map(function($item) {
                return array(
                'id' => $item['articleID'],
                'name' => $item['articlename'],
                'price' => (float) $item['priceNumeric'],
                'url' => $item['linkDetails'],
                'quantity' => (int) $item['quantity']
                );
            }, $cart['content'])
        );
    }

    public function getCustomer() {
        $data = Shopware()->System()->sMODULES['sAdmin']->sGetUserData();

        $customer = array();

        if (!empty($data['additional']['user'])) {
            $user = $data['additional']['user'];

            $customer = array(
                'id' => $user['id'],
                'firstName' => $user['firstname'],
                'lastName' => $user['lastname'],
                'email' => $user['email'],
                'country' => $data['additional']['country']['countryiso'],
                'signedUp' => strtotime($user['firstlogin'])
            );

            if ($data['billingaddress']['phone']) {
                $customer['phoneNumber'] = $data['billing']['phone'];
            }
        }

        return $customer;
    }

    public function getWidgetConfig() {
        $shopId = $this->Config()->get('shopId');

        if (!$shopId) {
            return;
        }

        $config = array(
            'shopId' => $shopId,
            'cart' => $this->getCart()
        );

        $customer = $this->getCustomer();

        if ($customer) {
        $order = $this->getOrderData($customer['id']);

        $config = array_merge($config, $customer, $order);
        }

        return json_encode($config);
    }

    private function createConfig() {
        $this->Form()->setElement('text', 'shopId', array(
            'label' => 'Shop Id'
        ));

        $this->Form()->setElement('text', 'apiKey', array(
            'label' => 'Api Key'
        ));
    }

    private function getOrderData($customerId) {
        $builder = Shopware()->Models()->createQueryBuilder();

        $builder->select(array(
            'SUM(orders.invoiceAmount) as totalSpent',
            'MAX(orders.orderTime) as lastOrder',
            'COUNT(orders.id) as orderCount',
        ));

        $builder
            ->from('Shopware\Models\Order\Order', 'orders')
            ->groupBy('orders.customerId')
            ->where($builder->expr()->eq('orders.customerId', $customerId))
            ->andWhere($builder->expr()->notIn('orders.status', array('-1', '4')))
            ->addOrderBy('orders.orderTime', 'ASC');

        $result = $builder->getQuery()->getOneOrNullResult();

        if ($result) {
            return array(
                'totalSpent' => (float) $result['totalSpent'],
                'lastOrder' => strtotime($result['lastOrder']),
                'orderCount' => (int) $result['orderCount']
            );
        }
    }
} */