<?php
namespace Modular\Models\Graph;

use Modular\Model;

/* abstract */
class Node extends Model implements \Modular\Interfaces\Graph\Node {
	const InjectorName = 'GraphNode';
	private static $injector_name = self::InjectorName;

	public static function create() {
		return \Injector::inst()->createWithArgs(static::config()->get('injector_name') ?: get_called_class(), func_get_args());
	}

}