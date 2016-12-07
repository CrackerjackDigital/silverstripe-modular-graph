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

/** abstract if SS would allow it */
class Directed extends \Modular\Models\GraphEdge {
	const FromClassName = 'Modular\Models\GraphNode';
	const FromFieldName = 'FromNode';

	const ToClassName = 'Modular\Models\GraphNode';
	const ToFieldName = 'ToNode';

	const NodeALabel = 'From';
	const NodeBLabel = 'To';

	private static $from_class_name = self::FromClassName;
	private static $from_field_name = self::FromFieldName;

	private static $to_class_name = self::ToClassName;
	private static $to_field_name = self::ToFieldName;

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
		return parent::nodeA($fromModel, $actionCodes);
	}

	/**
	 * @param DataObject   $toModel
	 * @param string|array $actionCodes e.g. 'CRT', 'REG'
	 * @return DataList
	 */
	public function to($toModel, $actionCodes = []) {
		return parent::nodeB($toModel, $actionCodes);
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
			?: (static::FromClassName
				? (static::FromFieldName . ($fieldName ? ".$fieldName" : ''))
				: parent::node_a_class_name($fieldName)
			);
	}

	public static function node_b_class_name($fieldName = '') {
		return static::config()->get('to_class_name')
			?: (static::ToClassName
				? (static::ToFieldName . ($fieldName ? ".$fieldName" : ''))
				: parent::node_b_class_name($fieldName)
			);
	}

	public static function node_a_field_name($suffix = '') {
		return static::config()->get('from_field_name')
			?: (static::FromFieldName
				? (static::FromFieldName . $suffix)
				: parent::node_a_field_name($suffix)
			);

	}

	public static function node_b_field_name($suffix = '') {
		return static::config()->get('to_field_name')
			?: (static::ToFieldName
				? (static::ToFieldName . $suffix)
				: parent::node_b_field_name($suffix)
			);
	}

}