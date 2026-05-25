{**MIT License
@license MIT

Copyright (c) 2026 Krajowy Integrator Płatności S.A.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.

@author Krajowy Integrator Płatności S.A.
@copyright Krajowy Integrator Płatności S.A.*}
<div id="formAddTpayRefundPanel" class="row">
  <div class="col-lg-12">
    <div class="panel">
      <div class="panel-heading">
            <i class="icon-money"></i>
            {l s='Tpay refunds' d='Modules.Tpay.Admin'}

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
              <th><span class="title_box ">{l s='Refund date' d='Modules.Tpay.Admin'}</span></th>
              <th><span class="title_box ">{l s='Refunded transaction title' d='Modules.Tpay.Admin'}</span></th>
              <th><span class="title_box ">{l s='Refund amount ' d='Modules.Tpay.Admin'}</span></th>
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
                       value="{l s='Process refund' d='Modules.Tpay.Admin'}">
              </td>
            </tr>
            </tbody>
          </table>
        </form>
      </div>
    </div>
  </div>
</div>

