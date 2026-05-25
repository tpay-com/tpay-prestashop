<?php
/**
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
*/

use PhpCsFixer\ConfigInterface;
use PrestaShop\CodingStandards\CsFixer\Config;

class TemporaryCsConfig extends Config
{
    public function __construct()
    {
        parent::__construct('');
        $this->setUsingCache(false);
    }
    public function getRules(): array {
        $parent = parent::getRules();
        $parent['blank_line_after_opening_tag'] = false;
        $parent['linebreak_after_opening_tag'] = false;
        $parent['no_blank_lines_after_phpdoc'] = true;
        $parent['align_multiline_comment'] = true;
        $parent['no_unused_imports'] = true;
        $parent['trailing_comma_in_multiline'] = ['elements' => ['arrays']];
        $parent['no_extra_blank_lines'] = [
            'tokens' => ['extra', 'use'],
        ];
        $parent['global_namespace_import'] = ['import_classes' => true];


        return $parent;
    }

}
