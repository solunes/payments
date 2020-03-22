<form action="https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/" method="post">  
    <p>Su pago aún no fue recibido en Neteller, una vez lo haga se registrará automáticamente y efectuaremos el envío.</p>
    <a href="{{ url('payments/finish-sale-payment/'.$sale->id.'/neteller') }}"><div class="btn btn-site">Realizar Pago</div></a>
</form>