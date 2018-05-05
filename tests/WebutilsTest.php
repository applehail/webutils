<?php
	namespace Applehail\Webutils\Tests;

	use \PHPUnit\Framework\TestCase;
	use \Symfony\Component\VarDumper\VarDumper;
	use \Applehail\Webutils as Applehail;

	class FunctionsTest extends TestCase
	{
		public function testConfigIni()
		{
			$fileName = __DIR__ . '/files/config.ini';
			$this->checkConfigValues($fileName);
		}

		public function testConfigXml()
		{
			$fileName = __DIR__ . '/files/config.xml';
			$this->checkConfigValues($fileName);
		}

		public function testConfigPhp()
		{
			$fileName = __DIR__ . '/files/config.php';
			$this->checkConfigValues($fileName);
		}

		public function testConfigException()
		{
			$this->expectException(\Exception::class);
			$fileName = __DIR__ . '/files/no_file.php';
			$config = Applehail\config($fileName);
		}

		public function testConfigTypeof()
		{
			$fileName = __DIR__ . '/files/config.ini';
			$config = Applehail\config($fileName);
			$this->assertInstanceOf(\Zend\Config\Config::class, $config);
		}

		public function testLogger()
		{
			$logFile = __DIR__ . '/files/test.log';
			$logger = Applehail\logger('test', $logFile);
			$this->assertInstanceOf(\Monolog\Logger::class, $logger);

			$handle = fopen($logFile, 'w');
			fclose($handle);

			$logger->debug('test message', array('foo' => 'bar'));
			$this->assertStringNotEqualsFile($logFile, '');

			$handle = fopen($logFile, 'w');
			fclose($handle);
		}

		public function testLoggerException()
		{
			$this->expectException(\Exception::class);
			$logger = Applehail\logger('test');
		}

		public function testDumps()
		{
			// just test functions exists and callable
			$this->assertNull(Applehail\d());
		}

		protected function checkConfigValues($fileName)
		{
			$config = Applehail\config($fileName);
			$this->assertEquals('value3', $config['group2']['param3']);
			$this->assertEquals('value1', $config->group1->param1);
			$this->assertEquals('value1', $config->group1->get('param1'));
			$this->assertEquals('not_found', $config->group1->get('bazzz', 'not_found'));
		}
	}