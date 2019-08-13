<?php
class WebspeaksBasket_Basket_Block_Basket extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getBasket()     
     { 
        if (!$this->hasData('basket')) {
            $this->setData('basket', Mage::registry('basket'));
        }
        return $this->getData('basket');
    }

	public function getBKConfigByName($key) {
		$config = Mage::getStoreConfig('bkconfig/bk_group/'.$key);
		return $config;
	}
}