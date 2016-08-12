<div class="ecsterSelectPayment">
  <div class="ecsterPaymentOption current">
    <h3>{l s='Ecster checkout' mod='ecster'}</h3>
      <img src="">
  </div>
  <div class="ecsterNormalPaymentOption">
    <h3>{l s='Other payment options' mod='ecster'}</h3><ul></ul></div>
</div>

<div class="ecsterCheckoutCarrier" style="display: block;">
  <h2>{l s='Delivery options' mod='ecster'}</h2>
  {if isset($isVirtualCart) && $isVirtualCart}
      <p class="alert alert-warning">{l s='No carrier is needed for this order.' mod='ecster'}</p>
    {else}
      <div class="delivery_options_address">
        {if isset($delivery_option_list)}
          {foreach $delivery_option_list as $id_address => $option_list}
            <p class="carrier_title">
              {if isset($address_collection[$id_address])}
                {l s='Choose a shipping option for this address:' mod='ecster'} {$address_collection[$id_address]->alias|escape:'htmlall':'UTF-8'}
              {else}
                {l s='Choose a shipping option' mod='ecster'}
              {/if}
            </p>
            <form action="{$link->getModuleLink('ecster', 'carrier')|escape:'htmlall':'UTF-8'}" method="POST">
            <div class="delivery_options">
              {foreach $option_list as $key => $option}
                <div class="delivery_option {if ($option@index % 2)}alternate_{/if}item">
                  <div>
                    <table class="resume table table-bordered{if !$option.unique_carrier} hide{/if}">
                      <tr>
                        <td class="delivery_option_radio" style="width:162px;">
                          <button id="delivery_option_{$id_address|intval|escape:'htmlall':'UTF-8'}_{$option@index|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-md" type="submit" name="delivery_option[{$id_address|intval}]" data-key="{$key|escape:'htmlall':'UTF-8'}" data-id_address="{$id_address|intval}" value="{$key|escape:'htmlall':'UTF-8'}"{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} checked="checked"{/if} >{l s='Select' mod='ecster'}</button>
                        </td>
                        <td class="delivery_option_logo">
                          {foreach $option.carrier_list as $carrier}
                            {if $carrier.logo}
                              <img class="order_carrier_logo" src="{$carrier.logo|escape:'htmlall':'UTF-8'}" alt="{$carrier.instance->name|escape:'htmlall':'UTF-8'}"/>
                            {elseif !$option.unique_carrier}
                              {$carrier.instance->name|escape:'htmlall':'UTF-8'}
                              {if !$carrier@last} - {/if}
                            {/if}
                          {/foreach}
                        </td>
                        <td>
                          {if $option.unique_carrier}
                            {foreach $option.carrier_list as $carrier}
                              <strong>{$carrier.instance->name|escape:'htmlall':'UTF-8'}</strong>
                            {/foreach}
                            {if isset($carrier.instance->delay[$cookie->id_lang])}
                              <br />{l s='Delivery time:' mod='ecster'}&nbsp;{$carrier.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}
                            {/if}
                          {/if}
                          {if count($option_list) > 1}
                          <br />
                            {if $option.is_best_grade}
                              {if $option.is_best_price}
                                <span class="best_grade best_grade_price best_grade_speed">{l s='The best price and speed' mod='ecster'}</span>
                              {else}
                                <span class="best_grade best_grade_speed">{l s='The fastest' mod='ecster'}</span>
                              {/if}
                            {elseif $option.is_best_price}
                              <span class="best_grade best_grade_price">{l s='The best price' mod='ecster'}</span>
                            {/if}
                          {/if}
                        </td>
                        <td class="delivery_option_price">
                          <div class="delivery_option_price">
                            {if $option.total_price_with_tax && !$option.is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
                              {if $use_taxes == 1}
                                {if $priceDisplay == 1}
                                  {convertPrice price=$option.total_price_without_tax}{if $display_tax_label} {l s='(tax excl.)' mod='ecster'}{/if}
                                {else}
                                  {convertPrice price=$option.total_price_with_tax}{if $display_tax_label} {l s='(tax incl.)' mod='ecster'}{/if}
                                {/if}
                              {else}
                                {convertPrice price=$option.total_price_without_tax}
                              {/if}
                            {else}
                              {l s='Free' mod='ecster'}
                            {/if}
                          </div>
                        </td>
                      </tr>
                    </table>
                    {if !$option.unique_carrier}
                      <table class="delivery_option_carrier{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} selected{/if} resume table table-bordered{if $option.unique_carrier} hide{/if}">
                        <tr>
                          {if !$option.unique_carrier}
                            <td rowspan="{$option.carrier_list|@count|escape:'htmlall':'UTF-8'}" class="delivery_option_radio first_item">
                              <input id="delivery_option_{$id_address|intval|escape:'htmlall':'UTF-8'}_{$option@index|escape:'htmlall':'UTF-8'}" class="delivery_option_radio" type="radio" name="delivery_option[{$id_address|intval}]" data-key="{$key|escape:'htmlall':'UTF-8'}" data-id_address="{$id_address|intval}" value="{$key|escape:'htmlall':'UTF-8'}"{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} checked="checked"{/if} />
                            </td>
                          {/if}
                          {assign var="first" value=current($option.carrier_list)}
                          <td class="delivery_option_logo{if $first.product_list[0].carrier_list[0] eq 0} hide{/if}">
                            {if $first.logo}
                              <img class="order_carrier_logo" src="{$first.logo|escape:'htmlall':'UTF-8'}" alt="{$first.instance->name|escape:'htmlall':'UTF-8'}"/>
                            {elseif !$option.unique_carrier}
                              {$first.instance->name|escape:'htmlall':'UTF-8'}
                            {/if}
                          </td>
                          <td class="{if $option.unique_carrier}first_item{/if}{if $first.product_list[0].carrier_list[0] eq 0} hide{/if}">
                            <input type="hidden" value="{$first.instance->id|intval}" name="id_carrier" />
                            {if isset($first.instance->delay[$cookie->id_lang])}
                              <i class="icon-info-sign"></i>
                              {strip}
                                {$first.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}
                                &nbsp;
                                {if count($first.product_list) <= 1}
                                  ({l s='For this product:' mod='ecster'}
                                {else}
                                  ({l s='For these products:' mod='ecster'}
                                {/if}
                              {/strip}
                              {foreach $first.product_list as $product}
                                {if $product@index == 4}
                                  <acronym title="
                                {/if}
                                {strip}
                                  {if $product@index >= 4}
                                    {$product.name|escape:'htmlall':'UTF-8'}
                                    {if isset($product.attributes) && $product.attributes}
                                      {$product.attributes|escape:'htmlall':'UTF-8'}
                                    {/if}
                                    {if !$product@last}
                                      ,&nbsp;
                                    {else}
                                      ">&hellip;</acronym>)
                                    {/if}
                                  {else}
                                    {$product.name|escape:'htmlall':'UTF-8'}
                                    {if isset($product.attributes) && $product.attributes}
                                      {$product.attributes|escape:'htmlall':'UTF-8'}
                                    {/if}
                                    {if !$product@last}
                                      ,&nbsp;
                                    {else}
                                      )
                                    {/if}
                                  {/if}
                                {/strip}
                              {/foreach}
                            {/if}
                          </td>
                          <td rowspan="{$option.carrier_list|@count|escape:'htmlall':'UTF-8'}" class="delivery_option_price">
                            <div class="delivery_option_price">
                              {if $option.total_price_with_tax && !$option.is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
                                {if $use_taxes == 1}
                                  {if $priceDisplay == 1}
                                    {convertPrice price=$option.total_price_without_tax}{if $display_tax_label} {l s='(tax excl.)' mod='ecster'}{/if}
                                  {else}
                                    {convertPrice price=$option.total_price_with_tax}{if $display_tax_label} {l s='(tax incl.)' mod='ecster'}{/if}
                                  {/if}
                                {else}
                                  {convertPrice price=$option.total_price_without_tax}
                                {/if}
                              {else}
                                {l s='Free' mod='ecster'}
                              {/if}
                            </div>
                          </td>
                        </tr>
                        {foreach $option.carrier_list as $carrier}
                          {if $carrier@iteration != 1}
                          <tr>
                            <td class="delivery_option_logo{if $carrier.product_list[0].carrier_list[0] eq 0} hide{/if}">
                              {if $carrier.logo}
                                <img class="order_carrier_logo" src="{$carrier.logo|escape:'htmlall':'UTF-8'}" alt="{$carrier.instance->name|escape:'htmlall':'UTF-8'}"/>
                              {elseif !$option.unique_carrier}
                                {$carrier.instance->name|escape:'htmlall':'UTF-8'}
                              {/if}
                            </td>
                            <td class="{if $option.unique_carrier} first_item{/if}{if $carrier.product_list[0].carrier_list[0] eq 0} hide{/if}">
                              <input type="hidden" value="{$first.instance->id|intval}" name="id_carrier" />
                              {if isset($carrier.instance->delay[$cookie->id_lang])}
                                <i class="icon-info-sign"></i>
                                {strip}
                                  {$carrier.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}
                                  &nbsp;
                                  {if count($first.product_list) <= 1}
                                    ({l s='For this product:' mod='ecster'}
                                  {else}
                                    ({l s='For these products:' mod='ecster'}
                                  {/if}
                                {/strip}
                                {foreach $carrier.product_list as $product}
                                  {if $product@index == 4}
                                    <acronym title="
                                  {/if}
                                  {strip}
                                    {if $product@index >= 4}
                                      {$product.name|escape:'htmlall':'UTF-8'}
                                      {if isset($product.attributes) && $product.attributes}
                                        {$product.attributes|escape:'htmlall':'UTF-8'}
                                      {/if}
                                      {if !$product@last}
                                        ,&nbsp;
                                      {else}
                                        ">&hellip;</acronym>)
                                      {/if}
                                    {else}
                                      {$product.name|escape:'htmlall':'UTF-8'}
                                      {if isset($product.attributes) && $product.attributes}
                                        {$product.attributes|escape:'htmlall':'UTF-8'}
                                      {/if}
                                      {if !$product@last}
                                        ,&nbsp;
                                      {else}
                                        )
                                      {/if}
                                    {/if}
                                  {/strip}
                                {/foreach}
                              {/if}
                            </td>
                          </tr>
                          {/if}
                        {/foreach}
                      </table>
                    {/if}
                  </div>
                </div> <!-- end delivery_option -->
              </form>
              {/foreach}
            </div> <!-- end delivery_options -->
            {foreachelse}
              {assign var='errors' value=' '|explode:''}
              <p class="alert alert-warning" id="noCarrierWarning">
                {foreach $cart->getDeliveryAddressesWithoutCarriers(true, $errors) as $address}
                  {if empty($address->alias)}
                    {l s='No carriers available.' mod='ecster'}
                  {else}
                    {assign var='flag_error_message' value=false}
                    {foreach $errors as $error}
                      {if $error == Carrier::SHIPPING_WEIGHT_EXCEPTION}
                        {$flag_error_message = true}
                        {l s='The product selection cannot be delivered by the available carrier(s): it is too heavy. Please amend your cart to lower its weight.' mod='ecster'}
                      {elseif $error == Carrier::SHIPPING_PRICE_EXCEPTION}
                        {$flag_error_message = true}
                        {l s='The product selection cannot be delivered by the available carrier(s). Please amend your cart.' mod='ecster'}
                      {elseif $error == Carrier::SHIPPING_SIZE_EXCEPTION}
                        {$flag_error_message = true}
                        {l s='The product selection cannot be delivered by the available carrier(s): its size does not fit. Please amend your cart to reduce its size.' mod='ecster'}
                      {/if}
                    {/foreach}
                    {if !$flag_error_message}
                      {l s='No carriers available for the address "%s".' sprintf=$address->alias mod='ecster'}
                    {/if}
                  {/if}
                  {if !$address@last}
                    <br />
                  {/if}
                {foreachelse}
                  {l s='No carriers available.' mod='ecster'}
                {/foreach}
              </p>
            {/foreach}
          {/if}
        {/if}
    </div> <!-- end delivery_options_address -->
</div>
<h1>hello</h1>

<script>
	// <![CDATA[
	var cartKey = "{$cartKey|escape:'htmlall':'UTF-8'}";
	var termsPage ="{$termsPage|escape:'htmlall':'UTF-8'}";
	//]]>
	EcsterPay.start({
    	cartKey: cartKey, // from create cart REST call
    	shopTermsUrl: termsPage
	});
</script>