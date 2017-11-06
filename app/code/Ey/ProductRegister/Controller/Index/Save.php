<?php

namespace Ey\ProductRegister\Controller\Index;

class Save extends \Magento\Framework\App\Action\Action
{

	protected $_requestsFactory;

    public function __construct(
    	\Magento\Framework\App\Action\Context $context,
    	\Ey\ProductRegister\Model\RequestsFactory $requestsFactory
    )
    {
    	$this->_requestsFactory = $requestsFactory;
    
        parent::__construct($context);
    }

    /**
     * The controller action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
		$data = $this->getRequest()->getParams();
		/*
		$reCaptcha = new ReCaptcha('6LdXqTUUAAAAAP7ioeXBX5uEOyeRIkvBJo6X7yFY');
		var_dump(get_class($reCaptcha)); die;
		*/		
		$item = $this->_requestsFactory->create();
		$item->setData($data);
		
		try {
			$item->save();
			$this->messageManager->addSuccess('Thanks for your product registration.');
		} catch (Exception $e) {
			$this->messageManager->addError('There is something went wrong');			
		}
		
		$this->_redirect('*/*/index');
    }
}