@extends('master::layouts/admin')

@section('content')
  <h1>Registrar Deuda Manual</h1>
  <p>Utilice la siguiente herramienta para crear deudas de prueba.</p>
  {!! Form::open(['url'=>'pagostt/create-custom-payment', 'method'=>'POST', 'class'=>'form-horizontal filter']) !!}
    <div class="row flex">
      {!! Field::form_input(0, 'edit', ['name'=>'name','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Compra de Abono Anual', 'value'=>'Venta de Muestra']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'invoice_name','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Razón Social', 'value'=>'Mealla']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'invoice_nit','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Número de NIT', 'value'=>'12345678']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'price','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Monto de Precio Unitario', 'value'=>'500']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'discount_price','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Monto de Descuento Unitario', 'value'=>'100']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'quantity','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Cantidad Total de Unidades', 'value'=>'2']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'economic_sin_activity','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Actividad Económica Impuestos', 'value'=>'1']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'product_sin_code','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Código de Producto Impuestos', 'value'=>'1']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'product_internal_code','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Código de Producto Interno', 'value'=>'1']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'product_serial_number','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Número de Serie de Producto', 'value'=>'9894612311']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'commerce_user_code','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Código de Usuario de Comercio', 'value'=>'CAJA2']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'customer_code','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Código de Cliente', 'value'=>'52544']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'customer_ci_number','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Carnet de Identidad del Cliente', 'value'=>'4768578017']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'customer_ci_extension','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Extensión de Carnet de Identidad', 'value'=>'1']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'customer_ci_expedition','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Tipo de Carnet de Identidad', 'value'=>'1']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'invoice_type','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Tipo de Factura', 'value'=>'1']) !!}
      {!! Field::form_input(0, 'edit', ['name'=>'payment_type_code','type'=>'string','required'=>true], ['cols'=>4,'label'=>'Código de Tipo de Pago', 'value'=>'1']) !!}
    </div>
    {!! Form::hidden('customer_id', $customer->id) !!}

    {!! Form::submit('Registrar Deuda', array('class'=>'btn btn-site')) !!}
  {!! Form::close() !!}
@endsection