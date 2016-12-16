<?php
namespace Modular\Controllers;

use Modular\Controller;

class GraphNode extends Controller {
	const InjectorName = 'GraphNodeController';
	private static $injector_name = self::InjectorName;

	public static function create() {
		return \Injector::inst()->createWithArgs(static::config()->get('injector_name') ?: get_called_class(), func_get_args());
	}
}