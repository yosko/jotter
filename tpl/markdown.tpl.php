<?php include DIR_TPL.'header.tpl.php'; ?>

<div id="markdown">
<br>
<h1>Markdown Syntax</h1>
<h2>Block elements</h2>
<h3>Titles</h3>
<pre>
# Title 1
## Title 2
### Title 3
#### Title 4
##### Title 5
###### Title 6
</pre>
<h3>Quotes</h3>
<pre>
> Quote (can be multiline and/or nested)
</pre>
<h3>List</h3>
<pre>
- Unordered
- list

1. Ordered
1. list (numbers used don't matter)
</pre>
<h3>Code</h3>
<pre>
    Some code
    (indented with
    4 spaces)
</pre>
<h3>Horizontal line</h3>
<pre>
---
</pre>
<h2>Inline elements</h2>
<h3>Link</h3>
<pre>
[Text](http://www.example.com/)
[Text](http://www.example.com/ "Title")
[Local link](sub/directory/)
Show the URL as the text: &lt;http://www.example.com/&gt;
And for an email: &lt;address@example.com&gt;
</pre>
<h3>Emphasis</h3>
<pre>
*italic*
**bold**
***bold and italic***
</pre>
<h3>Code</h3>
<pre>
A `short code` within a paragraph
</pre>
<h3>Images</h3>
<pre>
![Replacement text](path/to/image.jpg "Image title")
[![Image within a link](path/to/image.jpg)](http://www.example.com)
</pre>
<h3>Special characters</h3>
<p>To show (without interpreting them) the characters used in Markdown syntax, you must escape them with <code>\</code> just like this:</p>
<pre>
\*
</pre>
<p>Complete list of characters that must be escaped:</p>
<pre>
\   backslash
`   backtick
*   asterisk
_   underscore
{}  curly braces
[]  square brackets
()  parentheses
#   hash mark
+   plus sign
-   minus sign (hyphen)
.   dot
!   exclamation mark
:   colon (in Markdown Extra)
|   pipe (in Markdown Extra)
</pre>
<h2>Markdown Extra</h2>
<h3>Anchors</h3>
<p>Add an id to the target element</p>
<pre>
## My title {#myId }
</pre>
<p>Then make a link to this id</p>
<pre>
[link to my title](#myId)
</pre>
<h3>Table</h3>
<pre>
Column  | Left-aligned column | Right-aligned column | Centered column |
------- |:--------------------| --------------------:|:---------------:|
Content | Content             | Content              | Content         |
Content | Content             | Content              | Content         |
</pre>
<h3>Definition list</h3>
<pre>
Element
:   Here is a multiline definition
    for this element
</pre>
<h3>Footnote</h3>
<p>Footnotes are automatically showed at the bottom of the text, right after an horizontal line.</p>
<pre>
Text with a reference[^1].

[^1]: Corresponding footnote.
</pre>
</div>

<?php include DIR_TPL.'footer.tpl.php'; ?>