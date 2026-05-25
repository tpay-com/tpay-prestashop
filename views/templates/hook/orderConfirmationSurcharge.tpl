{**
* @author Krajowy Integrator Płatności S.A.
* @copyright Krajowy Integrator Płatności S.A.
* @license MIT
* 
* Copyright (c) 2026 Krajowy Integrator Płatności S.A.
* 
* Permission is hereby granted, free of charge, to any person obtaining a copy
* of this software and associated documentation files (the "Software"), to deal
* in the Software without restriction, including without limitation the rights
* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
* copies of the Software, and to permit persons to whom the Software is
* furnished to do so, subject to the following conditions:
* 
* The above copyright notice and this permission notice shall be included in all
* copies or substantial portions of the Software.
* 
* THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
* SOFTWARE.
*}
<script type="text/javascript">
  document.addEventListener("DOMContentLoaded", function (event) {
    $(document).ready(function () {
        {literal}const surchargeTitle = "{/literal}{$surcharge_title|escape:'javascript'}";
        {literal}const surchargeCost = "{/literal}{$surcharge_cost|escape:'javascript'}"

      createBackOfficeSurchargeCost(surchargeTitle, surchargeCost);
      createFrontOfficeSurchargeCost(surchargeTitle, surchargeCost);

        {if $tpay_is_old_presta}
          legacyCreateFrontOfficeSurchargeCost(surchargeTitle, surchargeCost);
        {else}
          createFrontOfficeSurchargeCost(surchargeTitle, surchargeCost);
        {/if}

      function createBackOfficeSurchargeCost(surchargeTitle, surchargeCost) {
        const orderTotal = $(".order-confirmation-table").find(".total-value");
        if (orderTotal.length !== 0) {
          orderTotal.before('<tr>' +
            '<td>' + surchargeTitle + '</td>' +
            '<td>' + surchargeCost + '</td>' +
            '</tr>'
          );
        }
      }

      function createFrontOfficeSurchargeCost(surchargeTitle, surchargeCost) {
        const orderTotal = $("#order-products").find(".line-shipping");
        if (orderTotal.length !== 0) {
          orderTotal.before('<tr class="text-xs-right line-surcharge">' +
            '<td colspan="3">' + surchargeTitle + '</td>' +
            '<td>' + surchargeCost + '</td>' +
            '</tr>'
          );
        }
      }

      function legacyCreateFrontOfficeSurchargeCost(surchargeTitle, surchargeCost) {
        const orderTotal = $("#order-items table .font-weight-bold");
        if (orderTotal.length !== 0) {
          orderTotal.before('<tr class="line-surcharge">' +
            '<td>' + surchargeTitle + '</td>' +
            '<td>' + surchargeCost + '</td>' +
            '</tr>'
          );
        }
      }

    });
  });
</script>
