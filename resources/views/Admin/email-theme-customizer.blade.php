<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMIS Email Theme Customizer</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .section h3 {
            margin-top: 0;
            color: #374151;
        }
        .color-picker {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .color-picker label {
            width: 150px;
            margin-right: 15px;
            font-weight: 500;
        }
        .color-picker input[type="color"] {
            width: 50px;
            height: 35px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .color-picker input[type="text"] {
            margin-left: 10px;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            width: 80px;
        }
        .toggle {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .toggle label {
            margin-left: 10px;
            font-weight: 500;
        }
        .preview-btn {
            background: #059669;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
        }
        .preview-btn:hover {
            background: #047857;
        }
        .save-btn {
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .save-btn:hover {
            background: #2563eb;
        }
        .preview-area {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            background: #f9fafb;
            min-height: 200px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 SMIS Email Theme Customizer</h1>
            <p>Customize your email notification appearance</p>
        </div>
        
        <div class="content">
            <!-- Color Configuration -->
            <div class="section">
                <h3>🎨 Color Palette</h3>
                <div class="color-picker">
                    <label>Primary Color:</label>
                    <input type="color" id="primary" value="#059669">
                    <input type="text" id="primary-text" value="#059669">
                </div>
                <div class="color-picker">
                    <label>Secondary Color:</label>
                    <input type="color" id="secondary" value="#047857">
                    <input type="text" id="secondary-text" value="#047857">
                </div>
                <div class="color-picker">
                    <label>Success Color:</label>
                    <input type="color" id="success" value="#10b981">
                    <input type="text" id="success-text" value="#10b981">
                </div>
                <div class="color-picker">
                    <label>Warning Color:</label>
                    <input type="color" id="warning" value="#f59e0b">
                    <input type="text" id="warning-text" value="#f59e0b">
                </div>
                <div class="color-picker">
                    <label>Error Color:</label>
                    <input type="color" id="error" value="#dc2626">
                    <input type="text" id="error-text" value="#dc2626">
                </div>
            </div>

            <!-- Branding Configuration -->
            <div class="section">
                <h3>🏢 Branding</h3>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">App Name:</label>
                    <input type="text" id="app-name" value="SMIS" style="width: 200px; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Tagline:</label>
                    <input type="text" id="tagline" value="Supply Management Information System" style="width: 400px; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 5px; font-weight: 500;">Logo URL (optional):</label>
                    <input type="text" id="logo-url" placeholder="https://example.com/logo.png" style="width: 400px; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px;">
                </div>
            </div>

            <!-- Customization Options -->
            <div class="section">
                <h3>⚙️ Customization Options</h3>
                <div class="toggle">
                    <input type="checkbox" id="show-emojis" checked>
                    <label for="show-emojis">Show Emojis</label>
                </div>
                <div class="toggle">
                    <input type="checkbox" id="show-icons" checked>
                    <label for="show-icons">Show Icons</label>
                </div>
                <div class="toggle">
                    <input type="checkbox" id="header-gradient" checked>
                    <label for="header-gradient">Header Gradient</label>
                </div>
                <div class="toggle">
                    <input type="checkbox" id="dark-mode" checked>
                    <label for="dark-mode">Dark Mode Support</label>
                </div>
                <div class="toggle">
                    <input type="checkbox" id="responsive" checked>
                    <label for="responsive">Responsive Design</label>
                </div>
            </div>

            <!-- Actions -->
            <div class="section">
                <h3>🎬 Actions</h3>
                <button class="preview-btn" onclick="previewEmail()">📧 Preview Email</button>
                <button class="save-btn" onclick="saveConfiguration()">💾 Save Configuration</button>
                <button class="preview-btn" onclick="testEmail()" style="background: #f59e0b;">📤 Send Test Email</button>
            </div>

            <!-- Preview Area -->
            <div class="section">
                <h3>👀 Live Preview</h3>
                <div class="preview-area" id="preview-area">
                    <p style="text-align: center; color: #6b7280;">Click "Preview Email" to see your customizations</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Color picker synchronization
        document.querySelectorAll('input[type="color"]').forEach(colorInput => {
            const textInput = document.getElementById(colorInput.id + '-text');
            
            colorInput.addEventListener('change', function() {
                textInput.value = this.value;
                updatePreview();
            });
            
            textInput.addEventListener('input', function() {
                if (/^#[0-9A-F]{6}$/i.test(this.value)) {
                    colorInput.value = this.value;
                    updatePreview();
                }
            });
        });

        // Update preview when any input changes
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('change', updatePreview);
            input.addEventListener('input', updatePreview);
        });

        function updatePreview() {
            // This would typically make an AJAX call to update the preview
            console.log('Preview updated');
        }

        function previewEmail() {
            const config = getConfiguration();
            
            // Mock email preview
            const previewArea = document.getElementById('preview-area');
            previewArea.innerHTML = `
                <div style="max-width: 570px; margin: 0 auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                    <div style="background: ${config.colors.primary}; color: white; padding: 20px; text-align: center;">
                        <h2 style="margin: 0;">${config.branding.app_name} - ${config.branding.tagline}</h2>
                    </div>
                    <div style="padding: 30px;">
                        <h1 style="color: ${config.colors.text};">Hello John Doe!</h1>
                        <div style="background: #f0fdfa; border-left: 4px solid ${config.colors.primary}; padding: 16px; border-radius: 0 8px 8px 0; margin: 16px 0;">
                            <p style="color: #065f46; margin: 0;">${config.customization.show_emojis ? '🎉 ' : ''}Great News! Your supply request has been APPROVED</p>
                        </div>
                        <h2 style="color: ${config.colors.text};">${config.customization.show_emojis ? '📋 ' : ''}Request Details</h2>
                        <table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
                            <tr><td style="padding: 8px; border-bottom: 1px solid #f3f4f6;"><strong>Stock Number:</strong></td><td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">ABC123</td></tr>
                            <tr><td style="padding: 8px; border-bottom: 1px solid #f3f4f6;"><strong>Supply Item:</strong></td><td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">Office Supplies</td></tr>
                            <tr><td style="padding: 8px; border-bottom: 1px solid #f3f4f6;"><strong>Quantity:</strong></td><td style="padding: 8px; border-bottom: 1px solid #f3f4f6;">10</td></tr>
                        </table>
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="#" style="background: ${config.colors.success}; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; font-weight: 600;">${config.customization.show_emojis ? '📄 ' : ''}View Request Details</a>
                        </div>
                        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 8px; padding: 16px; margin: 16px 0;">
                            <p style="color: #92400e; margin: 0;"><strong>${config.customization.show_emojis ? '⚠️ ' : ''}Next Steps:</strong> Please coordinate with the Property Custodian for item pickup.</p>
                        </div>
                    </div>
                </div>
            `;
        }

        function saveConfiguration() {
            const config = getConfiguration();
            
            // In a real application, this would send the configuration to the server
            fetch('/admin/email-theme/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(config)
            })
            .then(response => response.json())
            .then(data => {
                alert('Configuration saved successfully!');
            })
            .catch(error => {
                alert('Error saving configuration: ' + error.message);
            });
        }

        function testEmail() {
            const config = getConfiguration();
            
            // Send test email
            fetch('/admin/email-theme/test', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(config)
            })
            .then(response => response.json())
            .then(data => {
                alert('Test email sent successfully!');
            })
            .catch(error => {
                alert('Error sending test email: ' + error.message);
            });
        }

        function getConfiguration() {
            return {
                colors: {
                    primary: document.getElementById('primary').value,
                    secondary: document.getElementById('secondary').value,
                    success: document.getElementById('success').value,
                    warning: document.getElementById('warning').value,
                    error: document.getElementById('error').value,
                    text: '#374151',
                    muted: '#6b7280',
                    background: '#f8fafc',
                    card: '#ffffff'
                },
                branding: {
                    app_name: document.getElementById('app-name').value,
                    tagline: document.getElementById('tagline').value,
                    logo_url: document.getElementById('logo-url').value || null
                },
                customization: {
                    show_emojis: document.getElementById('show-emojis').checked,
                    show_icons: document.getElementById('show-icons').checked,
                    show_header_gradient: document.getElementById('header-gradient').checked,
                    dark_mode_support: document.getElementById('dark-mode').checked,
                    responsive_design: document.getElementById('responsive').checked
                }
            };
        }

        // Initialize preview on page load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(previewEmail, 500);
        });
    </script>
</body>
</html>
