<?php

namespace HexCoupon\App\Core;

use HexCoupon\App\Controllers\AdminMenuController;
use HexCoupon\App\Controllers\AjaxApiController;
use HexCoupon\App\Controllers\Api\SpinWheelSettingsApiController;
use HexCoupon\App\Controllers\ConvertCartPageToClassic;
use HexCoupon\App\Controllers\Licensing\LicenseExpiry;
use HexCoupon\App\Controllers\RedirectUserToPluginDashboard;
use HexCoupon\App\Controllers\WooCommerce\Admin\CouponGeneralTabController;
use HexCoupon\App\Controllers\WooCommerce\Admin\PaymentAndShippingTabController;
use HexCoupon\App\Controllers\WooCommerce\Admin\CouponSharableUrlTabController;
use HexCoupon\App\Controllers\WooCommerce\Admin\CouponUsageRestrictionTabController;
use HexCoupon\App\Controllers\WooCommerce\Admin\CouponUsageLimitsTabController;
use HexCoupon\App\Controllers\WooCommerce\Admin\CouponGeographicRestrictionTabController;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\HexcouponBogoController;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetSpecificProductForSpecificProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetSameProductForSpecificProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetCombinationOfProductForSpecificProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetProductFromListForSpecificProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetSpecificProductForCombinationOfProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetCombinationOfProductForCombinationOfProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetProductFromListForCombinationOfProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetSpecificProductForAnyListedProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetCombinationOfProductForAnyListedProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetAnyListedProductForAnyListedProduct;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetSpecificProductAndCombinationOfProductForProductCategory;
use HexCoupon\App\Controllers\WooCommerce\Admin\Bogo\GetAnyProductFromListForProductCategory;
use HexCoupon\App\Controllers\WooCommerce\LoyaltyProgram\FlushRewriteForLoyaltyProgram;
use HexCoupon\App\Controllers\WooCommerce\StoreCredit\FlushRewriteForStoreCredit;
use HexCoupon\App\Controllers\WooCommerce\StoreCredit\SaveStoreCreditOptionsValueControllers;
use HexCoupon\App\Controllers\WooCommerce\StoreCredit\StoreCreditRefund;
use HexCoupon\App\Core\Helpers\LoyaltyProgram\DisplayAllNotice;
use HexCoupon\App\Core\Helpers\LoyaltyProgram\LoyaltyProgramHelpers;
use HexCoupon\App\Core\Helpers\StoreCredit\UpdateOrderTotalOnCheckoutPage;
use HexCoupon\App\Core\Lib\SingleTon;
use HexCoupon\App\Core\WooCommerce\AddCustomLinksInAllPluginsPage;
use HexCoupon\App\Core\WooCommerce\CheckoutBlock\StoreBlock;
use HexCoupon\App\Core\WooCommerce\CouponCategory;
use HexCoupon\App\Core\WooCommerce\CouponEmailSMS;
use HexCoupon\App\Core\WooCommerce\CouponPaymentandShipping;
use HexCoupon\App\Core\WooCommerce\CouponSingleDaysAndHoursTab;
use HexCoupon\App\Core\WooCommerce\CouponSingleGeneralTab;
use HexCoupon\App\Core\WooCommerce\CouponSingleGeographicRestrictions;
use HexCoupon\App\Core\WooCommerce\CouponSingleSharableUrl;
use HexCoupon\App\Core\WooCommerce\CouponSingleUsageRestriction;
use HexCoupon\App\Core\WooCommerce\CouponSingleUsageLimits;
use HexCoupon\App\Core\WooCommerce\CouponShortcode;
use HexCoupon\App\Core\WooCommerce\LoyaltyProgram\LoyaltyProgram;
use HexCoupon\App\Core\WooCommerce\MyAccount;
use HexCoupon\App\Core\WooCommerce\StoreCredit\AddStoreCreditCheckbox;
use HexCoupon\App\Core\WooCommerce\StoreCredit\AddStoreCreditDeductionRow;
use HexCoupon\App\Core\WooCommerce\StoreCredit\OrderDetailsForStoreCredit;
use HexCoupon\App\Core\WooCommerce\StoreCredit\StoreCreditRowInCheckoutOrderDetails;
use HexCoupon\App\Services\ActivationService;
use HexCoupon\App\Services\DeactivationService;
use HexCoupon\App\Controllers\Api\StoreCreditSettingsApiController;
use HexCoupon\App\Controllers\Api\LoyaltyProgramSettingsApiController;
use HexCoupon\App\Core\WooCommerce\StoreCredit;
use HexCoupon\App\Core\WooCommerce\SpinWheel\SpinWheel;
use Kathamo\Framework\Lib\BootManager;

final class Core extends BootManager
{
	use SingleTon;

	protected function registerList()
	{
		/**
		 * need to resgiter controller
		 * */
		$this->registerList = [
			ActivationService::class,
			DeactivationService::class,
			AssetsManager::class,
			AdminMenuController::class,
			AdminNoticeManager::class,
			MyAccount::class,
			CouponCategory::class,
			CouponShortcode::class,
			CouponPaymentandShipping::class,
			PaymentAndShippingTabController::class,
			CouponSingleGeneralTab::class,
			CouponGeneralTabController::class,
			CouponSingleDaysAndHoursTab::class,
			CouponSingleGeographicRestrictions::class,
			CouponGeographicRestrictionTabController::class,
			CouponSingleUsageRestriction::class,
			CouponUsageRestrictionTabController::class,
			CouponSingleUsageLimits::class,
			CouponUsageLimitsTabController::class,
			CouponSingleSharableUrl::class,
			CouponSharableUrlTabController::class,
			AjaxApiController::class,
			HexcouponBogoController::class,
			GetSpecificProductForSpecificProduct::class,
			GetSameProductForSpecificProduct::class,
			GetCombinationOfProductForSpecificProduct::class,
			GetProductFromListForSpecificProduct::class,
			GetSpecificProductForCombinationOfProduct::class,
			GetCombinationOfProductForCombinationOfProduct::class,
			GetProductFromListForCombinationOfProduct::class,
			GetSpecificProductForAnyListedProduct::class,
			GetCombinationOfProductForAnyListedProduct::class,
			GetAnyListedProductForAnyListedProduct::class,
			GetSpecificProductAndCombinationOfProductForProductCategory::class,
			GetAnyProductFromListForProductCategory::class,
			StoreCredit::class,
			StoreCreditSettingsApiController::class,
			LoyaltyProgramSettingsApiController::class,
			SpinWheelSettingsApiController::class,
			AddCustomLinksInAllPluginsPage::class,
			RedirectUserToPluginDashboard::class,
			ConvertCartPageToClassic::class,
			StoreCreditRefund::class,
			AddStoreCreditCheckbox::class,
			StoreCreditRowInCheckoutOrderDetails::class,
			AddStoreCreditDeductionRow::class,
			UpdateOrderTotalOnCheckoutPage::class,
			SaveStoreCreditOptionsValueControllers::class,
			StoreBlock::class,
			FlushRewriteForStoreCredit::class,
			DisplayAllNotice::class,
			LoyaltyProgramHelpers::class,
			LoyaltyProgram::class,
			FlushRewriteForLoyaltyProgram::class,
			OrderDetailsForStoreCredit::class,
			LicenseExpiry::class,
			SpinWheel::class,
		];
	}
}
