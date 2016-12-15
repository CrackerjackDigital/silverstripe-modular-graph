<?php
namespace Modular\Extensions\Model\Graph;

use Modular\ModelExtension;

class Node extends ModelExtension {
	private static $db = [
		'GraphRef' => 'Varchar(255)'
	];

}