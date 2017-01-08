<?php
namespace Modular\Collections\Graph;

use Modular\Edges\Directed;
use Modular\Edges\Edge;

/**
 * DirectedEdgeList is a DataList whose items are instances of a Directed edge. This means they have a FromModelID, a ToModelID and an EdgeTypeID.
 *
 * It can be used to do fluid graph traverals such as:
 *
 * $nodeList = Node::get()->to();       returns a list of all nodes related to this node
 *
 * @package Modular\Collections
 */
class DirectedEdgeList extends EdgeList {
	// setting this will use this class for the Node lists returned traversing this edge.
	private static $custom_class_name = '';
	
	/**
	 * Return a list of the 'To' nodes in this list.
	 *
	 * @return DirectedNodeList|\DataList
	 */
	public function to() {
		return static::node_list()->filter('ID', $this->column(static::to_field_name('ID')));
	}

	/**
	 * Return a list of the 'From' nodes in this list.
	 *
	 * @return DirectedNodeList|\DataList
	 */
	public function from() {
		return static::node_list()->filter('ID', $this->column(static::from_field_name('ID')));
	}
	
	/**
	 * @return Directed
	 */
	protected static function edge() {
		static $edge;
		return $edge ?: $edge = Directed::create();
	}
	
	/**
	 * Returns the 'from field' name for this list's edge model.
	 * @param string $suffix
	 * @return string
	 */
	protected static function from_field_name($suffix = 'ID') {
		return static::edge()->from_field_name($suffix);
	}
	
	/**
	 * Returns the 'to field' name for this list's edge model.
	 * @param string $suffix
	 * @return string
	 */
	protected static function to_field_name($suffix = 'ID') {
		return static::edge()->to_field_name($suffix);
	}
}