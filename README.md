# WP_MLT_Query

WordPress "More Like This" Query class for getting highly relevant related posts.

## Usage

```php
$mlt = new WP_MLT_Query( array( 
	'p' => 587936, 
	'posts_per_page' => 6, 
	'fields' => 'all', 
));

if ( $query->results->have_posts() ) : while ( $query->results->have_posts() ) : $query->results->the_post();
	the_title(); echo '<br />';
endwhile; endif;
```

## Notes

* Currently accepts only three arguments: "posts_per_page", "fields", and "p"
* The arguments **should** work exactly like WP_Query arguments, but this code is still in alpha