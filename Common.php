<?php

use s\Request;

if (!function_exists('input')) {
	function input($key = '') {
		return Request::param($key);
	}
}