<?php
if (!defined('ABSPATH')) {
    exit;
}
$ui_text = is_array($mmqs_config['ui_text'] ?? null) ? $mmqs_config['ui_text'] : [];
$loading_text = esc_html($ui_text['loading'] ?? '');
$lordicon_src = esc_url($ui_text['lordicon_src'] ?? '');
$primary_form_id = sanitize_text_field($mmqs_config['primary_form_id'] ?? '');
$secondary_form_id = sanitize_text_field($mmqs_config['secondary_form_id'] ?? '');
$render_form = function (string $form_id): string {
    if ($form_id === '') {
        return '';
    }
    if (function_exists('wpforms_display')) {
        ob_start();
        wpforms_display($form_id, false, false);
        return (string)ob_get_clean();
    }
    return do_shortcode('[wpforms id="' . esc_attr($form_id) . '"]');
};
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
<style>
    .wpforms-error-container p {
        font-size: 1.5rem !important;
        font-weight: bold;
        padding-bottom: 1rem;
        text-decoration: underline;
    }

    #wpforms-4331-field_102-container  {
        justify-items: center !important;
    }

    .wpforms-error {
        padding-bottom: 1rem;
        font-size: 1.5rem !important;
        font-weight: bold;
        text-decoration: underline;
        color: red !important;
    }

    /* Main Form Container */
    .callMeForm {
        display: flex !important;
        text-align: center;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Form 4333 - Initial Address Form */
    .requestForm {
        text-align: center;
    }

    .requestFormBtn {
        height: 5rem;
        width: 15rem !important;
        border-radius: 5px;
    }

    /* Form 4331 - Detailed Form Labels */
    .callMeForm label {
        min-height: 80px;
        margin-top: .5rem !important;
        height: auto !important;
        align-items: center !important;
        justify-content: center !important;
        display: flex !important;
        text-align: center !important;
    }

    /* Input Fields */
    .callMeForm input[type="text"],
    .callMeForm input[type="email"],
    .callMeForm input[type="tel"],
    .callMeForm input[type="number"],
    .callMeForm textarea {
        width: 100%;
        padding: 10px;
        color: black !important;
        margin: 20px 0;
        border: none;
        font-weight: 500;
        border-bottom: 1px solid #ddd;
        box-sizing: border-box;
        background-color: transparent !important;
    }

    /* Override autofill styles */
    .callMeForm input[type="text"]:-webkit-autofill,
    .callMeForm input[type="email"]:-webkit-autofill,
    .callMeForm input[type="tel"]:-webkit-autofill {
        -webkit-box-shadow: 0 0 0 1000px white inset !important;
        background-color: transparent !important;
    }

    .callMeForm input[type="text"]:focus,
    .callMeForm input[type="email"]:focus,
    .callMeForm input[type="tel"]:focus,
    .callMeForm input[type="number"]:focus,
    .callMeForm textarea:focus {
        background-color: transparent !important;
        outline: none;
    }

    /* Submit Button - Green */
    .callMeForm .wpforms-submit {
        background: #7ac242 !important;
        color: white !important;
        padding: 10px 20px !important;
        border: none !important;
        cursor: pointer !important;
        border-radius: 5px !important;
        height: 4em !important;
        width: 250px !important;
        font-size: 1em !important;
    }

    .wpforms-submit:hover {
        background: #5a9127 !important;
    }

    /* WPForms General Styles */
    .wpforms-container {
        text-align: center;
    }

    .wpforms-field {
        padding: 0;
        margin: 20px 0;
    }

    .wpforms-field-label {
        font-weight: 700 !important;
        padding-bottom: .25rem;
        padding-top: 1rem !important;
        font-size: 1em !important;
    }

    .wpforms-submit-container {
        width: 100%;
        display: flex;
        justify-content: center;
        height: 8rem !important;
        margin-top: 1rem;
    }

    /* Multi-step Form Navigation */
    .wpforms-page-indicator-progress {
        background-color: #7AC342 !important;
    }

    .wpforms-page-button {
        background-color: #464644 !important;
        padding: 10px 20px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        height: 4em !important;
        width: 15em !important;
    }

    .wpforms-page-next {
        height: 4em !important;
        width: 15em !important;
        border-radius: 5px;
        background-color: #464644 !important;
    }

    .wpforms-page-prev {
        height: 4em;
        width: 15em;
        opacity: .6;
        background: transparent !important;
        border: .2rem solid black !important;
        color: black !important;
    }

    .wpforms-pagebreak-center {
        display: flex !important;
        justify-content: space-between !important;
        width: 100% !important;
        gap: 20px !important;
    }

    /* FIXED: fastBtn - "No Thanks" and "Yes Sure" buttons side by side */
    .fastBtn ul {
        display: flex !important;
        flex-direction: row !important;
        justify-content: center !important;
        gap: 20px !important;
        list-style: none !important;
        padding: 0 !important;
    }

    .fastBtn li {
        display: inline-block !important;
        width: auto !important;
        max-width: 300px !important;
        margin-bottom: 0 !important;
    }

    .fastBtn input[type="radio"] {
        display: none !important;
    }

    .fastBtn label {
        display: block !important;
        padding: 20px 40px !important;
        border: 1px solid #7ac342 !important;
        background-color: #f8f4eb !important;
        border-radius: 15px !important;
        cursor: pointer !important;
        text-align: center !important;
        font-size: 1em !important;
        min-width: 200px !important;
        box-sizing: border-box !important;
    }

    .fastBtn .wpforms-selected label,
    .fastBtn input[type="radio"]:checked + label,
    .fastBtn li.wpforms-selected label {
        background-color: #7ac342 !important;
        color: white !important;
        border-radius: 15px !important;
        padding: 20px 40px !important;
        min-width: 200px !important;
        box-sizing: border-box !important;
    }

    /* Custom Checkbox/Radio Styles - Field 95 and others */
    .custom-checkbox-list ul {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 20px !important;
        list-style: none !important;
        padding: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
    }

    .custom-checkbox-list li {
        display: inline-block !important;
        max-width: 200px !important;
        width: auto !important;
        flex: 0 0 auto !important;
        margin-bottom: 8px !important;
    }

    .custom-checkbox-list li input[type="checkbox"],
    .custom-checkbox-list li input[type="radio"] {
        display: none !important;
    }

    .custom-checkbox-list li label {
        display: block !important;
        padding: 10px 20px !important;
        border: 1px solid #7ac342 !important;
        background-color: #f8f4eb !important;
        color: #333 !important;
        text-align: center !important;
        font-size: 1em !important;
        cursor: pointer !important;
        border-radius: 15px !important;
        min-width: 150px !important;
        height: 100% !important;
        box-sizing: border-box !important;
    }

    .custom-checkbox-list li input[type="checkbox"]:checked + label,
    .custom-checkbox-list li input[type="radio"]:checked + label,
    .custom-checkbox-list li.wpforms-selected label {
        background-color: #7ac342 !important;
        color: white !important;
        border-radius: 15px !important;
        padding: 10px 20px !important;
        min-width: 150px !important;
        box-sizing: border-box !important;
    }

    .custom-checkbox-list .wpforms-selected label {
        background-color: #7ac342 !important;
        color: white !important;
        border-radius: 15px !important;
        padding: 10px 20px !important;
        box-sizing: border-box !important;
    }

    /* Custom Checkbox List 2 (Radio buttons with circles) */
    .custom-checkbox-list2 ul {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        justify-content: center !important;
        gap: 20px !important;
        list-style: none !important;
        padding: 0 !important;
        width: 100% !important;
    }

    .custom-checkbox-list2 li {
        display: inline-block !important;
        max-width: 250px !important;
        width: auto !important;
        flex: 0 0 auto !important;
    }

    .custom-checkbox-list2 input[type='radio'] {
        display: none !important;
    }

    .custom-checkbox-list2 .wpforms-field-label-inline {
        display: flex !important;
        align-items: center !important;
        padding: 10px 20px !important;
        border: 1px solid #7ac342 !important;
        background-color: #f8f4eb !important;
        border-radius: 15px !important;
        cursor: pointer !important;
        min-width: 150px !important;
        box-sizing: border-box !important;
    }

    .custom-checkbox-list2 .wpforms-field-label-inline:before {
        content: '';
        display: inline-block;
        margin-right: 10px;
        width: 2rem !important;
        height: 2rem !important;
        border: 2px solid #7ac342;
        border-radius: 50%;
        background-color: white;
        vertical-align: middle;
        transition: background-color 0.2s ease-in-out;
        flex-shrink: 0;
    }

    .custom-checkbox-list2 input[type='radio']:checked + label,
    .custom-checkbox-list2 li.wpforms-selected label {
        background-color: #7ac342 !important;
        color: white !important;
        border-radius: 15px !important;
        padding: 10px 20px !important;
        box-sizing: border-box !important;
    }

    .custom-checkbox-list2 input[type='radio']:checked + label:before,
    .custom-checkbox-list2 li.wpforms-selected label:before {
        background-color: white !important;
        border-color: white !important;
    }

    .custom-checkbox-list2 .wpforms-selected label {
        background-color: #7ac342 !important;
        color: white !important;
        border-radius: 15px !important;
        padding: 10px 20px !important;
        box-sizing: border-box !important;
    }

    /* Not Sure Checkbox */
    .notSure > ul {
        display: flex !important;
        justify-content: center !important;
    }

    .notSure ul li {
        max-width: 300px !important;
    }

    .notSure > ul > li > label:before {
        display: none !important;
    }

    .notSure .wpforms-field-label-inline:before {
        display: none !important;
    }

    .notSure .wpforms-field-label-inline {
        border-radius: 15px !important;
    }

    .notSure input[type='checkbox']:checked + label {
        border-radius: 15px !important;
    }

    /* FIXED: Counter Buttons (Bedrooms, Baths, etc.) - Green squares */
    .counter {
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 5px 10px !important;
        border-radius: 5px !important;
        margin: 10px auto !important;
    }

    .counter-button,
    .counter-button.minus,
    .counter-button.plus,
    .counter-button.minus-bath,
    .counter-button.plus-bath,
    .counter-button.minus-full-bath,
    .counter-button.plus-full-bath,
    .counter-button.minus-floor,
    .counter-button.plus-floor,
    .counter-button.minus-carpet,
    .counter-button.plus-carpet,
    .counter-button.minus-live,
    .counter-button.plus-live,
    .counter-button.minus-pet,
    .counter-button.plus-pet {
        background-color: #7ac342 !important;
        border: none !important;
        color: white !important;
        font-size: 2rem !important;
        font-weight: bold !important;
        cursor: pointer !important;
        padding: 0 !important;
        width: 60px !important;
        height: 60px !important;
        border-radius: 5px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        line-height: 1 !important;
    }

    .counter-button:hover {
        background-color: #5a9127 !important;
    }

    .counter-value,
    .counter-value-bath,
    .counter-value-full-bath,
    .counter-value-floor,
    .counter-value-carpet,
    .counter-value-live,
    .counter-value-pet,
    #counterValue,
    #counterBathValue,
    #counterFullBathValue,
    #counterFloorValue,
    #counterCarpetValue,
    #counterLiveValue,
    #counterPetValue {
        margin: 0 20px !important;
        color: #7ac342 !important;
        text-align: center !important;
        font-size: 1.5rem !important;
        font-weight: bold !important;
        min-width: 50px !important;
        width: 50px !important;
        border: none !important;
        background: transparent !important;
        display: inline-block !important;
        vertical-align: middle !important;
    }

    /* Fix counter container alignment */
    .button-container {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Hidden Fields for Counters */
    .hiddenBedrooms,
    .hiddenhalfBath,
    .hiddenFullBath,
    .hiddenLive,
    .hiddenPet,
    .hiddenFloors,
    .hiddenPeople,
    .hiddenCarpet {
        display: none !important;
    }

    /* SVG Icons */
    .bed, .sink {
        margin-top: 2rem;
        width: auto !important;
        height: 7rem;
    }

    .pet {
        height: 6rem !important;
        width: auto;
        stroke: #7ac342;
    }

    /* Square Footage Input */
    .enterSq {
        display: grid;
        justify-content: center;
        text-align: center;
    }

    .enterSq input[type="number"],
    .enterSq > input {
        width: 100% !important;
        max-width: 400px;
        border-radius: 10px !important;
        border: .3rem solid #f5f5dc !important;
        text-align: center;
        background-color: white !important;
        padding: 10px !important;
    }

    /* Dividers */
    .divider1 {
        margin: 1rem 0;
        border-top: 1px dashed silver;
        opacity: .5;
    }

    .linedDivide {
        border-top: 1px solid silver !important;
    }

    /* Special Field Styles */
    .needsInput > textarea {
        text-align: center;
        background: #ededed !important;
        border: 1px solid silver !important;
    }

    /* International Phone Input */
    .iti {
        padding: 0 !important;
        margin: 0 !important;
    }

    .iti__selected-country {
        margin-top: 0px !important;
        padding: 0px !important;
    }

    /* Fix email field alignment with phone field */
    .callMeForm div[data-field-id="79"],
    .callMeForm div[data-field-id="80"] {
        display: flex !important;
        flex-direction: column !important;
        justify-content: flex-start !important;
        align-items: stretch !important;
    }

    .callMeForm div[data-field-id="79"] label,
    .callMeForm div[data-field-id="80"] label {
        margin: 0 !important;
        padding: 0 !important;
    }

    .callMeForm div[data-field-id="79"] input,
    .callMeForm div[data-field-id="80"] input {
        margin-top: 20px !important;
    }

    /* Ensure layout columns in field 153 are aligned at top */
    .callMeForm div[data-field-id="153"] .wpforms-layout-column {
        align-items: stretch !important;
    }

    /* Mobile Responsive */
    @media only screen and (max-width: 768px) {
        .bed, .sink {
            display: none !important;
        }

        .custom-checkbox-list ul,
        .custom-checkbox-list2 ul,
        .fastBtn ul {
            flex-direction: column !important;
            align-items: center !important;
        }

        .custom-checkbox-list li,
        .custom-checkbox-list2 li,
        .fastBtn li {
            width: 100% !important;
            max-width: 300px !important;
        }

        .callMeForm label {
            height: auto !important;
        }

        div[data-field-id="72"],
        div[data-field-id="139"],
        div[data-field-id="104"] {
            display: none !important;
        }

        .wpforms-page-button {
            height: auto !important;
            width: 100% !important;
            max-width: 15rem !important;
        }

        .wpforms-pagebreak-center {
            flex-direction: column !important;
            gap: 10px !important;
        }

        /* Fix mobile layout overflow */
        .callMeForm .wpforms-field-layout-columns {
            flex-direction: column !important;
            gap: 0 !important;
        }

        .callMeForm .wpforms-layout-column {
            max-width: 100% !important;
            width: 100% !important;
        }

        .callMeForm div[data-field-id="75"] .wpforms-field-layout-columns,
        .callMeForm div[data-field-id="153"] .wpforms-field-layout-columns {
            flex-direction: column !important;
        }

        .callMeForm {
            padding: 10px !important;
        }

        body {
            overflow-x: hidden !important;
        }

        .wpforms-container {
            max-width: 100% !important;
            overflow-x: hidden !important;
        }
    }

    /* Fix for initial address form to not inherit callMeForm styles */
    #wpforms-form-4333 label {
        min-height: auto !important;
        height: auto !important;
        display: block !important;
        align-items: normal !important;
        justify-content: normal !important;
    }

    #wpforms-form-4333 .wpforms-field {
        text-align: left !important;
    }

    /* Ensure proper WPForms selected state */
    .wpforms-selected {
        background: #7AC342 !important;
        border: 1px solid #7ac342 !important;
        border-radius: 15px !important;
    }

    .wpforms-selected > label {
        color: white !important;
        border-radius: 15px !important;
    }

    /* Additional input field fixes for WPForms */
    .callMeForm .wpforms-field input,
    .callMeForm .wpforms-field textarea {
        background-color: transparent !important;
    }

    .callMeForm .wpforms-field input:not([type="radio"]):not([type="checkbox"]):not([type="submit"]),
    .callMeForm .wpforms-field textarea {
        background-color: transparent !important;
        background-image: none !important;
    }

    /* Fix layout columns to be properly centered */
    .callMeForm .wpforms-field-layout-columns {
        display: flex !important;
        justify-content: center !important;
        align-items: stretch !important;
        gap: 40px !important;
        width: 100% !important;
    }

    .callMeForm .wpforms-layout-column {
        flex: 1 1 0 !important;
        max-width: 350px !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: flex-start !important;
    }

    /* Special handling for name and contact layout - first step fields */
    .callMeForm div[data-field-id="75"] .wpforms-field-layout-columns,
    .callMeForm div[data-field-id="153"] .wpforms-field-layout-columns {
        gap: 20px !important;
    }

    .callMeForm div[data-field-id="75"] .wpforms-layout-column,
    .callMeForm div[data-field-id="153"] .wpforms-layout-column {
        flex: 1 !important;
        max-width: 100% !important;
        align-items: stretch !important;
    }

    .callMeForm div[data-field-id="75"] .wpforms-field,
    .callMeForm div[data-field-id="153"] .wpforms-field {
        width: 100% !important;
    }

    /* Ensure pet question and other radio fields take full width */
    .callMeForm div[data-field-id="105"],
    .callMeForm div[data-field-id="64"],
    .callMeForm div[data-field-id="65"],
    .callMeForm div[data-field-id="95"],
    .callMeForm div[data-field-id="81"] {
        max-width: 100% !important;
        width: 100% !important;
    }

    .callMeForm div[data-field-id="105"] .custom-checkbox-list,
    .callMeForm div[data-field-id="64"] .custom-checkbox-list,
    .callMeForm div[data-field-id="65"] .custom-checkbox-list,
    .callMeForm div[data-field-id="95"] .custom-checkbox-list {
        max-width: 100% !important;
        width: 100% !important;
    }

    /* Ensure HTML fields in layouts are centered */
    .callMeForm .wpforms-field-html {
        text-align: center !important;
        width: 100% !important;
    }

    .callMeForm .wpforms-field-html h3 {
        text-align: center !important;
        margin-bottom: 1rem !important;
        min-height: 60px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    /* Global fix for all selected button border-radius */
    .callMeForm .wpforms-selected label,
    .callMeForm input[type="radio"]:checked + label,
    .callMeForm input[type="checkbox"]:checked + label {
        border-radius: 15px !important;
    }

    .callMeForm li.wpforms-selected label {
        border-radius: 15px !important;
    }

    /* Placeholder styles - MUST be at end */
    .wpforms-field input::placeholder,
    .wpforms-field textarea::placeholder,
    input[name*="wpforms"]::placeholder,
    textarea[name*="wpforms"]::placeholder {
        font-style: italic !important;
        color: #000000 !important;
        font-weight: 500 !important;
        opacity: 1 !important;
    }

    .wpforms-field input::-webkit-input-placeholder,
    .wpforms-field textarea::-webkit-input-placeholder,
    input[name*="wpforms"]::-webkit-input-placeholder,
    textarea[name*="wpforms"]::-webkit-input-placeholder {
        font-style: italic !important;
        color: #000000 !important;
        font-weight: 500 !important;
        opacity: 1 !important;
    }

    .wpforms-field input::-moz-placeholder,
    .wpforms-field textarea::-moz-placeholder,
    input[name*="wpforms"]::-moz-placeholder,
    textarea[name*="wpforms"]::-moz-placeholder {
        font-style: italic !important;
        color: #000000 !important;
        font-weight: 500 !important;
        opacity: 1 !important;
    }

    .wpforms-field input:-ms-input-placeholder,
    .wpforms-field textarea:-ms-input-placeholder,
    input[name*="wpforms"]:-ms-input-placeholder,
    textarea[name*="wpforms"]:-ms-input-placeholder {
        font-style: italic !important;
        color: #000000 !important;
        font-weight: 500 !important;
        opacity: 1 !important;
    }
</style>

<div id="postal-check-container">
    <div id="postal-error" class="error-message">
        Postal Code not found. Please try another one. (ex. T6N or T6N 1B7)
    </div>
    <?php wp_nonce_field('check_postal_code', 'postal_code_nonce'); ?>

    <div id="mmqs-forms">
        <?php
        if ($primary_form_id !== '') {
            $primary_output = $render_form($primary_form_id);
            echo $primary_output !== '' ? $primary_output : '<!-- MMQS: primary form rendered empty -->';
        } else {
            echo '<p>Primary form ID missing.</p>';
        }

        if ($secondary_form_id !== '') {
            $secondary_output = $render_form($secondary_form_id);
            echo $secondary_output !== '' ? $secondary_output : '<!-- MMQS: secondary form rendered empty -->';
        } else {
            echo '<p>Secondary form ID missing.</p>';
        }
        ?>
    </div>

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
        <?php if ($lordicon_src !== '') : ?>
            <div class="ha-lordicon-wrapper">
                <lord-icon
                    src="<?php echo $lordicon_src; ?>"
                    trigger="loop" stroke="20" target=".ha-lordicon-wrapper"
                    colors="primary:#121331,secondary:#08a88a,tertiary:#0816A8,quaternary:#2CA808"
                    style="width:150px;height:150px">
                </lord-icon>
            </div>
        <?php endif; ?>
        <div class="loading-text"><?php echo $loading_text !== '' ? $loading_text : 'Loading...'; ?></div>
    </div>
</div>
