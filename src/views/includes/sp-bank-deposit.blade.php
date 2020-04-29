{!! Form::open(['url'=>'process/bank-deposit', 'method'=>'post', 'files'=>true]) !!}
  <div class="row">
    <div class="col-md-12"><p>Puede realizar su deposito a cualquiera de las siguientes cuentas bancarias:</p></div>
    <ul>
      @foreach(\Solunes\Payments\App\OnlineBank::where('currency_id', $sale->currency_id)->get() as $online_bank)
        <li><strong>{{ $online_bank->name }} - {{ $online_bank->currency->name }} - {{ $online_bank->account_number }}</strong><br>{!! $online_bank->content !!}</li>
      @endforeach
    </ul>
    <div class="col-md-12"><p>Una vez realice su transferencia, puede confirmar el pago subiendo su comprobante aquí y luego seleccionar .</p></div>
    @if(count($sale_payment->online_bank_deposits)>0)
      <div class="col-md-12">
        <p><strong>Comprobante cargado.. Procesando pago..</strong></p>
        <p>Ya cargó el siguiente comprobante que será validado en las próximas horas, sin embargo si hubo un error le recomendamos recargarlo:</p>
        @foreach($sale_payment->online_bank_deposits as $online_bank_deposit)
          <a target="_blank" href="{{ Asset::get_file('online-bank-deposit-file',$online_bank_deposit->file) }}"> 
            <button type="button" class="btn btn-site">Ver Archivo Cargado</button>
          </a>
        @endforeach
        <br><br>
      </div>
    @endif
    <div class="col-md-12">
      <div class="checkout-form-list">
        <label>Seleccione la cuenta bancaria a la que está depositando <span class="required">*</span></label>
        {!! Form::select('online_bank_id', \Solunes\Payments\App\OnlineBank::where('currency_id', $sale->currency_id)->get()->lists('full_name','id')->toArray(), NULL) !!}                   
      </div>
    </div>
    <div class="col-md-12">
      <div class="checkout-form-list">
        <label>Cargar comprobante de depósito <span class="required">*</span></label>
        {!! Form::file('file', NULL) !!}                   
      </div>
    </div>
    <div class="col-md-12">
      <input name="sale_payment_id" type="hidden" value="{{ $sale_payment->id }}">
      <input class="btn btn-site" type="submit" value="Enviar Comprobante">
    </div>
  </div>
{!! Form::close() !!}