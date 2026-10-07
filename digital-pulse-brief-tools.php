<?php
/**
 * Plugin Name: Digital Pulse Brief Tools
 * Plugin URI: https://digitalpulsebrief.com/
 * Description: A privacy-first toolkit for Digital Pulse Brief: image, PDF, design, SEO and text utilities. Heavy processing runs in the visitor's browser whenever practical.
 * Version: 2.1.0
 * Author: Digital Pulse Brief
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 * Text Domain: dpb-tools
 */

if (!defined('ABSPATH')) { exit; }

final class DPB_Tools_Plugin {
    const VERSION = '2.1.0';
    private static $instance = null;

    public static function instance() {
        if (!self::$instance) { self::$instance = new self(); }
        return self::$instance;
    }

    private function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'register_assets']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_shortcode('dpb_tools_hub', [$this, 'render_hub']);

        foreach ($this->tools() as $slug => $tool) {
            add_shortcode($tool['shortcode'], function($atts = [], $content = null) use ($slug) {
                return $this->render_tool($slug);
            });
        }
    }

    public function tools() {
        return [
            'image-compressor' => ['title'=>'Image Compressor','category'=>'Image Tools','shortcode'=>'dpb_image_compressor','description'=>'Compress JPG, PNG and WebP images locally in your browser.'],
            'image-resizer' => ['title'=>'Image Resizer','category'=>'Image Tools','shortcode'=>'dpb_image_resizer','description'=>'Resize images to exact pixel dimensions while preserving aspect ratio when needed.'],
            'image-cropper' => ['title'=>'Image Cropper','category'=>'Image Tools','shortcode'=>'dpb_image_cropper','description'=>'Crop an image visually with common aspect-ratio presets or free crop.'],
            'image-converter' => ['title'=>'Universal Image Converter','category'=>'Image Tools','shortcode'=>'dpb_image_converter','description'=>'Convert JPG, PNG, WebP, AVIF and HEIC/HEIF to common web formats.'],
            'heic-converter' => ['title'=>'HEIC / HEIF Converter','category'=>'Image Tools','shortcode'=>'dpb_heic_converter','description'=>'Convert Apple HEIC/HEIF photos to JPG/JPEG, PNG, WebP or AVIF where the browser supports the requested output.'],
            'jpg-to-png' => ['title'=>'JPG to PNG','category'=>'Image Tools','shortcode'=>'dpb_jpg_to_png','description'=>'Convert JPG/JPEG images to PNG.'],
            'png-to-jpg' => ['title'=>'PNG to JPG','category'=>'Image Tools','shortcode'=>'dpb_png_to_jpg','description'=>'Convert PNG images to JPG with adjustable quality and background.'],
            'to-webp' => ['title'=>'JPG / PNG to WebP','category'=>'Image Tools','shortcode'=>'dpb_to_webp','description'=>'Convert JPG and PNG images to lightweight WebP.'],
            'webp-converter' => ['title'=>'WebP to JPG / PNG','category'=>'Image Tools','shortcode'=>'dpb_webp_converter','description'=>'Convert WebP images to JPG or PNG.'],
            'avif-converter' => ['title'=>'AVIF Converter','category'=>'Image Tools','shortcode'=>'dpb_avif_converter','description'=>'Convert AVIF to JPG, PNG or WebP; create AVIF where browser encoding supports it.'],
            'image-to-pdf' => ['title'=>'Image to PDF','category'=>'Image Tools','shortcode'=>'dpb_image_to_pdf','description'=>'Turn one image into a downloadable PDF.'],
            'image-dimensions' => ['title'=>'Image Dimensions Checker','category'=>'Image Tools','shortcode'=>'dpb_image_dimensions','description'=>'Check width, height, megapixels, file size and aspect ratio.'],
            'dpi-checker' => ['title'=>'DPI Checker','category'=>'Image Tools','shortcode'=>'dpb_dpi_checker','description'=>'Read embedded DPI/PPI density metadata from supported JPEG and PNG files.'],
            'aspect-ratio' => ['title'=>'Aspect Ratio Calculator','category'=>'Image Tools','shortcode'=>'dpb_aspect_ratio','description'=>'Calculate and scale image/video aspect ratios.'],

            'images-to-pdf' => ['title'=>'Images to PDF','category'=>'PDF Tools','shortcode'=>'dpb_images_to_pdf','description'=>'Combine multiple images into one PDF in the selected order.'],
            'pdf-merge' => ['title'=>'Merge PDF','category'=>'PDF Tools','shortcode'=>'dpb_pdf_merge','description'=>'Merge multiple PDF files into one document in your browser.'],
            'pdf-split' => ['title'=>'Split PDF','category'=>'PDF Tools','shortcode'=>'dpb_pdf_split','description'=>'Split every page of a PDF into separate PDF files and download a ZIP.'],
            'pdf-extract' => ['title'=>'Extract PDF Pages','category'=>'PDF Tools','shortcode'=>'dpb_pdf_extract','description'=>'Extract selected pages or ranges into a new PDF.'],
            'pdf-reorder' => ['title'=>'Reorder PDF Pages','category'=>'PDF Tools','shortcode'=>'dpb_pdf_reorder','description'=>'Create a new PDF using any page order you specify.'],
            'pdf-compress' => ['title'=>'Compress PDF','category'=>'PDF Tools','shortcode'=>'dpb_pdf_compress','description'=>'Reduce image-heavy PDFs by rasterizing pages at your chosen quality.'],

            'qr-generator' => ['title'=>'QR Code Generator','category'=>'Design & Web Tools','shortcode'=>'dpb_qr_generator','description'=>'Generate a downloadable QR code from text or a URL.'],
            'contrast-checker' => ['title'=>'Contrast Checker','category'=>'Design & Web Tools','shortcode'=>'dpb_contrast_checker','description'=>'Check WCAG contrast ratio for foreground and background colors.'],
            'px-rem' => ['title'=>'Pixel ↔ REM Converter','category'=>'Design & Web Tools','shortcode'=>'dpb_px_rem','description'=>'Convert pixels and rem units using any root font size.'],
            'social-image-size' => ['title'=>'Social Media Image Size Calculator','category'=>'Design & Web Tools','shortcode'=>'dpb_social_image_size','description'=>'Use common social-media canvas presets and calculate proportional sizes.'],
            'meta-generator' => ['title'=>'Meta Tag Generator','category'=>'Design & Web Tools','shortcode'=>'dpb_meta_generator','description'=>'Create clean title, description, canonical and robots meta tags.'],
            'open-graph' => ['title'=>'Open Graph Generator','category'=>'Design & Web Tools','shortcode'=>'dpb_open_graph','description'=>'Generate Open Graph and social sharing meta tags.'],
            'word-counter' => ['title'=>'Word Counter','category'=>'Design & Web Tools','shortcode'=>'dpb_word_counter','description'=>'Count words, characters, sentences, paragraphs and estimated reading time.'],
            'character-counter' => ['title'=>'Character Counter','category'=>'Design & Web Tools','shortcode'=>'dpb_character_counter','description'=>'Count characters with and without spaces, lines and UTF-8 bytes.'],
        ];
    }

    public function register_assets() {
        $base = plugin_dir_url(__FILE__) . 'assets/';
        wp_register_style('dpb-tools', $base . 'css/dpb-tools.css', [], self::VERSION);
        wp_register_script('dpb-tools', $base . 'js/dpb-tools.js', [], self::VERSION, true);

        // Tool-specific third-party libraries. They are registered globally but loaded only by relevant shortcodes.
        wp_register_style('dpb-cropper', 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css', [], '1.6.2');
        wp_register_script('dpb-cropper', 'https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js', [], '1.6.2', true);
        wp_register_script('dpb-heic2any', 'https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js', [], '0.0.4', true);
        wp_register_script('dpb-pdf-lib', 'https://cdn.jsdelivr.net/npm/pdf-lib@1.17.1/dist/pdf-lib.min.js', [], '1.17.1', true);
        wp_register_script('dpb-pdfjs', 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js', [], '3.11.174', true);
        wp_register_script('dpb-jszip', 'https://cdn.jsdelivr.net/npm/jszip@3.10.1/dist/jszip.min.js', [], '3.10.1', true);
        wp_register_script('dpb-qrcode', 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js', [], '1.0.0', true);
    }

    private function enqueue_for_tool($slug) {
        wp_enqueue_style('dpb-tools');
        wp_enqueue_script('dpb-tools');

        $needs_heic = ['image-converter','heic-converter','image-dimensions'];
        $needs_pdf = ['image-to-pdf','images-to-pdf','pdf-merge','pdf-split','pdf-extract','pdf-reorder','pdf-compress'];
        $needs_zip = ['pdf-split'];

        if ($slug === 'image-cropper') { wp_enqueue_style('dpb-cropper'); wp_enqueue_script('dpb-cropper'); }
        if (in_array($slug, $needs_heic, true)) { wp_enqueue_script('dpb-heic2any'); }
        if (in_array($slug, $needs_pdf, true)) { wp_enqueue_script('dpb-pdf-lib'); }
        if ($slug === 'pdf-compress') { wp_enqueue_script('dpb-pdfjs'); }
        if (in_array($slug, $needs_zip, true)) { wp_enqueue_script('dpb-jszip'); }
        if ($slug === 'qr-generator') { wp_enqueue_script('dpb-qrcode'); }

        wp_localize_script('dpb-tools', 'DPBToolsConfig', [
            'version' => self::VERSION,
            'pdfWorker' => 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.worker.min.js',
            'maxFiles' => 50,
            'maxFileMB' => 50,
            'privacy' => __('Files are processed in your browser and are not uploaded to Digital Pulse Brief.', 'dpb-tools'),
        ]);
    }

    public function admin_menu() {
        add_management_page('DPB Tools', 'DPB Tools', 'manage_options', 'dpb-tools', [$this, 'admin_page']);
    }

    public function admin_page() {
        if (!current_user_can('manage_options')) { return; }
        echo '<div class="wrap"><h1>Digital Pulse Brief Tools</h1><p>Version '.esc_html(self::VERSION).' — 28 front-end tools. Add the hub shortcode or any individual shortcode to an Elementor Shortcode widget, Gutenberg Shortcode block, or classic editor.</p>';
        echo '<p><strong>Hub:</strong> <code>[dpb_tools_hub]</code></p><table class="widefat striped"><thead><tr><th>Tool</th><th>Category</th><th>Shortcode</th></tr></thead><tbody>';
        foreach ($this->tools() as $tool) {
            echo '<tr><td>'.esc_html($tool['title']).'</td><td>'.esc_html($tool['category']).'</td><td><code>['.esc_html($tool['shortcode']).']</code></td></tr>';
        }
        echo '</tbody></table><p style="margin-top:18px"><strong>Privacy architecture:</strong> the plugin does not provide a file-upload endpoint. Tool files stay in the visitor browser unless another site script/plugin independently uploads them.</p></div>';
    }

    public function render_hub() {
        wp_enqueue_style('dpb-tools');

        $tools = $this->tools();
        $selected = '';
        if (isset($_GET['dpb_tool'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only navigation parameter.
            $selected = sanitize_key(wp_unslash($_GET['dpb_tool'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        }

        $page_id = get_queried_object_id();
        $base_url = $page_id ? get_permalink($page_id) : home_url('/');
        $base_url = remove_query_arg('dpb_tool', $base_url);

        // The hub itself doubles as a reliable tool router. This avoids broken
        // links when individual WordPress pages have not been created yet.
        if ($selected && isset($tools[$selected])) {
            return '<div class="dpb-tool-route"><a class="dpb-back-tools" href="'.esc_url($base_url).'">&larr; All tools</a>'.$this->render_tool($selected).'</div>';
        }

        $groups = [];
        foreach ($tools as $slug => $tool) { $groups[$tool['category']][$slug] = $tool; }
        ob_start(); ?>
        <section class="dpb-tools-hub">
            <div class="dpb-kicker">DIGITAL PULSE BRIEF TOOLS</div>
            <h2>Free browser-based tools</h2>
            <p class="dpb-lead">Fast image, PDF, design and web utilities. File tools are designed to process locally in your browser whenever practical.</p>
            <?php foreach ($groups as $category => $items): ?>
                <div class="dpb-hub-section">
                    <h3><?php echo esc_html($category); ?></h3>
                    <div class="dpb-hub-grid">
                        <?php foreach ($items as $slug => $tool):
                            $tool_url = add_query_arg('dpb_tool', $slug, $base_url);
                        ?>
                            <article class="dpb-hub-card">
                                <h4><?php echo esc_html($tool['title']); ?></h4>
                                <p><?php echo esc_html($tool['description']); ?></p>
                                <a class="dpb-hub-open" href="<?php echo esc_url($tool_url); ?>" aria-label="<?php echo esc_attr('Open '.$tool['title']); ?>">
                                    <span>Open tool</span><span class="dpb-hub-arrow" aria-hidden="true">&rarr;</span>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>
        <?php return ob_get_clean();
    }

    public function render_tool($slug) {
        $tools = $this->tools();
        if (!isset($tools[$slug])) { return ''; }
        $this->enqueue_for_tool($slug);
        $tool = $tools[$slug];

        ob_start(); ?>
        <section class="dpb-tool" data-tool="<?php echo esc_attr($slug); ?>">
            <header class="dpb-tool-head">
                <div class="dpb-kicker">FREE • PRIVATE-FIRST • BROWSER-BASED</div>
                <h2><?php echo esc_html($tool['title']); ?></h2>
                <p><?php echo esc_html($tool['description']); ?></p>
            </header>
            <?php echo $this->tool_ui($slug); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <div class="dpb-status" role="status" aria-live="polite"></div>
            <div class="dpb-results"></div>
            <?php if (!in_array($slug, ['aspect-ratio','contrast-checker','px-rem','social-image-size','meta-generator','open-graph','word-counter','character-counter','qr-generator'], true)): ?>
                <div class="dpb-privacy"><strong>Privacy:</strong> Files selected in this tool are processed in your browser. This plugin does not include a WordPress upload endpoint for them.</div>
            <?php endif; ?>
            <noscript><div class="dpb-error">JavaScript is required for this tool.</div></noscript>
        </section>
        <?php return ob_get_clean();
    }

    private function file_drop($accept, $multiple = true, $label = 'Drop files here or choose files') {
        return '<label class="dpb-drop" tabindex="0"><input class="dpb-file-input" type="file" accept="'.esc_attr($accept).'" '.($multiple?'multiple':'').'><span class="dpb-drop-title">'.esc_html($label).'</span><span class="dpb-drop-sub">Maximum 50 MB per file • processing limits also depend on device memory</span></label>';
    }

    private function tool_ui($slug) {
        switch ($slug) {
            case 'image-compressor':
                return $this->file_drop('image/jpeg,image/png,image/webp,image/avif') . '<div class="dpb-controls"><label>Quality <input class="dpb-quality" type="range" min="35" max="95" value="78"><output>78%</output></label><label>Maximum dimension <select class="dpb-maxdim"><option value="0">Keep original</option><option value="3840">3840 px</option><option value="2560">2560 px</option><option value="1920">1920 px</option><option value="1600">1600 px</option><option value="1200">1200 px</option><option value="800">800 px</option></select></label><label>Output format <select class="dpb-compressor-format"><option value="original">Keep original format</option><option value="image/jpeg|jpg">JPG (.jpg)</option><option value="image/jpeg|jpeg">JPEG (.jpeg)</option><option value="image/png|png">PNG (.png)</option><option value="image/webp|webp">WebP (.webp)</option><option value="image/avif|avif">AVIF (.avif — browser support required)</option></select></label></div>'.$this->buttons('Compress images');
            case 'image-resizer':
                return $this->file_drop('image/jpeg,image/png,image/webp,image/avif') . '<div class="dpb-controls"><label>Width (px)<input class="dpb-width" type="number" min="1" max="16000" placeholder="1600"></label><label>Height (px)<input class="dpb-height" type="number" min="1" max="16000" placeholder="Auto"></label><label class="dpb-check"><input class="dpb-lock" type="checkbox" checked> Preserve aspect ratio</label><label>Output <select class="dpb-format"><option value="original">Original format</option><option value="image/jpeg">JPG</option><option value="image/png">PNG</option><option value="image/webp">WebP</option></select></label></div>'.$this->buttons('Resize images');
            case 'image-cropper':
                return $this->file_drop('image/jpeg,image/png,image/webp,image/avif', false, 'Choose one image to crop') . '<div class="dpb-crop-stage"><img class="dpb-crop-image" alt="Crop preview"></div><div class="dpb-controls"><label>Aspect ratio <select class="dpb-ratio"><option value="free">Free</option><option value="1">1:1</option><option value="1.3333333333">4:3</option><option value="1.5">3:2</option><option value="1.7777777778">16:9</option><option value="0.8">4:5</option><option value="0.5625">9:16</option></select></label><label>Output <select class="dpb-format"><option value="image/jpeg">JPG</option><option value="image/png">PNG</option><option value="image/webp">WebP</option></select></label><label>Quality <input class="dpb-quality" type="range" min="50" max="100" value="92"><output>92%</output></label></div>'.$this->buttons('Crop & download');
            case 'image-converter':
                return $this->file_drop('image/jpeg,image/png,image/webp,image/avif,.heic,.heif,image/heic,image/heif') . '<div class="dpb-controls"><label>Output format <select class="dpb-format"><option value="image/jpeg">JPG</option><option value="image/png">PNG</option><option value="image/webp">WebP</option><option value="image/avif">AVIF (browser support required)</option></select></label><label>Quality <input class="dpb-quality" type="range" min="40" max="100" value="90"><output>90%</output></label></div>'.$this->buttons('Convert images');
            case 'heic-converter':
                return $this->file_drop('.heic,.heif,image/heic,image/heif') . '<div class="dpb-controls"><label>Output format <select class="dpb-format"><option value="image/jpeg">JPG / JPEG</option><option value="image/png">PNG</option><option value="image/webp">WebP</option><option value="image/avif">AVIF (browser support required)</option></select></label><label>Quality <input class="dpb-quality" type="range" min="50" max="100" value="90"><output>90%</output></label></div>'.$this->buttons('Convert HEIC / HEIF');
            case 'jpg-to-png':
                return $this->file_drop('image/jpeg') . $this->buttons('Convert to PNG');
            case 'png-to-jpg':
                return $this->file_drop('image/png') . '<div class="dpb-controls"><label>JPG quality <input class="dpb-quality" type="range" min="40" max="100" value="92"><output>92%</output></label><label>Transparent background <input class="dpb-bg" type="color" value="#ffffff"></label></div>'.$this->buttons('Convert to JPG');
            case 'to-webp':
                return $this->file_drop('image/jpeg,image/png') . '<div class="dpb-controls"><label>WebP quality <input class="dpb-quality" type="range" min="40" max="100" value="88"><output>88%</output></label></div>'.$this->buttons('Convert to WebP');
            case 'webp-converter':
                return $this->file_drop('image/webp') . '<div class="dpb-controls"><label>Output <select class="dpb-format"><option value="image/jpeg">JPG</option><option value="image/png">PNG</option></select></label><label>Quality <input class="dpb-quality" type="range" min="40" max="100" value="92"><output>92%</output></label></div>'.$this->buttons('Convert WebP');
            case 'avif-converter':
                return $this->file_drop('image/avif,image/jpeg,image/png,image/webp') . '<div class="dpb-controls"><label>Output <select class="dpb-format"><option value="image/jpeg">JPG</option><option value="image/png">PNG</option><option value="image/webp">WebP</option><option value="image/avif">AVIF</option></select></label><label>Quality <input class="dpb-quality" type="range" min="40" max="100" value="88"><output>88%</output></label></div><div class="dpb-note">AVIF decoding/encoding depends on browser support. The tool will report an error rather than silently create a wrong file.</div>'.$this->buttons('Convert AVIF / image');
            case 'image-to-pdf':
                return $this->file_drop('image/jpeg,image/png,image/webp,image/avif', false, 'Choose one image') . '<div class="dpb-controls"><label>Page size <select class="dpb-page-size"><option value="image">Match image</option><option value="a4">A4 portrait</option><option value="letter">US Letter portrait</option></select></label><label>Margin <select class="dpb-margin"><option value="0">None</option><option value="18">Small</option><option value="36" selected>Medium</option><option value="54">Large</option></select></label></div>'.$this->buttons('Create PDF');
            case 'image-dimensions':
                return $this->file_drop('image/*,.heic,.heif,image/heic,image/heif') . $this->buttons('Check dimensions');
            case 'dpi-checker':
                return $this->file_drop('image/jpeg,image/png') . '<div class="dpb-note">DPI is metadata. Many web images contain no explicit DPI/PPI value even though their pixel dimensions are valid.</div>'.$this->buttons('Read DPI metadata');
            case 'aspect-ratio':
                return '<div class="dpb-controls dpb-controls-static"><label>Width<input class="dpb-width" type="number" min="1" value="1920"></label><label>Height<input class="dpb-height" type="number" min="1" value="1080"></label><label>New width<input class="dpb-new-width" type="number" min="1" placeholder="Optional"></label><label>New height<input class="dpb-new-height" type="number" min="1" placeholder="Optional"></label></div>'.$this->buttons('Calculate', false);
            case 'images-to-pdf':
                return $this->file_drop('image/jpeg,image/png,image/webp,image/avif') . '<div class="dpb-controls"><label>Page size <select class="dpb-page-size"><option value="a4">A4</option><option value="letter">US Letter</option><option value="image">Match each image</option></select></label><label>Margin <select class="dpb-margin"><option value="0">None</option><option value="18">Small</option><option value="36" selected>Medium</option></select></label></div>'.$this->buttons('Create combined PDF');
            case 'pdf-merge':
                return $this->file_drop('application/pdf,.pdf') . '<div class="dpb-note">Files are merged in the order selected by your browser.</div>'.$this->buttons('Merge PDFs');
            case 'pdf-split':
                return $this->file_drop('application/pdf,.pdf', false, 'Choose one PDF') . '<div class="dpb-note">Every page will become a separate PDF inside one ZIP download.</div>'.$this->buttons('Split PDF');
            case 'pdf-extract':
                return $this->file_drop('application/pdf,.pdf', false, 'Choose one PDF') . '<div class="dpb-controls"><label>Pages / ranges<input class="dpb-pages" type="text" placeholder="Example: 1,3,5-8"></label></div>'.$this->buttons('Extract pages');
            case 'pdf-reorder':
                return $this->file_drop('application/pdf,.pdf', false, 'Choose one PDF') . '<div class="dpb-controls"><label>New page order<input class="dpb-pages" type="text" placeholder="Example: 3,1,2,4"></label></div>'.$this->buttons('Reorder PDF');
            case 'pdf-compress':
                return $this->file_drop('application/pdf,.pdf', false, 'Choose one PDF') . '<div class="dpb-controls"><label>Image quality <input class="dpb-quality" type="range" min="35" max="90" value="68"><output>68%</output></label><label>Render scale <select class="dpb-scale"><option value="1">1× smaller</option><option value="1.25" selected>1.25× balanced</option><option value="1.5">1.5× sharper</option><option value="2">2× high quality</option></select></label></div><div class="dpb-warning"><strong>Important:</strong> this is lossy compression for image-heavy PDFs. Pages are rasterized, so selectable text, links, forms and vector data will not be preserved.</div>'.$this->buttons('Compress PDF');
            case 'qr-generator':
                return '<div class="dpb-controls dpb-controls-static"><label class="dpb-wide">Text or URL<textarea class="dpb-text" rows="4" placeholder="https://digitalpulsebrief.com/"></textarea></label><label>Size <select class="dpb-qr-size"><option value="256">256 × 256</option><option value="512" selected>512 × 512</option><option value="1024">1024 × 1024</option></select></label><label>Foreground <input class="dpb-fg" type="color" value="#050816"></label><label>Background <input class="dpb-bg" type="color" value="#ffffff"></label></div><div class="dpb-qr-preview"></div>'.$this->buttons('Generate QR', false);
            case 'contrast-checker':
                return '<div class="dpb-controls dpb-controls-static"><label>Foreground <input class="dpb-fg" type="color" value="#ffffff"></label><label>Background <input class="dpb-bg" type="color" value="#050816"></label></div><div class="dpb-contrast-preview">Digital Pulse Brief</div>'.$this->buttons('Check contrast', false);
            case 'px-rem':
                return '<div class="dpb-controls dpb-controls-static"><label>Root font size (px)<input class="dpb-base" type="number" min="1" value="16"></label><label>Pixels<input class="dpb-px" type="number" step="0.01" value="16"></label><label>REM<input class="dpb-rem" type="number" step="0.001" value="1"></label></div><div class="dpb-note">Edit either Pixels or REM; the other value updates automatically.</div>';
            case 'social-image-size':
                return '<div class="dpb-controls dpb-controls-static"><label>Preset <select class="dpb-social-preset"><option value="1080x1350">Instagram portrait — 1080×1350</option><option value="1080x1080">Square post — 1080×1080</option><option value="1080x1920">Story / Reel — 1080×1920</option><option value="1200x630">Facebook / link share — 1200×630</option><option value="1200x627">LinkedIn landscape — 1200×627</option><option value="1200x1200">LinkedIn square — 1200×1200</option><option value="1584x396">LinkedIn cover — 1584×396</option><option value="1600x900">X landscape — 1600×900</option><option value="1280x720">YouTube thumbnail — 1280×720</option></select></label><label>Custom width<input class="dpb-width" type="number" min="1" placeholder="Optional"></label></div><div class="dpb-note">Presets are common working canvas sizes; social platforms can revise presentation/cropping behavior over time.</div>'.$this->buttons('Calculate size', false);
            case 'meta-generator':
                return '<div class="dpb-controls dpb-controls-static"><label class="dpb-wide">SEO title<input class="dpb-meta-title" type="text" maxlength="200"></label><label class="dpb-wide">Meta description<textarea class="dpb-meta-desc" rows="3"></textarea></label><label class="dpb-wide">Canonical URL<input class="dpb-canonical" type="url" placeholder="https://example.com/page/"></label><label>Robots <select class="dpb-robots"><option value="index,follow">index, follow</option><option value="noindex,follow">noindex, follow</option><option value="index,nofollow">index, nofollow</option><option value="noindex,nofollow">noindex, nofollow</option></select></label></div>'.$this->buttons('Generate meta tags', false);
            case 'open-graph':
                return '<div class="dpb-controls dpb-controls-static"><label class="dpb-wide">Title<input class="dpb-og-title" type="text"></label><label class="dpb-wide">Description<textarea class="dpb-og-desc" rows="3"></textarea></label><label>Type<select class="dpb-og-type"><option value="article">article</option><option value="website">website</option></select></label><label class="dpb-wide">Page URL<input class="dpb-og-url" type="url"></label><label class="dpb-wide">Image URL<input class="dpb-og-image" type="url"></label><label>Site name<input class="dpb-og-site" type="text" value="Digital Pulse Brief"></label></div>'.$this->buttons('Generate Open Graph tags', false);
            case 'word-counter':
                return '<div class="dpb-controls dpb-controls-static"><label class="dpb-wide">Text<textarea class="dpb-text" rows="12" placeholder="Paste or type text here..."></textarea></label></div><div class="dpb-live-stats"></div>';
            case 'character-counter':
                return '<div class="dpb-controls dpb-controls-static"><label class="dpb-wide">Text<textarea class="dpb-text" rows="12" placeholder="Paste or type text here..."></textarea></label></div><div class="dpb-live-stats"></div>';
        }
        return '';
    }

    private function buttons($primary = 'Process', $clear = true) {
        return '<div class="dpb-actions"><button type="button" class="dpb-process">'.esc_html($primary).'</button>'.($clear?'<button type="button" class="dpb-clear">Clear</button>':'').'</div>';
    }
}

DPB_Tools_Plugin::instance();
