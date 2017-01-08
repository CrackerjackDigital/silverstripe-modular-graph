<?php
namespace Modular\Types\Graph;

/**
 * GraphEdgeType would probably not be used directly but derived from. Mainly implemented in trait edgetype and to provide a factory method, however
 * could be used to derive edges from instead of using an existing class, using the trait and implemnting the EdgeType interface.
 *
 * @package Modular\Types
 */
/* abstract */
class DirectedEdgeType extends EdgeType {
	const NodeAFieldName = 'FromModel';
	const NodeBFieldName = 'ToModel';

	private static $node_a_field_name = self::NodeAFieldName;
	private static $node_b_field_name = self::NodeBFieldName;

	private static $db = [
		self::NodeAFieldName => 'Varchar(64)',                             // e.g. 'Member'
		self::NodeBFieldName => 'Varchar(64)',                             // e.g. 'SocialOrganisation'
	];

	private static $indexes = [
		'AllowedClassNames' => 'FromModel,ToModel'
	];

	public static function from_field_name($suffix = '') {
		return parent::nodeAFieldName($suffix);
	}

	public static function to_field_name($suffix = '') {
		return parent::nodeBFieldName($suffix);
	}

}