<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\ResourceConnections\Db\Adapter;

use Magento\TestFramework as TestFramework;
use Magento\ResourceConnections\DB\Adapter\Pdo\MysqlProxy as ResourceConnectionsMysqlProxy;

/**
 * See \Magento\TestFramework\Db\Adapter\TransactionInterface
 */
class MysqlProxy extends ResourceConnectionsMysqlProxy implements TestFramework\Db\Adapter\TransactionInterface
{
    /**
     * Return master connection
     *
     * @return TestFramework\Db\Adapter\Mysql
     */
    protected function getMasterConnection()
    {
        if (!isset($this->masterConnection)) {
            $this->masterConnection = new TestFramework\Db\Adapter\Mysql(
                $this->string,
                $this->dateTime,
                $this->logger,
                $this->selectFactory,
                $this->masterConfig
            );
        }

        return $this->masterConnection;
    }

    /**
     * See \Magento\TestFramework\Db\Adapter\TransactionInterface
     *
     * @return TestFramework\Db\Adapter\Mysql
     */
    public function beginTransparentTransaction()
    {
        $this->masterConnectionOnly = true;
        $this->getMasterConnection()->beginTransparentTransaction();
        return $this;
    }

    /**
     * See \Magento\TestFramework\Db\Adapter\TransactionInterface
     *
     * @return TestFramework\Db\Adapter\Mysql
     */
    public function commitTransparentTransaction()
    {
        $this->masterConnectionOnly = true;
        $this->getMasterConnection()->commitTransparentTransaction();
        return $this;
    }

    /**
     * See \Magento\TestFramework\Db\Adapter\TransactionInterface
     *
     * @return TestFramework\Db\Adapter\Mysql
     */
    public function rollbackTransparentTransaction()
    {
        $this->masterConnectionOnly = true;
        $this->getMasterConnection()->rollbackTransparentTransaction();
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionLevel()
    {
        return $this->getMasterConnection()->getTransactionLevel();
    }
}
