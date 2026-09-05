<?php
/**
 * Persian fallback for the most visible WooCommerce core strings.
 *
 * WooCommerce ships its own Persian language pack (installed automatically by
 * WordPress when the site language is fa_IR and the pack is available). On
 * offline / freshly-installed sites that pack may be missing, which leaves the
 * checkout, cart and account UI half-English. This file translates the strings
 * a shopper actually sees, and only when WooCommerce did NOT translate them.
 *
 * It never overrides an existing WooCommerce translation.
 *
 * @package Nexora
 */

defined( 'ABSPATH' ) || exit;

/**
 * Map of WooCommerce msgid => Persian.
 *
 * @return array<string,string>
 */
function nexora_wc_fa_strings() {
	static $map = null;
	if ( null !== $map ) {
		return $map;
	}
	$map = array(
		// Price HTML (screen-reader text).
		'Original price was: %s.'            => 'قیمت اصلی: %s.',
		'Current price is: %s.'              => 'قیمت فعلی: %s.',
		// Checkout fields.
		'First name'                         => 'نام',
		'Last name'                          => 'نام خانوادگی',
		'Company name'                       => 'نام شرکت',
		'Country / Region'                   => 'کشور / منطقه',
		'Street address'                     => 'آدرس',
		'House number and street name'       => 'خیابان، کوچه و پلاک',
		'Apartment, suite, unit, etc.'       => 'واحد، طبقه و … ',
		'Apartment, suite, unit, etc. (optional)' => 'واحد، طبقه و … (اختیاری)',
		'Town / City'                        => 'شهر',
		'State / County'                     => 'استان',
		'Postcode / ZIP'                     => 'کد پستی',
		'Phone'                              => 'شماره تماس',
		'Email address'                      => 'ایمیل',
		'Order notes'                        => 'یادداشت سفارش',
		'Notes about your order, e.g. special notes for delivery.' => 'توضیحات سفارش، مثلاً نکات لازم برای تحویل.',
		'(optional)'                         => '(اختیاری)',
		'optional'                           => 'اختیاری',
		'required'                           => 'الزامی',
		'Select a country / region&hellip;'  => 'انتخاب کشور…',
		'Select an option&hellip;'           => 'انتخاب کنید…',
		'Choose an option'                   => 'انتخاب کنید',
		'Select options'                     => 'انتخاب گزینه‌ها',
		'Update country / region'            => 'به‌روزرسانی کشور',
		'Ship to a different address?'       => 'ارسال به آدرس دیگر؟',
		'Create an account?'                 => 'ساخت حساب کاربری؟',
		'Create account password'            => 'رمز عبور حساب',
		'Password'                           => 'رمز عبور',
		'Username or email address'          => 'نام کاربری یا ایمیل',
		'Username or email'                  => 'نام کاربری یا ایمیل',
		'Remember me'                        => 'مرا به خاطر بسپار',
		'Lost your password?'                => 'رمز عبور را فراموش کرده‌اید؟',
		'Log in'                             => 'ورود',
		'Login'                              => 'ورود',
		'Register'                           => 'ثبت‌نام',
		'Log out'                            => 'خروج',
		'Logout'                             => 'خروج',
		'Reset password'                     => 'بازنشانی رمز عبور',
		'Save changes'                       => 'ذخیره تغییرات',
		'Save address'                       => 'ذخیره آدرس',
		'Place order'                        => 'ثبت سفارش',
		'Proceed to checkout'                => 'ادامه و تسویه‌حساب',
		'Update cart'                        => 'به‌روزرسانی سبد',
		'Apply coupon'                       => 'اعمال کد تخفیف',
		'Coupon code'                        => 'کد تخفیف',
		'Coupon:'                            => 'کد تخفیف:',
		'Add to cart'                        => 'افزودن به سبد',
		'Read more'                          => 'مشاهده',
		'Buy now'                            => 'خرید',
		'View cart'                          => 'مشاهده سبد خرید',
		'Checkout'                           => 'تسویه‌حساب',
		'Cart'                               => 'سبد خرید',
		'Shop'                               => 'فروشگاه',
		'My account'                         => 'حساب کاربری',
		'Dashboard'                          => 'پیشخوان',
		'Orders'                             => 'سفارش‌ها',
		'Order'                              => 'سفارش',
		'Downloads'                          => 'دانلودها',
		'Addresses'                          => 'آدرس‌ها',
		'Account details'                    => 'جزئیات حساب',
		'Payment methods'                    => 'روش‌های پرداخت',
		'Billing address'                    => 'آدرس صورتحساب',
		'Shipping address'                   => 'آدرس ارسال',
		'Billing details'                    => 'اطلاعات صورتحساب',
		'Shipping details'                   => 'اطلاعات ارسال',
		'Additional information'             => 'اطلاعات تکمیلی',
		'Your order'                         => 'سفارش شما',
		'Product'                            => 'محصول',
		'Products'                           => 'محصولات',
		'Price'                              => 'قیمت',
		'Quantity'                           => 'تعداد',
		'Subtotal'                           => 'جمع جزء',
		'Total'                              => 'مجموع',
		'Totals'                             => 'جمع کل',
		'Shipping'                           => 'ارسال',
		'Free shipping'                      => 'ارسال رایگان',
		'Flat rate'                          => 'هزینه ثابت',
		'Local pickup'                       => 'تحویل حضوری',
		'Free!'                              => 'رایگان!',
		'Free'                               => 'رایگان',
		'Tax'                                => 'مالیات',
		'VAT'                                => 'مالیات بر ارزش افزوده',
		'Discount'                           => 'تخفیف',
		'Date'                               => 'تاریخ',
		'Status'                             => 'وضعیت',
		'Actions'                            => 'عملیات',
		'View'                               => 'مشاهده',
		'Pay'                                => 'پرداخت',
		'Cancel'                             => 'لغو',
		'Edit'                               => 'ویرایش',
		'Add'                                => 'افزودن',
		'Remove'                             => 'حذف',
		'Remove this item'                   => 'حذف این مورد',
		'Description'                        => 'توضیحات',
		'Reviews'                            => 'دیدگاه‌ها',
		'Related products'                   => 'محصولات مرتبط',
		'You may also like&hellip;'          => 'شاید بپسندید…',
		'In stock'                           => 'موجود',
		'Out of stock'                       => 'ناموجود',
		'Available on backorder'             => 'قابل پیش‌سفارش',
		'%s in stock'                        => '%s عدد در انبار',
		'Only %s left in stock'              => 'فقط %s عدد باقی مانده',
		'(can be backordered)'               => '(قابل پیش‌سفارش)',
		'SKU:'                               => 'شناسه:',
		'SKU'                                => 'شناسه',
		'Category:'                          => 'دسته:',
		'Categories:'                        => 'دسته‌ها:',
		'Tag:'                               => 'برچسب:',
		'Tags:'                              => 'برچسب‌ها:',
		'Sale!'                              => 'حراج!',
		'Default sorting'                    => 'مرتب‌سازی پیش‌فرض',
		'Sort by popularity'                 => 'محبوب‌ترین',
		'Sort by average rating'             => 'بالاترین امتیاز',
		'Sort by latest'                     => 'جدیدترین',
		'Sort by price: low to high'         => 'قیمت: کم به زیاد',
		'Sort by price: high to low'         => 'قیمت: زیاد به کم',
		'Search products&hellip;'            => 'جستجوی محصولات…',
		'Search'                             => 'جستجو',
		'Clear'                              => 'پاک‌کردن',
		'Filter'                             => 'فیلتر',
		'Filter by price'                    => 'فیلتر قیمت',
		'Home'                               => 'خانه',
		'Your cart is currently empty.'      => 'سبد خرید شما خالی است.',
		'Return to shop'                     => 'بازگشت به فروشگاه',
		'No products in the cart.'           => 'محصولی در سبد نیست.',
		'Thank you. Your order has been received.' => 'متشکریم. سفارش شما ثبت شد.',
		'Order number:'                      => 'شماره سفارش:',
		'Date:'                              => 'تاریخ:',
		'Email:'                             => 'ایمیل:',
		'Total:'                             => 'مجموع:',
		'Payment method:'                    => 'روش پرداخت:',
		'Order details'                      => 'جزئیات سفارش',
		'Customer details'                   => 'اطلاعات مشتری',
		'Note:'                              => 'یادداشت:',
		'Direct bank transfer'               => 'کارت به کارت / واریز بانکی',
		'Cash on delivery'                   => 'پرداخت در محل',
		'Check payments'                     => 'پرداخت با چک',
		'Pay with cash upon delivery.'       => 'هزینه سفارش را هنگام تحویل پرداخت کنید.',
		'Make your payment directly into our bank account. Please use your Order ID as the payment reference. Your order will not be shipped until the funds have cleared in our account.' => 'مبلغ سفارش را به حساب بانکی ما واریز کنید و شماره سفارش را در توضیحات پرداخت بنویسید. سفارش پس از تأیید واریز ارسال می‌شود.',
		'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our %s.' => 'اطلاعات شخصی شما برای پردازش سفارش، پشتیبانی از تجربه شما در این وب‌سایت و سایر اهدافی که در %s توضیح داده شده استفاده می‌شود.',
		'privacy policy'                     => 'سیاست حفظ حریم خصوصی',
		'Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.' => 'اطلاعات شخصی شما برای پشتیبانی از تجربه شما در این وب‌سایت، مدیریت دسترسی به حساب کاربری و سایر اهدافی که در %s توضیح داده شده استفاده می‌شود.',
		'I have read and agree to the website %s' => '%s وب‌سایت را خوانده‌ام و می‌پذیرم',
		'terms and conditions'               => 'قوانین و مقررات',
		'Have a coupon?'                     => 'کد تخفیف دارید؟',
		'Click here to enter your code'      => 'برای واردکردن کد اینجا کلیک کنید',
		'Returning customer?'                => 'قبلاً ثبت‌نام کرده‌اید؟',
		'Click here to login'                => 'برای ورود کلیک کنید',
		'Delivers to'                        => 'ارسال به',
		'Shipping to %s.'                    => 'ارسال به %s.',
		'Change address'                     => 'تغییر آدرس',
		'Calculate shipping'                 => 'محاسبه هزینه ارسال',
		'Update'                             => 'به‌روزرسانی',
		'Order summary'                      => 'خلاصه سفارش',
		'Add a coupon'                       => 'افزودن کد تخفیف',
		'Estimated total'                    => 'مجموع تقریبی',
		'No saved methods found.'            => 'روش پرداخت ذخیره‌شده‌ای وجود ندارد.',
		'No order has been made yet.'        => 'هنوز سفارشی ثبت نشده است.',
		'Browse products'                    => 'مشاهده محصولات',
		'No downloads available yet.'        => 'هنوز دانلودی موجود نیست.',
		'You have not set up this type of address yet.' => 'هنوز این نوع آدرس را ثبت نکرده‌اید.',
		'The following addresses will be used on the checkout page by default.' => 'آدرس‌های زیر به‌صورت پیش‌فرض در صفحه تسویه‌حساب استفاده می‌شوند.',
		'Display name'                       => 'نام نمایشی',
		'Current password (leave blank to leave unchanged)' => 'رمز عبور فعلی (برای عدم تغییر خالی بگذارید)',
		'New password (leave blank to leave unchanged)'     => 'رمز عبور جدید (برای عدم تغییر خالی بگذارید)',
		'Confirm new password'               => 'تکرار رمز عبور جدید',
		'Password change'                    => 'تغییر رمز عبور',
		'Rated %s out of 5'                  => 'امتیاز %s از ۵',
		'out of 5'                           => 'از ۵',
		'customer review'                    => 'دیدگاه مشتری',
		'customer reviews'                   => 'دیدگاه مشتریان',
		'Add a review'                       => 'افزودن دیدگاه',
		'Be the first to review &ldquo;%s&rdquo;' => 'اولین نفری باشید که درباره «%s» دیدگاه می‌نویسد',
		'Your review'                        => 'دیدگاه شما',
		'Your rating'                        => 'امتیاز شما',
		'Submit'                             => 'ارسال',
		'Name'                               => 'نام',
		'Email'                              => 'ایمیل',
		'Perfect'                            => 'عالی',
		'Good'                               => 'خوب',
		'Average'                            => 'متوسط',
		'Not that bad'                       => 'قابل قبول',
		'Very poor'                          => 'ضعیف',
		'Rate&hellip;'                       => 'امتیاز دهید…',
		'Pending payment'                    => 'در انتظار پرداخت',
		'Processing'                         => 'در حال پردازش',
		'On hold'                            => 'در انتظار بررسی',
		'Completed'                          => 'تکمیل‌شده',
		'Cancelled'                          => 'لغوشده',
		'Refunded'                           => 'مسترد‌شده',
		'Failed'                             => 'ناموفق',
		'Draft'                              => 'پیش‌نویس',
		'Iran'                               => 'ایران',
		'Tehran'                             => 'تهران',
	);
	return $map;
}

/**
 * gettext fallback: only when WooCommerce returned the untranslated msgid.
 */
function nexora_wc_gettext_fallback( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain || $translation !== $text || ! nexora_is_fa() ) {
		return $translation;
	}
	$map = nexora_wc_fa_strings();
	return isset( $map[ $text ] ) ? $map[ $text ] : $translation;
}
add_filter( 'gettext', 'nexora_wc_gettext_fallback', 20, 3 );
add_filter(
	'gettext_with_context',
	static function ( $translation, $text, $context, $domain ) {
		return nexora_wc_gettext_fallback( $translation, $text, $domain );
	},
	20,
	4
);

/**
 * Country and state names: Persian for the common Iranian storefront.
 */
add_filter(
	'woocommerce_countries',
	static function ( $countries ) {
		if ( nexora_is_fa() && isset( $countries['IR'] ) && 'Iran' === $countries['IR'] ) {
			$countries['IR'] = 'ایران';
		}
		return $countries;
	}
);
add_filter(
	'woocommerce_states',
	static function ( $states ) {
		if ( ! nexora_is_fa() || empty( $states['IR'] ) ) {
			return $states;
		}
		$fa = array(
			'KHZ' => 'خوزستان', 'THR' => 'تهران', 'ILM' => 'ایلام', 'BHR' => 'بوشهر', 'ADL' => 'اردبیل', 'ESF' => 'اصفهان', 'YZD' => 'یزد', 'KRH' => 'کرمانشاه', 'KRN' => 'کرمان',
			'HDN' => 'همدان', 'GZN' => 'قزوین', 'ZJN' => 'زنجان', 'LRS' => 'لرستان', 'ABZ' => 'البرز', 'EAZ' => 'آذربایجان شرقی', 'WAZ' => 'آذربایجان غربی', 'CHB' => 'چهارمحال و بختیاری',
			'SKH' => 'خراسان جنوبی', 'RKH' => 'خراسان رضوی', 'NKH' => 'خراسان شمالی', 'SMN' => 'سمنان', 'FRS' => 'فارس', 'QHM' => 'قم', 'KRD' => 'کردستان', 'KBD' => 'کهگیلویه و بویراحمد',
			'GLS' => 'گلستان', 'GIL' => 'گیلان', 'MZN' => 'مازندران', 'MKZ' => 'مرکزی', 'HRZ' => 'هرمزگان', 'SBN' => 'سیستان و بلوچستان',
		);
		foreach ( $states['IR'] as $code => $label ) {
			if ( isset( $fa[ $code ] ) && ! preg_match( '/[\x{0600}-\x{06FF}]/u', $label ) ) {
				$states['IR'][ $code ] = $fa[ $code ];
			} elseif ( isset( $fa[ $code ] ) && false !== strpos( $label, '(' ) ) {
				$states['IR'][ $code ] = $fa[ $code ];
			}
		}
		return $states;
	}
);

/**
 * The checkout / registration privacy text is stored as an option in English at
 * WooCommerce install time. Translate it when the admin never customised it.
 */
add_filter(
	'woocommerce_get_privacy_policy_text',
	static function ( $text, $type ) {
		if ( ! nexora_is_fa() || false === strpos( $text, 'Your personal data will be used' ) ) {
			return $text;
		}
		if ( 'registration' === $type ) {
			return 'اطلاعات شخصی شما برای پشتیبانی از تجربه شما در این وب‌سایت، مدیریت دسترسی به حساب کاربری و سایر اهدافی که در [privacy_policy] توضیح داده شده استفاده می‌شود.';
		}
		return 'اطلاعات شخصی شما برای پردازش سفارش، پشتیبانی از تجربه شما در این وب‌سایت و سایر اهدافی که در [privacy_policy] توضیح داده شده استفاده می‌شود.';
	},
	10,
	2
);

/**
 * WooCommerce creates its pages with English titles at install time (Shop, Cart, …).
 * Show localised titles on the front end when the title is still the untouched default.
 *
 * @param string $title   Page title.
 * @param int    $post_id Post ID.
 * @return string
 */
function nexora_wc_page_title_fallback( $title, $post_id = 0 ) {
	static $map = null;
	if ( is_admin() || ! $post_id || ! function_exists( 'wc_get_page_id' ) || ! nexora_is_fa() ) {
		return $title;
	}
	if ( null === $map ) {
		$map = array(
			'Shop'          => __( 'Shop', 'nexora' ),
			'Cart'          => __( 'Cart', 'nexora' ),
			'Checkout'      => __( 'Checkout', 'nexora' ),
			'My account'    => __( 'My account', 'nexora' ),
			'Refund and Returns Policy' => __( 'Refund and Returns Policy', 'nexora' ),
		);
	}
	if ( isset( $map[ $title ] ) ) {
		foreach ( array( 'shop', 'cart', 'checkout', 'myaccount', 'refund_returns' ) as $key ) {
			if ( (int) wc_get_page_id( $key ) === (int) $post_id ) {
				return $map[ $title ];
			}
		}
	}
	return $title;
}
add_filter( 'the_title', 'nexora_wc_page_title_fallback', 10, 2 );
