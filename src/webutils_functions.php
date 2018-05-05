<?php

	/**
	 * Proxy wrappers to use utils without namespaces
	 */

	if (!function_exists('config'))
	{
		function config() {
			return call_user_func_array('Applehail\Webutils\config', func_get_args());
		}
	}

	if (!function_exists('logger'))
	{
		function logger() {
			return call_user_func_array('Applehail\Webutils\logger', func_get_args());
		}
	}

	if (!function_exists('file_get_ext'))
	{
		function file_get_ext() {
			return call_user_func_array('Applehail\Webutils\file_get_ext', func_get_args());
		}
	}

	if (!function_exists('d'))
	{
		function d() {
			call_user_func_array('Applehail\Webutils\d', func_get_args());
		}
	}

	if (!function_exists('dd'))
	{
		function dd() {
			call_user_func_array('Applehail\Webutils\dd', func_get_args());
		}
	}

	if (!function_exists('get_tag'))
	{
		function get_tag() {
			return call_user_func_array('Applehail\Webutils\get_tag', func_get_args());
		}
	}

	if (!function_exists('get_tag_regexp'))
	{
		function get_tag_regexp() {
			return call_user_func_array('Applehail\Webutils\get_tag_regexp', func_get_args());
		}
	}

	if (!function_exists('get_tag_array'))
	{
		function get_tag_array() {
			return call_user_func_array('Applehail\Webutils\get_tag_array', func_get_args());
		}
	}

	if (!function_exists('filecache'))
	{
		function filecache() {
			return call_user_func_array('Applehail\Webutils\filecache', func_get_args());
		}
	}

	if (!function_exists('translit'))
	{
		function translit() {
			return call_user_func_array('Applehail\Webutils\translit', func_get_args());
		}
	}

	if (!function_exists('get_cpu'))
	{
		function get_cpu() {
			return str_replace(array(' ', '\\', '+'), array('-', '-', '-'), mb_strtolower(translit(func_get_args())));
		}
	}

	if (!function_exists('gen_js'))
	{
		function gen_js() {
			return call_user_func_array('Applehail\Webutils\gen_js', func_get_args());
		}
	}

	if (!function_exists('gen_css'))
	{
		function gen_css() {
			return call_user_func_array('Applehail\Webutils\gen_css', func_get_args());
		}
	}

	if (!function_exists('mail'))
	{
		function mail() {
			return call_user_func_array('Applehail\Webutils\mail', func_get_args());
		}
	}

	if (!function_exists('attr_esc'))
	{
		function attr_esc() {
			return call_user_func_array('Applehail\Webutils\attr_esc', func_get_args());
		}
	}

	if (!function_exists('redirect'))
	{
		function redirect() {
			return call_user_func_array('Applehail\Webutils\redirect', func_get_args());
		}
	}

	if (!function_exists('request'))
	{
		function request() {
			return call_user_func_array('Applehail\Webutils\request', func_get_args());
		}
	}
