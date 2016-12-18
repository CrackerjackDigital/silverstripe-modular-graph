<?php
namespace Modular\Types\Graph;

use Modular\config;
use Modular\reflection;
use Modular\Traits\custom_create;
use Modular\Traits\custom_get;
use Modular\Type;

/* abstract */
class EdgeType extends Type implements \Modular\Interfaces\Graph\EdgeType {
	use \Modular\Traits\Graph\edgetype;
	use reflection;
	use config;
	use custom_create;
	use custom_get;

	private static $custom_class_name = '';
	private static $custom_list_class_name = '';

	private static $node_a_field_name = '';
	private static $node_b_field_name = '';

	/**
	 * Return an instance of whatever is configured on Custom with key 'GraphEdgeType' instead of (or otherwise if not defined) this class.
	 *
	 * @return DirectedEdgeType
	 */
	public static function create() {
		return static::custom_create(func_get_args());
	}

	/**
	 * Substitute an alternate list if this class and custom has custom_list_name configured to use instead of the standard DataList.
	 *
	 * This list class con be configured on EdgeTypeList config.custom_class_name
	 *
	 * @param null   $callerClass
	 * @param string $filter
	 * @param string $sort
	 * @param string $join
	 * @param null   $limit
	 * @param string $containerClass
	 * @return \DataList
	 */
	public static function get($callerClass = null, $filter = "", $sort = "", $join = "", $limit = null, $containerClass = 'DataList') {
		return static::custom_get($callerClass, $filter, $sort, $join, $limit, $containerClass);
	}

	public static function node_a_field_name($suffix = '') {
		return static::config()->get('node_a_field_name') . $suffix;
	}

	public static function node_b_field_name($suffix = '') {
		return static::config()->get('node_b_field_name') . $suffix;
	}

}