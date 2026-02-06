<?php
if (!defined('ABSPATH')) {
    exit;
}
$ui_text = is_array($mmqs_config['ui_text'] ?? null) ? $mmqs_config['ui_text'] : [];
$loading_text = esc_html($ui_text['loading'] ?? 'Finding your neighbourhood - hang tight!');
$lordicon_src = esc_url($ui_text['lordicon_src'] ?? 'https://cdn.lordicon.com/tdrtiskw.json');
?>
<script src="https://cdn.lordicon.com/lordicon.js"></script>
<style>
    .error-message {
        color: #dc2626 !important;
        margin-bottom: 10px !important;
        font-size: 14px !important;
        display: none;
        padding: 8px !important;
        border-radius: 4px !important;
        background-color: #fef2f2 !important;
        border: 1px solid #fee2e2 !important;
    }

    .error-message.show {
        display: block;
    }

    .form-title {}

    .wpforms-field-address-postal[readonly],
    .wpforms-field-address input[readonly],
    .wpforms-field-address input[disabled] {
        background-color: #f8f8f8 !important;
        cursor: not-allowed !important;
        color: #666666 !important;
    }

    .start-over-link {
        font-size: 12px !important;
        color: #0073aa !important;
        text-decoration: underline !important;
        cursor: pointer !important;
        margin-top: 5px !important;
        display: block !important;
        text-align: left !important;
    }

    .wpforms-field-address-postal[readonly] {
        background-color: #f8f8f8 !important;
        cursor: not-allowed !important;
    }

    #loading-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(255, 255, 255, 0.95) !important;
        z-index: 999999 !important;
        display: none;
        justify-content: center !important;
        align-items: center !important;
    }

    .loading-content {
        text-align: center !important;
        background: white !important;
        padding: 30px !important;
        border-radius: 10px !important;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1) !important;
    }

    .ha-lordicon-wrapper {
        margin-bottom: 20px !important;
    }

    .loading-text {
        color: #333333 !important;
        font-size: 20px !important;
        font-weight: 700 !important;
        font-family: "proxima nova", Sans-serif !important;
    }

    #option-buttons {
        display: none;
        text-align: center;
        margin: 40px 0;
    }

    .quote-buttons {
        display: flex !important;
        justify-content: center !important;
        gap: 80px !important;
        margin: 30px 0 !important;
    }

    .quote-btn {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        background: #f0fdf4 !important;
        border-radius: 100% !important;
        width: 180px !important;
        height: 180px !important;
        border: none !important;
        cursor: pointer !important;
        padding: 20px !important;
        transition: all 0.3s ease !important;
    }

    .quote-btn.active {
        background: #4caf50 !important;
    }

    .quote-btn.inactive {
        opacity: 0.5 !important;
    }

    .quote-btn svg {
        margin-bottom: 20px !important;
        stroke: #2e7d32 !important;
    }

    .quote-btn.active svg {
        stroke: white !important;
    }

    .quote-btn span {
        color: #2e7d32 !important;
        font-size: 18px !important;
        font-weight: 500 !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
    }

    .quote-btn.active span {
        color: white !important;
    }
</style>

<div id="postal-check-container">
    <div id="postal-error" class="error-message">
        Postal Code not found. Please try another one. (ex. T6N or T6N 1B7)
    </div>
    <?php wp_nonce_field('check_postal_code', 'postal_code_nonce'); ?>

    <div id="option-buttons" style="display: none;">
        <h2>How would you like to get a quote?</h2>
        <div class="quote-buttons">
            <button id="quick-form" class="quote-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                <span>Book Online</span>
            </button>

            <button id="detailed-form" class="quote-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="45" height="45" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path
                        d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
                </svg>
                <span>Call Me</span>
            </button>
        </div>
    </div>
</div>

<div id="loading-overlay">
    <div class="loading-content">
        <div class="ha-lordicon-wrapper">
            <lord-icon
                src="<?php echo $lordicon_src; ?>"
                trigger="loop" stroke="20" target=".ha-lordicon-wrapper"
                colors="primary:#121331,secondary:#08a88a,tertiary:#0816A8,quaternary:#2CA808"
                style="width:150px;height:150px">
            </lord-icon>
        </div>
        <div class="loading-text"><?php echo $loading_text; ?></div>
    </div>
</div>
