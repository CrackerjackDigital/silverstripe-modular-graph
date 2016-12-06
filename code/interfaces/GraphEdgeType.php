<?php
namespace Modular\Interfaces;
/**
 * A model or type which is to be used as an Edge should implement this interface
 *
 * @package Modular\Interfaces
 */
interface GraphEdgeType extends Graph {

	/**
	 * Check we can perform the action represented by the type
	 *
	 * @param $typeCode
	 * @param $nodeBModel
	 * @param $nodeAModel
	 * @return bool
	 */
	public static function check_permission($typeCode, $nodeBModel, $nodeAModel);

	/**
	 * Return a list of EdgeTypes which satisfy the from, to and typeCodes, e.g. on a SocialAction this
	 * would be SocialActions allowed from $nodeA to $nodeB with provided type code(s)
	 *
	 * @param              $nodeAModel
	 * @param              $nodeBModel
	 * @param string|array $typeCodes
	 * @return \DataList|\ArrayList
	 */
	public static function get_by_edge_type_code($nodeAModel, $nodeBModel, $typeCodes = []);

	/**
	 * Return the name of the field to use when searching on 'codes' for the edge type,
	 * e.g. for a SocialAction this would be 'Code'
	 *
	 * @return string
	 */
	public static function edge_type_field_name();

	/**
	 * Build a query used in checking a SocialAction exists for the codes.
	 */
	public function buildGraphEdgeTypeQuery();

	/**
	 * Build a filter array
	 *
	 * @param $typeCodes
	 * @return array e.g. ['AllowedFrom' => 'ModelA', 'AllowedTo' => 'ModelB' ]
	 */
	public function buildGraphEdgeTypeArchetype();

	/**
	 * Returns a query which uses this GraphEdgeType to find records in a relationship (previous action performed)
	 * table which match the passed in object IDs. e.g. MemberOrganisation with
	 * Member.ID = $formObjectID and OrganisationModelID = $toModelID
	 *
	 * NB we take ints not models here as the model class etc comes from instance of SocialAction
	 *
	 * @param int  $nodeAID
	 * @param int  $nodeBID
	 * @param null $archetype
	 * @return \SS_List
	 */
	public function buildGraphEdgeTypeInstanceQuery($nodeAID, $nodeBID, &$archetype = null);

}