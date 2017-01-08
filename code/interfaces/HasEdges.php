<?php
namespace Modular\Interfaces;

use Modular\Collections\Graph\EdgeList;
use Modular\Interfaces\Graph\EdgeType;

interface HasEdges {
	/**
	 * Return a list of all edges which have the model as an endpoint (either nodeA or nodeB).
	 *
	 * @param null|string   $edgeClassName optionally only a particular edge class, e.g. 'MemberMember'
	 * @param null|EdgeType $edgeType      optionally filter by a particular edge type, e.g. 'REG'
	 * @return \Modular\Collections\Graph\EdgeList
	 */
	public function edges($edgeClassName = null, $edgeType = null);
}

