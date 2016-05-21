<?php
/**
 * Mexbs_Tieredcoupon_Adminhtml_tieredcouponController
 * class that is used for handling the grouping coupons backend actions
 *
 * @copyright MexBS
 * @author MexBS <it@mexbs.com>
 */
class Mexbs_Tieredcoupon_Adminhtml_TieredcouponController extends Mage_Adminhtml_Controller_Action
{
    /**
     * checks whether the current user is allowed to use the groouping coupons module
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('promo/tieredcoupon');
    }

    /**
     * grouping coupons main backend view (grid)
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->renderLayout();
    }

    /**
     * grouping coupons grid (used for ajax)
     */
    public function gridAction()
    {
        $this->loadLayout(false)
            ->renderLayout();
    }

    /**
     * create new grouping coupon
     */
    public function createAction()
    {
        $this->_forward('edit');
    }

    /**
     * loads the layout and sets the current active menu
     */
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('promo/groupincoupons');
        return $this;
    }

    /**
     * edit grouping coupon
     */
    public function editAction()
    {
        $groupingCouponId = $this->getRequest()->getParam('tieredcoupon_id');
        /**
         * @var Mexbs_Tieredcoupon_Model_Tieredcoupon $groupingCoupon
         */
        $groupingCoupon = Mage::getModel('mexbs_tieredcoupon/tieredcoupon');

        if ($groupingCouponId) {
            $groupingCoupon->load($groupingCouponId);
            if (!$groupingCoupon->getCode()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('mexbs_tieredcoupon')->__('This grouping coupon no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($groupingCoupon->getCode() ? $groupingCoupon->getName() : $this->__('New Tiered Coupon'));

        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $groupingCoupon->addData($data);
        }

        Mage::register('current_tieredcoupon', $groupingCoupon);

        $this->_initAction()->getLayout()->getBlock('tieredcoupon_edit')
            ->setData('action', $this->getUrl('*/*/save'));

        $breadcrumb = $groupingCoupon->getCode()
            ? Mage::helper('mexbs_tieredcoupon')->__('Edit Tiered Coupon')
            : Mage::helper('mexbs_tieredcoupon')->__('New Tiered Coupon');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb)->renderLayout();

    }

    /**
     * decodes the query string of the selected sub coupon codes
     *
     * @param string $encodedSubCoupons
     * @return array
     */
    protected function _decodeGridSerializedSubCouponCodes($encodedSubCoupons)
    {
        $decoded = array();
        parse_str($encodedSubCoupons, $decoded);
        if(is_array($decoded)){
            $decoded = array_keys($decoded);
        }
        return $decoded;
    }

    /**
     * saves the grouping coupon
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                /** @var $groupincoupon Mexbs_Tieredcoupon_Model_Tieredcoupon */
                $groupincoupon = Mage::getModel('mexbs_tieredcoupon/tieredcoupon');
                $data = $this->getRequest()->getPost();
                $data['sub_coupon_codes'] = $this->_decodeGridSerializedSubCouponCodes($data['sub_coupon_codes']);
                $data['code'] = $data['tieredcoupon_code'];

                if(!$data['code']){
                    Mage::throwException(Mage::helper('mexbs_tieredcoupon')->__('The grouping coupon code cannot be empty.'));
                }

                $id = $this->getRequest()->getParam('tieredcoupon_id');
                if ($id) {
                    $groupincoupon->load($id);
                    if ($id != $groupincoupon->getId()) {
                        Mage::throwException(Mage::helper('mexbs_tieredcoupon')->__('Wrong grouping coupon specified.'));
                    }
                }else{
                    if(Mage::helper('mexbs_tieredcoupon/tieredcoupon')->getIsTieredCoupon($data['code'])){
                        Mage::throwException(Mage::helper('mexbs_tieredcoupon')->__('A grouping coupon with the same code exists. Please specify a different code.'));
                    }
                }

                $session = Mage::getSingleton('adminhtml/session');
                $groupincoupon->setData($data)->save();
                $session->addSuccess(Mage::helper('mexbs_tieredcoupon')->__('The grouping coupon has been saved.'));
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('tieredcoupon_id' => $groupincoupon->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('tieredcoupon_id');
                if(isset($data)){
                    Mage::getSingleton('adminhtml/session')->setPageData($data);
                }
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', array('tieredcoupon_id' => $id));
                } else {
                    $this->_redirect('*/*/create');
                }
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('mexbs_tieredcoupon')->__('An error occurred while saving the rule data. Please review the log and try again.'));
                Mage::logException($e);
                if(isset($data)){
                    Mage::getSingleton('adminhtml/session')->setPageData($data);
                }
                $this->_redirect('*/*/edit', array('tieredcoupon_id' => $this->getRequest()->getParam('tieredcoupon_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * shows the sub coupons grid
     */
    public function subcouponsgridAction()
    {
        $groupingCouponId = $this->getRequest()->getParam('tieredcoupon_id');
        /**
         * @var Mexbs_Tieredcoupon_Model_Tieredcoupon $groupingCoupon
         */
        $groupingCoupon = Mage::getModel('mexbs_tieredcoupon/tieredcoupon');

        if ($groupingCouponId) {
            $groupingCoupon->load($groupingCouponId);
            Mage::register('current_tieredcoupon', $groupingCoupon);
        }
        $this->loadLayout();
        $this->getLayout()
            ->getBlock('tieredcoupon_edit_tab_subcoupons_grid')
            ->setSelectedSubcouponsInGrid($this->getRequest()->getPost('selected_subcoupons'));
        $this->renderLayout();
    }

    /**
     * delete the current grouping coupon
     */
    public function deleteAction()
    {
        if ($groupingCouponId = $this->getRequest()->getParam('tieredcoupon_id')) {
            try {
                /**
                 * @var Mexbs_Tieredcoupon_Model_Tieredcoupon $groupingCoupon
                 */
                $groupingCoupon = Mage::getModel('mexbs_tieredcoupon/tieredcoupon');
                $groupingCoupon->load($groupingCouponId);
                $groupingCoupon->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('mexbs_tieredcoupon')->__('The grouping coupon has been deleted.'));
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('mexbs_tieredcoupon')->__('An error occurred while deleting the grouping coupon. Please review the log and try again.'));
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('tieredcoupon_id' => $this->getRequest()->getParam('tieredcoupon_id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('mexbs_tieredcoupon')->__('Unable to find a grouping coupon to delete.'));
        $this->_redirect('*/*/');
    }

}