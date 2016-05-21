<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (https://www.amasty.com)
 * @package Amasty_Perm
 */
class Amasty_Perm_Model_Observer
{
    protected $_permissibleActions = array('index', 'grid', 'exportCsv', 'exportExcel');
    protected $_exportActions = array('exportCsv', 'exportExcel');
    protected $_controllerNames = array('sales_', 'orderspro_', 'adminhtml_sales_');

    public function handleAdminUserSaveAfter($observer) 
    {
        $editor = Mage::getSingleton('admin/session')->getUser();
        if (!$editor) // API or smth else
            return $this;  
             
        $user = $observer->getDataObject(); 
        if ($editor->getId() == $user->getId()){ // My Account
            return $this;     
        }

        $str = '';
        if ($user->getCustomerGroupId()) {
            $str = implode(",", $user->getCustomerGroupId());
        }
        Mage::getModel('amperm/perm')->getResource()->assignGroups($user->getId(), $str);
                 
        $ids = $user->getSelectedCustomers();
        if (is_null($ids))
            return $this;
        $ids = Mage::helper('adminhtml/js')->decodeGridSerializedInput($ids);
        
        Mage::getModel('amperm/perm')->assignCustomers($user->getId(), $ids);
        
        return $this;           
    }
    
    public function handleOrderCollectionLoadBefore($observer) 
    {
        if ('amperm' == Mage::app()->getRequest()->getModuleName())
            return $this;
            
        $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
        if ($uid){
            $permissionManager = Mage::getModel('amperm/perm');
            $collection = $observer->getOrderGridCollection();
            if ($collection){
                $permissionManager->addOrdersRestriction($collection, $uid);
            }
            else {
                $keys = array_keys($observer->getData());
                $collection = $observer->getData($keys[1]);
                $permissionManager->addOrderDataRestriction($collection, $uid);
            }
        }       
        
        return $this;    
    }
    
    public function handleCustomerCollectionLoadBefore($observer) 
    {
        $collection = $observer->getCollection();
        if (strpos(get_class($collection),'Customer_Collection')){
            $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
            $permissionManager = Mage::getModel('amperm/perm');
            if ($uid){
                $permissionManager->addCustomersRestriction($collection, $uid);
            } else {
                $collection->getSelect()
                    ->joinLeft(
                        array('amperm' => Mage::getSingleton('core/resource')->getTableName('amperm/perm')),
                        'e.entity_id = amperm.cid',
                        array('am_uid' => 'uid')
                    );
            }
        }

        return $this;    
    } 
       
    public function handleCustomerSaveAfter($observer) 
    {
        $posted = array_key_exists('sales_person', Mage::app()->getRequest()->getParams());
        $uid = Mage::app()->getRequest()->getParam('sales_person');
        $user = Mage::getSingleton('admin/session')->getUser();

        if ($uid) {
            Mage::getModel('amperm/perm')->assignOneCustomer($uid, $observer->getCustomer()->getId());
        } elseif ($posted) {
            Mage::getModel('amperm/perm')->removeOneCustomer($observer->getCustomer()->getId());
        } elseif ($user
            && 'customer' == Mage::app()->getRequest()->getControllerName()) {
            if (Mage::helper('amperm')->isSalesPerson($user)) { // creation of customer by dealer
                Mage::getModel('amperm/perm')->assignOneCustomer($user->getId(), $observer->getCustomer()->getId());
            }
        }
               
        return $this; 
    }
    
    public function handleOrderCreated($observer)
    {
        $user = null;
        
        $isGuest = true;
        $orders = $observer->getOrders(); // multishipping
        if (!$orders) { // all other situations like single checkout, google checkout, admin
            $orders = array($observer->getOrder());
            if (is_object($orders[0])) { // no order if recurring profiles
                $isGuest = $orders[0]->getCustomerIsGuest();
            }
        }

        if ($this->_isAdmin()) {
            $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
            if ($uid) {
                Mage::getModel('amperm/perm')->assignOneOrder($uid, $orders[0]->getId());
                $user = Mage::getSingleton('admin/session')->getUser();
            } else {
                $uid = Mage::getModel('amperm/perm')->assignOrderByCustomer($orders[0]->getCustomerId(), $orders[0]->getId());
                $user = Mage::getModel('admin/user')->load($uid);
            }
        } elseif (!$isGuest) {
            foreach ($orders as $order) {
                $uid = Mage::getModel('amperm/perm')->assignOrderByCustomer($order->getCustomerId(), $order->getId());
            }
            $user = Mage::getModel('admin/user')->load($uid);
        }

        if (!$user || !$user->getId()
            && $uid = Mage::getStoreConfig('amperm/general/default_dealer')) {
            $user = Mage::getModel('admin/user')->load($uid);
            if ($user->getId()
                && Mage::helper('amperm')->isSalesPerson($user)) {
                Mage::getModel('amperm/perm')->assignOneOrder($uid, $orders[0]->getId());
            }
        }
        
        // send email
        if (Mage::getStoreConfig('amperm/general/send_email')
            && $user
            && $user->getId()) {
        	
        	/*
        	 * Get Sales Man email
        	 */
        	$emails = array(
        		$user->getEmail()
        	);
        	
        	/*
        	 * Get additional emails to send
        	 */
        	$additionalEmails = $user->getEmails();
        	if (!empty($additionalEmails)) {
        		$additionalEmails = explode(",", $additionalEmails);
        		if (is_array($additionalEmails)) {
        			foreach ($additionalEmails as $email) {
        				$emails[] = trim($email);
        			}
        		}
        	}
        	        	
        	foreach ($emails as $email) {
	            foreach ($orders as $order){
	                try {
	                    $this->_sendEmail($email, $order);
	                } 
	                catch (Exception $e) {
	                    print_r($e);
	                    Mage::logException($e);
	                }   
	            }              
        	}
        }

        return $this;
    }

    // for old versions
    public function handleCoreCollectionAbstractLoadBefore($observer)
    {
        if (!Mage::helper('ambase')->isVersionLessThan(1, 4, 2))
            return;
        
        $collection = $observer->getCollection();
        if ($collection instanceof Mage_Sales_Model_Mysql4_Order_Grid_Collection)
        {
            $mod  = Mage::app()->getRequest()->getModuleName();
            $uid = Mage::helper('amperm')->getCurrentSalesPersonId();
            if ($uid && 'amperm' != $mod){
                $permissionManager = Mage::getModel('amperm/perm');
                if ($collection){
                    $permissionManager->addOrdersRestriction($collection, $uid);
                }
            }
        }
    }
       
    protected function _sendEmail($to, $order)
    {
        if (!Mage::getStoreConfig('amperm/general/send_email')){
            return;
        }
            
        if (!Mage::helper('sales')->canSendNewOrderEmail($order->getStoreId())) {
            return;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $paymentBlock = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($order->getStoreId());

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */


        $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$order->getStoreId()))
            ->sendTransactional(
                Mage::getStoreConfig('sales_email/order/template', $order->getStoreId()),
                Mage::getStoreConfig('sales_email/order/identity', $order->getStoreId()),
                $to,
                null,
                array(
                    'order'         => $order,
                    'billing'       => $order->getBillingAddress(),
                    'payment_html'  => $paymentBlock->toHtml(),
                )
            );
            
        $translate->setTranslateInline(true);
    }
    
    protected function _isAdmin()
    {
        if (Mage::app()->getStore()->isAdmin())
            return true;
        // for some reason isAdmin does not work here
        if (Mage::app()->getRequest()->getControllerName() == 'sales_order_create')
            return true;
            
        return false;
    }
   
    protected function _isControllerName($place)
    {
        if ('customer' == $place)
            return true;

        $found = false;
        foreach ($this->_controllerNames as $controllerName) {
            if (Mage::app()->getRequest()->getControllerName() == $controllerName . $place) {
                $found = true;
            }
        }
        return $found;
    }

    protected function _prepareColumns(&$grid, $export = false, $place = 'order', $after = 'entity_id')
    {
        if (!$this->_isControllerName($place) ||
            !in_array(Mage::app()->getRequest()->getActionName(), $this->_permissibleActions))
            return $grid;

        $index = 'uid';
        if ('customer' == $place)
            $index = 'am_uid';

        $column = array(
            'header'   => Mage::helper('amperm')->__('Dealer'),
            'type'     => 'options',
            'align'    => 'center',
            'index'    => $index,
            'options'  => Mage::helper('amperm')->getSalesPersonList(),
            'sortable' => false,
            'filter_condition_callback' => array('Amasty_Perm_Block_Adminhtml_Relation', 'dealerFilter'),
        );
        $grid->addColumnAfter($column['index'], $column, $after);

        return $grid;
    }

    public function handleCoreLayoutBlockCreateAfter($observer)
    {
        $block = $observer->getBlock();
        $hlp = Mage::helper('amperm');
        $uid = $hlp->getCurrentSalesPersonId();

        if (!$uid) {
            $gridClass = Mage::getConfig()->getBlockClassName('adminhtml/sales_order_grid');
            if ($gridClass == get_class($block)) {
                $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions));
            }
            $gridClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_grid');
            if ($gridClass == get_class($block)) {
                $this->_prepareColumns($block, in_array(Mage::app()->getRequest()->getActionName(), $this->_exportActions), 'customer');
            }

        }

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit');
        if ($blockClass == get_class($block)) {
            $customer = Mage::registry('current_customer');
            if ($customer->getId()
                && Mage::getSingleton('admin/session')->isAllowed('customer/manage/login_as_customer')) {
                $url = $this->_getLoginUrl($customer);
                $block->addButton('customer_login', array(
                    'label'   => Mage::helper('amperm')->__('Log In as Customer'),
                    'onclick' => 'window.open(\'' . $url . '\', \'customer\');',
                    'class'   => 'back',
                ), 0, 1);
            }
        }
    }

    protected function _getLoginUrl($customer)
    {
        $customerId = $customer->getId();
        $key = $customer->getPasswordHash();
        $permKey = md5($customerId . $key);
        $action = Mage::getSingleton('customer/config_share')->isWebsiteScope() ? 'login' : 'index';
        return Mage::helper('adminhtml')->getUrl('adminhtml/ampermlogin/' . $action, array('customer_id' => $customerId, 'perm_key' => $permKey));
    }

    public function handleCoreBlockAbstractToHtmlAfter($observer)
    {
        $block = $observer->getBlock();
        $transport = $observer->getTransport();
        $html = $transport->getHtml();
        $hlp = Mage::helper('amperm');

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/sales_order_view_info');
        if ($blockClass == get_class($block)
            && Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/assign_order')
            && false === strpos($html, 'amperm_form')
            && Mage::getStoreConfig('amperm/general/reassign_fields')
            && $this->_isControllerName('order')) {
            $tempPos = strpos($html, '<!--Account Information-->');
            if (false !== $tempPos) {
                $pos = strpos($html, '</table>', $tempPos);
                $insert = Mage::app()->getLayout()->createBlock('amperm/adminhtml_info')->setOrderId($block->getOrder()->getId())->toHtml();
                $html = substr_replace($html, $insert, $pos-1, 0);
            }
        }

        $storeId = Mage::app()->getStore()->getId();
        $blockClass = Mage::getConfig()->getBlockClassName('customer/form_register');
        if ($blockClass == get_class($block)
            && Mage::getStoreConfig('amperm/frontend/on_registration', $storeId)
            && false === strpos($html, 'name="sales_person"')) {
            $pos = strpos($html, '<div class="buttons-set');
            $insert = Mage::app()->getLayout()->createBlock('amperm/select')->toHtml();
            $html = substr_replace($html, $insert, $pos-1, 0);
        }

        $blockClass = Mage::getConfig()->getBlockClassName('customer/form_edit');
        if ($blockClass == get_class($block)
            && Mage::getStoreConfig('amperm/frontend/in_account', $storeId)
            && false === strpos($html, 'name="sales_person"')) {
            $pos = strpos($html, '<div class="buttons-set');
            $insert = Mage::app()->getLayout()->createBlock('amperm/select')->toHtml();
            $html = substr_replace($html, $insert, $pos-1, 0);
        }

        $user = Mage::getSingleton('admin/session')->getUser();
        if ($user
            && $hlp->isSalesPerson($user)) {
            $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/sales_order_create_form_account');
            if ('sales_order_create' == Mage::app()->getRequest()->getControllerName()
                && $blockClass == get_class($block)) {
                $html = $this->_restrictGroup($html, $block->getCustomer()->getGroupId(), $user, '<select id="group_id');
            }
            $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit_tab_account');
            if ($blockClass == get_class($block)) {
                $html = $this->_restrictGroup($html, Mage::registry('current_customer')->getGroupId(), $user, '<select id="_accountgroup_id');
            }
        }

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit_tab_view');
        if ($blockClass == get_class($block)) {
            $customer = Mage::registry('current_customer');
            $dealerId = Mage::getModel('amperm/perm')->getUserByCustomer($customer->getId());
            $name = $hlp->__('Not Assigned');
            if ($dealerId) {
                $dealer = Mage::getModel('admin/user')->load($dealerId);
                $name = $dealer->getFirstname() . ' ' . $dealer->getLastname();
            }
            $pos = strpos($html, '</table>');
            $insert = '
                <tr>
                    <td><strong>' . $hlp->__('Dealer') . ':</strong></td>
                    <td>' . $name .'</td>
                </tr>
            ';
            $html = substr_replace($html, $insert, $pos-1, 0);
        }

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_edit_tab_account');
        if ($user
            && !$hlp->isSalesPerson($user)
            && $blockClass == get_class($block)) {
            $pos = strpos($html, '_accountgroup_id');
            $pos = strpos($html, '</tr>', $pos);
            $dealers = $hlp->getSalesPersonList();
            $customer = Mage::registry('current_customer');
            $dealerId = Mage::getModel('amperm/perm')->getUserByCustomer($customer->getId());
            $insert = '
                <tr>
                    <td class="label"><label for="sales_person">' . $hlp->__('Dealer') . '</label></td>
                    <td class="value">
                        <select id="sales_person" name="sales_person" class=" select">
                            <option value="" ' . (!$dealerId ? 'selected="selected" ' : '') . '></option>
            ';
            foreach ($dealers as $userId => $name) {
                $insert .= '<option value="' . $userId . '" ' . ($userId == $dealerId ? 'selected="selected" ' : '') . '>' . $name . '</option>';
            }
            $insert .= '
                        </select>
                    </td>
                </tr>
            ';
            $html = substr_replace($html, $insert, $pos + 5, 0);
        }

        $transport->setHtml($html);
    }

    private function _restrictGroup($html, $groupId, $user, $selector)
    {
        $pos = strpos($html, $selector);
        $begin = strpos($html, '<option', $pos);
        $end = strpos($html, '</select>', $begin);
        $insert = '';
        if ('0' == $user->getCustomerGroupId()
            || $user->getCustomerGroupId()) {
            $allowedGroups = explode(',', $user->getCustomerGroupId());
        } else {
            $allowedGroups = 'empty';
        }
        if ('empty' !== $allowedGroups) {
            $groups = Mage::helper('customer')->getGroups()->toOptionArray();
            foreach ($groups as $group) {
                if (in_array($group['value'], $allowedGroups)
                    || in_array(0, $allowedGroups)) {
                    $insert .= '<option value="' . $group['value'] . '"';
                    if ($groupId == $group['value']) {
                        $insert .= ' selected="selected"';
                    }
                    $insert .= '>' . $group['label'] . '</option>';
                }
            }
        }
        $html = substr_replace($html, $insert, $begin, $end-$begin);
        return $html;
    }

    public function onCoreBlockAbstractToHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        $hlp = Mage::helper('amperm');
        $user = Mage::getSingleton('admin/session')->getUser();

        $massactionClass = Mage::getConfig()->getBlockClassName('adminhtml/widget_grid_massaction');
        $customerGridClass = Mage::getConfig()->getBlockClassName('adminhtml/customer_grid');
        $parentClass = get_class($block->getParentBlock());
        if (Mage::getSingleton('admin/session')->isAllowed('customer/manage/assign_dealer')
            && $user
            && !$hlp->isSalesPerson($user)
            && $massactionClass == get_class($block)
            && $parentClass == $customerGridClass) {
            $block->addItem('assign_dealer', array(
                'label'      => $hlp->__('Assign to Dealer'),
                'url'        => Mage::helper('adminhtml')->getUrl('adminhtml/ampermassigncustomer/massAssign'),
                'additional' => array('amperm_value' => array(
                    'name'   => 'amperm_value',
                    'type'   => 'select',
                    'class'  => 'required-entry',
                    'label'  => $hlp->__('Dealer'),
                    'values' => $hlp->getSalesPersonList(),
                )),
            ));
        }

        $user = Mage::registry('permissions_user');

        if (!$user)
            return $this;

        if (!$user->getId())
            return $this;

        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/permissions_user_edit_tabs');
        if ($blockClass == get_class($block)
            && Mage::helper('amperm')->isSalesPerson($user)) {

            if (!Mage::getStoreConfig('amperm/general/edit_no_grid')) {
                $block->addTab('customers_section', array(
                    'label'     => $hlp->__('Manage Customers'),
                    'title'     => $hlp->__('Manage Customers'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('adminhtml/perm/relation', array('_current' => true)),
                ));
            }

            $block->addTab('orders_section', array(
                'label'     => $hlp->__('Reports'),
                'title'     => $hlp->__('Reports'),
                'class'     => 'ajax',
                'url'       => $block->getUrl('adminhtml/perm/reports', array('_current' => true)),
            ));

            $block->addTab('restrictions_section', array(
                'label'     => $hlp->__('Restrictions'),
                'title'     => $hlp->__('Restrictions'),
                'content'   => $block->getLayout()->createBlock('amperm/adminhtml_restrictions')->toHtml()));

            $block->addTab('additional_information', array(
                'label'     => $hlp->__('Additional'),
                'title'     => $hlp->__('Additional'),
                'content'   => $block->getLayout()->createBlock('amperm/adminhtml_additional')->toHtml()));
        }

        return $this;
    }

    public function onCoreBlockAbstractPrepareLayoutAfter($observer)
    {
        $block = $observer->getBlock();
        $blockClass = Mage::getConfig()->getBlockClassName('adminhtml/permissions_user_edit_tabs');
        if ($blockClass == get_class($block)
            && Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $block->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        return $this;
    }
}