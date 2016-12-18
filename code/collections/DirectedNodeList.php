<?php
namespace Modular\Collections\Graph;

use Modular\Edges\Directed;
use Modular\Interfaces\Graph\Edge;

/**
 * A DirectedNodeList is a list of graph Nodes or derived models, and so could be at either of an Edge.
 *
 * @package Modular\Collections
 */
class DirectedNodeList extends NodeList {
	const InjectorName = 'GraphNode';
	private static $injector_name = self::InjectorName;

	/**
	 * Return a list of all nodes which are related to nodes in this list as the 'To' node.
	 *
	 * @return DirectedNodeList
	 */
	public function to($filter = []) {
		return DirectedEdgeList::create(
			get_class(static::edge())
		)->filter([
			static::from_field_name('ID') => $this->column('ID')
		])->to($filter);
	}

	/**
	 * Return a list of all nodes which are related to nodes in this list as the 'From' node.
	 *
	 * @return DirectedNodeList
	 */
	public function from($filter = []) {
		return DirectedEdgeList::create(
			get_class(static::edge())
		)->filter([
			static::to_field_name('ID') => $this->column('ID')
		])->from($filter);
	}

	/**
	 * @return Edge
	 */
	protected static function edge() {
		static $edge;
		return $edge ?: $edge = Directed::create();
	}

	/**
	 * @param string $suffix
	 * @return string
	 */
	protected static function from_field_name($suffix = 'ID') {
		return static::edge()->node_a_field_name($suffix);
	}

	/**
	 * @param string $suffix
	 * @return string
	 */
	protected static function to_field_name($suffix = 'ID') {
		return static::edge()->node_b_field_name($suffix);
	}

}