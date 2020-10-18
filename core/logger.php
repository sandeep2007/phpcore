<?php

class Base_log
{

	/**
	 * Path to save log files
	 *
	 * @var string
	 */
	protected $_log_path;

	/**
	 * File permissions
	 *
	 * @var	int
	 */
	protected $_file_permissions = 0644;

	/**
	 * Level of logging
	 *
	 * @var int
	 */
	protected $_threshold = 1;

	/**
	 * Array of threshold levels to log
	 *
	 * @var array
	 */
	protected $_threshold_array = array();

	/**
	 * Format of timestamp for log files
	 *
	 * @var string
	 */
	protected $_date_fmt = 'Y-m-d H:i:s';

	/**
	 * Filename extension
	 *
	 * @var	string
	 */
	protected $_file_ext;

	/**
	 * Whether or not the logger can write to the log files
	 *
	 * @var bool
	 */
	protected $_enabled = TRUE;

	/**
	 * Predefined logging levels
	 *
	 * @var array
	 */
	protected $_levels = array('ERROR' => 1, 'DEBUG' => 2, 'INFO' => 3, 'ALL' => 4, 'MESSAGE' => 5);

	/**
	 * mbstring.func_overload flag
	 *
	 * @var	bool
	 */
	protected static $func_overload;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{

		$config = &getConfig();

		isset(self::$func_overload) or self::$func_overload = (extension_loaded('mbstring') && ini_get('mbstring.func_overload'));
		if (php_sapi_name() !== "cli") {
			$this->_log_path = ($config['log_path'] !== '' && $config['log_path'] !== NULL) ? $config['log_path'] : APPPATH . '\logs\\';
		} else {
			$this->_log_path = ($config['log_path'] !== '' && $config['log_path'] !== NULL) ? $config['log_path'] : str_replace('index.php', '', APPPATH) . '\logs\\';
		}

		$this->_file_ext = (isset($config['log_file_extension']) && $config['log_file_extension'] !== '')
			? ltrim($config['log_file_extension'], '.') : 'php';

		file_exists($this->_log_path) or mkdir($this->_log_path, 0755, TRUE);

		if (!is_dir($this->_log_path) or !is_really_writable($this->_log_path)) {
			$this->_enabled = FALSE;
		}

		if (is_numeric($config['log_threshold'])) {
			$this->_threshold = (int) $config['log_threshold'];
		} elseif (is_array($config['log_threshold'])) {
			$this->_threshold = 0;
			$this->_threshold_array = array_flip($config['log_threshold']);
		}

		if (!empty($config['log_date_format'])) {
			$this->_date_fmt = $config['log_date_format'];
		}

		if (!empty($config['log_file_permissions']) && is_int($config['log_file_permissions'])) {
			$this->_file_permissions = $config['log_file_permissions'];
		}
	}

	public function write_log($level, $msg)
	{
		if ($this->_enabled === FALSE) {
			return FALSE;
		}

		$level = strtoupper($level);

		if ((!isset($this->_levels[$level]) or ($this->_levels[$level] > $this->_threshold))
			&& !isset($this->_threshold_array[$this->_levels[$level]])
		) {
			return FALSE;
		}

		$filepath = $this->_log_path . 'log-' . date('Y-m-d') . '.' . $this->_file_ext;
		$message = '';

		if (!file_exists($filepath)) {
			$newfile = TRUE;
			// Only add protection to php files
			if ($this->_file_ext === 'php') {
				$message .= "<?php defined('LIBPATH') OR exit('No direct script access allowed'); ?>\n\n";
			}
		}

		if (!$fp = @fopen($filepath, 'ab')) {
			return FALSE;
		}

		flock($fp, LOCK_EX);

		// Instantiating DateTime with microseconds appended to initial date is needed for proper support of this format
		if (strpos($this->_date_fmt, 'u') !== FALSE) {
			$microtime_full = microtime(TRUE);
			$microtime_short = sprintf("%06d", ($microtime_full - floor($microtime_full)) * 1000000);
			$date = new DateTime(date('Y-m-d H:i:s.' . $microtime_short, $microtime_full));
			$date = $date->format($this->_date_fmt);
		} else {
			$date = date($this->_date_fmt);
		}

		$message .= $this->_format_line($level, $date, $msg);

		for ($written = 0, $length = self::strlen($message); $written < $length; $written += $result) {
			if (($result = fwrite($fp, self::substr($message, $written))) === FALSE) {
				break;
			}
		}

		flock($fp, LOCK_UN);
		fclose($fp);

		if (isset($newfile) && $newfile === TRUE) {
			chmod($filepath, $this->_file_permissions);
		}

		return is_int($result);
	}


	protected function _format_line($level, $date, $message)
	{
		return $level . ' - ' . $date . ' --> ' . $message . "\n";
	}

	protected static function strlen($str)
	{
		return (self::$func_overload)
			? mb_strlen($str, '8bit')
			: strlen($str);
	}

	protected static function substr($str, $start, $length = NULL)
	{
		if (self::$func_overload) {
			// mb_substr($str, $start, null, '8bit') returns an empty
			// string on PHP 5.3
			isset($length) or $length = ($start >= 0 ? self::strlen($str) - $start : -$start);
			return mb_substr($str, $start, $length, '8bit');
		}

		return isset($length)
			? substr($str, $start, $length)
			: substr($str, $start);
	}
}

class Base_exceptions
{

	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var	int
	 */
	public $ob_level;

	/**
	 * List of available error levels
	 *
	 * @var	array
	 */
	public $levels = array(
		E_ERROR			=>	'Error',
		E_WARNING		=>	'Warning',
		E_PARSE			=>	'Parsing Error',
		E_NOTICE		=>	'Notice',
		E_CORE_ERROR		=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_STRICT		=>	'Runtime Notice'
	);

	public function __construct()
	{
		$this->ob_level = ob_get_level();
		// Note: Do not log messages from this constructor.
	}

	public function log_exception($severity, $message, $filepath, $line)
	{
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;
		log_message('error', 'Severity: ' . $severity . ' --> ' . $message . ' ' . $filepath . ' ' . $line);
	}

	public function show_404($page = '', $log_error = TRUE)
	{
		// if (is_cli())
		// {
		// 	$heading = 'Not Found';
		// 	$message = 'The controller/method pair you requested was not found.';
		// }
		// else
		// {
		// 	$heading = '404 Page Not Found';
		// 	$message = 'The page you requested was not found.';
		// }

		// // By default we log this, but allow a dev to skip it
		// if ($log_error)
		// {
		// 	log_message('error', $heading.': '.$page);
		// }

		// echo $this->show_error($heading, $message, 'error_404', 404);
		// exit(4); // EXIT_UNKNOWN_FILE
	}

	public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		// $templates_path = config_item('error_views_path');
		// if (empty($templates_path))
		// {
		// 	$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
		// }

		// if (is_cli())
		// {
		// 	$message = "\t".(is_array($message) ? implode("\n\t", $message) : $message);
		// 	$template = 'cli'.DIRECTORY_SEPARATOR.$template;
		// }
		// else
		// {
		// 	set_status_header($status_code);
		// 	$message = '<p>'.(is_array($message) ? implode('</p><p>', $message) : $message).'</p>';
		// 	$template = 'html'.DIRECTORY_SEPARATOR.$template;
		// }

		// if (ob_get_level() > $this->ob_level + 1)
		// {
		// 	ob_end_flush();
		// }
		// ob_start();
		// include($templates_path.$template.'.php');
		// $buffer = ob_get_contents();
		// ob_end_clean();
		// return $buffer;
	}

	public function show_exception($exception)
	{
		// $templates_path = config_item('error_views_path');
		// if (empty($templates_path))
		// {
		// 	$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
		// }

		// $message = $exception->getMessage();
		// if (empty($message))
		// {
		// 	$message = '(null)';
		// }

		// if (is_cli())
		// {
		// 	$templates_path .= 'cli'.DIRECTORY_SEPARATOR;
		// }
		// else
		// {
		// 	$templates_path .= 'html'.DIRECTORY_SEPARATOR;
		// }

		// if (ob_get_level() > $this->ob_level + 1)
		// {
		// 	ob_end_flush();
		// }

		// ob_start();
		// include($templates_path.'error_exception.php');
		// $buffer = ob_get_contents();
		// ob_end_clean();
		// echo $buffer;
	}

	public function show_php_error($severity, $message, $filepath, $line)
	{
		// $templates_path = config_item('error_views_path');
		// if (empty($templates_path))
		// {
		// 	$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
		// }

		// $severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

		// // For safety reasons we don't show the full file path in non-CLI requests
		// if ( ! is_cli())
		// {
		// 	$filepath = str_replace('\\', '/', $filepath);
		// 	if (FALSE !== strpos($filepath, '/'))
		// 	{
		// 		$x = explode('/', $filepath);
		// 		$filepath = $x[count($x)-2].'/'.end($x);
		// 	}

		// 	$template = 'html'.DIRECTORY_SEPARATOR.'error_php';
		// }
		// else
		// {
		// 	$template = 'cli'.DIRECTORY_SEPARATOR.'error_php';
		// }

		// if (ob_get_level() > $this->ob_level + 1)
		// {
		// 	ob_end_flush();
		// }
		// ob_start();
		// include($templates_path.$template.'.php');
		// $buffer = ob_get_contents();
		// ob_end_clean();
		// echo $buffer;
	}
}


if (!function_exists('is_cli')) {
	function is_cli()
	{
		return FALSE;
	}
}

if (!function_exists('is_really_writable')) {
	function is_really_writable($file)
	{
		if (DIRECTORY_SEPARATOR === '/' && (is_php('5.4') or !ini_get('safe_mode'))) {
			return is_writable($file);
		}

		if (is_dir($file)) {
			$file = rtrim($file, '/') . '/' . md5(mt_rand());
			if (($fp = @fopen($file, 'ab')) === FALSE) {
				return FALSE;
			}

			fclose($fp);
			@chmod($file, 0777);
			@unlink($file);
			return TRUE;
		} elseif (!is_file($file) or ($fp = @fopen($file, 'ab')) === FALSE) {
			return FALSE;
		}

		fclose($fp);
		return TRUE;
	}
}

if (!function_exists('log_message')) {
	function log_message($level, $message)
	{
		static $_log;

		if ($_log === NULL) {
			$_log = new Base_log;
		}

		$_log->write_log($level, $message);
	}
}

if (!function_exists('set_status_header')) {
	function set_status_header($code = 200, $text = '')
	{
		if (is_cli()) {
			return;
		}

		if (empty($code) or !is_numeric($code)) {
			show_error('Status codes must be numeric', 500);
		}

		if (empty($text)) {
			is_int($code) or $code = (int) $code;
			$stati = array(
				100    => 'Continue',
				101    => 'Switching Protocols',

				200    => 'OK',
				201    => 'Created',
				202    => 'Accepted',
				203    => 'Non-Authoritative Information',
				204    => 'No Content',
				205    => 'Reset Content',
				206    => 'Partial Content',

				300    => 'Multiple Choices',
				301    => 'Moved Permanently',
				302    => 'Found',
				303    => 'See Other',
				304    => 'Not Modified',
				305    => 'Use Proxy',
				307    => 'Temporary Redirect',

				400    => 'Bad Request',
				401    => 'Unauthorized',
				402    => 'Payment Required',
				403    => 'Forbidden',
				404    => 'Not Found',
				405    => 'Method Not Allowed',
				406    => 'Not Acceptable',
				407    => 'Proxy Authentication Required',
				408    => 'Request Timeout',
				409    => 'Conflict',
				410    => 'Gone',
				411    => 'Length Required',
				412    => 'Precondition Failed',
				413    => 'Request Entity Too Large',
				414    => 'Request-URI Too Long',
				415    => 'Unsupported Media Type',
				416    => 'Requested Range Not Satisfiable',
				417    => 'Expectation Failed',
				422    => 'Unprocessable Entity',
				426    => 'Upgrade Required',
				428    => 'Precondition Required',
				429    => 'Too Many Requests',
				431    => 'Request Header Fields Too Large',

				500    => 'Internal Server Error',
				501    => 'Not Implemented',
				502    => 'Bad Gateway',
				503    => 'Service Unavailable',
				504    => 'Gateway Timeout',
				505    => 'HTTP Version Not Supported',
				511    => 'Network Authentication Required',
			);

			if (isset($stati[$code])) {
				$text = $stati[$code];
			} else {
				show_error('No status text available. Please check your status code number or supply your own message text.', 500);
			}
		}

		if (strpos(PHP_SAPI, 'cgi') === 0) {
			header('Status: ' . $code . ' ' . $text, TRUE);
			return;
		}

		$server_protocol = (isset($_SERVER['SERVER_PROTOCOL']) && in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.0', 'HTTP/1.1', 'HTTP/2'), TRUE))
			? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';
		header($server_protocol . ' ' . $code . ' ' . $text, TRUE, $code);
	}
}



if (!function_exists('_error_handler')) {
	function _error_handler($severity, $message, $filepath, $line)
	{
		$is_error = (((E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR) & $severity) === $severity);

		if ($is_error) {
			set_status_header(500);
		}

		if (($severity & error_reporting()) !== $severity) {
			return;
		}

		$_error = new Base_exceptions;
		$_error->log_exception($severity, $message, $filepath, $line);

		if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
			$_error->show_php_error($severity, $message, $filepath, $line);
		}
		if ($is_error) {
			exit(1);
		}
	}
}

if (!function_exists('_exception_handler')) {
	function _exception_handler($exception)
	{
		$_error = new Base_exceptions;
		$_error->log_exception('error', 'Exception: ' . $exception->getMessage(), $exception->getFile(), $exception->getLine());

		is_cli() or set_status_header(500);

		if (str_ireplace(array('off', 'none', 'no', 'false', 'null'), '', ini_get('display_errors'))) {
			$_error->show_exception($exception);
		}

		exit(1);
	}
}

if (!function_exists('_shutdown_handler')) {
	function _shutdown_handler()
	{
		$last_error = error_get_last();
		if (
			isset($last_error) && ($last_error['type'] & (E_ERROR | E_PARSE | E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_COMPILE_WARNING))
		) {
			_error_handler($last_error['type'], $last_error['message'], $last_error['file'], $last_error['line']);
		}
	}
}

set_error_handler('_error_handler');
set_exception_handler('_exception_handler');
register_shutdown_function('_shutdown_handler');
