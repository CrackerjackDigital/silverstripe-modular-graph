<?php
namespace Modular\Collections\Graph;

use Modular\Traits\custom_create;

abstract class NodeList extends \DataList {
	use custom_create;
	
	// setting this will cause an instance of this class to be used as the model for each item in the list
	private static $custom_class_name = '';

	/**
	 * @return NodeList
	 */
	public static function create() {
		return static::custom_create(func_get_args());
	}

}