<?php
namespace Modular\Fields;

use FieldList;
use FormField;

class GraphRef extends UniqueField {
	const Name   = 'GraphRef';
	const Schema = 'Varchar(255)';

	private static $admin_only = true;

	public function onBeforeWrite() {
		if (!$this()->{static::field_name()}) {
			$this()->{self::field_name()} = static::graph_ref($this());
		}
	}

	public static function graph_ref($model) {
		return get_class($model) . ':' . md5(microtime() . \random_bytes(10));
	}

}