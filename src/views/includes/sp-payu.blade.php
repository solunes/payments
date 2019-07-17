<!--<form action="{{ url('process/finish-sale') }}" method="post">
  <h3>PAGO PENDIENTE</h3>
  <div class="store-form">           
    <p>Su pago aún no fue recibido en PayMe, una vez lo haga se registrará automáticamente y efectuaremos el envío.</p>
  	<a href="{{ url('payments/finish-sale-payment/'.$sale->id.'/payu') }}"><div class="btn btn-site">Realizar Pago</div></a>
  </div>
</form>-->
<form action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/" method="post">
  <h3>PAGO PENDIENTE</h3>
  <div class="store-form">           
    <p>Su pago aún no fue recibido en PayU, una vez lo haga se registrará automáticamente y efectuaremos el envío.</p>
    <input name="merchantId"    type="hidden"  value="508029"   >
    <input name="accountId"     type="hidden"  value="512321" >
    <input name="description"   type="hidden"  value="{{ $sale->name }}"  >
    <input name="referenceCode" type="hidden"  value="TestPayU" >
    <input name="amount"        type="hidden"  value="{{ round($sale->amount*100) }}"   >
    <input name="tax"           type="hidden"  value="{{ round($sale->amount*100*0.16) }}"  >
    <input name="taxReturnBase" type="hidden"  value="{{ round($sale->amount*100*0.84) }}" >
    <input name="currency"      type="hidden"  value="COP" >
    <input name="signature"     type="hidden"  value="7ee7cf808ce6a39b17481c54f2c57acc"  >
    <input name="test"          type="hidden"  value="1" >
    <input name="buyerEmail"    type="hidden"  value="{{ $sale->customer->email }}" >
    <input name="responseUrl"    type="hidden"  value="http://www.test.com/response" >
    <input name="confirmationUrl"    type="hidden"  value="http://www.test.com/confirmation" >
    <input name="Submit"        type="submit" class="btn btn-site" value="Realizar Pago" >
  </div>
</form>