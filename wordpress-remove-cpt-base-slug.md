# WordPress: Remove CPT Base Slug While Keeping Elementor & ACF Compatible

## Overview

This document explains how to remove the Custom Post Type (CPT) base slug (e.g. `/locations/`) from URLs while keeping WordPress, ACF, and Elementor Pro working correctly.

### Example

Default URL:

```text
https://example.com/locations/rancho-cordova-ca-hot-tub-dealer/
```

Desired URL:

```text
https://example.com/rancho-cordova-ca-hot-tub-dealer/
```

---

## Environment

* WordPress
* ACF Pro (CPT UI)
* Elementor Pro Theme Builder
* Permalink Structure: `/%postname%/`

---

## Problem

Setting the CPT rewrite slug to `/` or an empty string:

```php
'rewrite' => [
    'slug' => '/',
    'with_front' => false,
]
```

or

```php
'rewrite' => [
    'slug' => '',
    'with_front' => false,
]
```

does **not** reliably work.

Symptoms include:

* 404 errors
* Elementor Single templates not loading
* Rewrite rules conflicting with Pages and Posts

---

## Recommended Solution

Keep the CPT registered normally:

```php
'rewrite' => [
    'slug' => 'locations',
    'with_front' => false,
]
```

Then modify only the public permalink.

---

## Step 1 — Remove the CPT slug from generated URLs

```php
add_filter( 'post_type_link', function ( $permalink, $post ) {

    if ( $post->post_type !== 'locations' ) {
        return $permalink;
    }

    return home_url( '/' . $post->post_name . '/' );

}, 10, 2 );
```

---

## Step 2 — Add a custom rewrite rule

```php
add_action( 'init', function () {

    add_rewrite_rule(
        '^([^/]+)/?$',
        'index.php?post_type=locations&name=$matches[1]',
        'top'
    );

});
```

---

## Step 3 — Flush rewrite rules

Go to:

**Settings → Permalinks**

Click:

**Save Changes**

No other changes are required.

---

## Alternative Rewrite Rule

If the first rewrite does not work because another plugin (or Elementor) overrides it, try:

```php
add_action( 'init', function () {

    add_rewrite_tag( '%locations%', '([^&]+)' );

    add_rewrite_rule(
        '^([^/]+)/?$',
        'index.php?locations=$matches[1]',
        'top'
    );

});
```

Then flush permalinks again.

---

# Notes

This approach is similar to how WooCommerce removes its product base.

Advantages:

* Keeps ACF CPT registration clean.
* Elementor Single templates continue working.
* Avoids modifying core CPT registration.
* Easier to maintain.

---

# Important Considerations

Root-level URLs can conflict with existing content.

Example:

```
/about/
/contact/
/services/
/blog/
```

If the rewrite rule captures every root URL, it may interfere with normal WordPress pages.

For production websites, consider adding validation so the rewrite only applies to the `locations` CPT.

---

# Troubleshooting Checklist

* Verify the permalink structure is `/%postname%/`.
* Flush rewrite rules after every rewrite change.
* Confirm the post slug is unique.
* Ensure no Page exists with the same slug.
* Test the original URL (`/locations/post-slug/`) before removing the base.
* Verify the Elementor Single template is assigned to the correct CPT.
* Check for conflicting rewrite plugins or permalink plugins.

---

# Example

Original URL:

```
https://example.com/locations/rancho-cordova-ca-hot-tub-dealer/
```

Final URL:

```
https://example.com/rancho-cordova-ca-hot-tub-dealer/
```

No changes are required in Elementor templates; only the permalink generation and rewrite handling are customized.
