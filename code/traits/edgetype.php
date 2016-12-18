<?php
namespace Modular\Traits\Graph;

use DataList;
use DataObject;
use Member;
use Modular\Extensions\Model\Graph\Edge;
use Modular\Fields\SystemData;
use Permission;
use SS_List;

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
		$filter = static::archtype($fromModelClass, $toModelClass, $typeCodes);
		return static::get()->filter($filter);
	}

	/**
	 * Return a filter which can be used to select a Action based on passed parameters (of which some may be empty).
	 *
	 * @param \DataObject|string $nodeAClass
	 * @param \DataObject|string $nodeBClass
	 * @param array              $typeCodes
	 * @return array e.g. [ 'FromModel' => 'Member', 'ToModel' => 'SocialOrganisation', 'Code' => ['CRT', 'REG'] ]
	 */
	public static function archtype($nodeAClass, $nodeBClass, $typeCodes = []) {
		$fromFieldName = static::from_field_name();
		$toFieldName = static::to_field_name();
		$codeFieldName = static::code_field_name();
		$nodeAClass = static::derive_class_name($nodeAClass);
		$nodeBClass = static::derive_class_name($nodeBClass);

		$archtype = [];

		if ($nodeAClass) {
			$archtype[ $fromFieldName ] = $nodeAClass;
		}
		if ($nodeBClass) {
			$archtype[ $toFieldName ] = $nodeBClass;
		}
		if ($typeCodes) {
			$archtype[ $codeFieldName ] = $typeCodes;
		}
		return $archtype;
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

		$filter = static::singleton()->archtype(
			$fromModel,
			$toModel,
			$restrictTo
		);
		return static::singleton()->get()->filter($filter);

	}

	/**
	 * Convenience fetch helper.
	 *
	 * @param string|array $actionCodes
	 * @return \Modular\Interfaces\Graph\EdgeType
	 */
	public static function get_by_code($actionCodes) {
		return self::get()->filter(static::code_field_name(), $actionCodes)->first();
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

	/**
	 * Return all SocialEdgeType records which have the particular code(s) passed as their parent(s).
	 * e.g. passing 'LIK' will return 'MLO', 'MLG' etc which are children of the 'LIK' record. Does not return the
	 * 'LIK' record.
	 *
	 * @param string|array $parentActionCodes
	 * @return SS_List
	 */
	public static function get_by_parent($parentActionCodes) {
		return static::singleton()->get()->filter(static::code_field_name(), $parentActionCodes);
	}

	/**
	 * Returns a list of SocialEdgeType models which have the provided code or have the code as a Parent.
	 *
	 * @param string|DataObject $fromModelClass
	 * @param string|DataObject $toModelClass
	 * @param string|array      $typeCodes
	 * @return DataList
	 */
	public static function get_heirarchy($fromModelClass, $toModelClass, $typeCodes) {
		if (\ClassInfo::exists('SystemData')) {
			$old = SystemData::disable();
		}

		$typeCodes = static::parse_type_codes($typeCodes);
		$fromModelClass = static::derive_class_name($fromModelClass);
		$toModelClass = static::derive_class_name($toModelClass);

		// get relationship types for the code and the parent matching that code.
		$heirarchy = static::get()->filter([
			static::from_field_name() => $fromModelClass,
			static::to_field_name()   => $toModelClass,
		]);
		if ($typeCodes) {
			$heirarchy = $heirarchy->filterAny([
				self::code_field_name() => $typeCodes,
				self::code_field_name() => $typeCodes,
			]);
		}
		if (\ClassInfo::exists('SystemData')) {
			SystemData::enable($old);
		}

		return $heirarchy;
	}

	/**
	 * Returns all defined Actions from one model to another,
	 * optionally filtered by passed SocialEdgeType.Codes
	 *
	 * @param string|DataObject $fromModelClass
	 * @param string|DataObject $toModelClass
	 * @param array             $actionCodes
	 * @return DataList
	 */
	public static function get_by_archtype($fromModelClass, $toModelClass, $actionCodes = null) {
		if (is_object($fromModelClass)) {
			$fromModelClass = get_class($fromModelClass);
		}
		if (is_object($toModelClass)) {
			$toModelClass = get_class($toModelClass);
		}
		return static::get_heirarchy($fromModelClass, $toModelClass, $actionCodes);
	}

	/**
	 * Check to see if valid permissions to perform an actione exist between two objects.
	 *
	 * The 'from' object is generally (and by default) the logged in member, the 'to' object is e.g. an SocialOrganisation
	 * and the permission code is the three-letter code such as 'MAO' for 'Member Administer SocialOrganisation'.
	 *
	 * If a direct relationship is not found then the parent relationship is also tried, e.g. passing in 'ADM' will
	 * check for all Administer actions.
	 *
	 * If the from object is not supplied then the current member is tried, if not logged in then the Guest Member is
	 * used.
	 *
	 * @param DataObject|string $fromModel                 - either class name or an instance of it
	 * @param DataObject|string $toModel                   - either class name or an instance of it
	 * @param string|array      $typeCodes                 - codes to check
	 * @param null              $member
	 * @param bool              $checkObjectInstances      - if we have instances of the from and to models then check
	 *                                                     rules are met
	 * @return bool|int
	 */
	public static function check_permission(
		$fromModel, $toModel, $typeCodes, $member = null, $checkObjectInstances = true
	) {
		// sometimes we only have the model class name to go on, get a singleton to make things easier
		$toModel = ($toModel instanceof DataObject) ? $toModel : singleton($toModel);

		// check if owner is a member of ADMIN, social-admin or can administer the type in general.
		if (static::check_admin_permissions($fromModel, $toModel, $member)) {
			return true;
		}
		$permissionOK = false;

		$actions = static::get_heirarchy($fromModel, $toModel, $typeCodes);

		// get the ids of permissions for the allowed relationships (and Codes to help debugging)
		if ($permissionIDs = $actions->map('PermissionID', self::code_field_name())->toArray()) {

			// get the codes for those permissions using keys from map
			if ($permissions = \Permission::get()->filter('ID', array_keys($permissionIDs))) {
				$permissionCodes = $permissions->column(self::code_field_name());

				// check the codes against the member/other object (which may be guest member)
				// this is a 'general' permission such as 'CAN_Edit_Member' or 'CAN_Like_Post'
				$permissionOK = \Permission::check(
					$permissionCodes,
					"any",
					$fromModel
				);

				// now we get more specific; if we were handed a model object it should have an ID so also check that
				// instance rules are met, such as a previous relationship existing (if just a class was passed to function
				// then we have a singleton and we can't check these requirements).
				// This check uses the SocialEdgeType.RequirePrevious relationship on the current SocialEdgeType

				if ($permissionOK && $toModel->ID && $checkObjectInstances) {

					$typeCodes = $actions->column(self::code_field_name());

					$permissionOK = static::check_rules(
						$fromModel,
						$toModel,
						$typeCodes
					);

					if (!$permissionOK) {
						$permissionOK = static::check_implied_rules(
							$fromModel,
							$toModel,
							$typeCodes
						);
					}
				}

				if ($permissionOK) {
					// now we ask the models to check themselves, e.g. if they require a field to be set outside of the permissions
					// SocialEdgeType model, such as a Member requiring to be Confirmed then the Confirmable extension will
					// intercept this and check the 'RegistrationConfirmed' field
					if ($modelCheck = $toModel->extend('checkPermissions', $fromModel, $toModel, $typeCodes)) {
						$permissionOK = count(array_filter($modelCheck)) != 0;
					}
				}
			}
		}
		return $permissionOK;
	}

	/**
	 * Checks if the logged in member is an admin (is a member of the groups defined in config.admin_groups), or if the
	 * from object has CAN_ADMIN_ on the to object type.
	 *
	 * @param DataObject $fromModel
	 * @param DataObject $toModel
	 * @param Member     $member to check for admin and other permissions
	 * @return bool
	 */
	public static function check_admin_permissions($fromModel, $toModel, $member = null) {
		$member = $member ?: Member::currentUser();
		// check if current or guest member is in admin groups first (guest should never be though!)
		if ($member->inGroups(static::admin_groups())) {
			return true;
		}

		$fromModel = $fromModel ?: $member;

		// get all the ADM type relationships for the models
		$actions = static::get_for_models(
			$fromModel,
			$toModel
		)->filter(self::ParentCodeFieldName, 'ADM');

		// get the permission IDs for the admin actions for the models and check the member has them
		if ($permissionIDs = $actions->map('PermissionID', self::CodeFieldName)->toArray()) {
			if ($permissionCodes = Permission::get()->filter('ID', $permissionIDs)->column(self::CodeFieldName)) {
				return Permission::checkMember($member, $permissionCodes, "any");
			}
		}
		return false;
	}

	/**
	 * Checks 'default' rules such as if passed a Member and an SocialOrganisation then the member can only EDT
	 * if a MemberOrganisationRelationship of type 'CRT' exists. These are set up by the
	 * SocialEdgeType.RequirePrevious relationship.
	 *
	 * @param string|array $actionCodes           - three letter code e.g. 'MEO' for Member edit Organistion
	 * @param DataObject   $fromModel
	 * @param DataObject   $toModel
	 * @param array        $requirementTally      - list of relationship Types checked and the result of permission
	 *                                            check
	 * @return boolean
	 */
	public static function check_rules(DataObject $fromModel, DataObject $toModel, $actionCodes, array &$requirementTally = []) {
		// e.g. get all 'EDT' Actions from e.g. Model to SocialOrganisation
		$edgeTypes = static::get_heirarchy(
			$fromModel,
			$toModel,
			$actionCodes
		);

		$old = SystemData::disable();
		// check each relationships 'RequirePrevious' exists in the corresponding relationship table for the model
		// instances
		/** @var \Modular\Interfaces\Graph\EdgeType $edgeType */
		foreach ($edgeTypes as $edgeType) {
			// NB: only handle has_ones at the moment, need to refactor if we move to multiple previous requirements
			if ($edgeType->RequirePreviousID) {

				/** @var SocialEdgeType $requiredAction */
				$requiredAction = SocialEdgeType::get()->byID($edgeType->RequirePreviousID);

				// now we have a required SocialEdgeType which may be a parent or child
				// if a parent we can't check the relationship exists directly, as there
				// are no Allowed... constraints on a parent, so we need to get the child
				// action which matches the parent code. e.g. for a CRT we need to
				// get the MemberOrganisationRelationship record with 'MCO'

				if (!$requiredAction->ParentID) {
					$requiredAction = static::get()->filter([
						static::FromFieldName => $fromModel->class,
						static::ToFieldName   => $toModel->class,
						self::ParentCodeFieldName      => $requiredAction->Code,
					])->first();
				}
				// get the instance of the required relationship if it exists
				$requiredRelationship = $requiredAction->checkRelationshipExists(
					$fromModel->ID,
					$toModel->ID
				);
				$recordExists = (bool) $requiredRelationship;

				$requirementTally[ $requiredAction->Code ] = $recordExists;
			}
		}
		SystemData::enable($old);

		// if no tally then we didn't hit any requirement to check so OK.
		if ($requirementTally) {
			foreach ($requirementTally as $exists) {
				if (!$exists) {
					// fail a requirement
					return false;
				}
			}
			// all requirements met
			return true;
		}
		// no requirements found so OK
		return true;
	}

	/**
	 * Given a relationship type code checks to see if that the check will pass 'as if' an action was previously created according to 'implied rules'.
	 *
	 * So we need to go back through all previous relationships between two models and see if any of them have a implied relationship which satisfies the
	 * required relationships being checked.
	 *
	 * For example given a relationship of type 'EDT' then that would be satisified by the immediate Require Previous rule of 'CRT' however it can also
	 * be satisfied by the relationship 'REG' from the 'implied relationship' of 'REG' to 'CRT' as if a 'CRT' record had been created in the past along
	 * with the 'REG' record which WAS created.
	 *
	 * @param \DataObject $fromModel
	 * @param \DataObject $toModel
	 * @param array       $actionCodes expected to be already a heirarchy
	 * @return bool true if an implied rule satisfying existing rules was found
	 */
	protected static function check_implied_rules(DataObject $fromModel, DataObject $toModel, $actionCodes) {
		// we start with fail as we are relying on an implied rule to make permissions OK
		$permissionOK = false;

		$old = SystemData::disable();

		$edgeTypes = static::get_heirarchy($fromModel, $toModel, $actionCodes);

		/** @var \Modular\Interfaces\Graph\EdgeType $edgeType */
		foreach ($edgeTypes as $edgeType) {

			// if the edge type requires a previous edge to have been created with this edge type
			if ($edgeType->RequirePreviousID) {
				// get the required relationship
				/** @var \Modular\Interfaces\Graph\EdgeType $requiredAction */
				if ($requiredAction = static::get()->byID($edgeType->RequirePreviousID)) {

					// get the relationship class name for this particular SocialEdgeType
					/** @var Edge|string $relationshipClassName */
					$relationshipClassName = $edgeType->getRelationshipClassName();

					// find all the
					$previous = $relationshipClassName::graph(
						$fromModel,
						$toModel
					);

					/** @var Edge $prev */
					foreach ($previous as $prev) {
						// search previous relationships for an implied relationship matching the expected one
						if ($found = $prev->Action()->ImpliedActions()->find('ID', $requiredAction->ID)) {
							$permissionOK = true;
							// break out of both foreach loops so we can continue to enable SystemData again so can't early return.
							break 2;
						}
					}

				}
			}
		}
		SystemData::enable($old);
		// will only be true if an implied rule was found
		return $permissionOK;
	}

	/**
	 * Build permission code from class name and prefix e.g. Member and CAN_FOLLOW_
	 *
	 * Pads $code right to one '_' if not already there.
	 * Replaces non-alpha in title with '_'.
	 *
	 * @param string $code  e.g. 'CAN_APPROVE' or 'SYS'
	 * @param string $title e.g. 'Members' or 'Placeholder'
	 * @return string
	 */
	public static function make_permission_code($code, $title) {
		return str_replace(
			['__'],
			['_'],
			$code . '_' . preg_replace('/[^A-Za-z_]/', '_', $title));
	}

}