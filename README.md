## Overview
The `WaterMark` class allows you to apply watermarks or custom text to images. It provides options for setting the position, size, and various configurations for both watermarks and text.

---

## Usage

### 1. Initializing the Class

First, import the `WaterMark` class and instantiate it:

```php
require_once 'WaterMark.php';

$wm = new WaterMark();
```

---

### 2. Setting the Base Image

To apply a watermark or text, you need to load the base image first:

```php
$wm->setImage('path/to/image.jpg');
```

**Parameters:**
- `filename` (string): Path to the image file on which you want to apply the watermark or text.

---

### 3. Adding a Watermark to the Image

To add a watermark, use the `addMark` method:

```php
$wm->addMark('path/to/watermark.png', 0.2, 'bottom_right');
```

**Parameters:**
- `filename` (string): Path to the watermark image.
- `size` (float): A relative size for scaling the watermark (e.g., `0.2` for 20% of the base image width).
- `position` (mixed): Either a predefined position like `'top_left'`, or a custom position in the form of an array `[x, y]` (e.g., `[100, 50]`).

---

### 4. Adding Text to the Image

To add custom text to the image, use the `pasteText` method:

```php
$wm->pasteText('Sample Text', [
    'font' => 'path/to/font.ttf',
    'size' => 30,
    'color' => '#ffffff',
    'opacity' => 50,
    'position' => [100, 200]
]);
```

**Parameters:**
- `text` (string): The text you want to add to the image.
- `config` (array): Configuration options for the text.
  - `font` (string): Path to the TrueType font file.
  - `size` (int): Font size.
  - `color` (string): Text color in hexadecimal format (e.g., `#ffffff`).
  - `opacity` (int): Opacity level for the text (0-127, where 0 is fully opaque and 127 is fully transparent).
  - `position` (mixed): Either a predefined position or a custom position in the form of an array `[x, y]`.

---

### 5. Exporting the Watermarked Image

After applying the watermark or text, you can export the final image using the `export` method:

```php
$wm->export('output_image', 'jpg');
```

**Parameters:**
- `filename` (string): Name of the output file (without the extension).
- `format` (string): File format, either `'jpg'` or `'png'`.

---

### 6. Freeing Up Memory

After you're done processing the image, use the `freeMemory` method to release the allocated resources:

```php
$wm->freeMemory();
```

---

## Example Usage

Here’s a complete example of how to use the `WaterMark` class:

```php
require_once 'WaterMark.php';

$wm = new WaterMark();

// Set the base image
$wm->setImage('image.jpg');

// Add a watermark at a custom position
$wm->addMark('watermark.png', 0.15, [300, 200]);

// Add text at a specific position
$wm->pasteText('Hello World', [
    'font' => 'path/to/font.ttf',
    'size' => 40,
    'color' => '#ffffff',
    'opacity' => 50,
    'position' => [100, 300]
]);

// Export the final watermarked image
$wm->export('watermarked_image', 'jpg');

// Free up resources
$wm->freeMemory();
```

## Predefined Positions

You can use the following predefined positions for both watermarks and text:
- `'top_left'`
- `'top_center'`
- `'top_right'`
- `'center'`
- `'middle_left'`
- `'middle_right'`
- `'bottom_left'`
- `'bottom_center'`
- `'bottom_right'`

Alternatively, you can pass an array `[x, y]` for a custom position.

---

## License

This class is open-source and free to use. Contributions are welcome!
