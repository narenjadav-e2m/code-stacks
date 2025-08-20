# Code Base and Snippets

## Restrict Browser Accessibility for User

See [`restrict-frontend.php`](restrict-frontend.php) for an example of restricting frontend access for specific users.

## High-Resolution YouTube Thumbnails

You can easily retrieve YouTube video thumbnails in different resolutions by replacing `<VIDEO_ID>` with the actual video’s ID in the URLs below:

- **Max resolution (JPG):**
  `https://img.youtube.com/vi/<VIDEO_ID>/maxresdefault.jpg`

- **Standard HD (JPG):**
  `https://img.youtube.com/vi/<VIDEO_ID>/hqdefault.jpg`

- **High-res WebP:**
  `https://i.ytimg.com/vi_webp/<VIDEO_ID>/maxresdefault.webp`

These URLs are useful for programmatically accessing or embedding video thumbnails in your projects.