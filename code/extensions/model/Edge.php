<?php
namespace Modular\Extensions\Model;

use Modular\ModelExtension;

/**
 * Edge
 *
 * @package Modular\Extensions\Model
 * @property int FromNodeID
 * @property int ToNodeID
 */
class GraphEdge extends ModelExtension {

	/**
	 * Add has_one relationships from node a to node b
	 *
	 * @param string|\Modular\Edges\Edge $class
	 * @param null                             $extension
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null) {
		$nodeAFieldName = $class::node_a_field_name('');
		$nodeBFieldName = $class::node_b_field_name('');
		$edgeTypeFieldName = $class::edge_type_filter_field_name('');

		return array_merge_recursive(
			parent::extraStatics($class, $extension) ?: [],
			[
				'has_one'        => [
					$nodeAFieldName => $class::node_a_class_name(),
					$nodeBFieldName => $class::node_b_class_name(),
				    $edgeTypeFieldName => $class::edge_type_class_name()
				],
				'summary_fields' => [
					$nodeAFieldName => $class::node_a_label(),
					$nodeBFieldName => $class::node_b_label()
				],

			]
		);
	}
}