<?php
/*
Plugin Name: JSON User Creator
Description: Create WordPress users from a JSON file
Version: 1.0
Author: Chalist
*/

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Add menu item to WordPress admin
function juc_add_admin_menu()
{
    add_menu_page(
        'JSON User Creator',
        'JUC',
        'manage_options',
        'json-user-creator',
        'juc_admin_page',
        'dashicons-groups',
        30
    );
}
add_action('admin_menu', 'juc_add_admin_menu');

// Add this new combined enqueue function
function juc_enqueue_assets($hook)
{
    // Only load on our plugin page
    if ($hook != 'toplevel_page_json-user-creator') {
        return;
    }

    // Enqueue our custom CSS with basic styles
    wp_enqueue_style(
        'juc-admin-styles',
        plugins_url('assets/css/admin.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin.css')
    );

    // Enqueue our custom JavaScript
    wp_enqueue_script(
        'juc-admin-script',
        plugins_url('assets/js/admin.js', __FILE__),
        array('jquery'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/admin.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'juc_enqueue_assets');

// Add this near the top of the file, after the initial checks
add_action('admin_init', 'juc_handle_form_submission');

function juc_handle_form_submission()
{
    // Check if our form was submitted
    if (!isset($_POST['submit_json_users'])) {
        return;
    }

    // Verify user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    // Check if file was uploaded
    if (!isset($_FILES['json_file']) || $_FILES['json_file']['error'] !== UPLOAD_ERR_OK) {
        add_settings_error(
            'json_user_creator',
            'no_file',
            'No file was uploaded or there was an upload error.',
            'error'
        );
        return;
    }

    // Read and parse JSON file
    $json_content = file_get_contents($_FILES['json_file']['tmp_name']);
    $users_data = json_decode($json_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        add_settings_error(
            'json_user_creator',
            'invalid_json',
            'Invalid JSON file format.',
            'error'
        );
        return;
    }

    $should_update_authors = isset($_POST['update_authors']) && $_POST['update_authors'] === 'on';
    $created = 0;
    $errors = 0;
    $updated_posts = 0;

    foreach ($users_data as $user) {
        // Generate username from first and last name
        $username = sanitize_user(
            juc_persian_to_english_slug($user['first_name'] . ' ' . $user['last_name'])
        );

        // Check if user exists (either by username or a variant of it)
        $existing_user_id = null;
        $base_username = $username;
        $counter = 1;

        // First try exact username
        $existing_user = get_user_by('login', $username);
        if ($existing_user) {
            $existing_user_id = $existing_user->ID;
        } else {
            // Try variants with numbers (username1, username2, etc.)
            while ($counter <= 10) { // Limit search to 10 variants
                $variant_username = $base_username . $counter;
                $existing_user = get_user_by('login', $variant_username);
                if ($existing_user) {
                    $existing_user_id = $existing_user->ID;
                    break;
                }
                $counter++;
            }
        }

        if ($existing_user_id) {
            // User exists, only update post authors if needed
            if ($should_update_authors && isset($user['post_id'])) {
                $post_ids = is_array($user['post_id']) ? $user['post_id'] : array($user['post_id']);

                foreach ($post_ids as $post_id) {
                    $post = get_post($post_id);
                    if ($post) {
                        $update_result = wp_update_post(array(
                            'ID' => $post_id,
                            'post_author' => $existing_user_id
                        ));

                        if ($update_result) {
                            $updated_posts++;
                        }
                    }
                }
            }
        } else {
            // User doesn't exist, create new user
            // Generate email
            $email = $username . '@mailinator.com';

            // Determine role based on type
            $role = 'subscriber'; // default role
            if (isset($user['type'])) {
                switch (strtolower($user['type'])) {
                    case 'author':
                        $role = 'author';
                        break;
                    case 'both':
                        $role = 'author';
                        break;
                    case 'translator':
                        $role = 'editor';
                        break;
                }
encoding:             }

            // Create user
            $userdata = array(
                'user_login'    => $username,
                'user_email'    => $email,
                'user_pass'     => wp_generate_password(),
                'role'          => $role,
                'first_name'    => sanitize_text_field($user['first_name']),
                'last_name'     => sanitize_text_field($user['last_name']),
                'display_name'  => sanitize_text_field($user['first_name'] . ' ' . $user['last_name'])
            );

            $user_id = wp_insert_user($userdata);

            if (is_wp_error($user_id)) {
                $errors++;
            } else {
                $created++;

                // Handle post IDs for new users too
                if ($should_update_authors && isset($user['post_id'])) {
                    $post_ids = is_array($user['post_id']) ? $user['post_id'] : array($user['post_id']);

                    foreach ($post_ids as $post_id) {
                        $post = get_post($post_id);
                        if ($post) {
                            $update_result = wp_update_post(array(
                                'ID' => $post_id,
                                'post_author' => $user_id
                            ));

                            if ($update_result) {
                                $updated_posts++;
                            }
                        }
                    }
                }
            }
        }
    }

    // Update success/error message to include post updates and skipped users
    if ($created > 0 || $updated_posts > 0) {
        $message = sprintf(
            'Created %d new users. Errors: %d.',
            $created,
            $errors
        );

        if ($should_update_authors) {
            $message .= sprintf(' Updated authors for %d posts.', $updated_posts);
        }

        add_settings_error(
            'json_user_creator',
            'users_created',
            $message,
            'success'
        );
    } else {
        add_settings_error(
            'json_user_creator',
            'no_users_created',
            'No new users were created. All users already exist in the system.',
            'info'
        );
    }
}

// Create the admin page
function juc_admin_page()
{
    // Add Tailwind CSS CDN
    wp_enqueue_style('tailwindcss', 'https://cdn.tailwindcss.com');

    // Add Tailwind configuration script
?>
    <script>
        tailwind.config = {
            prefix: '', // Add prefix to avoid conflicts
            corePlugins: {
                preflight: false, // Disable Tailwind's reset
            }
        }
    </script>

    <div class="wrap">
        <h1 class="text-3xl font-bold mb-6">JSON User Creator</h1>

        <?php
        // Display settings errors/messages
        $settings_errors = get_settings_errors('json_user_creator');
        foreach ($settings_errors as $error) {
            $class = $error['type'] === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800';
        ?>
            <div class="rounded-md p-4 mb-4 <?php echo $class; ?>">
                <p class="text-sm"><?php echo esc_html($error['message']); ?></p>
            </div>
        <?php
        }
        ?>

        <div class="main-container">
            <form method="post" enctype="multipart/form-data" class="space-y-4">
                <!-- File Input Section -->
                <div class="space-y-2">

                    <div class="flex items-center space-x-4">
                        <input type="file"
                            name="json_file"
                            accept=".json"
                            required
                            id="json-file-input"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>
                </div>

                <!-- Mapping Section -->
                <div id="mapping-section" class="hidden mt-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Username Field -->
                        <div class="form-input ">
                            <label class="block text-sm font-medium text-gray-700">Username</label>
                            <select name="map_user_login" id="map-user-login" class="">
                                <option value=""> - </option>
                            </select>
                            <div class="juc-preview">
                                Preview: <span id="preview-user-login" class="preview"></span>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="form-input">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <select name="map_user_email" id="map-user-email" class="">
                                <option value=""> - </option>
                            </select>
                            <div class="juc-preview">
                                Preview: <span id="preview-user-email" class="preview"></span>
                            </div>
                        </div>

                        <!-- First Name Field -->
                        <div class="form-input">
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <select name="map_first_name" id="map-first-name" class="">
                                <option value=""> - </option>
                            </select>
                            <div class="juc-preview">
                                Preview: <span id="preview-first-name" class="preview"></span>
                            </div>
                        </div>

                        <!-- Last Name Field -->
                        <div class="form-input">
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <select name="map_last_name" id="map-last-name" class="">
                                <option value=""> - </option>
                            </select>
                            <div class="juc-preview">
                                Preview: <span id="preview-last-name" class="preview"></span>
                            </div>
                        </div>
                    </div>

                    <!-- JSON Preview -->
                    <div id="json-preview" class="">
                        <pre class=""></pre>
                    </div>

                    <!-- Add this before the Submit Button -->
                    <div class="update_user_authors">
                        <div class="flex items-start">
                            <div class="flex items-center">
                                <input type="checkbox"
                                    id="update_authors"
                                    name="update_authors">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="update_authors">Update Post Authors</label>
                                <p>If checked, post authors will be updated based on the post_ids in the JSON data</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-6">
                        <button type="submit" name="submit_json_users" class="submit-btn ">Create Users</button>
                    </div>
                </div>
            </form>

            <div class="doc">
                <p>
                <h2>JSON User Creator Documentation</h2>

                <h3>Overview</h3>
                <p>JSON User Creator allows you to bulk create WordPress users from a JSON file. It supports mapping JSON fields to user attributes and handles Persian/Arabic text conversion.</p>

                <h3>How to Use</h3>
                <ol class="list-decimal">
                    <li class="mb-2">Upload a JSON file containing user data</li>
                    <li class="mb-2">Map JSON fields to WordPress user fields (Username, Email, First Name, Last Name)</li>
                    <li class="mb-2">Preview the mapped data in real-time</li>
                    <li class="mb-2">Optionally enable post author updates</li>
                    <li class="mb-2">Click "Create Users" to process the import</li>
                </ol>

                <h3>Features</h3>
                <ul class="list-disc">
                    <li class="mb-2">Automatic Persian/Arabic to English conversion for usernames</li>
                    <li class="mb-2">Automatic email generation if not provided</li>
                    <li class="mb-2">Real-time preview of converted data</li>
                    <li class="mb-2">Post author update capability</li>
                    <li class="mb-2">Duplicate username handling</li>
                </ul>

                <h3>JSON Format</h3>
                <p>Your JSON file should be an array of objects containing user data. Example:</p>
                <pre>
[
    {
        "name": "John Doe",
        "email": "john@example.com",
        "first_name": "John",
        "last_name": "Doe",
        "post_id": [12, 121, 332, 45]
    }
]</pre>
                </p>
            </div>
        </div>
    </div>

<?php
}

// Add this function to handle Persian to English transliteration
function juc_persian_to_english_slug($string)
{
    // Persian characters mapping
    $persian_chars = array(
        'آ' => 'a',
        'ا' => 'a',
        'ب' => 'b',
        'پ' => 'p',
        'ت' => 't',
        'ث' => 'th',
        'ج' => 'j',
        'چ' => 'ch',
        'ح' => 'h',
        'خ' => 'kh',
        'د' => 'd',
        'ذ' => 'z',
        'ر' => 'r',
        'ز' => 'z',
        'ژ' => 'zh',
        'س' => 's',
        'ش' => 'sh',
        'ص' => 's',
        'ض' => 'z',
        'ط' => 't',
        'ظ' => 'z',
        'ع' => 'a',
        'غ' => 'gh',
        'ف' => 'f',
        'ق' => 'gh',
        'ک' => 'k',
        'گ' => 'g',
        'ل' => 'l',
        'م' => 'm',
        'ن' => 'n',
        'و' => 'v',
        'ه' => 'h',
        'ی' => 'y',
        'ئ' => 'y',
        // Additional Persian/Arabic characters
        'ً' => '',
        'ٌ' => '',
        'ٍ' => '',
        'َ' => '',
        'ُ' => '',
        'ِ' => '',
        'ّ' => '',
        'ة' => 'h',
        '' => 'v'
    );

    // Convert Persian numbers to English
    $persian_numbers = array(
        '۰' => '0',
        '۱' => '1',
        '۲' => '2',
        '۳' => '3',
        '۴' => '4',
        '۵' => '5',
        '۶' => '6',
        '۷' => '7',
        '۸' => '8',
        '۹' => '9'
    );

    // Convert to lowercase and trim
    $string = mb_strtolower($string, 'UTF-8');
    $string = trim($string);

    // Replace Persian characters with English equivalents
    $string = str_replace(array_keys($persian_chars), array_values($persian_chars), $string);
    $string = str_replace(array_keys($persian_numbers), array_values($persian_numbers), $string);

    // Replace spaces with hyphens and remove other special characters
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    $string = trim($string, '-');

    return $string;
}

// Add this helper function at the top of the file
function juc_get_role_from_type($user_type)
{
    $user_type = strtolower(trim($user_type));
    switch ($user_type) {
        case 'author':
            return 'author';
        case 'translator':
            return 'editor';
        default:
            return 'subscriber'; // Default fallback role
    }
}

// Update the process function to use the slugify function
function juc_process_json_file()
{
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    if (!isset($_FILES['json_file'])) {
        echo '<div class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg"><p>No file uploaded</p></div>';
        return;
    }

    $file_content = file_get_contents($_FILES['json_file']['tmp_name']);
    $users = json_decode($file_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '<div class="mt-4 p-4 bg-red-100 text-red-700 rounded-lg"><p>Invalid JSON file</p></div>';
        return;
    }

    $created = 0;
    $errors = 0;

    // Get the field mappings
    $map_user_login = $_POST['map_user_login'];
    $map_user_email = $_POST['map_user_email'];
    $map_first_name = $_POST['map_first_name'];
    $map_last_name = $_POST['map_last_name'];
    $default_role = $_POST['default_role'];

    // Handle custom role creation
    if (empty($default_role) && !empty($_POST['custom_role_name'])) {
        $role_name = sanitize_text_field($_POST['custom_role_name']);
        $role_slug = sanitize_title($role_name);

        // Create the role if it doesn't exist
        if (!role_exists($role_slug)) {
            // Get selected capabilities
            $capabilities = array();
            if (!empty($_POST['custom_role_caps']) && is_array($_POST['custom_role_caps'])) {
                foreach ($_POST['custom_role_caps'] as $cap) {
                    $capabilities[sanitize_text_field($cap)] = true;
                }
            }

            // Add the role
            add_role($role_slug, $role_name, $capabilities);
        }

        $default_role = $role_slug;
    }

    foreach ($users as $user) {
        if (!isset($user[$map_user_login])) {
            $errors++;
            continue;
        }

        // Create username from Persian name
        $username = juc_persian_to_english_slug($user[$map_user_login]);

        // If username already exists, append a number
        $base_username = $username;
        $counter = 1;
        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }

        // Generate email if not provided
        $user_email = '';
        if (!empty($map_user_email) && isset($user[$map_user_email])) {
            $user_email = sanitize_email($user[$map_user_email]);
        } else {
            // Get first and last names (use empty string if not set)
            $first_name = isset($user[$map_first_name]) ? juc_persian_to_english_slug($user[$map_first_name]) : '';
            $last_name = isset($user[$map_last_name]) ? juc_persian_to_english_slug($user[$map_last_name]) : '';

            // Generate random string
            $random_string = substr(md5(uniqid()), 0, 6);

            // Construct email
            $email_parts = array_filter([$first_name, $last_name, $random_string]);
            $user_email = implode('.', $email_parts) . '@mailinator.com';
        }

        // Determine role based on user type
        $role = $default_role; // Keep default role as fallback
        if (isset($user['type'])) {
            $role = juc_get_role_from_type($user['type']);
        }

        $userdata = array(
            'user_login'    => $username,
            'user_email'    => $user_email,
            'user_pass'     => wp_generate_password(),
            'role'          => $role,
            'first_name'    => isset($user[$map_first_name]) ? sanitize_text_field($user[$map_first_name]) : '',
            'last_name'     => isset($user[$map_last_name]) ? sanitize_text_field($user[$map_last_name]) : ''
        );

        $user_id = wp_insert_user($userdata);

        if (is_wp_error($user_id)) {
            $errors++;
        } else {
            $created++;
        }
    }

    echo '<div class="mt-4 p-4 bg-green-100 text-green-700 rounded-lg">
            <p>Created ' . $created . ' users. Errors: ' . $errors . '</p>
          </div>';
}

// Helper function to check if role exists
function role_exists($role)
{
    if (!empty($role)) {
        return get_role($role) !== null;
    }
    return false;
}