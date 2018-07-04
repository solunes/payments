<form action="{{ url('process/finish-sale') }}" method="post">
  <h3>PAGO PENDIENTE</h3>
  <div class="store-form">           
    <p>Su pago aún no fue recibido en Paypal, una vez lo haga se registrará automáticamente.</p>
  	<a href="{{ url('payments/paypal/make-payment/'.$sale->id) }}"><div class="btn btn-site">Ir a Paypal</div></a>
  </div>
</form>          