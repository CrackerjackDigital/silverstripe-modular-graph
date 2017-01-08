<?php
namespace Modular\Interfaces\Graph;

use Modular\Interfaces\Graph;

use DataObject;
use Modular\Model;

/**
 * Implemented by models which implement edges and models which implement an EdgeType
 *
 * @package Modular\Interfaces
 * @property int RequirePreviousID
 */
interface EdgeType extends Graph {
	
	/**
	 * Check that an EdgeType can exist (be created or is still valid) between two models.
	 *
	 * @param Node|\DataObject|Model $nodeA
	 * @param Node|\DataObject|Model $nodeB
	 * @param EdgeType|mixed         $edgeType an EdgeType, ID or Code which can be used to identify an EdgeType
	 * @return bool
	 */
	public static function valid(DataObject $nodeA, DataObject $nodeB, $edgeType);
	
	/**
	 * Return an instance of the Edge or EdgeType, possibly of a custom class.
	 */
	public static function create();
	
	/**
	 * Return a \DataList or one derived from \DataList.
	 *
	 * @param null   $callerClass
	 * @param string $filter
	 * @param string $sort
	 * @param string $join
	 * @param null   $limit
	 * @param string $containerClass
	 * @return mixed
	 */
	public static function get($callerClass = null, $filter = "", $sort = "", $join = "", $limit = null, $containerClass = 'DataList');
	
	/**
	 * Return the name of the field for the 'nodeA' model that this edge handles, 'FromModelID' in a Directed graph
	 * edge, or 'FromModel' on a Directed graph EdgeType
	 *
	 * @param string $suffix to append to the base field name, in the case of has_many this would be 'ID' to give e.g.
	 *                       'FromID'
	 * @return string
	 */
	public static function node_a_field_name($suffix = 'ID');
	
	/**
	 * Return the name of the field for the nodeB model that this edge handles, e.g 'ToModelID' in a Directed graph
	 * or 'ToModel' on a Directed graph EdgeType
	 *
	 * @param string $suffix to append to the base field name, in the case of has_many this would be 'ID' to give e.g.
	 *                       'ToID'
	 * @return string
	 */
	public static function node_b_field_name($suffix = 'ID');
	
}