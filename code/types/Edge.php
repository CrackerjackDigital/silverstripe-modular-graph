<?php
namespace Modular\Types\Graph;

use DataObject;
use Modular\Interfaces\Graph\Node;
use Modular\Model;
use Modular\Traits\config;
use Modular\Traits\reflection;
use Modular\Traits\custom_create;
use Modular\Traits\custom_get;
use Modular\Type;

/* abstract */

class EdgeType extends Type implements \Modular\Interfaces\Graph\EdgeType {
	use \Modular\Traits\Graph\edgetype;
	use reflection;
	use config;
	use custom_create;
	use custom_get;

	private static $custom_class_name = '';
	private static $custom_list_class_name = '';

	private static $node_a_field_name = '';
	private static $node_b_field_name = '';


	public function nodeAFieldName($suffix = '') {
		return static::config()->get('node_a_field_name') . $suffix;
	}

	public function nodeBFieldName($suffix = '') {
		return static::config()->get('node_b_field_name') . $suffix;
	}

	/**
	 * Check that an EdgeType can exist (be created or is still valid) between two models.
	 *
	 * @param Node|\DataObject|Model                   $nodeA
	 * @param Node|\DataObject|Model                   $nodeB
	 * @param \Modular\Interfaces\Graph\EdgeType|mixed $edgeType an EdgeType, ID or Code which can be used to identify an EdgeType
	 * @return bool
	 */
	public static function valid(DataObject $nodeA, DataObject $nodeB, $edgeType) {
		return true;
	}

	/**
	 * Return the name of the field for the 'nodeA' model that this edge handles, 'FromModelID' in a Directed graph
	 * edge, or 'FromModel' on a Directed graph EdgeType
	 *
	 * @param string $suffix to append to the base field name, in the case of has_many this would be 'ID' to give e.g.
	 *                       'FromID'
	 * @return string
	 */
	public static function node_a_field_name($suffix = 'ID') {
		return static::config()->get('node_a_field_name') . $suffix;
	}

	/**
	 * Return the name of the field for the nodeB model that this edge handles, e.g 'ToModelID' in a Directed graph
	 * or 'ToModel' on a Directed graph EdgeType
	 *
	 * @param string $suffix to append to the base field name, in the case of has_many this would be 'ID' to give e.g.
	 *                       'ToID'
	 * @return string
	 */
	public static function node_b_field_name($suffix = 'ID') {
		return static::config()->get('node_b_field_name') . $suffix;
	}

	/**
	 * Return the name of the EdgeType class for this graph, e.g. 'Modular\Types\SocialActionType'
	 *
	 * @param string $fieldName
	 * @return mixed
	 */
	public static function edgetype_class_name($fieldName = '') {
		return static::EdgeTypeClassName;
	}

	/**
	 * Return the name of the field on associated EdgeType which is used to filter EdgeTypes
	 *
	 * @return string e.g. 'Code'
	 */
	public static function edgetype_field_name() {
		return static::EdgeTypeCodeFieldName;
	}

}