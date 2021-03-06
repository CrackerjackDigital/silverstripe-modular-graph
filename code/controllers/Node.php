<?php
namespace Modular\Controllers\Graph;

use Modular\Controller;
use Modular\Traits\custom_create;

class Node extends Controller {
	use custom_create;

	private static $custom_class_name = '';

	public static function create() {
		return static::custom_create(func_get_args());
	}
}