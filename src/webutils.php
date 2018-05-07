<?php
	namespace Applehail\Webutils;

	use League\Flysystem\Adapter\Local;
	use League\Flysystem\Filesystem;
	use Cache\Adapter\Filesystem\FilesystemCachePool;
    use GuzzleHttp\Client;

	require_once(__DIR__ . '/webutils_functions.php');

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

    /**
     * http request
     *  $data = $request->getBody()->getContents();
     *  $headers = $request->getHeaders();
     *  $request->getStatusCode()
     *
     * @param  string  $url
     * @param  string  $type            POST / GET / json
     * @param  array   $params          data to request
     * @param  array   $params_headers  request headers
     * @param  boolean $redirects
     * @param  string  $ref             referer
     * @param  integer $connect_timeout sec
     * @param  boolean $debug
     * @return request                   result
     */
    function request($url, $type = 'POST', $params = [], $params_headers = [], $redirects = true, $ref = '', $connect_timeout = 3, $debug = false)
    {
        $guzzle = new \GuzzleHttp\Client(['cookies' => true, 'connect_timeout' => $connect_timeout]);

        switch ($type) {
            case 'GET':
                $paramsType = 'request';
                break;
            case 'POST':
                $paramsType = 'form_params';
                break;
            case 'json':
                $type = 'POST';
                $paramsType = GuzzleHttp\RequestOptions::JSON;
                break;
            default:
                die('error getPage type');
                break;
        }
        $options = ['allow_redirects' => ['referer' => true],  $paramsType => $params];
        if (!$redirects){
            $options['allow_redirects'] = false;
        }
        $options['headers'] = [
            'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36',
            //'Referer' => 'https://twitter.com/',
            //'Origin' => 'https://twitter.com/',
        ];
        $options['headers'] = array_merge($options['headers'], $params_headers);
        if ($ref){
            $options['headers']['Referer'] = $ref;
        }

        $res = $guzzle->request($type, $url, $options);
        if (!$redirects){
            //d($res);
        }
        if ($debug){
            d($type . ' : ' . $url);
            d($options);
            d($res->getStatusCode());
        }

        return $res;
    }

	/**
	 * return file extension without dot
	 * @param  string $fileName
	 * @return string file extension
	 */
	function file_get_ext($fileName)
	{
		$ext = strtolower( substr (strrchr ($fileName,'.'), 1 ) );
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
	 * @return array       array of results [[1=>first, 2=>second], [1=>...] ]
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
	 * translit string
	 * @param  [type] $string [description]
	 * @param  string $lang   [description]
	 * @return [type]         [description]
	 */
	function translit($string, $lang = 'ru')
	{
		return transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $string);
	}

	/**
	 * @see https://github.com/mrclay/minify
	 * generate js include html code
	 * @param  array  $items      web path to files
	 * @param  boolean $production production mode
	 * @param  array  $with_build files with build
	 * @param  int  $build      build number
	 * @return void
	 */
	function gen_js($items, $production = false, $with_build = '', $build)
	{
    	if ($production) {
    		$files = 'f=' . join(',', $items);
    		?>
    		<script type="text/javascript" src="<?php echo www_root?>min/?<?php echo $files?>"></script>
    		<?php
    	} else {
    		foreach ($items as $key => $value) {
    			$add = '';
    			if ($with_build && in_array(basename($value), $with_build)){
    				$add = '?v=' . $build;
    			}
    			?>
    			<script type="text/javascript" src="<?php echo $value . $add?>"></script>
        		<?php
    		}
    	}
	}

	/**
	 * @see https://github.com/mrclay/minify
	 * generate css include html code
	 * @param  array  $items      web path to files
	 * @param  boolean $production production mode
	 * @param  array  $with_build files with build
	 * @param  int  $build      build number
	 * @return void
	 */
	function gen_css($items, $production = false, $with_build = '', $build)
	{
    	if ($production) {
	   		$files = 'f=' . join(',', $items);
    		?>
			<link rel="stylesheet" href="<?php echo www_root?>min/?<?php echo $files?>" type="text/css" media="screen,projection" />
    		<?php
    	} else {
    		foreach ($items as $key => $value) {
    			$add = '';
    			if ($with_build && in_array(basename($value), $with_build)){
    				$add = '?v=' . $build;
    			}
    			?>
        		<link rel="stylesheet" href="<?php echo $value . $add?>" type="text/css" media="screen,projection" />
        		<?php
    		}
    	}
	}

	/**
	 * email to
	 * @param  [type] $to      [description]
	 * @param  [type] $subject [description]
	 * @param  [type] $msg     [description]
	 * @param  [type] $from    [description]
	 * @return [type]          [description]
	 */
	function mail($to, $subject, $msg, $from)
	{
		$headers   = array();
		$headers[] = "Content-type: text/plain; charset=utf-8";
		$headers[] = "From: ".$from['title']." <".$from['email'].">";
		//$headers[] = "Bcc: JJ Chong <bcc@domain2.com>";
		$headers[] = "Reply-To: ".$from['email'];
		$headers[] = "X-Mailer: PHP/".phpversion();
		return mail($to['title']." <".$to['email'].">", $subject, $msg, join("\r\n", $headers), '-f'.$from['email']);
	}

	/**
	 * escape string for html
	 * @param  [type] $string [description]
	 * @return [type]         [description]
	 */
	function attr_esc($string)
	{
		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'utf-8');
	}

	/**
	 * redirect
	 * @param  [type]  $url    [description]
	 * @param  boolean $client [description]
	 * @return [type]          [description]
	 */
	function redirect($url, $client = true)
	{
		if ($client) {
			?><script type="text/javascript">location.replace('<?php echo $url?>');</script><?php
		} else {
			header('location: '.$url);
		}
	}

    /**
     * add data to file
     * @param string $fileName
     * @param [type] $data
     * @param string $mode
     */
    function add_to_file($fileName, $data, $rewrite = false)
    {
        $mode = $rewrite ? 'w+' : 'a';
        $file = fopen($fileName, $mode);
        if (!$file) {
            throw new \Exception(sprintf(
                "Error create file '%s'",
                $fileName
            ));

        }
        flock($file, LOCK_EX);
        fwrite($file, $data);
        flock($file, LOCK_UN);
        fclose($file);
    }

    /**
     * return domain path of url with punycode converted to utf8
     * @param  string $string
     * @return string
     */
    function puny_to_text($string)
    {
        $tag = '://';
        if (strpos($string, $tag) !== false){
            $string = explode($tag, $string);
            $string = $string[1];
        }
        $string = explode('/', $string);
        $string = $string[0];

        return idn_to_utf8($string);
    }

    /**
     * get youtube video img
     * @param  string $video video url
     * @return string
     */
    function get_youtube_img($video)
    {
       if (strstr($video, '://youtu.be/')){
            $img = str_replace('https://youtu.be/','',$video);
        } else {
            $img = str_replace('https://www.youtube.com/watch?v=','',$video);
        }
        return 'https://i1.ytimg.com/vi/'.$img.'/mqdefault.jpg';
    }

    /**
     * get youtube player html code by link
     * @param  string  $video    video url
     * @param  integer $w        [description]
     * @param  integer $h        [description]
     * @param  boolean $controls [description]
     * @param  boolean $api      [description]
     * @return [type]            [description]
     */
    function get_youtube_player($video, $w = 970, $h = 546, $controls = false, $api = false)
    {
       if (strstr($video, '://youtu.be/')){
            $img = str_replace('https://youtu.be/','',$video);
        } else {
            $img = str_replace('https://www.youtube.com/watch?v=','',$video);
        }
        if ($api){
            return '<div class="yt-item" data-id="'.$video.'" data-controls="'.$controls.'" width="'.$w.'" height="'.$h.'"></div>';
        } else {
            $add = '';
            if (!$controls){
                $add .= '&amp;controls=0&amp;showinfo=0';
            }
            return '<iframe width="'.$w.'" height="'.$h.'" src="https://www.youtube.com/embed/'.$img.'?rel=0.'.$add.'" frameborder="0" allowfullscreen></iframe>';
        }
    }
