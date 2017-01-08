<?php
namespace Modular\Extensions\Model;

use Modular\Interfaces\Graph\EdgeType;
use Modular\Interfaces\HasEdges;
use Modular\ModelExtension;
use Modular\Models\Graph\Node;

class GraphNode extends ModelExtension implements HasEdges {
	
	/**
	 * @return Node|\DataObject|\Modular\Model
	 */
	public function node() {
		return $this->owner();
	}
	
	/**
	 * Return a list of all edges which have the model as an endpoint (either nodeA or nodeB).
	 *
	 * @param null|string   $edgeClassName optionally only a particular edge class, e.g. 'MemberMember'
	 * @param null|EdgeType $edgeType      optionally filter by a particular edge type, e.g. 'REG'
	 * @return \Modular\Collections\Graph\EdgeList
	 */
	public function edges($edgeClassName = null, $edgeType = null) {
		/**
		 * @var string $name e.g. 'Members'
		 * @var string $className e.g. 'MemberMember'
		 */
		foreach ($this->relationships() as $name => $className) {
		}
	}
}