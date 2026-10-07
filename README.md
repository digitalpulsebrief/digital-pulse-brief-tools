# Digital Pulse Brief Tools

A free, privacy-first WordPress toolkit from **Digital Pulse Brief** with **28 browser-based image, PDF, design, SEO and writing utilities**.

Live tools hub: https://digitalpulsebrief.com/tools/

## What is included

### Image tools (14)
Image Compressor, Image Resizer, Image Cropper, Universal Image Converter, HEIC / HEIF Converter, JPG to PNG, PNG to JPG, JPG / PNG to WebP, WebP to JPG / PNG, AVIF Converter, Image to PDF, Image Dimensions Checker, DPI Checker and Aspect Ratio Calculator.

### PDF tools (6)
Images to PDF, Merge PDF, Split PDF, Extract PDF Pages, Reorder PDF Pages and Compress PDF.

### Design & web tools (8)
QR Code Generator, Contrast Checker, Pixel ↔ REM Converter, Social Media Image Size Calculator, Meta Tag Generator, Open Graph Generator, Word Counter and Character Counter.

## WordPress usage

Activate the plugin, then use the hub shortcode:

```text
[dpb_tools_hub]
```

Every tool also has its own shortcode. The full shortcode list is available in **WordPress → Tools → DPB Tools** after activation.

The plugin works with Elementor Shortcode widgets, Gutenberg Shortcode blocks and the Classic Editor.

## Privacy architecture

The plugin does **not** provide a WordPress file-upload endpoint for tool inputs. File tools are designed to process the selected file in the visitor's browser whenever practical. Large files and batches can still be limited by browser/device memory.

## Current release: 2.1.0

This GitHub build includes the QA fixes that were previously applied to the live site as compatibility patches:

- improved select/dropdown contrast;
- darker, more visible range-slider handles;
- safer cropper readiness and null-canvas handling;
- HEIC/HEIF support in Image Dimensions Checker;
- HEIC output options for JPG/JPEG, PNG, WebP and AVIF where browser encoding supports the output;
- Image Compressor output selection for original, JPG, JPEG, PNG, WebP and AVIF;
- live original/estimated image-size readouts;
- clearer Aspect Ratio Calculator instructions and common presets.

## Important limitations

- AVIF decoding/encoding depends on the visitor's browser. The plugin disables or reports unsupported AVIF output rather than intentionally creating a falsely labelled file.
- HEIC/HEIF conversion can lose original metadata.
- PDF compression is a lossy raster mode intended mainly for image-heavy PDFs. It can remove selectable text, links, forms and vector structure.
- Already optimized PDFs may not become smaller.
- Very large files or batches can exhaust memory on low-RAM devices because processing is browser-side.
- Social-media size presets can become outdated as platforms change their recommendations.

## Runtime dependencies

Third-party libraries are conditionally loaded from jsDelivr only on tools that need them. They are **not vendored into this repository**. See [`THIRD-PARTY-NOTICES.md`](THIRD-PARTY-NOTICES.md).

## Installation

1. Download or clone this repository.
2. Zip the `digital-pulse-brief-tools` folder if needed.
3. In WordPress, go to **Plugins → Add Plugin → Upload Plugin**.
4. Upload the ZIP and activate **Digital Pulse Brief Tools**.
5. Add the hub or individual tool shortcodes to the required pages.

### Existing DPB live-site note

If this integrated `2.1.0` build replaces the earlier plugin on the Digital Pulse Brief live site, disable the temporary WPCode compatibility snippets **1114, 1115 and 1116** after confirming the new plugin build is active. Keeping both can duplicate event handlers.

## License

Digital Pulse Brief Tools is licensed under **GPL-2.0-or-later**. See [`LICENSE`](LICENSE).

## Website

Digital Pulse Brief — AI, Technology & Business — Explained Clearly  
https://digitalpulsebrief.com/
