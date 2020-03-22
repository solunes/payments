<form action="#" method="get">
    <p>Su pago aún no fue recibido en Paypal, una vez lo haga se registrará automáticamente.</p>
  	<a href="{{ url('payments/finish-sale-payment/'.$sale->id.'/paypal') }}"><div class="btn btn-site">Realizar Pago</div></a>
</form>          