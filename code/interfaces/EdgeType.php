<?php
namespace Modular\Interfaces\Graph;

use Modular\Interfaces\Graph;

use DataObject;

/**
 * A model or type which is to be used as an Edge should implement this interface
 *
 * @package Modular\Interfaces
 * @property int RequirePreviousID
 */
interface EdgeType extends Graph {

	/**
	 * We need to override implementations of this to provide a custom model if required, though this should be supplied by \DataObject ultimately.
	 */
	public static function create();

	/**
	 * We need to override implementations of this to provide a custom model if required, though this should be supplied by \DataObject ultimately.
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
	 * Return the name of the field for the 'From' model that this edge handles
	 *
	 * @return string
	 */
	public static function node_a_field_name($suffix = '');

	/**
	 * Return the name of the field for the 'To' model that this edge handles
	 *
	 * @return string
	 */
	public static function node_b_field_name($suffix = '');

	/**
	 * Return a list of edge types
	 * e.g. given 'Member', 'Organisation' return all allowed edge type between the two
	 *      given 'Member', null return all allowed edge types from a Member
	 *      given nul, 'Organisation' return all allowed edge types to an Organisation
	 *
	 * @param  DataObject|string|null $fromModelClass
	 * @param  DataObject|string|null $toModelClass
	 * @return \DataList of GraphEdgeType derived classes
	 */
	public static function get_for_models($fromModelClass, $toModelClass);

	/**
	 * Build a filter to fetch all GraphEdgesTypes based on this GraphEdgeType instance's settings
	 *
	 * @param DataObject|string $nodeAClass
	 * @param DataObject|string $nodeBClass
	 * @return array e.g. ['FromModel' => 'ModelA', 'ToModel' => 'ModelB' ]
	 */
	public static function archtype($nodeAClass, $nodeBClass);

}