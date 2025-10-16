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
<div id="formAddTpayRefundPanel">
	<div class="card mt-2">
		<div class="card-header">
			<h3 class="card-header-title">
                {l s='Tpay refunds' d='Modules.Tpay.Admin'}
			</h3>
		</div>

		<div class="card-body">

            {if isset($tpay_refund_status)}
                {$tpay_refund_status}
            {/if}

			<form id="formAddTpayRefund" method="post" action="">
				<input type="hidden" name="tpay-refund" value="1">
				<div class="table-responsive">
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
				</div>
			</form>
		</div>

	</div>
</div>

