<?php
namespace Modular\Models\Graph;

use Modular\Model;
use Modular\Traits\custom_create;
use Modular\Traits\custom_get;
/**
 * Node in a graph which is connected to other nodes by Edges.
 */
/* abstract */
class Node extends Model implements \Modular\Interfaces\Graph\Node {
	use custom_create;
	use custom_get;

	// setting this will cause an instance of this class to be created whenever Node::create() is called.
	// see custom_create
	private static $custom_class_name = '';
	
	// setting this will cause an instance of this class to be used instead of \DataList whenever a list is
	// constructed via ::get(). see custom_get.
	private static $custom_list_class_name = '';

	/**
	 * Return an instance of whatever is on config.custom_class_name or just called class.
	 *
	 * @return \Modular\Interfaces\Graph\Edge
	 */
	public static function create() {
		return static::custom_create(func_get_args());
	}

	/**
	 * Substitute an alternate list if this class and injector has injector_list_name configured to use instead of the standard DataList.
	 *
	 * This list class con be configured on config.custom_list_class_name
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

}