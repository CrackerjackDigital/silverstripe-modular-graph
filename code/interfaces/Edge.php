<?php
namespace Modular\Interfaces\Graph;

use Modular\Interfaces\Graph;

interface Edge extends Graph {
	/**
	 * Do whatever it takes to get this edge out of the system.
	 *
	 * @return bool true if pruned successfully, false otherwise
	 */
	public function prune();

	/**
	 * Set the 'A' node.
	 *
	 * @param Node|\DataObject|int $nodeA
	 *
	 * @return $this
	 */
	public function setNodeA( $nodeA );

	/**
	 * Set the 'B' node.
	 *
	 * @param Node|\DataObject|int $nodeB
	 *
	 * @return $this
	 */
	public function setNodeB( $nodeB );

	/**
	 * Set the edge type reference and also any additional data on the Edge itself.
	 *
	 * @param EdgeType $edgeType
	 *
	 * @return $this
	 */
	public function setEdgeType( $edgeType );

	/**
	 * Return the name of the class which is used for the Edge Type
	 * e.g. 'RelationshipType'
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public static function edgetype_class_name( $fieldName = '' );

	/**
	 * Return the name of the relationship to the edge type class
	 * e.g. 'RelationshipType'
	 *
	 * @param string $fieldName
	 *
	 * @return mixed
	 */
	public static function edgetype_relationship( $fieldName = '' );

	/**
	 * Return the name of the field used for the relationship
	 * e.g. 'RelationshipTypeID' )
	 *
	 * @return mixed
	 */
	public static function edgetype_field_name();

	/**
	 * Return the name of the commonly used filter field on edge types
	 * e.g. 'Code'
	 *
	 * @return mixed
	 */
	public static function edgetype_filter_field_name();

	/**
	 * Return a filter ready to add to filter() method.
	 * e.g. if given a value of 'CFM' then [ 'RelationshipType.Code' => 'CFM' ]
	 * @param $value
	 *
	 * @return mixed
	 */
	public static function edgetype_filter($value);
}