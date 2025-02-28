<?php

namespace Tests\Antlers\Runtime;

use Statamic\Facades\Nav;
use Statamic\View\Antlers\Language\Utilities\StringUtilities;
use Tests\Antlers\ParserTestCase;
use Tests\PreventSavingStacheItemsToDisk;

class RecursiveNodesTest extends ParserTestCase
{
    use PreventSavingStacheItemsToDisk;

    public function test_recursive_nodes_on_structures()
    {
        $tree = [
            ['id' => 'home', 'title' => 'Home', 'url' => '/'],
            [
                'id' => 'about', 'title' => 'About', 'url' => 'about',
                'children' => [
                    ['id' => 'team', 'title' => 'Team', 'url' => 'team'],
                    ['id' => 'leadership', 'title' => 'Leadership', 'url' => 'leadership'],
                ],
            ],
            [
                'id' => 'projects', 'title' => 'Projects', 'url' => 'projects',
                'children' => [
                    ['id' => 'project-1', 'title' => 'Project-1', 'url' => 'project-1'],
                    [
                        'id' => 'project-2', 'title' => 'Project-2', 'url' => 'project-2',
                        'children' => [
                            ['id' => 'project-2-nested', 'title' => 'Project 2 Nested', 'url' => 'project-2-nested'],
                        ],
                    ],
                ],
            ],
            ['id' => 'contact', 'title' => 'Contact', 'url' => 'contact'],
        ];

        $nav = Nav::make('main');
        $nav->makeTree('en', $tree)->save();
        $nav->save();

        $template = <<<'EOT'
{{ nav:main include_home="true" }}
<div>{{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}
EOT;

        $expected = <<<'EOT'
<div>1 Home</div>


<div>1 About</div>

<div>2 Team</div>


<div>2 Leadership</div>



<div>1 Projects</div>

<div>2 Project-1</div>


<div>2 Project-2</div>

<div>3 Project 2 Nested</div>




<div>1 Contact</div>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
{{ nav:main include_home="true" }}
<div>ONE: {{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}

{{ nav:main include_home="true" }}
<div>TWO: {{ depth }} {{ title }}</div>
{{ if children }}{{ *recursive children* }}{{ /if }}
{{ /nav:main }}
EOT;

        $expected = <<<'EOT'
<div>ONE: 1 Home</div>


<div>ONE: 1 About</div>

<div>ONE: 2 Team</div>


<div>ONE: 2 Leadership</div>



<div>ONE: 1 Projects</div>

<div>ONE: 2 Project-1</div>


<div>ONE: 2 Project-2</div>

<div>ONE: 3 Project 2 Nested</div>




<div>ONE: 1 Contact</div>




<div>TWO: 1 Home</div>


<div>TWO: 1 About</div>

<div>TWO: 2 Team</div>


<div>TWO: 2 Leadership</div>



<div>TWO: 1 Projects</div>

<div>TWO: 2 Project-1</div>


<div>TWO: 2 Project-2</div>

<div>TWO: 3 Project 2 Nested</div>




<div>TWO: 1 Contact</div>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
<ul>
{{ nav:main }}
{{ if depth === 1 }}
<li class="if-depth-one">
{{ title }} - {{ depth }}<br />
{{ if children }}
<ul class="if-depth-one-children">
{{ *recursive children* }}
</ul>
{{ /if }}

</li>
{{ elseif depth == 2 }}
<li class="else-depth-two">
{{ title }} - {{ depth }}<br />
{{ if children }}
<ul class="if-else-depth-two-children">
{{ *recursive children* }}
</ul>
{{ /if }}
</li>

{{ else }}
<li class="else-other-depths">
{{ title }} -- {{ depth }}
</li>
{{ /if }}
{{ /nav:main }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>


<li class="if-depth-one">
Home - 1<br />


</li>



<li class="if-depth-one">
About - 1<br />

<ul class="if-depth-one-children">


<li class="else-depth-two">
Team - 2<br />

</li>




<li class="else-depth-two">
Leadership - 2<br />

</li>



</ul>


</li>



<li class="if-depth-one">
Projects - 1<br />

<ul class="if-depth-one-children">


<li class="else-depth-two">
Project-1 - 2<br />

</li>




<li class="else-depth-two">
Project-2 - 2<br />

<ul class="if-else-depth-two-children">


<li class="else-other-depths">
Project 2 Nested -- 3
</li>


</ul>

</li>



</ul>


</li>



<li class="if-depth-one">
Contact - 1<br />


</li>


</ul>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));

        $template = <<<'EOT'
<ul class="parent-menu">
{{ nav:main }}
{{ if depth == 1 }}
<li {{ if children }} something{{ endif }}>
<a>
{{ title }}
</a>
{{ if children }}
<ul class="child-menu">
{{ *recursive children* }}
</ul>
{{ endif }}
</li>
{{ else }}
<li>
<a>
{{ title }}
</a>
</li>
{{ endif }}
{{ /nav:main }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul class="parent-menu">


<li >
<a>
Home
</a>

</li>



<li  something>
<a>
About
</a>

<ul class="child-menu">


<li>
<a>
Team
</a>
</li>



<li>
<a>
Leadership
</a>
</li>


</ul>

</li>



<li  something>
<a>
Projects
</a>

<ul class="child-menu">


<li>
<a>
Project-1
</a>
</li>



<li>
<a>
Project-2
</a>
</li>


</ul>

</li>



<li >
<a>
Contact
</a>

</li>


</ul>
EOT;

        $this->assertSame($expected, trim($this->renderString($template, [], true)));
    }

    public function test_recursive_node_can_be_root()
    {
        $this->parseNodes(<<<'EOT'
    $template = <<<'EOT'
{{ records }}
    {{ title }}
    <br />
    {{ children }}
        {{ title }}
        <br />
        {{ *recursive children* }}
    {{ /children }}
{{ /records }}
EOT
);

        // The parseNodes will throw an exception if it fails to parse correctly.
        // We will just assert true is true to shut up the risky assertions warning.
        $this->assertTrue(true);
    }

    public function test_sub_recursive_nodes()
    {
        $data = [
            'records' => [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two',
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four',
                                'foo' => 'Baz',
                                'children' => [
                                    [
                                        'title' => 'Five',
                                        'colors' => [
                                            [
                                                'name' => 'Blue',
                                                'colors' => [
                                                    [
                                                        'name' => 'Green',
                                                        'colors' => [
                                                            [
                                                                'name' => 'Yellow',
                                                            ],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $template = <<<'EOT'
<ul>
{{ records }}
<li>
{{ title }} - {{ depth }} - {{ children_depth }}

{{ if colors }}
Start Colors
{{ colors }}
Outer Title: {{ title }}
Color Name: {{ name }}
Active Depth: {{ depth }}
Outer Depth: {{ children_depth }}
Color Depth: {{ colors_depth }}

{{ if colors }}
REC-COLOR-START
{{ *subrecursive colors* }}
REC-COLOR-STOP
{{ /if }}
{{ /colors }}

End Colors
{{ /if }}

{{ if children }}
<ul>
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;

        $expected = <<<'EOT'
<ul>

<li>
One - 1 - 1




<ul>

<li>
Two - 2 - 2




</li>

<li>
Three - 2 - 2




<ul>

<li>
Four - 3 - 3




<ul>

<li>
Five - 4 - 4


Start Colors

Outer Title: Five
Color Name: Blue
Active Depth: 1
Outer Depth: 4
Color Depth: 1


REC-COLOR-START

Outer Title: Five
Color Name: Green
Active Depth: 2
Outer Depth: 4
Color Depth: 2


REC-COLOR-START

Outer Title: Five
Color Name: Yellow
Active Depth: 3
Outer Depth: 4
Color Depth: 3



REC-COLOR-STOP


REC-COLOR-STOP



End Colors



</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($this->renderString($template, $data)));
    }

    public function test_simple_depth_tree_class()
    {
        $data = [
            'records' => [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two',
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four',
                                'foo' => 'Baz',
                                'children' => [
                                    [
                                        'title' => 'Five',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
<ul>
{{ records }}
<li>
<span>{{ title }} -- {{ depth }}</span>

{{ if children }}
<ul class="depth-{{ depth ?? 'root' }}">
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;
        $expected = <<<'EOT'
<ul>

<li>
<span>One -- 1</span>


<ul class="depth-1">

<li>
<span>Two -- 2</span>


</li>

<li>
<span>Three -- 2</span>


<ul class="depth-2">

<li>
<span>Four -- 3</span>


<ul class="depth-3">

<li>
<span>Five -- 4</span>


</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings(
            $this->renderString($template, $data)
        ));
    }

    public function test_recursive_node_that_is_not_from_a_tag()
    {
        $data = [
            'records' => [
                'title' => 'One',
                'children' => [
                    [
                        'title' => 'Two',
                    ],
                    [
                        'title' => 'Three',
                        'children' => [
                            [
                                'title' => 'Four',
                                'foo' => 'Baz',
                                'children' => [
                                    ['title' => 'Five'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $template = <<<'EOT'
<ul>
{{ records }}
<li>{{ title }} - {{ depth }}
{{ if children }}
<ul>
{{ *recursive children* }}
</ul>
{{ /if }}
</li>
{{ /records }}
</ul>
EOT;
        $results = $this->renderString($template, $data);
        $expected = <<<'EOT'
<ul>

<li>One - 1

<ul>

<li>Two - 2

</li>

<li>Three - 2

<ul>

<li>Four - 3

<ul>

<li>Five - 4

</li>

</ul>

</li>

</ul>

</li>

</ul>

</li>

</ul>
EOT;

        $this->assertSame(StringUtilities::normalizeLineEndings($expected), StringUtilities::normalizeLineEndings($results));
    }
}
