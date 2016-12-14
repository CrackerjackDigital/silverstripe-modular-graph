<?php
namespace Modular\Models;

use ArrayList;
use DataList;
use DataObject;
use Modular\Edges\SocialRelationship;
use Modular\Interfaces\GraphEdgeType;
use Modular\Type;
use Modular\Types\SocialActionType;
use SS_List;

/**
 * GraphEdge
 *
 * @package Modular\Models
 * @property GraphNode FromNode
 * @property GraphNode ToNode
 *
 */

/** abstract if SS would allow it */
class GraphEdge extends \Modular\Model implements \Modular\Interfaces\GraphEdge {
	// these should be defined on derived class and are picked up by SocialRelationshipExtension to
	// add the corresponding relationship between the classes as has_one relationships.
	const TypeClassName = '';       // e.g. 'Modular\Models\$typeClassName'
	const TypeFieldName = '';       # 'Action'

	const NodeAClassName = '';       # 'Modular\Models\GraphNode' or 'Member'
	const NodeAFieldName = '';       # 'FromModel'
	const NodeALabel     = 'Node A';

	const NodeBClassName = '';         # 'Modular\Models\GraphNode' or 'Modular\Models\SocialOrganisation'
	const NodeBFieldName = '';         # 'ToModel'
	const NodeBLabel     = 'Node B';

	/**
	 * @param DataObject $nodeA
	 * @param DataObject $nodeB
	 * @param array      $typeCodes
	 * @return DataList
	 */
	protected static function graph($nodeA, $nodeB, $typeCodes = []) {
		$all = \DataObject::get(get_called_class());
		if ($nodeA) {
			$nodeAID = is_numeric($nodeA) ? $nodeA : $nodeA->ID;
			$all = $all->filter([
				static::node_a_field_name('ID') => $nodeAID,
			]);
		}
		if ($nodeB) {
			$nodeBID = is_numeric($nodeB) ? $nodeB : $nodeB->ID;
			$all = $all->filter([
				static::node_b_field_name('ID') => $nodeBID,
			]);
		}
		if ($typeCodes) {
			/** @var GraphEdgeType $typeClassName */
			$typeClassName = static::type_class_name();

			$typeIDs = $typeClassName::get_by_models(
				static::node_a_class_name(),
				static::node_b_class_name()
			)->filter('Code', $typeCodes)->column('ID');

			$all = $all->filter([
				static::type_field_name('.ID') => $typeIDs,
			]);
		}
		return $all;
	}

	protected function setNodeA($model) {
		if (is_numeric($model)) {
			$this->{static::node_a_field_name()} = $model;
		} elseif (is_object($model)) {
			$this->{static::node_a_field_name()} = $model->ID;
		} else {
			$this->debug_fail(new \Exception("Don't know what to do with what I was passed, should be a model or an integer ID"));
		}
		return $this;
	}

	/**
	 * Returns the 'NodeA' object instance.
	 *
	 * @return \Modular\Interfaces\GraphNode|DataObject
	 */
	protected function getNodeA() {
		/** @var DataObject $nodeA */
		$nodeA = $this->{static::node_a_field_name()}();
		return $nodeA && $nodeA->exists() ? $nodeA : null;
	}

	/**
	 * @return int|null
	 */
	protected function getNodeAID() {
		return ($model = $this->getNodeA()) ? $model->ID : null;
	}

	protected function setNodeB($model) {
		if (is_numeric($model)) {
			$this->{static::node_b_field_name()} = $model;
		} elseif (is_object($model)) {
			$this->{static::node_b_field_name()} = $model->ID;
		} else {
			$this->debug_fail(new \Exception("Don't know what to do with what I was passed, should be a model or an integer ID"));
		}
		return $this;
	}

	/**
	 * Returns the 'NodeB' object instance.
	 *
	 * @return \Modular\Interfaces\GraphNode|DataObject
	 */
	protected function getNodeB() {
		/** @var DataObject $nodeB */
		$nodeB = $this->{static::node_b_field_name()}();
		return $nodeB && $nodeB->exists() ? $nodeB : null;
	}

	/**
	 * @return int|null
	 */
	protected function getNodeBID() {
		return ($model = $this->getNodeB()) ? $model->ID : null;
	}



	/**
	 * Returns object related 'from' the FromModel (so the to objects).
	 *
	 * e.g. for a MemberOrganisationRelationship given a Member
	 * will return the related Organisations.
	 *
	 * @param \DataObject  $nodeAModel e.g. Member for a MemberOrganisationRelationship
	 * @param string|array $typeCodes  what action performed are we looking for e.g 'CRT'
	 * @return \DataList of 'to' models
	 * @api
	 */
	protected static function node_a_for_type(DataObject $nodeAModel, $typeCodes = []) {
		// return of list of relationship from $nodeAObject to any object
		$relationships = static::graph($nodeAModel, null, $typeCodes);
		return \DataObject::get(static::node_b_class_name())->filter([
			'ID' => $relationships->column(static::node_b_field_name()),
		])->sort('Created', 'Desc');

	}

	/**
	 * Returns objects related to the ToModel (so the 'from' objects) sorted by creation date ascending.
	 *
	 * e.g. for a MemberOrganisation relationship given an SocialOrganisation
	 * will return its related Members.
	 *
	 * @param \DataObject  $nodeBModel e.g. SocialOrganisation for a MemberOrganisationRelationship
	 * @param string|array $typeCodes
	 * @return \DataList of 'from' models
	 * @api
	 */
	protected static function node_b_for_type(DataObject $nodeBModel, $typeCodes = []) {
		// return of list of relationship from $nodeAObject to any object
		$relationships = static::graph($nodeBModel, null, $typeCodes);
		return \DataObject::get(static::node_a_class_name())->filter([
			'ID' => $relationships->column(static::node_a_field_name()),
		])->sort('Created', 'Desc');
	}

	/**
	 * If not existing creates a relationship between two objects of the
	 * concrete type
	 * (e.g. MemberOrganisationRelationship) after checking permissions for the
	 * logged in member for the provided typeCode. If already
	 * exists then leaves alone.
	 *
	 * @param DataObject                    $nodeAModel
	 * @param DataObject                    $nodeBModel
	 * @param string|array|SocialActionType $typeCodes            - csv string or array of
	 *                                                        relationship types or a
	 *                                                        $typeClassName object
	 * @param bool                          $createImpliedActions - also create relationships many many records listed in
	 *                                                        $typeClassName.ImpliedTypes.
	 * @return null
	 * @api
	 */
	public static function make(DataObject $nodeAModel, DataObject $nodeBModel, $typeCodes, $createImpliedActions = true) {
		// check permissions
		if ($typeCodes instanceof Type) {
			$typeCodes = $typeCodes->Code;
		} elseif (!is_array($typeCodes)) {
			$typeCodes = array_filter(explode(',', $typeCodes));
		}

		$types = GraphEdgeType::get_by_models($nodeAModel, $nodeBModel, $typeCodes);

		if ($typeClassName::check_permission($typeCodes, $nodeBModel, $nodeAModel)) {
			$archetype = [];

			$relationship = $type
				->buildGraphEdgeTypeInstanceQuery($nodeAModel->ID, $nodeBModel->ID, $archetype)
				->first();

			if (!$relationship) {
				$relationshipClassName = $type->getTypeClassName();

				$relationship = new $relationshipClassName($archetype);
				$relationship->write();

				if ($createImpliedActions) {
					$type->createImpliedActions($nodeAModel, $nodeBModel, $type);
				}

			}
		}
		return $relationship;
	}

	/**
	 * Remove all relationships of a type from $nodeAObject to $toObject.
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @param            $typeCode
	 * @api
	 */
	public static function remove(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode) {
		/** @var GraphEdgeType $typeClassName */
		$typeClassName = static::type_class_name();

		// check we have permissions to perform supplied relationship
		if ($typeClassName::check_permission($typeCode, $nodeBModel, $nodeAModel)) {
			/** @var \SS_List $types */
			$types = $typeClassName::get_by_archtype($nodeAModel, $nodeBModel, $typeCode);
			if (!$types->count()) {
				$types = $typeClassName::get_by_archtype(
					$nodeAModel,
					$nodeBModel
				)->filter('ParentCode', $typeCode);
			}

			if ($types->count()) {
				/** @var SocialActionType $type */
				foreach ($types as $type) {
					$types = $type->buildGraphEdgeTypeInstanceQuery(
						$nodeAModel->ID,
						$nodeBModel->ID
					);
					if ($types->count()) {
						foreach ($types as $type) {
							$type->delete();
						}
					}
				}
			}
		}
	}

	/**
	 * Check to to see if a type of a particular Code exists between
	 * two objects.
	 *
	 * @param DataObject   $nodeAModel       e.g. a Member
	 * @param DataObject   $nodeBModel       e.g. a Post
	 * @param array|string $typeCode         e.g. 'MLP' for 'Member Likes
	 *                                       Post'
	 * @return int count of types (0 if not related).
	 * @api
	 */
	public static function has_type(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode) {
		return self::types($nodeAModel, $nodeBModel, $typeCode)->count();
	}

	/**
	 * Return the type models (ie SocialModel) from one model
	 * to another
	 * (NOT the actual related objects!)
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @param null       $typeCode
	 * @return \SS_List
	 * @api
	 */
	public static function types(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode = '') {
		/** @var string|SocialActionType $typeClassName */
		$typeClassName = static::type_class_name();

		/** @var SocialActionType $type */
		$type = $typeClassName::get_by_code($typeCode);

		if ($type) {
			return $type->buildGraphEdgeTypeInstanceQuery(
				$nodeAModel->ID,
				$nodeBModel->ID
			);
		}
		return ArrayList::create();
	}

	/**
	 * Return the actual models associated by a type (ie SocialModels)
	 * (NOT the type objects).
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @param            $typeCode
	 * @return SS_List
	 * @api
	 */
	public static function related(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode) {
		/** @var SS_List $types */
		$types = self::types($nodeAModel, $nodeBModel, $typeCode);

		if ($types->count()) {

			$nodeBFieldName = static::node_b_field_name();

			return $nodeBModel::get()->filter([
				'ID' => $types->column($nodeBFieldName),
			]);
		}
		return ArrayList::create();
	}

	public static function type_class_name($fieldName = '') {
		return static::TypeClassName ? (static::TypeClassName . ($fieldName ? ".$fieldName" : '')) : '';
	}

	/**
	 *
	 * @param null $suffix if null then the type code field will be appended to the type model class to get
	 * @return string
	 */
	public static function type_field_name($suffix = null) {
		return static::TypeFieldName ? (static::TypeFieldName . $suffix) : '';
	}

	public static function node_a_label() {
		return _t(static::node_a_class_name() . ".EdgeLabel", static::NodeALabel);
	}

	public static function node_b_label() {
		return _t(static::node_b_class_name() . ".EdgeLabel", static::NodeBLabel);
	}

	public static function node_a_class_name($fieldName = '') {
		return static::NodeAClassName ? (static::NodeAClassName . ($fieldName ? ".$fieldName" : '')) : '';
	}

	public static function node_a_field_name($suffix = 'ID') {
		return static::NodeAFieldName ? (static::NodeAFieldName . $suffix) : '';
	}

	public static function node_b_class_name($fieldName = '') {
		return static::NodeBClassName ? (static::NodeBClassName . ($fieldName ? ".$fieldName" : '')) : '';
	}

	public static function node_b_field_name($suffix = 'ID') {
		return static::NodeBFieldName ? (static::NodeBFieldName . $suffix) : '';
	}

}