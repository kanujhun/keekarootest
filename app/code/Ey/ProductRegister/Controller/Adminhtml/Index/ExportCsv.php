<?php

namespace Ey\ProductRegister\Controller\Adminhtml\Index;

class ExportCsv extends \Magento\Backend\App\Action
{

	protected $_fileFactory;
	protected $_csvParser;
	protected $_fs;
	protected $_dl;
	protected $_requestsFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvParser,
        \Magento\Framework\Filesystem $fs,
        \Magento\Framework\App\Filesystem\DirectoryList $dl,
        \Ey\ProductRegister\Model\RequestsFactory $requestsFactory
    ) {
        $this->_fileFactory = $fileFactory;    
        $this->_csvParser = $csvParser;
        $this->_fs = $fs;
        $this->_dl = $dl;
        $this->_requestsFactory = $requestsFactory;
        parent::__construct($context);
    }
    
    protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Ey_ProductRegister::register');
	}    	
	
    protected function _getFileName()
    {
        return 'registrations_'.strtotime('NOW').'.csv';
    }	
    
    public function execute()
    {
    	$model = $this->_requestsFactory->create();
    	
    	$fileName = $this->_getFileName();
    	
    	$collection = $model->getCollection()
    		->addFieldToSelect('autoId')
    		->addFieldToSelect('name_prefix')
    		->addFieldToSelect('name_first')
    		->addFieldToSelect('name_last')
    		->addFieldToSelect('address_1')
    		->addFieldToSelect('address_2')
    		->addFieldToSelect('city')
    		->addFieldToSelect('state')
    		->addFieldToSelect('zip')
    		->addFieldToSelect('phone_number')
    		->addFieldToSelect('email_address')
    		->addFieldToSelect('product_purchased')    
    		->addFieldToSelect('model_number')
    		->addFieldToSelect('date_of_manufacture')
    		->addFieldToSelect('where_purchased')    
    		->addFieldToSelect('registration_date');
    		   		    				    		    		    		    		    		    		    		    		    		
    	$content = '';
    	
    	$item = $collection->getFirstItem();
    	$headerArray = array_keys($item->toArray());
	
    	$header = implode(',',$headerArray).PHP_EOL;
    	
    	foreach ($collection as $item) {
    		$row = implode('","',$item->getData());
    		$row = '"'.$row.'"'.PHP_EOL;
    		$content .= $row;
    	}
        
        $writer = $this->_fs->getDirectoryWrite('var');
        $file = $writer->openFile($fileName, 'w');
		try {
			$file->lock();
			try {
				$file->write($header.$content);
			}
			finally {
				$file->unlock();
			}
		}
		finally {
			$file->close();
		}

		$this->downloadCsv($this->_dl->getPath('var') . '/' . $fileName);
		
    }   
    
	protected function downloadCsv($file){
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();flush();
            readfile($file);
        } 
    }      

}