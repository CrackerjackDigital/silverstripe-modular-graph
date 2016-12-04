<?php
namespace Modular\Models;

class GraphNode extends \Modular\Model {
	private static $db = [];
	private static $has_one = [];

	public function getModelClass() {
		return get_class($this);
	}

	public function getModelID() {
		return $this->ID;
	}

	public function getModelInstance() {
		return $this;
	}

}