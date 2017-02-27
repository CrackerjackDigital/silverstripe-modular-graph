<?php
namespace Modular\Traits\Graph;

use DataList;
use DataObject;

/**
 * A trait which can be added to a model to so it exhibits ås a Graph Edge Type.
 *
 * @package Modular\Traits\Graph
 */
trait edgetype {

	/**
	 * Returns a list of SocialEdgeType records from the database which apply to actions between two models provided (by their class names).
	 *
	 * e.g.     given 'Member', 'Organisation' ( or an instance of each/either) then would return all SocialEdgeType records that
	 *          implement a Edge between 'Member' and 'Organisation' by filtering by SocialEdgeType SocialEdgeType.FromFieldName and SocialEdgeType.ToFieldName
	 *          fields.
	 *
	 *          given 'Member', null returns all SocialEdgeType records can be performed going from a Member to any model
	 *
	 *          given null, 'Organisation' returns all SocialEdgeType records which can be performed on an Organisation
	 *
	 * @param  DataObject|string|array|null $fromModelClass an instance, class name, array of class names or null to not include in filter
	 * @param  DataObject|string|array|null $toModelClass
	 * @return \DataList
	 */
	public static function get_for_models($fromModelClass, $toModelClass, $typeCodes = []) {
		$filter = static::archetype($fromModelClass, $toModelClass, $typeCodes);
		return static::get()->filter($filter);
	}

	/**
	 * Return a filter which can be used to select a Action based on passed parameters (of which some may be empty).
	 *
	 * @param \DataObject|string $nodeAClass
	 * @param \DataObject|string $nodeBClass
	 * @return array e.g. [ 'FromModel' => 'Member', 'ToModel' => 'SocialOrganisation', 'Code' => ['CRT', 'REG'] ]
	 */
	public static function archetype($nodeAClass = null, $nodeBClass = null, $filters = null) {
		$fromFieldName = static::node_a_field_name();
		$toFieldName = static::node_b_field_name();
		$nodeAClass = static::derive_class_name($nodeAClass ?: get_called_class());
		$nodeBClass = static::derive_class_name($nodeBClass ?: get_called_class());

		$archetype = [];

		if ($nodeAClass) {
			$archetype[ $fromFieldName ] = $nodeAClass;
		}
		if ($nodeBClass) {
			$archetype[ $toFieldName ] = $nodeBClass;
		}
		return $archetype;
	}

	/**
	 * Return the possible actions between two objects, optionally restricted by SocialEdgeType.SocialEdgeType.
	 *
	 * @param                   $fromModel
	 * @param                   $toModel
	 * @param null|string|array $restrictTo
	 * @return DataList
	 */

	public static function get_possible_actions(DataObject $fromModel, DataObject $toModel, $restrictTo = null) {
		$restrictTo = $restrictTo
			? is_array($restrictTo) ? $restrictTo : explode(',', $restrictTo)
			: null;

		$filter = static::archetype(
			$fromModel,
			$toModel,
			$restrictTo
		);
		return static::get()->filter($filter);

	}

	/**
	 * Given two lists of codes either as a single code, CSV or array merge together and return a set of codes. Can
	 * also be used without second parameter to turn $codes into an array.
	 *
	 * @param string|array      $codes e.g. 'MFR', 'MFR,MLO' or ['MFR', 'MLO']
	 * @param null|string|array $merge e.g. 'MFR', 'MFR,MLO' or ['MFR', 'MLO']
	 * @return array numerically indexed array of unique codes
	 */
	public static function merge_code_lists($codes, $merge = []) {
		if (!is_array($codes)) {
			$codes = explode(',', $codes);
		}
		if (!is_array($merge)) {
			$merge = explode(',', $merge);
		}
		return array_unique(array_merge($codes, $merge));
	}

}