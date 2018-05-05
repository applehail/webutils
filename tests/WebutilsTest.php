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

		public function testSmallFunctions()
		{
			$this->assertEquals('zip', Applehail\file_get_ext('somefile.zip'));
			$this->assertEquals('test', Applehail\get_tag('<some title="123">test<two>', '123">', '<'));
			$this->assertEquals('test', Applehail\get_tag_regexp('<some title="123">test<two>', '<some.*?>', '<two>'));
			$this->assertEquals(array(array(1 => '1'), array(1 => '2')), Applehail\get_tag_array('<p>1</p><p>2</p> text', '<p>(.*?)</p>'));
			$this->assertEquals('test', Applehail\translit('тест'));
			$this->assertEquals('&quot;test&quot;&gt;&lt;', Applehail\attr_esc('"test"><'));
		}

		public function testFileCache()
		{
			$dir = __DIR__ . '/files/cache';
			$cache = filecache($dir);
			$item_name = 'test1';
			$item_value = 'value1';

	 		$item = $cache->getItem($item_name);
	 		$item->set($item_value);
	 		//$item->expiresAfter(60);
	 		$cache->save($item);
	 		$this->assertEquals(true, $cache->hasItem($item_name));
	 		$cache->deleteItem($item_name);
	 		$this->assertEquals(false, $cache->hasItem($item_name));
		}

		public function testRequest()
		{
			$test = Applehail\request('https://ya.ru', 'GET');
			$this->assertNotNull($test);
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