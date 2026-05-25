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
{extends file="page.tpl"}
{block name='page_content'}
	<div style="padding: 40px 10px">
		<h2>
            {l s='An error occurred while ordering.' d='Modules.Tpay.Shop'}
		</h2>
        {if isset($error) && !empty($error)}
			<h2 style="margin-top: 20px;">
                {$error|escape:'html'}
			</h2>
        {/if}

		<div style="margin-top: 20px;">
			<a href="{$urls.pages.history|escape:'html'}" class="btn btn-primary">
	            {l s='Order history' d='Modules.Tpay.Shop'}
			</a>
		</div>
	</div>
{/block}
