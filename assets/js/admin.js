jQuery(document).ready(function($) {
    const $fileInput = $('#json-file-input');
    const $mappingSection = $('#mapping-section');
    const $jsonPreview = $('#json-preview pre');

    // Define field mappings with their preview elements
    const fieldMappings = [{
            select: $('#map-user-login'),
            preview: $('#preview-user-login'),
            transform: value => jucPersianToEnglishSlug(value)
        },
        {
            select: $('#map-user-email'),
            preview: $('#preview-user-email'),
            transform: (value, select) => {
                if (!select.val() && jsonData) {
                    // If no email field is selected, generate preview email
                    return generatePreviewEmail(
                        jsonData[0],
                        $('#map-first-name').val(),
                        $('#map-last-name').val()
                    );
                }
                return value;
            }
        },
        {
            select: $('#map-first-name'),
            preview: $('#preview-first-name')
        },
        {
            select: $('#map-last-name'),
            preview: $('#preview-last-name')
        }
    ];

    let jsonData = null;

    // Persian to English slug function
    function jucPersianToEnglishSlug(str) {
        const persian = {
            'آ': 'a', 'ا': 'a', 'ب': 'b', 'پ': 'p', 'ت': 't',
            'ث': 'th', 'ج': 'j', 'چ': 'ch', 'ح': 'h', 'خ': 'kh',
            'د': 'd', 'ذ': 'z', 'ر': 'r', 'ز': 'z', 'ژ': 'zh',
            'س': 's', 'ش': 'sh', 'ص': 's', 'ض': 'z', 'ط': 't',
            'ظ': 'z', 'ع': 'a', 'غ': 'gh', 'ف': 'f', 'ق': 'gh',
            'ک': 'k', 'گ': 'g', 'ل': 'l', 'م': 'm', 'ن': 'n',
            'و': 'v', 'ه': 'h', 'ی': 'y', 'ئ': 'y'
        };

        return str.split('').map(char => persian[char] || char)
            .join('')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    // Generate preview email function
    function generatePreviewEmail(jsonData, firstNameField, lastNameField) {
        const firstName = firstNameField ? jucPersianToEnglishSlug(jsonData[firstNameField] || '') : '';
        const lastName = lastNameField ? jucPersianToEnglishSlug(jsonData[lastNameField] || '') : '';
        const randomString = Math.random().toString(36).substring(2, 8);

        const parts = [firstName, lastName, randomString].filter(Boolean);
        return parts.join('.') + '@mailinator.com';
    }

    // Generate preview username function (new)
    function generatePreviewUsername(jsonData, firstNameField, lastNameField) {
        const firstName = firstNameField ? jucPersianToEnglishSlug(jsonData[firstNameField] || '') : '';
        const lastName = lastNameField ? jucPersianToEnglishSlug(jsonData[lastNameField] || '') : '';
        return [firstName, lastName].filter(Boolean).join('-');
    }

    // Update previews when selections change
    fieldMappings.forEach(mapping => {
        mapping.select.on('change', function() {
            if (jsonData) {
                if ($(this).val()) {
                    const value = jsonData[0][$(this).val()];
                    const displayValue = mapping.transform ? mapping.transform(value, this) : value;
                    mapping.preview.text(displayValue || '');
                } else {
                    // Special handling for email and username when no field is selected
                    if ($(this).attr('id') === 'map-user-email') {
                        mapping.preview.text(generatePreviewEmail(
                            jsonData[0],
                            $('#map-first-name').val(),
                            $('#map-last-name').val()
                        ));
                    } else if ($(this).attr('id') === 'map-user-login') {
                        mapping.preview.text(generatePreviewUsername(
                            jsonData[0],
                            $('#map-first-name').val(),
                            $('#map-last-name').val()
                        ));
                    } else {
                        mapping.preview.text('');
                    }
                }
            }
        });
    });

    // Update both email and username previews when first/last name changes
    $('#map-first-name, #map-last-name').on('change', function() {
        const $emailSelect = $('#map-user-email');
        const $usernameSelect = $('#map-user-login');

        if (jsonData) {
            // Update email preview if no email field selected
            if (!$emailSelect.val()) {
                $('#preview-user-email').text(generatePreviewEmail(
                    jsonData[0],
                    $('#map-first-name').val(),
                    $('#map-last-name').val()
                ));
            }

            // Update username preview if no username field selected
            if (!$usernameSelect.val()) {
                $('#preview-user-login').text(generatePreviewUsername(
                    jsonData[0],
                    $('#map-first-name').val(),
                    $('#map-last-name').val()
                ));
            }
        }
    });

    // File input change handler
    $fileInput.on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    jsonData = JSON.parse(e.target.result);
                    if (Array.isArray(jsonData) && jsonData.length > 0) {
                        $mappingSection.removeClass('hidden');
                        $jsonPreview.text(JSON.stringify(jsonData[0], null, 2));

                        const fields = Object.keys(jsonData[0]);

                        // Update select fields and clear previews
                        fieldMappings.forEach(mapping => {
                            mapping.select.html('<option value="">Select JSON field</option>');
                            fields.forEach(field => {
                                mapping.select.append(
                                    $('<option>', {
                                        value: field,
                                        text: field
                                    })
                                );
                            });
                            mapping.preview.text('');
                        });
                    }
                } catch (error) {
                    alert('Invalid JSON file');
                }
            };
            reader.readAsText(file);
        }
    });
});