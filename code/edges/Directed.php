<?php
namespace Modular\Edges;

/*
 *
 * @method SocialEdgeType SocialEdgeType
 *
 */
use DataList;
use DataObject;
use Modular\Model;
use Modular\Models\Graph\Node;

/* abstract */

class Directed extends Edge {
	const NodeAFieldName = 'FromModel';
	const NodeBFieldName = 'ToModel';

	const NodeALabel = 'From';
	const NodeBLabel = 'To';
	
	/**
	 * Return a filter which can be used to select Edges based on From and To models passed as instances or class names.
	 * class names.
	 *
	 * @param DataObject|string $fromModel       a model instance or a class name
	 * @param DataObject|string $toModel         a model instance or a class name
	 * @param array|string      $actionTypeCodes single or array of codes
	 * @return array e.g. ['FromModel' => 'Member', 'ToModel' => 'Modular\Models\Social\Organisation', 'Code' => 'CRT' ]
	 */
	public static function archetype($fromModel = null, $toModel = null, $actionTypeCodes = []) {
		$filter = [];
		if ($fromModel) {
			if (is_int($fromModel)) {
				// we are selecting from a model instance so e.g. 'FromModelID' => <id>
				$filter[ static::node_a_field_name('ID') ] = $fromModel;
			} else {
				$filter[ static::node_a_field_name()] = self::derive_class_name($fromModel);
			}
		}
		if ($toModel) {
			if (is_int($toModel)) {
				// we are selecting to a model instance so e.g. 'ToModelID' => <id>
				$filter[ static::node_b_field_name('ID') ] = $toModel;
			} else {
				$filter[ static::node_b_field_name() ] = self::derive_class_name($toModel);
			}
		}
		if ($actionTypeCodes) {
			$filter[ static::edge_type_class_name(static::edge_type_filter_field_name()) ] = $actionTypeCodes;
		}
		
		return $filter;
	}
	
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
	 * Return one if any found, not in any special order. If not found returns null;
	 *
	 * @param        $fromModel
	 * @param        $toModel
	 * @return \DataObject|null
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
	 * @param Node|DataObject|Model $node
	 * @return \Modular\Edges\Edge
	 * @throws \Modular\Exceptions\Exception
	 */
	public function setFrom($node) {
		return parent::setNodeA($node);
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
	 * @param Node|DataObject|Model $node
	 * @return \Modular\Edges\Edge
	 * @throws \Modular\Exceptions\Exception
	 */
	public function setTo($node) {
		return parent::setNodeB($node);
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
	 * @param string $suffix generally a has_one relationship so default to 'ID'
	 * @return string
	 */
	public static function from_field_name($suffix = 'ID') {
		return static::node_a_field_name($suffix);
	}
	
	/**
	 * friendly name for node_b_class_name
	 *
	 * @param string $fieldName
	 * @return string
	 */
	public static function to_class_name($fieldName = '') {
		return static::node_b_class_name($fieldName);
	}
	
	/**
	 * friendly name for node_b_field_name
	 *
	 * @param string $suffix generally a has_one relationship so default to 'ID'
	 * @return string
	 */
	public static function to_field_name($suffix = 'ID') {
		return static::node_b_field_name($suffix);
	}

	public static function node_a_class_name($fieldName = '') {
		return static::NodeAClassName . ($fieldName ? ".$fieldName" : '');
	}

	public static function node_a_field_name($suffix = 'ID') {
		return static::NodeAFieldName . $suffix;
	}

	public static function node_b_class_name($fieldName = '') {
		return static::NodeBClassName . ($fieldName ? ".$fieldName" : '');
	}

	public static function node_b_field_name($suffix = 'ID') {
		return static::NodeBFieldName . $suffix;
	}

}