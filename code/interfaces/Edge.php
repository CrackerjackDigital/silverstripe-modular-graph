<?php
namespace Modular\Interfaces\Graph;

use Modular\Interfaces\Graph;

interface Edge extends Graph {
	const EdgeTypeClassName = '';           # 'Modular\Types\Graph\DirectedEdgeType' or derived class
	const EdgeTypeFieldName = 'EdgeType';   #

	const TypeVariantFieldName = ''; # 'Action'

	const NodeAClassName = '';       # 'Modular\Models\Node' or 'Member'
	const NodeALabel     = 'Node A';

	const NodeBClassName = '';         # 'Modular\Models\Node' or 'Modular\Models\Social\Organisation'
	const NodeBLabel     = 'Node B';

	/**
	 * Do whatever it takes to get this edge out of the system.
	 *
	 * @return bool true if pruned successfully, false otherwise
	 */
	public function prune();

	/**
	 * Set the 'A' node.
	 * @param Node|\DataObject|int $nodeA
	 * @return $this
	 */
	public function setNodeA($nodeA);

	/**
	 * Set the 'B' node.
	 *
	 * @param Node|\DataObject|int $nodeB
	 * @return $this
	 */
	public function setNodeB($nodeB);

	/**
	 * Set the edge type reference and also any additional data on the Edge itself.
	 *
	 * @param EdgeType $edgeType
	 * @return $this
	 */
	public function setEdgeType($edgeType);
}