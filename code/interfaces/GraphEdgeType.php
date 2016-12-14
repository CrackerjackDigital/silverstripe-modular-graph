<?php
namespace Modular\Interfaces;

use DataObject;

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
	 * Return a list of edge types
	 * e.g. given 'Member', 'Organisation' return all allowed edge type between the two
	 *      given 'Member', null return all allowed edge types from a Member
	 *      given nul, 'Organisation' return all allowed edge types to an Organisation
	 *
	 * @param  DataObject|string|null $nodeAModel
	 * @param  DataObject|string|null $nodeBModel
	 * @return \DataList
	 */
	public static function get_by_models($nodeAModel, $nodeBModel);

	/**
	 * Build a query used in checking a SocialActionType exists for the codes.
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
	 * NB we take ints not models here as the model class etc comes from instance of SocialActionType
	 *
	 * @param int  $nodeAID
	 * @param int  $nodeBID
	 * @param null $archetype
	 * @return \SS_List
	 */
	public function buildGraphEdgeTypeInstanceQuery($nodeAID, $nodeBID, &$archetype = null);

}