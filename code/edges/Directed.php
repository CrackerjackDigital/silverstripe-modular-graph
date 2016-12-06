<?php
namespace Modular\Edges;

/*
 *
 * @method ActionType ActionType
 *
 */
use ArrayList;
use DataObject;
use Modular\Types\SocialAction;
use SS_List;

class Directed extends \Modular\Model {
	// these should be defined on derived class and are picked up by SocialRelationshipExtension to
	// add the corresponding relationship between the classes as has_one relationships.
	const FromModelClass = '';
	const ToModelClass   = '';
	const FromFieldName  = '';
	const ToFieldName    = '';

	private static $db = [];

	// all social relationships have a ActionType
	private static $has_one = [
		'Type'   => '\Modular\Types\SocialAction',
		'FromNode' => '\Modular\GraphNode',
		'ToNode'   => '\Modular\GraphNode',
	];

	private static $summary_fields = [
		'Action.Title'   => 'Action',
		'FromNode.Title' => 'From',
		'ToNode.Title'   => 'To',
	];

	/**
	 * Use subclasses of this class to find the ones that match the from and to models. One or both of from an to could be falsish, with both being falsish
	 * being equivalent to all subclasses..
	 *
	 * @param string|array $fromModelClasses e.g. 'Member' or '' or [ 'SocialOrganisation', 'Forum' ]
	 * @param string|array $toModelClasses   e.g. 'Member' or '' or [ 'Forum', 'ForumTopic' ]
	 * @return array of SocialModel derived class names that handle from, to or both the passed model classes.
	 */
	public static function implementors($fromModelClasses, $toModelClasses) {
		$fromModelClasses = is_array($fromModelClasses) ? $fromModelClasses : [$fromModelClasses];
		$toModelClasses = is_array($toModelClasses) ? $toModelClasses : [$toModelClasses];
		$classes = [];
		/** @var string|SocialRelationship $class */
		foreach (static::subclasses() as $class) {
			foreach ($fromModelClasses as $fromModelClass) {
				foreach ($toModelClasses as $toModelClass) {
					if ((!$fromModelClass || static::from_class_name() == $fromModelClass) && (!$toModelClass || static::to_class_name() == $toModelClass)) {
						$classes[] = $class;
					}
				}
			}
		}
		return $classes;
	}

	/**
	 * Returns object related 'from' the FromObject (so the to objects).
	 *
	 * e.g. for a MemberOrganisationRelationship given a Member
	 * will return the related Organisations.
	 *
	 * @param \DataObject  $fromObject e.g. Member for a MemberOrganisationRelationship
	 * @param string|array $relationshipTypeCodes
	 * @return \DataList of 'to' models
	 * @api
	 */
	public static function from(DataObject $fromObject, $relationshipTypeCodes) {
		$fromClass = get_class($fromObject);
		$toClass = static::to_class_name();

		$relationshipTypeIDs = SocialAction::get_heirarchy(
			$fromClass,
			$toClass,
			$relationshipTypeCodes
		)->column('ID');

		$toIDs = static::get()->filter([
			static::from_field_name('ID') => $fromObject->ID,
			'RelationshipTypeID'          => $relationshipTypeIDs,
		])->column(static::to_field_name('ID'));

		return $toClass::get()->filter('ID', $toIDs)->sort('Created');
	}

	/**
	 * Returns objects related to the ToObject (so the 'from' objects) sorted by creation date ascending.
	 *
	 * e.g. for a MemberOrganisationRelationship given an SocialOrganisation
	 * will return its related Members.
	 *
	 * @param \DataObject  $toObject e.g. SocialOrganisation for a MemberOrganisationRelationship
	 * @param string|array $relationshipTypeCodes
	 * @return \DataList of 'from' models
	 * @api
	 */
	public static function to(DataObject $toObject, $relationshipTypeCodes) {
		$fromClass = static::from_class_name();
		$toClass = get_class($toObject);

		$relationshipTypeIDs = SocialAction::get_heirarchy(
			$fromClass,
			$toClass,
			$relationshipTypeCodes
		)->column('ID');

		$fromIDs = static::get()->filter([
			static::to_field_name('ID') => $toObject->ID,
			'RelationshipTypeID'        => $relationshipTypeIDs,
		])->column(static::to_field_name('ID'));

		return $fromClass::get()->filter('ID', $fromIDs)->sort('Created');
	}

	/**
	 * If not existing creates a relationship between two objects of the
	 * concrete type
	 * (e.g. MemberOrganisationRelationship) after checking permissions for the
	 * logged in member for the provided relationshipTypeCode. If already
	 * exists then leaves alone.
	 *
	 * @param DataObject $fromModel
	 * @param DataObject $toModel
	 * @param            $relationshipTypeCodes      - csv string or array of
	 *                                               relationship types or a
	 *                                               SocialAction object
	 * @param bool       $createImpliedRelationships - also create relationships many many records listed in
	 *                                               SocialAction.ImpliedRelationshipTypes.
	 * @return null
	 * @api
	 */
	public static function make(DataObject $fromModel, DataObject $toModel, $relationshipTypeCodes, $createImpliedRelationships = true) {
		// check permissions
		if ($relationshipTypeCodes instanceof SocialAction) {
			$relationshipTypeCodes = $relationshipTypeCodes->Code;
		}
		$relationship = null;
		if (SocialAction::check_permission($relationshipTypeCodes, $toModel, $fromModel)) {

			$relationshipTypes = SocialAction::get_heirarchy(
				$fromModel,
				$toModel,
				$relationshipTypeCodes
			);
			if ($relationshipTypes->count()) {
				/** @var SocialAction $relationshipType */
				foreach ($relationshipTypes as $relationshipType) {
					$archetype = [];

					$relationship = $relationshipType
						->buildRelationshipInstanceQuery($fromModel->ID, $toModel->ID, $archetype)
						->first();

					if (!$relationship) {
						$relationshipClassName = $relationshipType->getRelationshipClassName();

						$relationship = new $relationshipClassName($archetype);
						$relationship->write();

						if ($createImpliedRelationships) {
							$relationshipType->createImpliedRelationships($fromModel, $toModel, $relationshipType);
						}

					}
				}
			}
		}
		return $relationship;
	}

	/**
	 * Remove all relationships of a type from $fromObject to $toObject.
	 *
	 * @param DataObject $fromModel
	 * @param DataObject $toModel
	 * @param            $relationshipTypeCode
	 * @api
	 */
	public static function remove(DataObject $fromModel, DataObject $toModel, $relationshipTypeCode) {
		// check we have permissions to perform supplied relationship
		if (SocialAction::check_permission($relationshipTypeCode, $toModel, $fromModel)) {
			$relationshipTypes = SocialAction::get_for_models($fromModel, $toModel, $relationshipTypeCode);
			if (!$relationshipTypes->count()) {
				$relationshipTypes = SocialAction::get_for_models(
					$fromModel,
					$toModel
				)->filter('ParentCode', $relationshipTypeCode);
			}

			if ($relationshipTypes->count()) {
				/** @var SocialAction $relationshipType */
				foreach ($relationshipTypes as $relationshipType) {
					$relationships = $relationshipType->buildRelationshipInstanceQuery(
						$fromModel->ID,
						$toModel->ID
					);
					if ($relationships->count()) {
						foreach ($relationships as $relationship) {
							$relationship->delete();
						}
					}
				}
			}
		}
	}

	/**
	 * Check to to see if a relationship of a particular type exists between
	 * two objects.
	 *
	 * @param DataObject   $fromObject       e.g. a Member
	 * @param DataObject   $toObject         e.g. a Post
	 * @param array|string $relationshipCode e.g. 'MLP' for 'Member Likes
	 *                                       Post'
	 * @return int count of relationships (0 if not related).
	 * @api
	 */
	public static function has_action(DataObject $fromObject, DataObject $toObject, $relationshipCode) {
		return self::actions($fromObject, $toObject, $relationshipCode)->count();
	}

	/**
	 * Return the relationship models (ie SocialModel) from one model
	 * to another
	 * (NOT the actual related objects!)
	 *
	 * @param DataObject $fromObject
	 * @param DataObject $toObject
	 * @param null       $relationshipCode
	 * @return \SS_List
	 * @api
	 */
	public static function actions(DataObject $fromObject, DataObject $toObject, $relationshipCode = '') {
		/** @var SocialAction $relationshipType */
		$relationshipType = SocialAction::get_by_code($relationshipCode);

		if ($relationshipType) {
			return $relationshipType->buildRelationshipInstanceQuery(
				$fromObject->ID,
				$toObject->ID,
				$archtype
			);
		}
		return ArrayList::create();
	}

	/**
	 * Return the actual models associated by a relationship (ie SocialModels)
	 * (NOT the relationship objects).
	 *
	 * @param DataObject $fromObject
	 * @param DataObject $toObject
	 * @param            $relationshipCode
	 * @return SS_List
	 * @api
	 */
	public static function related(DataObject $fromObject, DataObject $toObject, $relationshipCode) {
		/** @var SS_List $relationships */
		$relationships = self::actions($fromObject, $toObject, $relationshipCode);

		if ($relationships->count()) {

			$toFieldName = static::to_field_name();

			return $toObject::get()->filter([
				'ID' => $relationships->column($toFieldName),
			]);
		}
		return ArrayList::create();
	}

	/**
	 * Return all relationships of this type between two models.
	 *
	 * @param \DataObject $fromModel
	 * @param \DataObject $toModel
	 * @param array       $relationshipTypeCodes optional filter history by certain relationship codes.
	 * @return \DataList
	 */
	public static function history(DataObject $fromModel, DataObject $toModel, $relationshipTypeCodes = []) {
		$relationships = static::get()->filter([
			static::from_field_name() => $fromModel->ID,
			static::to_field_name()   => $toModel->ID,
		]);
		if ($relationshipTypeCodes) {
			$relationships = $relationships->filter('Type.Code', $relationshipTypeCodes);
		}
		return $relationships;
	}

	/**
	 * Returns the 'From' object instance.
	 *
	 * @return DataObject
	 */
	public function getFrom() {
		$from = $this->{static::FromFieldName}();
		return $from && $from->exists() ? $from : null;
	}

	/**
	 * @return int|null
	 */
	public function getFromID() {
		return ($model = $this->getFrom()) ? $model->ID : null;
	}

	/**
	 * Returns the 'To' object instance.
	 *
	 * @return DataObject
	 */
	public function getTo() {
		$to = $this->{static::ToFieldName}();
		return $to && $to->exists() ? $to : null;
	}

	/**
	 * @return int|null
	 */
	public function getToID() {
		return ($model = $this->getTo()) ? $model->ID : null;
	}

	public static function from_class_name($fieldName = '') {
		return static::FromModelClass . ($fieldName ? ".$fieldName" : '');
	}

	public static function to_class_name($fieldName = '') {
		return static::ToModelClass . ($fieldName ? ".$fieldName" : '');
	}

	public static function from_field_name($suffix = 'ID') {
		return static::FromFieldName . $suffix;
	}

	public static function to_field_name($suffix = 'ID') {
		return static::ToFieldName . $suffix;
	}

}