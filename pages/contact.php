<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Contact Us | Elocanto Support', 'contact');
?>

<main>
    <!-- Page Hero -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: var(--accent-gold); font-size: 3.5rem; font-weight: 900; margin-bottom: 15px;">Contact Us</h1>
            <p style="color: rgba(255,255,255,0.7); font-size: 1.2rem;">We're here to help you 24/7</p>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.6); font-size: 1.1rem; margin-top: 15px;">
                <a href="<?php echo BASE_URL; ?>/index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>Contact Us</span>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="section-padding" style="padding: 80px 0; background: var(--bg-dark);">
        <div class="container-wide">
            <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; align-items: start;">
                <!-- Contact Info -->
                <div style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 24px; padding: 40px; backdrop-filter: blur(10px);">
                    <h2 style="color: var(--white); font-size: 1.8rem; margin-bottom: 30px; font-family: 'Outfit', sans-serif;">Get in Touch</h2>
                    
                    <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 20px;">
                        <div style="width: 50px; height: 50px; background: rgba(212, 175, 55, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--accent-gold);">
                            <i class="fas fa-envelope fa-lg"></i>
                        </div>
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">Email Support</p>
                            <p style="color: var(--white); font-weight: 600; font-size: 1.1rem; margin: 0;">support@elocanto.pk</p>
                        </div>
                    </div>

                    <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 20px;">
                        <div style="width: 50px; height: 50px; background: rgba(37, 211, 102, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #25d366;">
                            <i class="fab fa-whatsapp fa-2x"></i>
                        </div>
                        <div>
                            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">WhatsApp Support</p>
                            <p style="color: var(--white); font-weight: 600; font-size: 1.1rem; margin: 0;">+447490809237</p>
                        </div>
                    </div>

                    <div style="padding-top: 30px; border-top: 1px solid var(--glass-border); margin-top: 30px;">
                        <h4 style="color: var(--accent-gold); margin-bottom: 15px;">Response Time</h4>
                        <p style="color: var(--text-muted); font-size: 1rem; line-height: 1.6;">We strive to reply to all inquiries within 24 hours. Urgent issues like frauds or security concerns are prioritized.</p>
                    </div>
                </div>

                <!-- Contact Form Placeholder/Content -->
                <div style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 24px; padding: 40px; backdrop-filter: blur(10px);">
                    <h2 style="color: var(--white); font-size: 1.8rem; margin-bottom: 30px; font-family: 'Outfit', sans-serif;">Report an Issue</h2>
                    <p style="color: var(--text-muted); margin-bottom: 30px;">If you've found suspect ads, fraudulent listings, or have copyright concerns, please let us know immediately.</p>
                    
                    <div style="padding: 30px; background: rgba(255,255,255,0.03); border-radius: 15px; border: 1px dashed var(--glass-border);">
                        <h4 style="color: var(--white); margin-bottom: 10px;">Why Contact Us?</h4>
                        <ul style="color: var(--text-muted); line-height: 1.8;">
                            <li>Report fake listings or scams</li>
                            <li>Assistance with your account</li>
                            <li>Feedback on platform experience</li>
                            <li>Business inquiries & partnerships</li>
                        </ul>
                    </div>

                    <div style="margin-top: 40px;">
                        <p style="color: var(--text-muted); font-style: italic;">"Your input is an essential influence on the direction of Elocanto. We are here to assist your needs."</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
renderFooter();
?>
