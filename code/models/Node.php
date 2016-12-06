<?php
namespace Modular\Models;

use Modular\Model;

class GraphNode extends Model implements \Modular\Interfaces\GraphNode {
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