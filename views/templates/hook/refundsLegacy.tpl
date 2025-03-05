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
<div id="formAddTpayRefundPanel" class="row">
  <div class="col-lg-12">
    <div class="panel">
      <div class="panel-heading">
            <i class="icon-money"></i>
            {l s='Tpay refunds' mod='tpay'}

      </div>

      <div class="card-body">

          {if isset($tpay_refund_status)}
              {$tpay_refund_status}
          {/if}

        <form id="formAddTpayRefund" method="post" action="" class="container-command-top-spacing">
          <input type="hidden" name="tpay-refund" value="1">

          <table class="table">
            <thead>
            <tr>
              <th><span class="title_box ">{l s='Refund date' mod='tpay'}</span></th>
              <th><span class="title_box ">{l s='Refunded transaction title' mod='tpay'}</span></th>
              <th><span class="title_box ">{l s='Refund amount ' mod='tpay'}</span></th>
              <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach $tpayRefunds as $tpayRefund}
              <tr>
                <td>{$tpayRefund.tpay_refund_date}</td>
                <td>{$tpayRefund.tpay_transaction_id}</td>
                <td>{$tpayRefund.tpay_refund_amount}</td>
              </tr>
            {/foreach}
            <tr>
              <td></td>
              <td></td>
              <td class="actions">
                <input type="text" name="tpay_refund_amount" id="tpay_refund_amount"
                       class="form-control fixed-width-sm" placeholder="1.00">
              </td>
              <td>
                <input type="submit" class="tpay-refund btn btn-primary"
                       value="{l s='Process refund' mod='tpay'}">
              </td>
            </tr>
            </tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>

