<?php

namespace HexCoupon\App\Services;

use Kathamo\Framework\Lib\Service;
use HexCoupon\App\Core\Lib\SingleTon;

class AddNewCouponMenuService extends Service
{
	use SingleTon;

	public function getData()
	{
		$data = [
			'redirect_link' => 'post-new.php?post_type=shop_coupon',
		];
		return $data;
	}
}
