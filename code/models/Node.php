<?php
namespace Modular\Models\Graph;

use DataObject;
use Modular\Exceptions\NotImplemented;
use Modular\Interfaces\Graph\EdgeType;
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

	/**
	 * Return a filter which can be used to select Edges or EdgeTypes.
	 *
	 * @param DataObject|string $nodeA    a model instance, ID of an instance or a class name (or null to omit)
	 * @param DataObject|string $nodeB    a model instance, ID of an instance or a class name (or null to omit)
	 * @param EdgeType|mixed    $edgeType
	 * @return array e.g. ['FromModel' => 'Member', 'ToModel' => 'Modular\Models\Social\Organisation' ]
	 *                                    or [ 'FromModelID' => 10, 'Code' => 'CRT' ]
	 * @throws \Modular\Exceptions\NotImplemented
	 */
	public static function archetype($nodeA = null, $nodeB = null, $edgeType = null) {
		throw new NotImplemented("Should be implemented in derived class");
	}

	/**
	 * Return the name of the EdgeType class for this graph, e.g. 'Modular\Types\SocialActionType'
	 *
	 * @param string $fieldName
	 * @return mixed
	 * @throws \Modular\Exceptions\NotImplemented
	 */
	public static function edge_type_class_name($fieldName = '') {
		throw new NotImplemented("Should be implemented in derived class");
	}

	/**
	 * Return the name of the field on associated EdgeType which is used to filter EdgeTypes
	 *
	 * @return string e.g. 'Code'
	 * @throws \Modular\Exceptions\NotImplemented
	 */
	public static function edge_type_filter_field_name() {
		throw new NotImplemented("Should be implemented in derived class");
	}
}