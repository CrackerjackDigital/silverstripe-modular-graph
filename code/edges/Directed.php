<?php
namespace Modular\Edges;

/*
 *
 * @method ActionType ActionType
 *
 */
use DataList;
use DataObject;
use Modular\Interfaces\Graph\Edge;

/* abstract */

class Directed extends \Modular\Models\Graph\Edge {
	const NodeAFieldName = 'FromModel';
	const NodeBFieldName = 'ToModel';

	const NodeALabel = 'From';
	const NodeBLabel = 'To';

	private static $list_class_name = 'Modular\Collections\DirectedEdgeList';

	/**
	 * Make more
	 *
	 * @param       $fromModel
	 * @param       $toModel
	 * @param array $typeCodes
	 * @return \DataList
	 */
	public static function get_for_models($fromModel, $toModel, $typeCodes = []) {
		return static::graph($fromModel, $toModel, $typeCodes);
	}

	/**
	 * Really just for nice 'Directed' style parameter names.
	 *
	 * @param DataObject $fromModel
	 * @param DataObject $toModel
	 * @param array      $typeCodes
	 * @return DataList
	 */
	public static function graph($fromModel, $toModel, $typeCodes = [], $action = '') {
		return parent::graph($fromModel, $toModel, $typeCodes, $action);
	}

	/**
	 * Return one if any found, not in any special order.
	 *
	 * @param        $fromModel
	 * @param        $toModel
	 * @param array  $typeCodes
	 * @param string $action
	 * @return \DataObject
	 */
	public static function one($fromModel, $toModel, $typeCodes = [], $action = '') {
		return static::graph($fromModel, $toModel, $typeCodes, $action)->first();
	}

	/**
	 * Return tha latest model which satisfies the supplied parameters.
	 *
	 * @param        $fromModel
	 * @param        $toModel
	 * @param array  $typeCodes
	 * @param string $action
	 * @return \DataObject
	 */
	public static function latest($fromModel, $toModel, $typeCodes = [], $action = '') {
		return static::graph($fromModel, $toModel, $typeCodes, $action)->sort('Created', 'Desc')->first();
	}

	/**
	 * Return tha oldest model which satisfies the supplied parameters.
	 *
	 * @param        $fromModel
	 * @param        $toModel
	 * @param array  $typeCodes
	 * @param string $action
	 * @return \DataObject
	 */
	public static function oldest($fromModel, $toModel, $typeCodes = [], $action = '') {
		return static::graph($fromModel, $toModel, $typeCodes, $action)->sort('Created', 'Asc')->first();
	}

	/**
	 * Return a list of class names which implement an Edge from a model to another model.
	 *
	 * @inheritdoc
	 *
	 * @param DataObject|string|null $fromModel
	 * @param DataObject|string|null $toModel
	 * @return array list of implementation class names
	 */
	public static function implementors($fromModel, $toModel) {
		return parent::implementors($fromModel, $toModel);
	}

	/**
	 * Add directed type 'From' syntax
	 *
	 * @param \Modular\Interfaces\Graph\Node|DataObject
	 * @return Edge
	 */
	public function setFrom($model) {
		return parent::setNodeA($model);
	}

	/**
	 * Add directed type 'From' syntax
	 *
	 * @return \Modular\Interfaces\Graph\Node|DataObject
	 */
	public function getFrom() {
		return parent::getNodeA();
	}

	/**
	 * Add directed type 'To' syntax
	 *
	 * @param \Modular\Interfaces\Graph\Node|DataObject $model
	 * @return Edge
	 */
	public function setTo($model) {
		return parent::setNodeB($model);
	}

	/**
	 * Add directed type 'To' syntax
	 *
	 * @return \Modular\Interfaces\Graph\Node|DataObject
	 */
	public function getTo() {
		return parent::getNodeB();
	}

	/**
	 * @param DataObject   $fromModel
	 * @param string|array $typeCodes e.g. 'CRT', 'REG'
	 * @return DataList
	 */
	public static function from_models($fromModel, $typeCodes = []) {
		return parent::node_a_for_type($fromModel, $typeCodes);
	}

	/**
	 * @param DataObject   $toModel
	 * @param string|array $typeCodes e.g. 'CRT', 'REG'
	 * @return DataList
	 */
	public static function to_models($toModel, $typeCodes = []) {
		return parent::node_b_for_type($toModel, $typeCodes);
	}

	/**
	 * friendly name for node_a_class_name
	 */
	public static function from_class_name($fieldName = '') {
		return static::node_a_class_name($fieldName);
	}

	/**
	 * friendly name for node_a_field_name
	 */
	public static function from_field_name($suffix = 'ID') {
		return static::node_a_field_name($suffix);
	}

	/**
	 * friendly name for node_b_class_name
	 */
	public static function to_class_name($fieldName = '') {
		return static::node_b_class_name($fieldName);
	}

	/**
	 * friendly name for node_b_field_name
	 */
	public static function to_field_name($suffix = 'ID') {
		return static::node_b_field_name($suffix);
	}

	public static function node_a_class_name($fieldName = '') {
		return static::NodeAClassName
			? (static::NodeAFieldName . ($fieldName ? ".$fieldName" : ''))
			: parent::node_a_class_name($fieldName);
	}

	public static function node_b_class_name($fieldName = '') {
		return static::NodeBClassName
			? (static::NodeBFieldName . ($fieldName ? ".$fieldName" : ''))
			: parent::node_b_class_name($fieldName);
	}

	public static function node_a_field_name($suffix = '') {
		return static::NodeAClassName
			? (static::NodeAFieldName . $suffix)
			: parent::node_a_field_name($suffix);
	}

	public static function node_b_field_name($suffix = '') {
		return static::NodeBFieldName
			? (static::NodeBFieldName . $suffix)
			: parent::node_b_field_name($suffix);
	}

}