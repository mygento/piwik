<?php

class Mygento_Stats_Block_Tracker extends Mage_Core_Block_Template {

    public function __construct() {

        //any construction code here
    }

    public function Basic1() {
        return '
		<!-- Piwik Module-->
		<script type="text/javascript">
		var pkBaseURL = (("https:" == document.location.protocol) ? "https://'.Mage::getStoreConfig('mystats/piwik/url').'/" : "http://'.Mage::getStoreConfig('mystats/piwik/url').'/");
		document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));
		</script><script type="text/javascript">
		try {
		var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", '.Mage::getStoreConfig('mystats/piwik/siteid').');
		'.PHP_EOL;
    }

    public function Basic2() {
        return '
		piwikTracker.trackPageView();
		piwikTracker.enableLinkTracking();
		} catch( err ) {}
		</script><noscript><p><img src="http://'.Mage::getStoreConfig('mystats/piwik/url').'/piwik.php?idsite='.Mage::getStoreConfig('mystats/piwik/siteid').'" style="border:0" alt="" /></p></noscript>
		<!-- End Piwik Module Tracking Code -->
		';
    }

    public function toDefault() {
        $html = '';
        $html.=$this->Basic1();
        $html.=$this->Basic2();
        return $html;
    }

    public function toProduct() {
        $html = '';
        $_product = Mage::registry('current_product');
        $_category = Mage::registry('current_category');
        $name = str_replace('"', '', $_product->getName());
        $sku = str_replace('"', '', $_product->getSku());

        $categories = array();
        $cats = $_product->getCategoryIds();
        foreach ($cats as $category_id) {
            $_cat = Mage::getModel('catalog/category')->load($category_id);
            $categories[] = $_cat->getName();
        }
        $html.=$this->Basic1();
        // add the first product to the order
        $html.='
		piwikTracker.setEcommerceView(
		"'.$this->jsQuoteEscape($sku).'",
		"'.$this->jsQuoteEscape($name).'",
		["'.implode("\",\"", $categories).'"],
		'.$_product->getFinalPrice().'
		);';
        $html.=$this->Basic2();

        return $html;
    }

    public function toCategory() {
        $html = '';
        $_category = Mage::registry('current_category');
        $html.=$this->Basic1();
        $name = str_replace('"', '', $_category->getName());
        $html.='
    	piwikTracker.setEcommerceView(
		productSku = false, 
		productName = false, 
		category = "'.$this->jsQuoteEscape($name).'"
		);
		';
        $html.=$this->Basic2();
        return $html;
    }

    public function toCart() {
        $html = '';
        $html.=$this->Basic1();
        $session = Mage::getSingleton('checkout/session');
        foreach ($session->getQuote()->getAllItems() as $item) {
            $_product = $item->getProduct();
            $categories = array();
            $cats = $_product->getCategoryIds();
            foreach ($cats as $category_id) {
                $_cat = Mage::getModel('catalog/category')->load($category_id);
                $categories[] = $_cat->getName();
            }
            $name = str_replace('"', '', $item->getName());
            $sku = str_replace('"', '', $item->getSku());
            $html.='
	    	piwikTracker.addEcommerceItem(
				"'.$this->jsQuoteEscape($sku).'",
				"'.$this->jsQuoteEscape($name).'",
				["'.implode("\",\"", $categories).'"], 
				'.$item->getBaseCalculationPrice().', 
				'.$item->getQty().'
			);
	    	'.PHP_EOL;
        }
        $grandTotal = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();
        if ($grandTotal) {
            $html.='piwikTracker.trackEcommerceCartUpdate('.$grandTotal.');'.PHP_EOL;
        }
        $html.=$this->Basic2();
        return $html;
    }

    public function toSuccess() {
        $html = '';

        $session = Mage::getSingleton('checkout/session');
        $lastid = Mage::getSingleton('checkout/type_onepage')->getCheckout()->getLastOrderId();
        $order = Mage::getSingleton('sales/order');
        $order->load($lastid);

        if (Mage::getStoreConfig('mystats/piwik/enabled')) {
            $html.=$this->Basic1();

            foreach ($order->getAllItems() as $item) {
                $categories = array();
                $cats = $item->getCategoryIds();
                if (count($cats)) {
                    foreach ($cats as $category_id) {
                        $_cat = Mage::getModel('catalog/category')->load($category_id);
                        $categories[] = $_cat->getName();
                    }
                }
                $qty = '0';
                $qty = number_format($item->getQtyOrdered(), 0, '.', '');
                $html.='
		    	piwikTracker.addEcommerceItem(
					"'.$this->jsQuoteEscape($item->getSku()).'",
					"'.$this->jsQuoteEscape($item->getName()).'", 
					["'.implode("\",\"", $categories).'"], 
					'.$item->getPrice().',
					'.$qty.'
				);
		    	'.PHP_EOL;
            }

            $subtotal = $order->getGrandTotal() - $order->getShippingAmount() - $order->getShippingTaxAmount();
            $html.='
	    	piwikTracker.trackEcommerceOrder(
			"'.$order->getIncrementId().'", 
			'.$order->getBaseGrandTotal().',
			'.$subtotal.',
			'.$order->getBaseTaxAmount().',
			'.$order->getBaseShippingAmount().'
			);    	
	    	'.PHP_EOL;

            $html.=$this->Basic2();
        }
        if (Mage::getStoreConfig('mystats/metrika/enabled')) {
            $html.='
			<script type="text/javascript">
			var yaParams = 
			{
			  order_id: "'.$order->getIncrementId().'",
			  order_price: "'.$order->getBaseGrandTotal().'",
			  currency: "'.Mage::app()->getStore()->getCurrentCurrencyCode().'",
			  exchange_rate: "1",
			  goods: 
			     [
			';
            $cnt = count($order->getAllItems());
            $i = 0;
            foreach ($order->getAllItems() as $item) {
                $i++;
                $html.='
				{
				  id: "'.$this->jsQuoteEscape($item->getSku()).'", 
			      name: "'.$this->jsQuoteEscape($item->getName()).'",
				  price: "'.$item->getPrice().'"
				}
			    ';
                if ($i != $cnt) {
                    $html.= ',';
                }
            }
            $html.='
				]
			};
			
			</script>';
        }


        return $html;
    }

    protected function _toHtml() {
        $html = '';
        if (Mage::getStoreConfig('mystats/general/enabled')) {
            if (Mage::getStoreConfig('mystats/piwik/enabled')) {
                $type = '';
                $type = $this->getData('viewpage');
                switch ($type) {
                    case 'product':
                        $html.=$this->toProduct();
                        break;
                    case 'category':
                        $html.=$this->toCategory();
                        break;
                    case 'cart':
                        $html.=$this->toCart();
                        break;
                    case 'order':
                        $html.=$this->toSuccess();
                        break;
                    default:
                        $html.=$this->toDefault();
                }
            }
        }
        return $html;
    }

    protected function _prepareLayout() {
        return parent::_prepareLayout();
    }

}

?>