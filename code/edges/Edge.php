<?php
namespace Modular\Edges;

use ArrayList;
use DataList;
use DataObject;
use Modular\Exceptions\Graph as Exception;
use Modular\Interfaces\Graph\EdgeType;
use Modular\Interfaces\Graph\Node;
use Modular\Traits\custom_create;
use Modular\Traits\custom_get;
use Modular\Types\Graph\DirectedEdgeType;

/**
 * Edge
 *
 * @package Modular\Models
 * @property Node FromNode
 * @property Node ToNode
 *
 */
/* abstract */

class Edge extends \Modular\Model implements \Modular\Interfaces\Graph\Edge {
	use custom_create;
	use custom_get;

	const EdgeTypeClassName = '';           # 'Modular\Types\Graph\DirectedEdgeType' or derived class
	const EdgeTypeFieldName = 'EdgeType';   #

	const TypeVariantFieldName = ''; # 'Action'

	const NodeAClassName = '';       # 'Modular\Models\Node' or 'Member'
	const NodeAFieldName = '';       # 'FromModel'
	const NodeALabel     = 'Node A';

	const NodeBClassName = '';         # 'Modular\Models\Node' or 'Modular\Models\Social\Organisation'
	const NodeBFieldName = '';         # 'ToModel'
	const NodeBLabel     = 'Node B';

	private static $custom_class_name = '';
	private static $custom_list_class_name = 'Modular\Collections\EdgeList';

	private static $default_sort = 'Created DESC';

	/**
	 * Return an instance of whatever is on config.custom_class_name or just called class.
	 *
	 * @return Directed
	 */
	public static function create() {
		return static::custom_create(func_get_args());
	}

	/**
	 * Substitute an alternate list if this class and injector has injector_list_name configured to use instead of the standard DataList.
	 *
	 * This list class con be configured on config.custom_list_class_name
	 *
	 * @param null   $callerClass
	 * @param string $filter
	 * @param string $sort
	 * @param string $join
	 * @param null   $limit
	 * @param string $containerClass
	 * @return \DataList
	 */
	public static function get($callerClass = null, $filter = "", $sort = "", $join = "", $limit = null, $containerClass = 'DataList') {
		return static::custom_get($callerClass, $filter, $sort, $join, $limit, $containerClass);
	}

	/**
	 * Returns a list of all edges which match on supplied models, edge types and edge type variants, not necessarily in any order.
	 *
	 * @param DataObject|int $nodeA       a model or an ID
	 * @param DataObject|int $nodeB       a model or an ID
	 * @param array          $typeCodes
	 * @param string         $typeVariant filter also by extra data set on the Edge
	 * @return \DataList
	 */
	protected static function graph($nodeA, $nodeB, $typeCodes = [], $typeVariant = '') {
		$graph = static::get(get_called_class());
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
			$typeIDs = static::edge_type()->get_for_models(
				static::node_a_class_name(),
				static::node_b_class_name(),
				$typeCodes
			)->column('ID');

			$graph = $graph->filter([
				static::edge_type_field_name() => $typeIDs,
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
	 * Return a list of concrete Edge model class names which implement an edge between two Nodes,
	 * or if null then entering or leaving the other Node class. If both null will return all Edge implementor class names.
	 *
	 * @param DataObject|string $nodeAClass
	 * @param DataObject|string $nodeBClass
	 * @return array list of implementation class names
	 */
	protected static function implementors($nodeAClass, $nodeBClass, $strict) {
		$nodeAClass = static::derive_class_name($nodeAClass);
		$nodeBClass = static::derive_class_name($nodeBClass);

		$implementors = [];

		$subclasses = static::subclasses();
		/** @var Edge|string $subclass */
		foreach ($subclasses as $subclass) {
			$nodeAMatch = $nodeBMatch = false;

			if (!$nodeAClass || ($subclass::node_a_class_name() == $nodeAClass)) {
				$nodeAMatch = true;
			}
			if (!$nodeBClass || ($subclass::node_b_class_name() == $nodeBClass)) {
				$nodeBMatch = true;
			}
			if ($strict) {
				// both have to match
				if ($nodeAMatch && $nodeBMatch) {
					$implementors[] = $subclass;
				}
			} else {
				// either one matches is ok
				if ($nodeAMatch || $nodeBMatch) {
					$implementors[] = $subclass;
				}
			}
		}
		return $implementors;
	}

	/**
	 * @param \DataObject|DirectedEdgeType|int $edgeType
	 * @param string|array                     $variantData optional to set on Edge record
	 * @return $this
	 * @throws \Modular\Exceptions\Graph
	 */
	public function setEdgeType($edgeType, $variantData = []) {
		// yes this is meant to be an '=', the value is being saved for use later in exception.
		if ($requested = $edgeType) {
			if (!is_object($edgeType)) {
				/** @var DirectedEdgeType|\DataObject $edgeTypeClass */
				$edgeTypeClass = static::edge_type_class_name();

				if (is_int($edgeType)) {
					$edgeType = static::edge_type()->get()->byID($edgeType);
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
		$this->{static::edge_type_field_name('ID')} = $edgeType;

		// now set the variant data on the Edge if passed
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
	 * @return \Modular\Interfaces\Graph\Node|DataObject
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
	 * @return \Modular\Interfaces\Graph\Node|DataObject
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
	 * Return in instance of the EdgeType used for this Edge.
	 *
	 * @return EdgeType
	 */
	public static function edge_type() {
		return \Injector::inst()->createWithArgs(static::edge_type_class_name(), func_get_args());
	}

	/**
	 *
	 * Return the nodeA models related from nodeB by a particular DirectedEdgeType
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @return DataList|ArrayList list of nodeB models with a relationship from nodeA model
	 * @api
	 */
	public static function node_as(DataObject $nodeAModel, DataObject $nodeBModel) {
		$edges = static::graph($nodeAModel, $nodeBModel);

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
	 * Return the nodeB models related to nodeA by a particular DirectedEdgeType
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @return \ArrayList|\DataList list of nodeB models with a relationship from nodeA model
	 * @api
	 */
	public static function node_bs(DataObject $nodeAModel, DataObject $nodeBModel) {
		$edges = static::graph($nodeAModel, $nodeBModel);

		if ($edges->count()) {
			$nodeBFieldName = static::node_b_field_name();

			return $nodeBModel::get()->filter([
				'ID' => $edges->column($nodeBFieldName),
			]);
		}
		return ArrayList::create();
	}

	/**
	 * Return the name of the DirectedEdgeType class for this Edge.
	 *
	 * @param string $fieldName optionally appended with a '.' e.g. for use when making a relationship join
	 * @return string
	 */
	public static function edge_type_class_name($fieldName = '') {
		return static::EdgeTypeClassName ? (static::EdgeTypeClassName . ($fieldName ? ".$fieldName" : '')) : '';
	}

	/**
	 * Return the name of the field on the edge which is the has_one to it's DirectedEdgeType model, e.g. 'EdgeType'
	 *
	 * @param string $suffix generally want 'ID" appended when we are dealing with the field name
	 * @return string
	 */
	public static function edge_type_field_name($suffix = 'ID') {
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