<?php
namespace Modular\Models\Graph;

use Modular\Model;

/* abstract */
class Node extends Model implements \Modular\Interfaces\Graph\Node {
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