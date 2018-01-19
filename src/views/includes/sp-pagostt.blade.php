<form action="{{ url('process/finish-sale') }}" method="post">
  <h3>PAGO PENDIENTE</h3>
  <div class="store-form">           
    <p>Su pago aún no fue recibido en TodoTix, una vez lo haga se registrará automáticamente y efectuaremos el envío.</p>
  	<a href="{{ url('payments/todotix/make-payment/'.$sale->id) }}"><div class="btn btn-site">Ir a TodoTix</div></a>
  </div>
</form>          