<?php

namespace HexCoupon\App\Services;

use Kathamo\Framework\Lib\Service;
use HexCoupon\App\Core\Lib\SingleTon;

class AdminMenuService extends Service
{
	use SingleTon;

	public function getData()
	{
		$data = [
			'plugin_name' => 'HexCoupon',
			'developed'   => 'Author',
			'author_name' => 'WpHex',
			'author_link' => 'https://wphex.com/',
		];
		return $data;
	}
}
