<?php
namespace Modular\Fields;

class GraphRef extends UniqueField  {
	const SingleFieldName = 'GraphRef';
	const SingleFieldSchema = 'Varchar(255)';

	public function onBeforeWrite() {
		if (!$this()->{self::SingleFieldName}) {
			$this()->{self::SingleFieldName} = static::graph_ref($this());
		}
	}

	public static function graph_ref($model) {
		return get_class($model) . ':' . md5(microtime() . random_bytes(10));
	}


}