<?php

/**
 *  Copyright (c) 2024
 *  @Version    : 1.0.1
 *  @Repository : https://github.com/salarizadi/php_watermark
 *  @Author     : https://salarizadi.github.io
 */

class WaterMark {
  
    private array $marks = []; // Array to hold watermark images
    private $outPic; // Output picture resource
    private $image; // Original image resource
    private $image_width; // Width of the original image
    private $image_height; // Height of the original image

    /**
     * Sets the image to be watermarked.
     *
     * @param string $filename Path to the image file.
     * @return $this The current instance for method chaining.
     */
    public function setImage (string $filename): WaterMark {
        $this->image = imagecreatefromstring(file_get_contents($filename)); // Create an image resource from the file
        list($this->image_width, $this->image_height) = getimagesize($filename); // Get dimensions of the original image
        $this->outPic = imagecreatetruecolor($this->image_width, $this->image_height); // Create a new true color image for output

        // Copy original image into the output picture
        imagecopyresampled(
            $this->outPic, $this->image,
            0, 0, 0, 0,
            $this->image_width, $this->image_height,
            $this->image_width, $this->image_height
        );

        return $this; // Return the instance for method chaining
    }

    /**
     * Adds a watermark to the image.
     *
     * @param string $filename Path to the watermark image file.
     * @param float $size Scale factor for the watermark size (default: 0.15).
     * @param mixed $position Position to place the watermark (default: 'top_left').
     * @return $this The current instance for method chaining.
     */
    public function addMark (string $filename, float $size = 0.15, $position = 'top_left'): WaterMark {
        $mark = imagecreatefromstring(file_get_contents($filename)); // Create an image resource for the watermark
        list($mark_width, $mark_height) = getimagesize($filename); // Get dimensions of the watermark

        // Calculate the new dimensions for the watermark
        $new_wk_width = $this->image_width * $size;
        $new_wk_height = ($mark_height / $mark_width) * $new_wk_width;

        // Store watermark details
        $this->marks[] = [
            'image'  => $mark,
            'width'  => $new_wk_width,
            'height' => $new_wk_height,
            'position' => $position
        ];

        // Get the position for the watermark and apply it to the output image
        list($x, $y) = $this->getMarkPosition($position, $new_wk_width, $new_wk_height);
        imagecopyresampled(
            $this->outPic, $mark,
            $x, $y, 0, 0,
            $new_wk_width, $new_wk_height,
            imagesx($mark), imagesy($mark)
        );

        return $this; // Return the instance for method chaining
    }

    /**
     * Pastes text onto the image with the specified configurations.
     *
     * @param string $text The text to be added.
     * @param array $config Configuration array for font, size, color, opacity, and position.
     * @return $this The current instance for method chaining.
     */
    public function pasteText (string $text, array $config): WaterMark {
        $fontPath = $config['font']; // Path to the font file
        $fontSize = $config['size'] ?? 20; // Font size (default: 20)
        $color = $this->hexToRgb($config['color'] ?? '#000000'); // Convert hex color to RGB
        $opacity = $config['opacity'] ?? 0; // Opacity of the text (default: 0)

        // Allocate color for the text
        $textColor = imagecolorallocatealpha($this->outPic, $color[0], $color[1], $color[2], $opacity);

        // Calculate the bounding box of the text
        $textBox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = abs($textBox[4] - $textBox[0]); // Width of the text
        $textHeight = abs($textBox[5] - $textBox[1]); // Height of the text

        $position = $config['position'] ?? 'top_left'; // Position of the text
        if (is_array($position)) {
            $x = $position[0]; // X coordinate
            $y = $position[1]; // Y coordinate
        } else {
            list($x, $y) = $this->textPosition($position, $textWidth, $textHeight); // Get calculated position for the text
        }

        // Add the text to the output image
        imagettftext($this->outPic, $fontSize, 0, $x, $y, $textColor, $fontPath, $text);

        return $this; // Return the instance for method chaining
    }

    /**
     * Exports the watermarked image to a file in the specified format.
     *
     * @param string $filename The name of the file to be saved without extension (default: 'watermarked').
     * @param string $format The format to export the image ('jpg', 'jpeg', or 'png'). Default is 'jpg'.
     * @param int $quality The quality of the exported image (1-100 for JPEG, 0-9 for PNG). Default is 100 for JPEG.
     * @return bool True if the image is successfully exported, false otherwise.
     */
    public function export (string $filename = 'watermarked', string $format = 'jpg', int $quality = 100): bool {
        $export = false; // Initialize export flag

        switch ($format) {
            case 'jpg':
            case 'jpeg':
                $export = imagejpeg($this->outPic, "$filename.jpg", $quality); // Export as JPEG
                break;
            case 'png':
                $export = imagepng($this->outPic, "$filename.png", $quality); // Export as PNG
        }

        return $export; // Return the export result
    }

    /**
     * Frees up memory used by the image resources.
     *
     * @return WaterMark The current instance for method chaining.
     */
    public function freeMemory (): WaterMark {
        imagedestroy($this->outPic); // Destroy output image resource
        imagedestroy($this->image); // Destroy original image resource

        // Destroy each watermark image resource
        foreach ($this->marks as $mark) {
            imagedestroy($mark['image']);
        }

        return $this; // Return the instance for method chaining
    }

    /**
     * Calculates the position for placing a watermark.
     *
     * @param mixed $position The position specified for the watermark (string or array).
     * @param float $mark_width The width of the watermark.
     * @param float $mark_height The height of the watermark.
     * @return array The calculated X and Y coordinates.
     */
    private function getMarkPosition ($position, float $mark_width, float $mark_height): array {
        if (is_array($position)) {
            return [$position[0], $position[1]]; // Use provided coordinates directly if position is an array
        }

        switch ($position) {
            case 'top_center':
                $x = ($this->image_width / 2) - ($mark_width / 2);
                $y = 50; // Distance from top
                break;
            case 'top_right':
                $x = $this->image_width - $mark_width - 10;
                $y = 50; // Distance from top
                break;
            case 'bottom_left':
                $x = 10;
                $y = $this->image_height - $mark_height - 10;
                break;
            case 'bottom_center':
                $x = ($this->image_width / 2) - ($mark_width / 2);
                $y = $this->image_height - $mark_height - 10;
                break;
            case 'bottom_right':
                $x = $this->image_width - $mark_width - 10;
                $y = $this->image_height - $mark_height - 10;
                break;
            case 'center':
                $x = ($this->image_width / 2) - ($mark_width / 2);
                $y = ($this->image_height / 2) - ($mark_height / 2);
                break;
            case 'middle_left':
                $x = 10;
                $y = ($this->image_height / 2) - ($mark_height / 2);
                break;
            case 'middle_right':
                $x = $this->image_width - $mark_width - 10;
                $y = ($this->image_height / 2) - ($mark_height / 2);
                break;
            case 'top_left':
            default:
                $x = 10;
                $y = 50; // Distance from top
                break;
        }

        return [$x, $y]; // Return calculated coordinates
    }

    /**
     * Calculates the position for placing text on the image.
     *
     * @param string $position The position specified for the text.
     * @param float $textWidth The width of the text.
     * @param float $textHeight The height of the text.
     * @return array The calculated X and Y coordinates for the text.
     */
    public function textPosition (string $position, float $textWidth, float $textHeight): array {
        switch ($position) {
            case 'top_center':
                $x = ($this->image_width / 2) - ($textWidth / 2);
                $y = 50; // Distance from top
                break;
            case 'top_right':
                $x = $this->image_width - $textWidth - 10;
                $y = 50; // Distance from top
                break;
            case 'bottom_left':
                $x = 10;
                $y = $this->image_height - $textHeight - 10;
                break;
            case 'bottom_center':
                $x = ($this->image_width / 2) - ($textWidth / 2);
                $y = $this->image_height - $textHeight - 10;
                break;
            case 'bottom_right':
                $x = $this->image_width - $textWidth - 10;
                $y = $this->image_height - $textHeight - 10;
                break;
            case 'center':
                $x = ($this->image_width / 2) - ($textWidth / 2);
                $y = ($this->image_height / 2) + ($textHeight / 2);
                break;
            case 'middle_left':
                $x = 10;
                $y = ($this->image_height / 2) + ($textHeight / 2);
                break;
            case 'middle_right':
                $x = $this->image_width - $textWidth - 10;
                $y = ($this->image_height / 2) + ($textHeight / 2);
                break;
            case 'top_left':
            default:
                $x = 10;
                $y = 50; // Distance from top
                break;
        }

        return [$x, $y]; // Return calculated coordinates
    }

    /**
     * Converts a hex color to an RGB array.
     *
     * @param string $hex The hex color code (e.g., '#ffffff').
     * @return array The RGB values as an array.
     */
    private function hexToRgb (string $hex): array {
        $hex = ltrim($hex, '#'); // Remove the '#' character
        if (strlen($hex) === 6) {
            return [
                hexdec(substr($hex, 0, 2)), // Red
                hexdec(substr($hex, 2, 2)), // Green
                hexdec(substr($hex, 4, 2)), // Blue
            ];
        } elseif (strlen($hex) === 3) {
            return [
                hexdec(str_repeat(substr($hex, 0, 1), 2)), // Red
                hexdec(str_repeat(substr($hex, 1, 1), 2)), // Green
                hexdec(str_repeat(substr($hex, 2, 1), 2)), // Blue
            ];
        }
        return [0, 0, 0]; // Default to black if hex is invalid
    }
  
}
