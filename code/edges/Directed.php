<?php
namespace Modular\Edges;

/*
 *
 * @method SocialEdgeType SocialEdgeType
 *
 */
use DataList;
use DataObject;

/* abstract */

class Directed extends Edge {
	const NodeAFieldName = 'FromModel';
	const NodeBFieldName = 'ToModel';

	const NodeALabel = 'From';
	const NodeBLabel = 'To';

	/**
	 * Alias for graph
	 *
	 * @param       $fromModel
	 * @param       $toModel
	 * @return \DataList
	 */
	public static function get_for_models($fromModel, $toModel) {
		return static::graph($fromModel, $toModel);
	}

	/**
	 * Really just for nice 'Directed' style parameter names.
	 *
	 * @param DataObject $fromModel
	 * @param DataObject $toModel
	 * @return DataList
	 */
	public static function graph($fromModel, $toModel) {
		return parent::graph($fromModel, $toModel);
	}

	/**
	 * Return one if any found, not in any special order.
	 *
	 * @param        $fromModel
	 * @param        $toModel
	 * @return \DataObject
	 */
	public static function one($fromModel, $toModel) {
		return static::graph($fromModel, $toModel)->first();
	}



	/**
	 * Return a list of class names which implement an Edge from a model to another model.
	 *
	 * @inheritdoc
	 *
	 * @param DataObject|string|null $fromModel
	 * @param DataObject|string|null $toModel
	 * @param bool $strict both have to match if true, otherwise either can match
	 * @return array list of implementation class names
	 */
	public static function implementors($fromModel, $toModel, $strict = true) {
		return parent::implementors($fromModel, $toModel, $strict);
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
			? (static::NodeAClassName . ($fieldName ? ".$fieldName" : ''))
			: parent::node_a_class_name($fieldName);
	}

	public static function node_a_field_name($suffix = '') {
		return static::NodeAFieldName
			? (static::NodeAFieldName . $suffix)
			: parent::node_a_field_name($suffix);
	}

	public static function node_b_class_name($fieldName = '') {
		return static::NodeBClassName
			? (static::NodeBClassName . ($fieldName ? ".$fieldName" : ''))
			: parent::node_b_class_name($fieldName);
	}

	public static function node_b_field_name($suffix = '') {
		return static::NodeBFieldName
			? (static::NodeBFieldName . $suffix)
			: parent::node_b_field_name($suffix);
	}

}