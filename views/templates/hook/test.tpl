{*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<div class="klarnaKcoChoosePayment">
  <div id="klarnaPaymentsKco" class="KlarnaCheckoutPaymentOption current">
    <h3>{l s='Klarna checkout' mod='klarnapayments'}</h3>
      <img src="https://cdn.klarna.com/1.0/shared/image/generic/badge/sv_se/checkout/short-blue.png?width=276&amp;eid={$klarna_eid|escape:'htmlall':'UTF-8'}" alt="Klarna Checkout">
  </div>
  <div id="klarnaCheckoutNormalPayment" class="KlarnaCheckoutPaymentOption">
    <h3>{l s='Other payment options' mod='klarnapayments'}</h3><ul></ul></div>
  </div>

<div class="klarnaCheckoutCarrier klarnaCheckout" style="display: block;">
  <h2>{l s='Delivery options' mod='klarnapayments'}</h2>
  {if isset($isVirtualCart) && $isVirtualCart}
      <p class="alert alert-warning">{l s='No carrier is needed for this order.' mod='klarnapayments'}</p>
    {else}
      <div class="delivery_options_address">
        {if isset($delivery_option_list)}
          {foreach $delivery_option_list as $id_address => $option_list}
            <p class="carrier_title">
              {if isset($address_collection[$id_address])}
                {l s='Choose a shipping option for this address:' mod='klarnapayments'} {$address_collection[$id_address]->alias|escape:'htmlall':'UTF-8'}
              {else}
                {l s='Choose a shipping option' mod='klarnapayments'}
              {/if}
            </p>
            <form action="{$link->getModuleLink('klarnapayments', 'carrier')|escape:'htmlall':'UTF-8'}" method="POST">
            <div class="delivery_options">
              {foreach $option_list as $key => $option}
                <div class="delivery_option {if ($option@index % 2)}alternate_{/if}item">
                  <div>
                    <table class="resume table table-bordered{if !$option.unique_carrier} hide{/if}">
                      <tr>
                        <td class="delivery_option_radio" style="width:162px;">
                          <button id="delivery_option_{$id_address|intval|escape:'htmlall':'UTF-8'}_{$option@index|escape:'htmlall':'UTF-8'}" class="btn btn-primary btn-md" type="submit" name="delivery_option[{$id_address|intval}]" data-key="{$key|escape:'htmlall':'UTF-8'}" data-id_address="{$id_address|intval}" value="{$key|escape:'htmlall':'UTF-8'}"{if isset($delivery_option[$id_address]) && $delivery_option[$id_address] == $key} checked="checked"{/if} >{l s='Select' mod='klarnapayments'}</button>
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
                              <br />{l s='Delivery time:' mod='klarnapayments'}&nbsp;{$carrier.instance->delay[$cookie->id_lang]|escape:'htmlall':'UTF-8'}
                            {/if}
                          {/if}
                          {if count($option_list) > 1}
                          <br />
                            {if $option.is_best_grade}
                              {if $option.is_best_price}
                                <span class="best_grade best_grade_price best_grade_speed">{l s='The best price and speed' mod='klarnapayments'}</span>
                              {else}
                                <span class="best_grade best_grade_speed">{l s='The fastest' mod='klarnapayments'}</span>
                              {/if}
                            {elseif $option.is_best_price}
                              <span class="best_grade best_grade_price">{l s='The best price' mod='klarnapayments'}</span>
                            {/if}
                          {/if}
                        </td>
                        <td class="delivery_option_price">
                          <div class="delivery_option_price">
                            {if $option.total_price_with_tax && !$option.is_free && (!isset($free_shipping) || (isset($free_shipping) && !$free_shipping))}
                              {if $use_taxes == 1}
                                {if $priceDisplay == 1}
                                  {convertPrice price=$option.total_price_without_tax}{if $display_tax_label} {l s='(tax excl.)' mod='klarnapayments'}{/if}
                                {else}
                                  {convertPrice price=$option.total_price_with_tax}{if $display_tax_label} {l s='(tax incl.)' mod='klarnapayments'}{/if}
                                {/if}
                              {else}
                                {convertPrice price=$option.total_price_without_tax}
                              {/if}
                            {else}
                              {l s='Free' mod='klarnapayments'}
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
                                  ({l s='For this product:' mod='klarnapayments'}
                                {else}
                                  ({l s='For these products:' mod='klarnapayments'}
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
                                    {convertPrice price=$option.total_price_without_tax}{if $display_tax_label} {l s='(tax excl.)' mod='klarnapayments'}{/if}
                                  {else}
                                    {convertPrice price=$option.total_price_with_tax}{if $display_tax_label} {l s='(tax incl.)' mod='klarnapayments'}{/if}
                                  {/if}
                                {else}
                                  {convertPrice price=$option.total_price_without_tax}
                                {/if}
                              {else}
                                {l s='Free' mod='klarnapayments'}
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
                                    ({l s='For this product:' mod='klarnapayments'}
                                  {else}
                                    ({l s='For these products:' mod='klarnapayments'}
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
                    {l s='No carriers available.' mod='klarnapayments'}
                  {else}
                    {assign var='flag_error_message' value=false}
                    {foreach $errors as $error}
                      {if $error == Carrier::SHIPPING_WEIGHT_EXCEPTION}
                        {$flag_error_message = true}
                        {l s='The product selection cannot be delivered by the available carrier(s): it is too heavy. Please amend your cart to lower its weight.' mod='klarnapayments'}
                      {elseif $error == Carrier::SHIPPING_PRICE_EXCEPTION}
                        {$flag_error_message = true}
                        {l s='The product selection cannot be delivered by the available carrier(s). Please amend your cart.' mod='klarnapayments'}
                      {elseif $error == Carrier::SHIPPING_SIZE_EXCEPTION}
                        {$flag_error_message = true}
                        {l s='The product selection cannot be delivered by the available carrier(s): its size does not fit. Please amend your cart to reduce its size.' mod='klarnapayments'}
                      {/if}
                    {/foreach}
                    {if !$flag_error_message}
                      {l s='No carriers available for the address "%s".' sprintf=$address->alias mod='klarnapayments'}
                    {/if}
                  {/if}
                  {if !$address@last}
                    <br />
                  {/if}
                {foreachelse}
                  {l s='No carriers available.' mod='klarnapayments'}
                {/foreach}
              </p>
            {/foreach}
          {/if}
        {/if}
        </div> <!-- end delivery_options_address -->
    
</div>      
<div class="klarnapaymentsKCO">
  <div class="heading">
    <h2>{l s='Checkout express' mod='klarnapayments'}</h2>
    <p>{l s='Finish your purchase fast, safe and frictionless' mod='klarnapayments'}</p>
  </div>
  <div class="snippet">
    <div id="klarna-checkout-container" style="overflow-x: hidden;">
      <noscript> Please &lt;a href="http://enable-javascript.com"&gt;enable JavaScript&lt;/a&gt;.</noscript>
      {$snippet}
    </div>
  </div>
</div>