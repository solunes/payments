<form action="#" method="get">
    <p>Su pago aún no fue recibido en PayMe, una vez lo haga se registrará automáticamente y efectuaremos el envío.</p>
  	<a href="{{ url('payments/finish-sale-payment/'.$sale->id.'/payme') }}"><div class="btn btn-site">Realizar Pago</div></a>
</form>          