<?php
namespace Modular\Interfaces\Graph;

use Modular\Interfaces\Graph;

interface Edge extends Graph {

	/**
	 * Do whatever it takes to get this edge out of the system.
	 *
	 * @return bool tru if pruned succesfully, false otherwise
	 */
	public function prune();

	public function setNodeA($nodeA);

	public function setNodeB($nodeB);

	/**
	 * Set the edge type reference and also any additional data on the Edge itself.
	 *
	 * @param       $edgeType
	 * @param array $variantData
	 * @return $this
	 */
	public function setEdgeType($edgeType, $variantData = null);
}