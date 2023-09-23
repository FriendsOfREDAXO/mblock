<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class Mblock_test extends TestCase
{
    public function testBlockShow()
    {
        // get form without whitespace
        $actual = preg_replace('/\s+/', '', MBlock::show(1, ''));
        $expected = '<divclass="mblock_wrapper"data-mblock-plain-sortitem="&lt;divclass=&quot;sortitem&quot;data-mblock_index=&quot;0&quot;&gt;&lt;spanclass=&quot;sorthandle&quot;&gt;&lt;/span&gt;&lt;spanclass=&quot;removeadded&quot;&gt;&lt;divclass=&quot;btn-groupbtn-group-xs&quot;&gt;&lt;buttontype=&quot;button&quot;class=&quot;btnbtn-defaultaddme&quot;title=&quot;duplicate&quot;&gt;&lt;iclass=&quot;rex-iconrex-icon-add-module&quot;&gt;&lt;/i&gt;&lt;/button&gt;&lt;buttontype=&quot;button&quot;class=&quot;btnbtn-deleteremoveme&quot;title=&quot;delete&quot;&gt;&lt;iclass=&quot;rex-iconrex-icon-delete&quot;&gt;&lt;/i&gt;&lt;/button&gt;&lt;/div&gt;&lt;divclass=&quot;btn-groupbtn-group-xs&quot;&gt;&lt;buttontype=&quot;button&quot;class=&quot;btnbtn-movemoveup&quot;title=&quot;moveup&quot;&gt;&lt;iclass=&quot;rex-iconrex-icon-up&quot;&gt;&lt;/i&gt;&lt;/button&gt;&lt;buttontype=&quot;button&quot;class=&quot;btnbtn-movemovedown&quot;title=&quot;movedown&quot;&gt;&lt;iclass=&quot;rex-iconrex-icon-down&quot;&gt;&lt;/i&gt;&lt;/button&gt;&lt;/div&gt;&lt;/span&gt;&lt;div&gt;&lt;/div&gt;&lt;/div&gt;"data-mblock-single-add="&lt;divclass=&quot;mblock-single-add&quot;&gt;&lt;spanclass=&quot;singleadded&quot;&gt;&lt;buttontype=&quot;button&quot;class=&quot;btnbtn-defaultaddme&quot;title=&quot;duplicate&quot;&gt;&lt;iclass=&quot;rex-iconrex-icon-add-module&quot;&gt;&lt;/i&gt;&lt;/button&gt;&lt;/span&gt;&lt;/div&gt;"data-input_delete="1"data-smooth_scroll="1"data-mblock_count="1"><divclass="sortitem"data-mblock_index="1"><spanclass="sorthandle"></span><spanclass="removeadded"><divclass="btn-groupbtn-group-xs"><buttontype="button"class="btnbtn-defaultaddme"title="duplicate"><iclass="rex-iconrex-icon-add-module"></i></button><buttontype="button"class="btnbtn-deleteremoveme"title="delete"><iclass="rex-iconrex-icon-delete"></i></button></div><divclass="btn-groupbtn-group-xs"><buttontype="button"class="btnbtn-movemoveup"title="moveup"><iclass="rex-iconrex-icon-up"></i></button><buttontype="button"class="btnbtn-movemovedown"title="movedown"><iclass="rex-iconrex-icon-down"></i></button></div></span><div></div></div></div>';

        static::assertEquals($expected, $actual, 'Mblock::show() should return a propper html form.');
    }
}
