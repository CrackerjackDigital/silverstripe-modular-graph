<?php
namespace Modular\Collections\Graph;

use Modular\Traits\custom_create;


abstract class EdgeList extends \DataList {
	use custom_create;

	// setting this will use this model as the data class for each item in the list
	private static $custom_class_name = '';

	/**
	 * @return EdgeList
	 */
	public static function create() {
		return static::custom_create(func_get_args());
	}
	
	/**
	 * @param $modelClassName
	 * @return \Modular\Collections\Graph\NodeList
	 */
	public static function node_list($modelClassName = '') {
		/** @var NodeList|string $nodeListClass */
		$nodeListClass = static::config()->get('custom_class_name');
		return $nodeListClass::create($modelClassName ?: static::edge()->class_name());
	}
	
	
}