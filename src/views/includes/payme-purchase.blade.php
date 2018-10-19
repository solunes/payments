<h1>Finalizar Pago en Payme</h1>
<p>Mostrar tabla con detalle de pago a realizarse.</p>
<form name="f1" id="f1" action="#" method="post" class="alignet-form-vpos2">
  <input type="hidden" name="acquirerId" value="<?php echo $acquirerId; ?>" />
  <input type="hidden" name="idCommerce" value="<?php echo $idCommerce; ?>" />
  <input type="hidden" name="purchaseOperationNumber" value="<?php echo $purchaseOperationNumber; ?>" />
  <input type="hidden" name="purchaseAmount" value="<?php echo $purchaseAmount; ?>" />
  <input type="hidden" name="purchaseCurrencyCode" value="<?php echo $purchaseCurrencyCode; ?>" />
  <input type="hidden" name="language" value="SP" />
  <input type="hidden" name="shippingFirstName" value="Juan" />
  <input type="hidden" name="shippingLastName" value="Perez" />
  <input type="hidden" name="shippingEmail" value="test@test.com" />
  <input type="hidden" name="shippingAddress" value="Direcion ABC" />
  <input type="hidden" name="shippingZIP" value="ZIP 123" />
  <input type="hidden" name="shippingCity" value="City ABC" />
  <input type="hidden" name="shippingState" value="State ABC" />
  <input type="hidden" name="shippingCountry" value="PE" />
  <!--Parametro que contiene el valor del codCardHolderCommerce.-->
  <input type="hidden" name="userCommerce" value="modal123" />
  <!--Parametro que contiene el valor del codAsoCardHolderWallet.-->
  <input type="hidden" name="userCodePayme" value="1--510--1010" />
  <input type="hidden" name="descriptionProducts" value="Producto ABC" />
  <input type="hidden" name="programmingLanguage" value="PHP" />
  <!--Ejemplo envío campos reservados en parametro reserved1.-->
  <input type="hidden" name="reserved1" value="Valor Reservado ABC" />
  <input type="hidden" name="purchaseVerification" value="<?php echo $purchaseVerification; ?>" />
  <input type="button" class="btn btn-site" onclick="javascript:AlignetVPOS2.openModal(<?php echo $model_url; ?>)" value="REALIZAR PAGO">
</form>