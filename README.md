# WordPress Pagination

This is a small collection of functions (two) that allow theme developers to show a pagination menu in pages which need it, without resorting to a plugin. If you ever heard of Kriesi pagination, you know what we’re talking about.

#### Refactoring Kriesi’s solution

I like Kriesi’s solution, still I needed something more flexible and customizable. For example, I wanted to control whether the menu would show the first/last links, or the next/prev links, based on the style and layout of the theme I was currently developing. Also, I wanted to decide whether to adopt the [BEM syntax](https://bem.info/) in the links.

Alright, before you start looking for a *TL;DR* chapter, let’s see how to use this thing.

## Usage

Functions are widely commented, so just look at the code if you need further info.

#### 1. Include the file

The usual story – download the main file and add `include "path/to/wp-pagination.php"` to your `functions.php` file.

#### 2. Call one of the functions

The script provides two functions: 

* **`get_pagination()`** retrieves the pagination menu as an array of pagination objects;
* **`the_pagination()`** echoes the pagination.

Both accept the same arguments (more on that in the next chapter), with the exception that, in `the_pagination()` function, you can pass a pagination array as the first argument.

So, if you ever want to modify the menu, you can:

1. get the pagination menu using `get_pagination()`;
2. add / remove / edit the items to your heart’s content;
3. display the menu using `the_pagination()` and passing it as the first argument, or using your custom html structure.

Ain’t it nice. Now, let’s talk about arguments.

### Arguments

As mentioned before, both functions accept the same arguments, which are all optional.

`the_pagination( $args );`

These are the defaults:

```php
$defaults = array(
  'range'        => 3,      // Range of pages to show before and after the current page.
  'total_pages'  => '',     // Total number of pages ( $wp_query->max_num_pages ).
  'class_prefix' => '',     // String to prepend to default classes ('first', 'next', 'current', ect. ).
  'count'        => true,   // Show page count ( current page and total number of pages ).
  'directional'  => true,   // Show previous and next page links.
  'edges'        => true    // Show first and last page links.
);
```

## Examples

#### Getting the pagination menu

```php
// Let’s pretend we are on page 5 of 12 in the News category
// ( e.g. http://mysite.com/category/news/page/5/ )

$pagination = get_pagination();

/* Returns:

Array
(
    [count] => stdClass Object
        (
            [current_page] => 5
            [total_pages] => 12
            [class] => count
        )

    [first] => stdClass Object
        (
            [text] => First
            [class] => first
            [link] => http://mysite.com/category/news/
        )

    [prev] => stdClass Object
        (
            [text] => Previous
            [class] => prev
            [link] => http://mysite.com/category/news/page/4/
        )

    ... ( second and third page )

    [4] => stdClass Object
        (
            [text] => 4
            [class] => 
            [link] => http://mysite.com/category/news/page/4/
        )

    [current] => stdClass Object
        (
            [text] => 5
            [class] => current is-active
            [link] => 
        )

    [6] => stdClass Object
        (
            [text] => 6
            [class] => 
            [link] => http://mysite.com/category/news/page/6/
        )

    ... ( seventh and eighth page )

    [next] => stdClass Object
        (
            [text] => Next
            [class] => next
            [link] => http://mysite.com/category/news/page/6/
        )

    [last] => stdClass Object
        (
            [text] => Last
            [class] => last
            [link] => http://mysite.com/category/news/page/12/
        )

)
*/
```

As you see, we get an array of page links. Each one of them is an object containing the text, the CSS class and the URL.

---

#### Displaying the pagination menu

```php
// In your template
if ( function_exists( 'the_pagination' ) ) the_pagination();
```

will echo on the page the following HTML:

```html
<nav class="pagination" role="navigation">
  <ul>
    <li class="count"><span>Page</span> <span class="current_page">5</span> <span>of</span> <span class="total_pages">12</span></li>
    <li class="first"><a href="http://mysite.com/category/news/">First</a></li>
    <li class="prev"><a href="http://mysite.com/category/news/page/4/">Previous</a></li>
    <li><a href="http://mysite.com/category/news/page/2/">2</a></li>
    <li><a href="http://mysite.com/category/news/page/3/">3</a></li>
    <li><a href="http://mysite.com/category/news/page/4/">4</a></li>
    <li class="current is-active"><span>5</span></li>
    <li><a href="http://mysite.com/category/news/page/6/">6</a></li>
    <li><a href="http://mysite.com/category/news/page/7/">7</a></li>
    <li><a href="http://mysite.com/category/news/page/8/">8</a></li>
    <li class="next"><a href="http://mysite.com/category/news/page/6/">Next</a></li>
    <li class="last"><a href="http://mysite.com/category/news/page/12/">Last</a></li>
  </ul>
</nav>
```

---


#### Changing default arguments

Let’s display a menu with a BEM CSS class prefix, reduced range, no first and last links, and no page counting.

```php
the_pagination( array(
    'range'        => 2,
    'class_prefix' => 'pagination__',
    'count'        => false,
    'edges'        => false
));
```

Result echoes:

```html
<nav class="pagination" role="navigation">
  <ul>
    <li class="pagination__prev"><a href="http://mysite.com/category/news/page/4/">Previous</a></li>
    <li><a href="http://mysite.com/category/news/page/3/">3</a></li>
    <li><a href="http://mysite.com/category/news/page/4/">4</a></li>
    <li class="pagination__current is-active"><span>5</span></li>
    <li><a href="http://mysite.com/category/news/page/6/">6</a></li>
    <li><a href="http://mysite.com/category/news/page/7/">7</a></li>
    <li class="pagination__next"><a href="http://mysite.com/category/news/page/6/">Next</a></li>
  </ul>
</nav>
```

---


#### Editing the menu before display

Let’s assume I need an actual link on the current page, instead of a `<span>`,with a `disabled` CSS class.

```php
$pagination = get_pagination();

// Get current page.
global $paged;
$current_page_number = ( !empty( $paged ) ) ? $paged : 1; 

// Edit the current page item.
$current_page = $pagination['current'];
$current_page->link = get_pagenum_link( $current_page_number );
$current_page->class .= ' disabled';

$pagination['current'] = $current_page;

// Display the modified pagination.
the_pagination( $pagination );
```

Result echoes:

```html
	...
	<li class="current is-active disabled"><a href="http://mysite.com/category/news/page/5/">5</a></li>
	...
```


## I18n

There are some strings which need translation. The script adds them to the current theme textdomain using the [`wp_get_theme`](http://codex.wordpress.org/Function_Reference/wp_get_theme) function.

This means that, if you want to translate the strings, you have to declare the theme textdomain in your `style.css` file, like this:

```css
/*
Theme Name: My Site
... (Theme URI, Version, ect.)
Text Domain: my-site
*/
```

The textdomain should be the same one declared in the [`load_theme_textdomain`](http://codex.wordpress.org/Function_Reference/load_theme_textdomain) function.

## The end

I made this with the intention of providing a simple but highly customizable function for displaying pagination.

Kudos to @Kriesi for the first implementation of the page code.

Critics, comments, suggestions and bug reports are welcome.