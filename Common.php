<?php

use s\Request;

if (!function_exists('input')) {
	function input($key = null, $type = 'param') {
		return Request::param($key, $type);
	}
}