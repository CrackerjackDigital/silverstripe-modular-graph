<?php
namespace Modular\Collections\Graph;

use Modular\Traits\custom_create;

abstract class EdgeTypeList extends \DataList {
	use custom_create;

	private static $custom_class_name = '';

	/**
	 * @return EdgeList
	 */
	public static function create() {
		return static::custom_create(func_get_args());
	}

}