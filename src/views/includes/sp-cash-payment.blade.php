{!! Form::open(['url'=>'process/cash-payment', 'method'=>'post']) !!}
    <div class="row">
      <div class="col-md-12"><p>Recomendamos introducir un monto con el que va a pagar, para que se le lleve el cambio que corresponda.</p></div>
      <div class="col-md-12">
        <div class="checkout-form-list">
          <label>Definir Monto en Bs. con el que va a pagar<span class="required">*</span></label>
          {!! Form::number('amount', NULL, ['placeholder'=>'Monto en Bs.']) !!}                   
        </div>
      </div>
      <div class="col-md-12">
        <input name="sale_payment_id" type="hidden" value="{{ $sale_payment->id }}">
        <input class="btn btn-site" type="submit" value="Definir Monto de Pago">
      </div>
    </div>
{!! Form::close(); !!}