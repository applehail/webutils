<?php
	namespace Applehail\Webutils;

	use League\Flysystem\Adapter\Local;
	use League\Flysystem\Filesystem;
	use Cache\Adapter\Filesystem\FilesystemCachePool;

	require_once(__DIR__ . '/utils_functions.php');

	/**
	 * @see https://zendframework.github.io/zend-config/
	 *
	 * @param  string $fileName absolute path
	 * @return \Zend\Config\Config
	 * @throws \Exception if file not exists or not readable
	 */
	function config($fileName)
	{
		$data = array();
		$lowerName = strtolower($fileName);
		$extension = file_get_ext($lowerName);

		switch ($extension) {
			case 'ini':
				$reader = new \Zend\Config\Reader\Ini();
				break;
			case 'xml':
				$reader = new \Zend\Config\Reader\Xml();
				break;
			default:
				$reader = false;
				break;
		}

		if ($reader) {
			$data = $reader->fromFile($fileName);
		} elseif (is_file($fileName) && is_readable($fileName)) {
			$data = require($fileName);
		} else {
			throw new \Exception(sprintf(
                "File '%s' doesn't exist or not readable",
                $fileName
            ));
		}

		return new \Zend\Config\Config($data, true);
	}

	/**
	 * @see https://github.com/Seldaek/monolog
	 * @param  string $channelName
	 * @param  string|null $logFileName full path to log, if empty - used constant LOG_FILENAME
	 * @return \Monolog\Logger
	 * @throws \Exception if log file not exists
	 */
	function logger($channelName = 'logger()', $logFileName = null)
	{
		$logger = new \Monolog\Logger($channelName);
		if (empty($logFileName))
		{
			if (!defined('LOG_FILENAME')) {
				throw new \Exception('Logfile not specified');
			} else {
				$logFileName = LOG_FILENAME;
			}
		}

		$handler = new \Monolog\Handler\StreamHandler($logFileName, \Monolog\Logger::DEBUG);
		$logger->pushHandler($handler);
		return $logger;
	}

	/**
	 * return file extension without dot
	 * @param  string $fileName
	 * @return string file extension
	 */
	function file_get_ext($fileName)
	{
		$ext = strtolower( substr (strrchr ($fn,'.'), 1 ) );
		$ext = explode('?', $ext);
		return $ext[0];
	}

	/**
	 * @see http://symfony.com/doc/current/components/var_dumper.html
	 * @return void
	 */
	function d()
	{
		foreach (func_get_args() as $var) {
			\Symfony\Component\VarDumper\VarDumper::dump($var);
		}
	}

	/**
	 * dump and die
	 * @see d()
	 * @return void
	 */
	function dd()
	{
		call_user_func_array('Applehail\Utils\d', func_get_args());
		die();
	}

	/**
	 * [assets description]
	 * @return [type] [description]
	 */
	function assets(){

	}

	/**
	 * get content between two tags by substring method
	 * @param  string $string
	 * @param  string $tag1
	 * @param  string $tag2
	 * @return string         found content or empty string
	 */
	function get_tag($string, $tag1, $tag2 = '</div>')
	{
		if (mb_stripos($string, $tag1) === false)
		{
			return '';
		}
		$string = mb_substr($string, mb_strpos($string, $tag1) + mb_strlen($tag1));
		if (($tag2 != '') && (mb_strpos($string, $tag2) !== false))
		{
			$string = mb_substr($string, 0, mb_strpos($string, $tag2));
		}

		return $string;
	}

	/**
	 * get content between two tags by regexp
	 * @param  string $text text
	 * @param  string $tag1 tag1
	 * @param  string $tag2 tag2
	 * @return string       found content or empty string
	 */
	function get_tag_regexp($text, $tag1, $tag2 = '</div>')
	{
		$results = array();
		$out = false;
	    if (preg_match('~'.$tag1.'(.*?)'.$tag2.'~ims', $text, $results))
	    {
    	  	$out = trim($results[1]);
		}
		return $out;
	}

	/**
	 * get items by regexp
	 * @param  string $text text
	 * @param  string $tag  regexp
	 * @return array       array of results
	 */
	function get_tag_array($text, $tag)
	{
		$out = false;
	    $results = array();
	    if (preg_match_all('~'.$tag.'~ims', $text, $results, PREG_SET_ORDER)){
	        foreach ($results as $key => $value) {
	        	unset($value[0]);
	        	$out[] = $value;
	        }
	    }
      	return $out;
	}

	/**
	 * get psr-6 filecache pool
	 *
	 * Get an item (existing or new)
	 *   $item = $pool->getItem('cache_key');
	 * Set some values and store
	 *   $item->set('value');
	 *   $item->expiresAfter(60);
	 *   $pool->save($item);
	 *
	 * @see  http://www.php-cache.com/
	 * @param  string $dirName absolute path to cache dir
	 * @return FilesystemCachePool
	 */
	function filecache($dirName)
	{
		$filesystemAdapter = new Local($dirName . '/');
		$filesystem        = new Filesystem($filesystemAdapter);
		$pool = new FilesystemCachePool($filesystem);
		$pool->setFolder('/');
		return $pool;
	}

	function translit($string, $lang = 'ru')
	{
		return Translit::object()->convert($string, $lang);
	}
