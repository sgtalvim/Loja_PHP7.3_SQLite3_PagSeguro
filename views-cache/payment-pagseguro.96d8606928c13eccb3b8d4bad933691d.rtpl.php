<?php if(!class_exists('Rain\Tpl')){exit;}?>Direcionando para o PagSeguro...

<!-- 08/06/2024 -->
<!-- https://pagseguro.uol.com.br/desenvolvedor/carrinho_proprio.jhtml#rmcl -->

<form method="post" action="https://pagseguro.uol.com.br/checkout/checkout.jhtml">
  <input type="hidden" name="email_cobranca" value="claudiopaiva1@yahoo.com.br">
  <input type="hidden" name="tipo" value="CP">
  <input type="hidden" name="moeda" value="BRL">

  <!-- PRODUTOS -->
  <?php $counter1=-1;  if( isset($products) && ( is_array($products) || $products instanceof Traversable ) && sizeof($products) ) foreach( $products as $key1 => $value1 ){ $counter1++; ?>

  <input type="hidden" name="item_id_<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" value="<?php echo htmlspecialchars( $value1["idproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
  <input type="hidden" name="item_descr_<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" value="<?php echo htmlspecialchars( $value1["desproduct"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
  <input type="hidden" name="item_quant_<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" value="<?php echo htmlspecialchars( $value1["nrqtd"], ENT_COMPAT, 'UTF-8', FALSE ); ?>">
  <input type="hidden" name="item_valor_<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" value="<?php echo htmlspecialchars( $value1["vlprice"]*100, ENT_COMPAT, 'UTF-8', FALSE ); ?>">
  <?php if( $counter1 != 0 ){ ?>

  <input type="hidden" name="item_frete_<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" value="0">
  <?php }else{ ?>

  <input type="hidden" name="item_frete_1" value="<?php echo htmlspecialchars( $order["vlfreight"]*100, ENT_COMPAT, 'UTF-8', FALSE ); ?>">
  <?php } ?>

  <input type="hidden" name="item_peso_<?php echo htmlspecialchars( $counter1+1, ENT_COMPAT, 'UTF-8', FALSE ); ?>" value="<?php echo htmlspecialchars( $value1["vlweight"]*1000, ENT_COMPAT, 'UTF-8', FALSE ); ?>">
  <?php } ?>

  
  <input type="hidden" name="tipo_frete" value="EN">
  <input type="image" src="https://p.simg.uol.com.br/out/pagseguro/i/botoes/pagamentos/99x61-pagar-assina.gif" name="submit" alt="Pague com PagBank - é rápido, grátis e seguro!">
</form>

<script type="text/javascript">
//document.forms[0].submit();
</script>