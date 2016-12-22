<?php
namespace Modular\Extensions\Edge;

use Modular\Edges\Edge;

/**
 * Should be added to Edge implementations providing the has_one relationships to the nodeA and nodeB models.
 */
class DirectedEdgeExtension extends \Modular\ModelExtension {
	public function extraStatics($class = null, $extension = null) {
		$config = parent::extraStatics($class, $extension) ?: [];

		/** @var string|Edge $class typehint for methods, really a string */
		if ($class && $class != Edge::class_name()) {
			$rels = array_filter([
				$class::node_a_field_name('') => $class::node_a_class_name(),
				$class::node_b_field_name('') => $class::node_b_class_name(),
			]);
			if ($rels) {
				$config = array_merge_recursive(
					$config,
					[
						'has_one' => $rels,
					]
				);
			}
		}
		return $config;
	}
}