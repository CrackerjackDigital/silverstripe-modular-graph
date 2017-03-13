<?php
namespace Modular\Interfaces;

use DataObject;
use Modular\Interfaces\Graph\EdgeType;

interface Graph {
	/**
	 * Return a filter which can be used to select Edges or EdgeTypes.
	 *
	 * @param DataObject|string $nodeA    a model instance, ID of an instance or a class name (or null to omit)
	 * @param DataObject|string $nodeB    a model instance, ID of an instance or a class name (or null to omit)
	 * @param EdgeType|mixed    $edgeType
	 * @return array e.g. ['FromModel' => 'Member', 'ToModel' => 'Modular\Models\Social\Organisation' ]
	 *                                    or [ 'FromModelID' => 10, 'Code' => 'CRT' ]
	 */
	public static function archetype($nodeA = null, $nodeB = null, $edgeType = null);

	/**
	 * Return the name of the EdgeType class for this graph, e.g. 'Modular\Types\SocialActionType'
	 * @param string $fieldName
	 * @return mixed
	 */
	public static function edgetype_class_name($fieldName = '');

	/**
	 * Return the name of the field on associated EdgeType which is used to filter EdgeTypes
	 * @return string e.g. 'Code'
	 */
	public static function edgetype_field_name();
}