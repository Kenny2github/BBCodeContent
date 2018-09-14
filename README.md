# BBCodeContent

This extension adds a new content model, "BBCode", that you can switch pages to.

## Installation

Run the following commands (assuming you're at your wiki's root, e.g. /w):
```bash
cd extensions
git clone https://github.com/Kenny2github/BBCodeContent.git
cd ..
echo "wfLoadExtension('BBCodeContent');" >> LocalSettings.php
```

## Configuration
The only configuration variable is `$wgBBCCTags`, which controls how each different BBCode tag is interpreted.

Use it as follows for a simple tag:
```php
$wgBBCCTags['red'] = [
	'<span style="color: red">', // opening tag
	'</span>', // closing tag
	false // if true, this is closed by a newline rather than a [/red]
];
```
Or as follows for a tag with a parameter:
```php
// this is already in there by default
$wgBBCCTags['url'] = [
	'<a rel="nofollow" href="{PARAM}">', // {PARAM} is replaced by FOO in [url=FOO]
	'</a>',
	false
];
```
Or as follows for a complex tag that needs special things:
```php
$wgBBCCTags['randomcolor'] = function ($tag, $content, $param) {
	// $param is FOO in [randomcolor=FOO]
	// it is not used here
	// $content is BAR in [randomcolor]BAR[/randomcolor]
	// it is directly echoed (everything has already been put through htmlspecialchars)
	$out = '<span style="color: ';
	$out .= sprintf('#%06X', mt_rand(0, 0xFFFFFF));
	$out .= '">';
	$out .= $content;
	$out .= '</span>';
	return $out;
};
```
