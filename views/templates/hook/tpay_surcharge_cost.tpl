{*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    tpay.com
*  @copyright 2010-2020 tpay.com
*  @license   LICENSE.txt
*}
<section>

    {if $surcharge }
      <p>
          {l s=' Fee charged for this payment method: ' mod='tpay'}
          {l s=$surcharge}
      </p>
    {/if}

</section>
