<?php
namespace Modular\Models;

use ArrayList;
use DataList;
use DataObject;
use Modular\Edges\SocialRelationship;
use Modular\Exceptions\Graph as Exception;
use Modular\Interfaces\GraphEdgeType;
use Modular\Types\SocialActionType;

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

	const EdgeTypeClassName = '';           # 'Modular\Types\GraphEdgeType' or derived class
	const EdgeTypeFieldName = 'EdgeType';   #

	const TypeVariantFieldName = ''; # 'Action'

	const NodeAClassName = '';       # 'Modular\Models\GraphNode' or 'Member'
	const NodeAFieldName = '';       # 'FromModel'
	const NodeALabel     = 'Node A';

	const NodeBClassName = '';         # 'Modular\Models\GraphNode' or 'Modular\Models\SocialOrganisation'
	const NodeBFieldName = '';         # 'ToModel'
	const NodeBLabel     = 'Node B';

	private static $default_sort = 'Created DESC';

	/**
	 * Returns a list of all edges which match on supplied models, edge types and edge type variants, not necessarily in any order.
	 *
	 * @param DataObject|int $nodeA a model or an ID
	 * @param DataObject|int $nodeB a model or an ID
	 * @param array          $typeCodes
	 * @param string         $typeVariant
	 * @return \DataList
	 */
	protected static function graph($nodeA, $nodeB, $typeCodes = [], $typeVariant = '') {
		$graph = \DataObject::get(get_called_class());
		if ($nodeA) {
			$nodeAID = is_numeric($nodeA) ? $nodeA : $nodeA->ID;
			$graph = $graph->filter([
				static::node_a_field_name('ID') => $nodeAID,
			]);
		}
		if ($nodeB) {
			$nodeBID = is_numeric($nodeB) ? $nodeB : $nodeB->ID;
			$graph = $graph->filter([
				static::node_b_field_name('ID') => $nodeBID,
			]);
		}
		if ($typeCodes) {
			/** @var GraphEdgeType $typeClassName */
			$typeClassName = static::type_class_name();

			$typeIDs = $typeClassName::get_for_models(
				static::node_a_class_name(),
				static::node_b_class_name()
			)->filter(static::edge_type_code_field_name(), $typeCodes)->column('ID');

			$graph = $graph->filter([
				static::type_field_name('.ID') => $typeIDs,
			]);
		}
		if ($typeVariant) {
			$graph = $graph->filter([
				self::TypeVariantFieldName => $typeVariant,
			]);
		}
		return $graph;
	}

	/**
	 * For now we delete the node, in future e.g. could use 'Historical' or some other archival method.
	 */
	public function prune() {
		$id = $this->ID;
		try {

			$this->delete();

		} catch (\Exception $e) {
			$this->debug_error("Failed to prune edge with id '$id'");
		}
		return $this->ID == 0;
	}

	/**
	 * Return a list of concrete GraphEdge model class names which implement an edge between two Nodes,
	 * or if null then entering or leaving the other Node class. If both null will return all GraphEdge implementor class names.
	 *
	 * @param DataObject|string|null $nodeA
	 * @param DataObject|string|null $nodeB
	 * @return array list of implementation class names
	 */
	protected static function implementors($nodeA, $nodeB) {
		$nodeAModelClass = is_object($nodeA) ? get_class($nodeA) : $nodeA;
		$nodeBModelClass = is_object($nodeB) ? get_class($nodeB) : $nodeB;

		$implementors = [];

		$subclasses = static::subclasses();
		/** @var GraphEdge|string $subclass */
		foreach ($subclasses as $subclass) {
			$nodeAMatch = $nodeBMatch = false;

			if (!$nodeAModelClass || ($subclass::node_a_class_name() == $nodeAModelClass)) {
				$nodeAMatch = true;
			}
			if (!$nodeBModelClass || ($subclass::node_b_class_name() == $nodeBModelClass)) {
				$nodeBMatch = true;
			}
			if ($nodeAMatch || $nodeBMatch) {
				$implementors[] = $subclass;
			}
		}
		return $implementors;
	}

	/**
	 * @param DataObject|GraphEdgeType|string|int $edgeType
	 * @param string|array                        $variantData optional to set on Edge record
	 * @return $this
	 * @throws \Modular\Exceptions\Graph
	 */
	public function setEdgeType($edgeType, $variantData = []) {
		// yes this is meant to be an '=', the value is being saved for use later in exception.
		if ($requested = $edgeType) {
			if (!is_object($edgeType)) {
				$edgeTypeClass = static::type_class_name();

				if (is_int($edgeType)) {
					$edgeType = \DataObject::get($edgeTypeClass)->byID($edgeType);
				} else {
					$edgeType = \DataObject::get($edgeTypeClass)->filter([
						static::edge_type_code_field_name() => $edgeType,
					])->first();
				}
				if (!$edgeType) {
					throw new Exception("Couldn't find a '$edgeTypeClass' using '$requested'");
				}
			}
			// edgeType should be an object or null by now.
			if ($edgeType) {
				$edgeType = $edgeType->ID;
			}
		} else if (!is_null($edgeType)) {
			throw new Exception("Can't set an edge type to '$edgeType'");
		}
		$this->{static::type_field_name('ID')} = $edgeType;

		// now set the variant data on the GraphEdge if passed
		if ($edgeType) {
			if (is_array($variantData)) {
				$this->update($variantData);
			} else if (static::TypeVariantFieldName) {
				$this->{static::TypeVariantFieldName} = $variantData;
			}
		} else {
			if (is_array($variantData)) {
				$this->update($variantData);
			} else if (static::TypeVariantFieldName) {
				$this->{static::TypeVariantFieldName} = $variantData;
			}
		}
		return $this;
	}

	public function setNodeA($model) {
		if (is_numeric($model)) {
			$this->{$this->node_a_field_name()} = $model;
		} elseif (is_object($model)) {
			$this->{$this->node_a_field_name()} = $model->ID;
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
	public function getNodeA() {
		/** @var DataObject $nodeA */
		$nodeA = $this->{$this->node_a_field_name()}();
		return $nodeA && $nodeA->exists() ? $nodeA : null;
	}

	/**
	 * @return int|null
	 */
	protected function getNodeAID() {
		return ($model = $this->getNodeA()) ? $model->ID : null;
	}

	public function setNodeB($model) {
		if (is_numeric($model)) {
			$this->{$this->node_b_field_name()} = $model;
		} elseif (is_object($model)) {
			$this->{$this->node_b_field_name()} = $model->ID;
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
	public function getNodeB() {
		/** @var DataObject $nodeB */
		$nodeB = $this->{$this->node_b_field_name()}();
		return $nodeB && $nodeB->exists() ? $nodeB : null;
	}

	/**
	 * @return int|null
	 */
	protected function getNodeBID() {
		return ($model = $this->getNodeB()) ? $model->ID : null;
	}

	/**
	 * Returns object related 'from' the FromModel (so the to objects), the latest first.
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
		])->sort(static::config()->get('default_sort'));

	}

	/**
	 * Returns objects related to the ToModel (so the 'from' objects) the latest first.
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
		$relationships = static::graph(null, $nodeBModel, $typeCodes);
		return \DataObject::get(static::node_a_class_name())->filter([
			'ID' => $relationships->column(static::node_a_field_name()),
		])->sort(static::config()->get('default_sort'));
	}

	/**
	 * Create an Edge or edges between the two models with the provided edge types.
	 *
	 * @param DataObject                      $nodeAModel
	 * @param DataObject                      $nodeBModel
	 * @param string|GraphEdgeType|DataObject $typeCode             Code string or a GraphEdgeType model
	 * @param array|string                    $variantData          Extra data to set on the created GraphEdge(s)
	 * @param bool                            $createImpliedActions also create relationships many many records listed in the GraphEdgeType.ImpliedTypes.
	 * @return \ArrayList of all edges created (including Implied ones if requested to)
	 * @throws \Modular\Exceptions\Graph
	 * @api
	 */
	public static function make(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode, $variantData = [], $createImpliedActions = true) {
		// check permissions
		if (is_object($typeCode)) {
			$typeCode = $typeCode->Code;
		}
		if (!isset($typeCode)) {
			throw new Exception("Need a type code to make an Edge");
		}

		$edges = new ArrayList();

		// get a list of GraphEdgeType records (e.g. SocialActionType) between teo models and which handle the provided codes.
		$edgeTypes = GraphEdgeType::get_for_models($nodeAModel, $nodeBModel, $typeCode);

		/** @var GraphEdgeType|SocialActionType $edgeType e.g. a SocialActionType implementor */
		foreach ($edgeTypes as $edgeType) {

			if ($edgeType::check_permission($typeCode, $nodeAModel, $nodeBModel)) {
				// get all the Edge implementation class names between the two models.
				$edgeClasses = GraphEdge::implementors($nodeAModel, $nodeBModel);

				/** @var GraphEdge $edge */
				foreach ($edgeClasses as $edgeClass) {
					$edge = new $edgeClass();

					$edge->setNodeA($nodeAModel);
					$edge->setNodeB($nodeBModel);
					$edge->setEdgeType($edgeType, $variantData);

					$edge->write();

					$edges->push($edge);

					if ($createImpliedActions) {
						$edges->merge(
							$edgeType->createImpliedRelationships($nodeAModel, $nodeBModel, $variantData)
						);
					}

				}
			}
		}
		return $edges;
	}
	/**
	 * Remove all relationships of a particular type between two models. Only one type allowed!
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @param string     $typeCode
	 * @return bool true if all removed, false if not (e.g. if no permissions or removing one of them failed).
	 *
	 * @api
	 */
	public static function removeAll(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode) {
		// check we have permissions to perform supplied relationship
		if ($ok = GraphEdgeType::check_permission($typeCode, $nodeAModel, $nodeBModel)) {
			$edges = SocialRelationship::graph(
				$nodeAModel,
				$nodeBModel,
				$typeCode
			);
			/** @var \Modular\Interfaces\GraphEdge $edge */
			foreach ($edges as $edge) {
				$ok = $ok && $edge->prune();
			}
		}
		return $ok;
	}

	/**
	 * Check to to see if a GraphEdgeType exists between two models with the supplied code.
	 *
	 * @param DataObject   $nodeAModel e.g. a Member
	 * @param DataObject   $nodeBModel e.g. a Post
	 * @param array|string $typeCode   e.g. 'MLP' for 'Member Likes Post'
	 * @return bool
	 * @api
	 */
	public static function exists_by_type(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode) {
		return !!self::graph($nodeAModel, $nodeBModel, $typeCode)->count();
	}

	/**
	 *
	 * Return the nodeA models related from nodeB by a particular GraphEdgeType
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @param            $typeCode
	 * @return DataList|ArrayList list of nodeB models with a relationship from nodeA model
	 * @api
	 */
	public static function node_as(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode) {
		$edges = static::graph($nodeAModel, $nodeBModel, $typeCode);

		if ($edges->count()) {
			$nodeAFieldName = static::node_a_field_name();

			return $nodeAModel::get()->filter([
				'ID' => $edges->column($nodeAFieldName),
			]);
		}
		return ArrayList::create();
	}

	/**
	 *
	 * Return the nodeB models related to nodeA by a particular GraphEdgeType
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @param            $typeCode
	 * @return DataList|ArrayList list of nodeB models with a relationship from nodeA model
	 * @api
	 */
	public static function node_bs(DataObject $nodeAModel, DataObject $nodeBModel, $typeCode) {
		$edges = static::graph($nodeAModel, $nodeBModel, $typeCode);

		if ($edges->count()) {
			$nodeBFieldName = static::node_b_field_name();

			return $nodeBModel::get()->filter([
				'ID' => $edges->column($nodeBFieldName),
			]);
		}
		return ArrayList::create();
	}

	/**
	 * Return the GraphEdgeType derived classes name for this edge type, e.g. 'SocialActionType'
	 *
	 * @param string $fieldName
	 * @return string|GraphEdgeType really a string but add interface for IDE hinting
	 */
	public static function type_class_name($fieldName = '') {
		return static::EdgeTypeClassName ? (static::EdgeTypeClassName . ($fieldName ? ".$fieldName" : '')) : '';
	}

	/**
	 * Return the name of the field on the edge type class that can be used for filtering for an edge type, e.g.  'Code'.
	 *
	 * @param string $suffix optionally appended to the field name, e.g. 'ID' for a has_one
	 * @return string
	 */
	public static function edge_type_identity_field_name($suffix = '') {
		/** @var GraphEdgeType $typeClassName */
		$typeClassName = static::type_class_name();
		return $typeClassName::identity_field_name($suffix);
	}

	/**
	 * Return the name of the GraphEdgeType class for this GraphEdge.
	 *
	 * @param string $fieldName optionally appended with a '.' e.g. for use when making a relationship join
	 * @return string
	 */
	public static function edge_type_class_name($fieldName = '') {
		return static::type_class_name() . ($fieldName ? ".$fieldName" : '');
	}

	/**
	 * Return the name of the field on the edge which is the has_one to it's GraphEdgeType model, e.g. 'EdgeType'
	 *
	 * @param string $suffix generally want 'ID" appended when we are dealing with the field name
	 * @return string
	 */
	public static function type_field_name($suffix = 'ID') {
		return static::EdgeTypeFieldName ? (static::EdgeTypeFieldName . $suffix) : '';
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