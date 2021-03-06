<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model\Cron;

use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfigInterface;
use Magento\MessageQueue\Model\Cron\ConsumersRunner\PidConsumerManager;
use Magento\Framework\App\DeploymentConfig\FileReader;
use Magento\Framework\App\DeploymentConfig\Writer;
use Magento\Framework\Config\File\ConfigFilePool;
use Magento\Framework\ShellInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Tests the different cases of consumers running by ConsumersRunner
 *
 * {@inheritdoc}
 */
class ConsumersRunnerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Consumer config provider
     *
     * @var ConsumerConfigInterface
     */
    private $consumerConfig;

    /**
     * @var PidConsumerManager
     */
    private $pid;

    /**
     * @var FileReader
     */
    private $reader;

    /**
     * @var ConsumersRunner
     */
    private $consumersRunner;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ConfigFilePool
     */
    private $configFilePool;

    /**
     * @var ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @var ShellInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $shellMock;

    /**
     * @var array
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->shellMock = $this->getMockBuilder(ShellInterface::class)
            ->getMockForAbstractClass();
        $this->pid = $this->objectManager->get(PidConsumerManager::class);
        $this->consumerConfig = $this->objectManager->get(ConsumerConfigInterface::class);
        $this->reader = $this->objectManager->get(FileReader::class);
        $this->filesystem = $this->objectManager->get(Filesystem::class);
        $this->configFilePool = $this->objectManager->get(ConfigFilePool::class);
        $this->appConfig = $this->objectManager->get(ReinitableConfigInterface::class);
        $this->consumersRunner = $this->objectManager->create(
            ConsumersRunner::class,
            ['shellBackground' => $this->shellMock]
        );
        $this->config = $this->loadConfig();

        $this->shellMock->expects($this->any())
            ->method('execute')
            ->willReturnCallback(function ($command, $arguments) {
                $command = vsprintf($command, $arguments);
                $params = \Magento\TestFramework\Helper\Bootstrap::getInstance()->getAppInitParams();
                $params['MAGE_DIRS']['base']['path'] = BP;
                $params = 'INTEGRATION_TEST_PARAMS="' . urldecode(http_build_query($params)) . '"';
                $command = str_replace('bin/magento', 'dev/tests/integration/bin/magento', $command);
                $command = $params . ' ' . $command;

                return exec("{$command} > /dev/null &");
            });
    }

    /**
     * Checks that pid files are created
     *
     * @return void
     */
    public function testCheckThatPidFilesWasCreated()
    {
        $this->consumersRunner->run();
        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $this->waitConsumerPidFile($consumer->getName());
        }
    }

    /**
     * Tests running of specific consumer and his re-running when it is working
     *
     * @return void
     */
    public function testSpecificConsumerAndRerun()
    {
        $specificConsumer = 'quoteItemCleaner';
        $config = $this->config;
        $config['cron_consumers_runner'] = ['consumers' => [$specificConsumer], 'max_messages' => 0];

        $this->writeConfig($config);
        $this->reRunConsumersAndCheckPidFiles($specificConsumer);
        $pid = $this->pid->getPid($specificConsumer);
        $this->reRunConsumersAndCheckPidFiles($specificConsumer);
        $this->assertSame($pid, $this->pid->getPid($specificConsumer));
    }

    /**
     * @param string $specificConsumer
     * @return void
     */
    private function reRunConsumersAndCheckPidFiles($specificConsumer)
    {
        $this->consumersRunner->run();

        sleep(20);

        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $consumerName = $consumer->getName();
            $pidFilePath = $this->pid->getPidFilePath($consumerName);

            if ($consumerName === $specificConsumer) {
                $this->assertTrue(file_exists($pidFilePath));
            } else {
                $this->assertFalse(file_exists($pidFilePath));
            }
        }
    }

    /**
     * Tests disabling cron job which runs consumers
     *
     * @return void
     */
    public function testCronJobDisabled()
    {
        $config = $this->config;
        $config['cron_consumers_runner'] = ['cron_run' => false];

        $this->writeConfig($config);

        $this->consumersRunner->run();

        sleep(20);

        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $pidFilePath = $this->pid->getPidFilePath($consumer->getName());
            $this->assertFalse(file_exists($pidFilePath));
        }
    }

    /**
     * @param string $consumerName
     * @return void
     */
    private function waitConsumerPidFile($consumerName)
    {
        $pidFilePath = $this->pid->getPidFilePath($consumerName);
        $i = 0;
        do {
            sleep(1);
        } while (!file_exists($pidFilePath) && ($i++ < 60));

        if (!file_exists($pidFilePath)) {
            $this->fail($consumerName . ' pid file does not exist.');
        }
    }

    /**
     * @return array
     */
    private function loadConfig()
    {
        return $this->reader->load(ConfigFilePool::APP_ENV);
    }

    /**
     * @param array $config
     * @return void
     */
    private function writeConfig(array $config)
    {
        /** @var Writer $writer */
        $writer = $this->objectManager->get(Writer::class);
        $writer->saveConfig([ConfigFilePool::APP_ENV => $config]);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        foreach ($this->consumerConfig->getConsumers() as $consumer) {
            $consumerName = $consumer->getName();
            $pid = $this->pid->getPid($consumerName);

            if ($pid && $this->pid->isRun($consumerName)) {
                posix_kill($pid, SIGKILL);
            }

            $path = $this->pid->getPidFilePath($consumerName);
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $this->filesystem->getDirectoryWrite(DirectoryList::CONFIG)->writeFile(
            $this->configFilePool->getPath(ConfigFilePool::APP_ENV),
            "<?php\n return array();\n"
        );
        $this->writeConfig($this->config);
        $this->appConfig->reinit();
    }
}
