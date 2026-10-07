=== Digital Pulse Brief Tools ===
Contributors: digitalpulsebrief
Tags: image tools, pdf tools, qr code, seo tools, converter
Requires at least: 6.3
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later

A privacy-first collection of 28 image, PDF, design, SEO and text tools for Digital Pulse Brief.

== Included tools ==
Image Tools (14)
1. Image Compressor
2. Image Resizer
3. Image Cropper
4. Universal Image Converter
5. HEIC / HEIF Converter
6. JPG to PNG
7. PNG to JPG
8. JPG / PNG to WebP
9. WebP to JPG / PNG
10. AVIF Converter
11. Image to PDF
12. Image Dimensions Checker
13. DPI Checker
14. Aspect Ratio Calculator

PDF Tools (6)
15. Images to PDF
16. Merge PDF
17. Split PDF
18. Extract PDF Pages
19. Reorder PDF Pages
20. Compress PDF (lossy raster mode)

Design & Web Tools (8)
21. QR Code Generator
22. Contrast Checker
23. Pixel ↔ REM Converter
24. Social Media Image Size Calculator
25. Meta Tag Generator
26. Open Graph Generator
27. Word Counter
28. Character Counter

== Installation ==
1. Upload the plugin ZIP in WordPress > Plugins > Add Plugin > Upload Plugin.
2. Activate Digital Pulse Brief Tools.
3. Open Tools > DPB Tools for all available shortcodes.
4. Add [dpb_tools_hub] to a Tools landing page, or add individual tool shortcodes to Elementor Shortcode widgets.

== Privacy and processing ==
The plugin does not implement a WordPress file-upload endpoint for tool inputs. Image/PDF processing is performed in the visitor browser when supported.

== External runtime libraries ==
Heavy third-party libraries load only on tools that require them, from jsDelivr CDN:
- Cropper.js 1.6.2
- heic2any 0.0.4
- pdf-lib 1.17.1
- PDF.js 3.11.174 (only PDF compression)
- JSZip 3.10.1 (only PDF split ZIP)
- qrcodejs 1.0.0

No tool-library scripts are enqueued on ordinary DPB pages/posts that do not contain a DPB Tools shortcode.

== Important limitations ==
- HEIC/HEIF conversion does not preserve all original metadata.
- AVIF decode/encode depends on browser support.
- PDF Compress uses lossy page rasterization. It can reduce image-heavy PDFs but removes selectable text, links, forms and vector structure. Already optimized PDFs may become larger.
- Very large files/batches can exhaust memory on low-RAM phones because processing is local.
- Social-media size presets are common working canvases and can require updates as platforms change.


== Changelog ==

= 2.1.0 =
* Integrated QA fixes directly into the plugin source.
* Improved dropdown and range-control contrast.
* Fixed cropper readiness and null-canvas handling.
* Added HEIC/HEIF support to Image Dimensions Checker.
* Added JPG/JPEG, PNG, WebP and AVIF output choices to HEIC converter where supported.
* Added output format selection and live size estimates to Image Compressor.
* Improved Aspect Ratio Calculator instructions and presets.

= 2.0.0 =
* Fixed Tools Hub navigation and dark-theme text contrast.
