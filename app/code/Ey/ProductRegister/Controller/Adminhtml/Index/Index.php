<?php

namespace Ey\ProductRegister\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{
	protected $_resultPageFactory = false;	
	protected $_resultPage = null;
	protected $_requestsModelFactory = null;
	
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Ey\ProductRegister\Model\RequestsFactory $requestsModelFactory
	) {
		$this->_resultPageFactory = $resultPageFactory;	
		$this->_requestsModelFactory = $requestsModelFactory;
		parent::__construct($context);
	}

	public function execute()
	{
		$this->_setPageData();
        return $this->getResultPage();
	}

	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Ey_RegisterProduct::register');
	}

    public function getResultPage()
    {
        if (is_null($this->_resultPage)) {
            $this->_resultPage = $this->_resultPageFactory->create();
        }
        return $this->_resultPage;
    }

    protected function _setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('Ey_RegisterProduct::register');
        $resultPage->getConfig()->getTitle()->prepend((__('Registered Products')));

        //Add bread crumb
        $resultPage->addBreadcrumb(__('Ey'), __('RegisterProduct'));
        $resultPage->addBreadcrumb(__('RegisterProduct'), __('Registered Products'));

        return $this;
    }


}