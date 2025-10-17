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
{extends file="page.tpl"}
{block name='page_content'}
	<div style="padding: 40px 10px">
		<h2>
            {l s='An error occurred while ordering.' d='Modules.Tpay.Shop'}
		</h2>
        {if isset($error) && !empty($error)}
			<h2 style="margin-top: 20px;">
                {$error}
			</h2>
        {/if}

		<div style="margin-top: 20px;">
			<a href="{$urls.pages.history}" class="btn btn-primary">
	            {l s='Order history' d='Modules.Tpay.Shop'}
			</a>
		</div>
	</div>
{/block}
