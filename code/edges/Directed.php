<?php
namespace Modular\Edges;

/*
 *
 * @method ActionType ActionType
 *
 */
use DataList;
use DataObject;
use Modular\Interfaces\GraphEdge;

/* abstract */
class Directed extends \Modular\Models\GraphEdge {
	const NodeAFieldName = 'FromModel';
	const NodeBFieldName = 'ToModel';

	const NodeALabel = 'From';
	const NodeBLabel = 'To';

	// these should be override in derived classes or config
	private static $from_class_name = '';
	private static $to_class_name = '';

	private static $from_field_name = '';
	private static $to_field_name = '';


	/**
	 * Really just for nice 'Directed' style parameter names.
	 *
	 * @param DataObject $from
	 * @param DataObject $to
	 * @param array      $typeCodes
	 * @return DataList
	 */
	public static function graph($from, $to, $typeCodes = []) {
		return parent::graph($from, $to, $typeCodes);
	}

	/**
	 * Add directed type 'From' syntax
	 *
	 * @param \Modular\Interfaces\GraphNode
	 * @return GraphEdge
	 */
	public function setFrom($model) {
		return parent::setNodeA($model);
	}

	/**
	 * Add directed type 'From' syntax
	 *
	 * @return \Modular\Interfaces\GraphNode
	 */
	public function getFrom() {
		return parent::getNodeA();
	}

	/**
	 * Add directed type 'To' syntax
	 *
	 * @param \Modular\Interfaces\GraphNode $model
	 * @return GraphEdge
	 */
	public function setTo($model) {
		return parent::setNodeB($model);
	}

	/**
	 * Add directed type 'To' syntax
	 *
	 * @return \Modular\Interfaces\GraphNode
	 */
	public function getTo() {
		return parent::getNodeB();
	}

	/**
	 * @param DataObject   $fromModel
	 * @param string|array $actionCodes e.g. 'CRT', 'REG'
	 * @return DataList
	 */
	public function from($fromModel, $actionCodes = []) {
		return parent::node_a_for_type($fromModel, $actionCodes);
	}

	/**
	 * @param DataObject   $toModel
	 * @param string|array $actionCodes e.g. 'CRT', 'REG'
	 * @return DataList
	 */
	public function to($toModel, $actionCodes = []) {
		return parent::node_b_for_type($toModel, $actionCodes);
	}

	public static function from_class_name($fieldName = '') {
		return static::node_a_class_name($fieldName);
	}

	public static function from_field_name($suffix = 'ID') {
		return static::node_a_field_name($suffix);
	}

	public static function to_class_name($fieldName = '') {
		return static::node_b_class_name($fieldName);
	}

	public static function to_field_name($suffix = 'ID') {
		return static::node_b_field_name($suffix);
	}

	public static function node_a_class_name($fieldName = '') {
		return static::config()->get('from_class_name')
			?: (static::NodeAClassName
				? (static::NodeAFieldName . ($fieldName ? ".$fieldName" : ''))
				: parent::node_a_class_name($fieldName)
			);
	}

	public static function node_b_class_name($fieldName = '') {
		return static::config()->get('to_class_name')
			?: (static::NodeBClassName
				? (static::NodeBFieldName . ($fieldName ? ".$fieldName" : ''))
				: parent::node_b_class_name($fieldName)
			);
	}

	public static function node_a_field_name($suffix = '') {
		return static::config()->get('from_field_name')
			?: (static::NodeAClassName
				? (static::NodeAFieldName . $suffix)
				: parent::node_a_field_name($suffix)
			);

	}

	public static function node_b_field_name($suffix = '') {
		return static::config()->get('to_field_name')
			?: (static::NodeBFieldName
				? (static::NodeBFieldName . $suffix)
				: parent::node_b_field_name($suffix)
			);
	}

}