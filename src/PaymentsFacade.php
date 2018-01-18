<?php
namespace Solunes\Payments;

use Illuminate\Support\Facades\Facade;

class PaymentsFacade extends Facade
{
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'payments';
	}
}