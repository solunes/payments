<h1>Finalizar Pago en Payme</h1>
<p>Mostrar tabla con detalle de pago a realizarse.</p>
<form name="f1" id="f1" action="#" method="post" class="alignet-form-vpos2">
  <input type="hidden" name="acquirerId" value="<?php echo $acquirerId; ?>" />
  <input type="hidden" name="idCommerce" value="<?php echo $idCommerce; ?>" />
  <input type="hidden" name="purchaseOperationNumber" value="<?php echo $purchaseOperationNumber; ?>" />
  <input type="hidden" name="purchaseAmount" value="<?php echo $purchaseAmount; ?>" />
  <input type="hidden" name="purchaseCurrencyCode" value="<?php echo $purchaseCurrencyCode; ?>" />
  <input type="hidden" name="language" value="SP" />
  <input type="hidden" name="shippingFirstName" value="Eduardo" />
  <input type="hidden" name="shippingLastName" value="Mejia" />
  <input type="hidden" name="shippingEmail" value="edumejia30@gmail.com" />
  <input type="hidden" name="shippingAddress" value="Direcion ABC" />
  <input type="hidden" name="shippingZIP" value="0000" />
  <input type="hidden" name="shippingCity" value="La-Paz" />
  <input type="hidden" name="shippingState" value="La Paz" />
  <input type="hidden" name="shippingCountry" value="BO" />
  <!--Parametro que contiene el valor del codCardHolderCommerce.-->
  @if($userCommerce)
  <input type="hidden" name="userCommerce" value="<?php echo $userCommerce; ?>" />
  @endif
  <!--Parametro que contiene el valor del codAsoCardHolderWallet.-->
  @if($userCodePayme)
  <input type="hidden" name="userCodePayme" value="<?php echo $userCodePayme; ?>" />
  @endif
  <input type="hidden" name="descriptionProducts" value="Producto ABC" />
  <input type="hidden" name="programmingLanguage" value="PHP" />
  <!--Ejemplo envío campos reservados en parametro reserved1.-->
  <input type="hidden" name="reserved1" value="Valor Reservado ABC" />
  <input type="hidden" name="purchaseVerification" value="<?php echo $purchaseVerification; ?>" />
  <input type="button" class="btn btn-site" onclick="javascript:AlignetVPOS2.openModal(<?php echo $model_url; ?>)" value="REALIZAR PAGO">
</form>