<?php
namespace Modular\Interfaces\Graph;

use Modular\Interfaces\Graph;

use DataObject;

/**
 * A model or type which is to be used as an Edge should implement this interface
 *
 * @package Modular\Interfaces
 */
interface EdgeType extends Graph {

	/**
	 * Return a graph edge type by it's code.
	 *
	 * @param string $typeCode
	 * @return EdgeType|null
	 */
	public static function get_by_code($typeCode);

	/**
	 * Return the name of the field on this edge type used to find them, e.g. 'Code'.
	 *
	 * @return string
	 */
	public static function code_field_name($suffix = '');

	/**
	 * Check we can perform the action represented by the type
	 *
	 * @param $typeCode
	 * @param $nodeAModel
	 * @param $nodeBModel
	 * @return bool
	 */
	public static function check_permission($typeCode, $nodeAModel, $nodeBModel);

	/**
	 * Return a list of edge types
	 * e.g. given 'Member', 'Organisation' return all allowed edge type between the two
	 *      given 'Member', null return all allowed edge types from a Member
	 *      given nul, 'Organisation' return all allowed edge types to an Organisation
	 *
	 * @param  DataObject|string|null $fromModelClass
	 * @param  DataObject|string|null $toModelClass
	 * @param array                   $typeCodes
	 * @return \DataList of GraphEdgeType derived classes
	 */
	public static function get_for_models($fromModelClass, $toModelClass, $typeCodes = []);

	/**
	 * Build a filter to fetch all GraphEdgesTypes based on this GraphEdgeType instance's settings
	 *
	 * @param DataObject|string $nodeAClass
	 * @param DataObject|string $nodeBClass
	 * @param array             $typeCodes
	 * @return array e.g. ['FromModel' => 'ModelA', 'ToModel' => 'ModelB', 'Code' => 'CRT' ]
	 */
	public static function archtype($nodeAClass, $nodeBClass, $typeCodes = []);

}