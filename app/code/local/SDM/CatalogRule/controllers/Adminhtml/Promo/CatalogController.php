<?php
/**
 * Separation Degrees One
 *
 * Magento catalog rule customizations
 *
 * PHP Version 5
 *
 * @category  SDM
 * @package   SDM_CatalogRule
 * @author    Separation Degrees One <magento@separationdegrees.com>
 * @copyright Copyright (c) 2015 Separation Degrees One (http://www.separationdegrees.com)
 */

$base = Mage::getModuleDir('controllers', 'Mage_Adminhtml');
require_once $base.DS."Promo".DS."CatalogController.php";

/**
 * SDM_CatalogRule_Adminhtml_Promo_CatalogController class
 */
class SDM_CatalogRule_Adminhtml_Promo_CatalogController
    extends Mage_Adminhtml_Promo_CatalogController
{
    /**
     * Edit action
     *
     * @return void
     */
    public function editAction()
    {
        $this->_title($this->__('Promotions'))->_title($this->__('Catalog Price Rules'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('catalogrule/rule');

        if ($id) {
            $model->load($id);
            if (! $model->getRuleId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('catalogrule')->__('This rule no longer exists.')
                );
                $this->_redirect('*/*');
                return;
            }
        }

        $this->_title($model->getRuleId() ? $model->getName() : $this->__('New Rule'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');

        Mage::register('current_promo_catalog_rule', $model);

        $this->_initAction()->getLayout()->getBlock('promo_catalog_edit')
            ->setData('action', $this->getUrl('*/promo_catalog/save'));

        $breadcrumb = $id
            ? Mage::helper('catalogrule')->__('Edit Rule')
            : Mage::helper('catalogrule')->__('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb)->renderLayout();

    }

    /**
     * Save action
     *
     * @return void
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('catalogrule/rule');
                Mage::dispatchEvent(
                    'adminhtml_controller_catalogrule_prepare_save',
                    array('request' => $this->getRequest())
                );
                $data = $this->getRequest()->getPost();
                $data = $this->_filterDates($data, array('from_date', 'to_date'));
                if ($id = $this->getRequest()->getParam('rule_id')) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('catalogrule')->__('Wrong rule specified.'));
                    }
                }

                $validateResult = $model->validateData(new Varien_Object($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->_getSession()->addError($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->_redirect('*/*/edit', array('id'=>$model->getId()));
                    return;
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);

                $autoApply = false;
                if (!empty($data['auto_apply'])) {
                    $autoApply = true;
                    unset($data['auto_apply']);
                }

                $this->_handleImages($data, $model);

                $model->loadPost($data);

                Mage::getSingleton('adminhtml/session')->setPageData($model->getData());

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('catalogrule')->__('The rule has been saved.')
                );
                Mage::getSingleton('adminhtml/session')->setPageData(false);
                if ($autoApply) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                } else {
                    Mage::getModel('catalogrule/flag')->loadSelf()
                        ->setState(1)
                        ->save();
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('*/*/edit', array('id' => $model->getId()));
                        return;
                    }
                    $this->_redirect('*/*/');
                }
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while saving the rule data. Please review the log and try again.')
                );
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('rule_id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Delete action
     *
     * @return void
     */
    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('catalogrule/rule');
                $model->load($id);
                //$this->_deleteSaleIconImage($model);
                $model->delete();
                Mage::getModel('catalogrule/flag')->loadSelf()
                    ->setState(1)
                    ->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('catalogrule')->__('The rule has been deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('catalogrule')->__('An error occurred while deleting the rule. Please review the log and try again.')
                );
                Mage::logException($e);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('catalogrule')->__('Unable to find a rule to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * Handle uploaded images
     *
     * @param array         $data
     * @param Varien_Object $model
     *
     * @return void
     */
    protected function _handleImages(&$data, $model)
    {
        $filename = 'custom_sale_icon';
        $media = Mage::getBaseDir('media');
        $path = DS . "sale_labels" . DS;

        if (isset($_FILES[$filename]['name']) && $_FILES[$filename]['name'] != '') {
            /*
                Move uploaded file logic when user select a image
            */

            // First delete any existing image
            $oldFile = $model->getData($filename);
            if (isset($oldFile) && !empty($oldFile)) {
                unlink($media . $oldFile);
            }

            // Now, handle the upload process for the image
            $uploader = new Varien_File_Uploader($filename);
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);

            $newName = hash('md5', rand().microtime().rand().$_FILES[$filename]['name']);
            $nameSplit = explode('.', $_FILES[$filename]['name']);
            $newName .= '.'.end($nameSplit);
            $result = $uploader->save($media.$path, $newName);

            $data[$filename] = $path.$newName;
        } else {
            if (isset($data[$filename]['delete']) && $data[$filename]['delete'] == 1) {
                $url = $model->getData($filename);
                if (isset($url) && !empty($url)) {
                    unlink(Mage::getBaseDir('media') . $url);
                }
                $data[$filename] = '';
            } else {
                // In edit mode when user did nothing then you must
                // remove element from data so magento will ignore field
                unset($data[$filename]);
            }
        }

        return $this;
    }
}
