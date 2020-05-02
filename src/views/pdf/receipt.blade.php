<!doctype html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <style>
    @font-face {
      font-family: 'pantonbold_italic';
      src: url('{{ asset("assets/admin/fonts/panton/panton_bold_italic-webfont.woff2") }}') format('woff2'),
           url('{{ asset("assets/admin/fonts/panton/panton_bold_italic-webfont.woff") }}') format('woff');
      font-weight: normal;
      font-style: normal;

    }
    @font-face {
      font-family: 'pantonsemibold_italic';
      src: url('{{ asset("assets/admin/fonts/panton/panton_semibold_italic-webfont.woff2") }}') format('woff2'),
           url('{{ asset("assets/admin/fonts/panton/panton_semibold_italic-webfont.woff") }}') format('woff');
      font-weight: normal;
      font-style: normal;

    }
    @font-face {
      font-family: 'pantonregular';
      src: url('{{ asset("assets/admin/fonts/panton/panton-webfont.woff2") }}') format('woff2'),
           url('{{ asset("assets/admin/fonts/panton/panton-webfont.woff") }}') format('woff');
      font-weight: normal;
      font-style: normal;

    }
    html, body { margin: 0; font-family: 'pantonregular'; padding: 7px; }
    .right { text-align: right; }
    .left { text-align: left; }
    .center { text-align: center; }
    .img-top img { margin-left:  80px; }
    .row { display: flex; flex-wrap: wrap; margin-right: -15px; margin-left: -15px; }
    .row:after { content: ""; display: table; clear: both; }
    .col-sm-4 { flex: 0 0 33.33333%; max-width: 33.33333%; position: relative; float: left; width: 30%; min-height: 1px; padding-right: 15px; padding-left: 15px; display: inline-block; }
    .col-sm-6 { flex: 0 0 50%; max-width: 50%; position: relative; float: left; width: 47%; min-height: 1px; padding-right: 15px; padding-left: 15px; display: inline-block; }
    .col-sm-8 { flex: 0 0 66.66667%; max-width: 66.66667%; position: relative; float: left; width: 62%; min-height: 1px; padding-right: 15px; padding-left: 15px; display: inline-block; }
    .col-sm-12 { flex: 0 0 100%; max-width: 100%; }
    .middle { position: relative; margin: auto; border: 1px solid #14393e; padding: 20px 20px 50px 20px; border-radius: 20px; }
    .description-rcp { border: 3px solid #000; padding: 10px 12px; margin-top: 12px; min-width: 270px; border-radius: 10px; display: inline-block; text-align: left; }
    .description-rcp p { margin: 2px 0px; }
    .description-rcp p span { font-weight: 700; }
    .padding-top-spacer { padding-top: 15px; }
    .description-sc { text-align: center; font-size: 15px; }
    .description-sc p.title-sc { font-size: 16px; font-weight: 700; }
    .description-sc p { margin: 2px 0px; }
    .details { padding: 0px 15px; border: 1px solid #000; border-radius: 10px; position: relative; margin-top: 20px; }
    .details span { font-weight: 700; padding-right: 15px; }
    table { text-align: center; width: 100%; background: #fff; margin: auto; margin-top: 20px; margin-bottom: 0; border: 1px solid #000; border-collapse: collapse; }
    table .title { font-weight: bold; background: #ECECEC; text-align: center; }
    table td { padding: 8px; line-height: 1.42857; vertical-align: top; border: 1px solid #000; margin: 0px !important; }
    table tfoot td.text { padding: 8px; line-height: 1.42857; vertical-align: top; border: 1px solid #000; margin: 0px !important; text-align: left; }
    table tr { margin: 0px; }
    .left { text-align: left !important; }
    .right { text-align: right !important; }
  </style>
</head>
<body>
  <div class="middle">
    
    <div class="row">
      <div class="col-sm-6 img-top">
        <img src="{{ asset('assets/img/logo-email.png') }}" />
      </div>
      <div class="col-sm-6 right">
        <div class="description-rcp">
          <p style="text-align:left;"><span>NIT:</span> 1006821025</p>
          <p style="text-align:left;"><span>Recibo N°:</span> {{ $receipt_number }}</p>
          <p style="text-align:left;"><span>Autorización N°:</span> 0</p>
        </div>
      </div>
    </div>

    <div class="row padding-top-spacer">
      <div class="col-sm-4">
        <div class="description-sc">
          <p class="title-sc">Casa Matriz</p>
          <p>Av Arequipa 8450</p>
          <p>Barrio La Florida</p>
          <p>Teléfono: 2792590</p>
          <p>La Paz - Bolivia</p>
        </div>
      </div>
      <div class="col-sm-4 center">
        <h1 style="letter-spacing: 1px;">RECIBO</h1>
      </div>
      <div class="col-sm-4 right">
        <p style="font-size: 19px; letter-spacing: 1px; padding-top: 5px;">ACTIVIDADES DEPORTIVAS Y OTRAS ACTIVIDADES DE ESPARCIMIENTO</p>
      </div>
    </div>

    <div class="details">
      <p><span>Lugar y Fecha:</span>  LA PAZ, {{ $date }}</p>
      <p><span>Señor (es):</span> @if($customer->nit_name!='S/N') {{ $customer->nit_name }} @else {{ $customer->full_name }} @endif </p>
      <p><span>NIT / CI:</span>  @if($customer->nit_number) {{ $customer->nit_number }} @else {{ $customer->ci_number }} @endif</p>
    </div>

    <table class="table">
      <thead>
        <tr class="title">
          <td class="">DETALLE</td>
          <td>SUBTOTAL</td>
        </tr>
      </thead>
      <tbody>
        @foreach($items as $item)
            <tr>
              <td>{!! $item->name !!}</td>
              <td style="white-space: nowrap;">Bs. {{ number_format(round($item->amount, 2),2) }}</td>
            </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr>
          <td class="text right"><strong>TOTAL BS.</strong></td>
          <td style="white-space: nowrap;"><strong>Bs. {{ number_format(round($total, 2),2) }}</strong></td>
        </tr>
        <tr>
          <td class="text"><strong>Son:</strong> {{ $total_text }} BOLIVIANOS.</td>
          <td style="white-space: nowrap;"></td>
        </tr>
      </tfoot>
    </table>
  
  </div>
</body>
</html>