<form action="{{ url('process/finish-sale') }}" method="post">
  <h3>PAGO PENDIENTE</h3>
  <div class="store-form">           
    <p>Su pago aún no fue recibido en Paypal, una vez lo haga se registrará automáticamente.</p>
  	<a href="{{ url('payments/finish-sale-payment/'.$sale->id.'/paypal') }}"><div class="btn btn-site">Realizar Pago</div></a>
  </div>
</form>          