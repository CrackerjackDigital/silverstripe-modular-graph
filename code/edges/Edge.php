<?php
namespace Modular\Edges;

use DataList;
use DataObject;
use Modular\Exceptions\Graph as Exception;
use Modular\Exceptions\NotImplemented;
use Modular\Interfaces\Graph\EdgeType;
use Modular\Interfaces\Graph\Node;
use Modular\Model;
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
class Edge extends Model implements \Modular\Interfaces\Graph\Edge {
	use custom_create;
	use custom_get;

	// should be provided in concrete derived class, e.g 'Directed'
	const NodeAFieldName = '';

	// should be provided in concrete derived class, e.g 'Directed'
	const NodeBFieldName = '';

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
	 * @param DataObject|int $nodeA  a model or an ID
	 * @param DataObject|int $nodeB  a model or an ID
	 * @return \DataList
	 */
	protected static function graph($nodeA, $nodeB) {
		$graph = static::get(get_called_class());
		if (is_object($nodeA)) {
			$nodeAID = $nodeA->ID;
		} else {
			$nodeAID = is_numeric($nodeA) ? $nodeA : null;
		}
		if ($nodeAID) {
			$graph = $graph->filter([
				static::node_a_field_name('ID') => $nodeAID,
			]);
		}
		if (is_object($nodeB)) {
			$nodeBID = $nodeB->ID;
		} else {
			$nodeBID = is_numeric($nodeB) ? $nodeB : null;
		}
		if ($nodeBID) {
			$graph = $graph->filter([
				static::node_a_field_name('ID') => $nodeBID,
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
	public function setEdgeType($edgeType) {
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
		$this->{static::edge_type_filter_field_name('ID')} = $edgeType;
		return $this;
	}

	/**
	 * Set the node instance for 'nodeA'.
	 *
	 * @param \DataObject|int|\Modular\Interfaces\Graph\Node $model
	 * @return $this
	 * @throws \Modular\Exceptions\Exception
	 */
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
	 * Defensive way to get node A's ID.
	 * @return int|null
	 */
	protected function getNodeAID() {
		return ($model = $this->getNodeA()) ? $model->ID : null;
	}

	/**
	 * Set the node instance for 'nodeB'.
	 *
	 * @param \DataObject|int|\Modular\Interfaces\Graph\Node $model
	 * @return $this
	 * @throws \Modular\Exceptions\Exception
	 */
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
	 * Defensive way to get node B's ID.
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
	 * Return the nodeA models (not the edges) related from nodeB
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @return DataList list of nodeB models with a relationship from nodeA model
	 * @api
	 */
	public static function node_as(DataObject $nodeAModel, DataObject $nodeBModel) {
		// first get all the edges between a and b
		$edges = static::graph($nodeAModel, $nodeBModel);

		// e.g 'FromModelID'
		$nodeAFieldName = static::node_a_field_name('ID');

		// now return all the A models which match the ToFieldID in the edges found
		return $nodeAModel::get()->filter([
			'ID' => $edges->column($nodeAFieldName),
		]);
	}

	/**
	 *
	 * Return the nodeB models (not the edges) related to nodeA
	 *
	 * @param DataObject $nodeAModel
	 * @param DataObject $nodeBModel
	 * @return DataList list of nodeB models with a relationship from nodeA model
	 * @api
	 */
	public static function node_bs(DataObject $nodeAModel, DataObject $nodeBModel) {
		// first get all the edges between a and b
		$edges = static::graph($nodeAModel, $nodeBModel);

		// e.g. 'ToModelID'
		$nodeBFieldName = static::node_b_field_name('ID');

		// now return all the B models which match the ToFieldID in the edges found
		return $nodeBModel::get()->filter([
			'ID' => $edges->column($nodeBFieldName),
		]);
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
	public static function edge_type_filter_field_name($suffix = 'ID') {
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

	/**
	 * Return a filter which can be used to select Edges or EdgeTypes.
	 *
	 * @param DataObject|string $nodeA    a model instance, ID of an instance or a class name (or null to omit)
	 * @param DataObject|string $nodeB    a model instance, ID of an instance or a class name (or null to omit)
	 * @param EdgeType|mixed    $edgeType
	 * @return array e.g. ['FromModel' => 'Member', 'ToModel' => 'Modular\Models\Social\Organisation' ]
	 *                                    or [ 'FromModelID' => 10, 'Code' => 'CRT' ]
	 * @throws \Modular\Exceptions\NotImplemented
	 */
	public static function archetype($nodeA = null, $nodeB = null, $edgeType = null) {
		throw new NotImplemented("Should be implemented in derived class");
	}
}