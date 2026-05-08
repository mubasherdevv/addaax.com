<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Copyright Policy | Elocanto', 'copyright');
?>

<main>
    <!-- Page Hero -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: var(--accent-gold); font-size: 3.5rem; font-weight: 900; margin-bottom: 15px;">Copyright Policy</h1>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.6); font-size: 1.1rem;">
                <a href="<?php echo BASE_URL; ?>/index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>Copyright Policy</span>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="section-padding" style="padding: 80px 0; background: var(--bg-dark);">
        <div class="container-wide" style="max-width: 900px; margin: 0 auto;">
            <div class="content-card" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 32px; padding: 60px; backdrop-filter: blur(10px);">
                <div class="legal-content" style="color: var(--text-muted); line-height: 1.8; font-size: 1.05rem;">
                    <h2 style="color: var(--white); font-size: 2rem; margin-bottom: 25px;">Respect for Intellectual Property Rights</h2>
                    <p style="margin-bottom: 20px;">At Elocanto, we respect the intellectual property rights of others and expect all users of our platform to do the same. We are committed to maintaining a safe and lawful environment where original content is protected.</p>
                    <p style="margin-bottom: 20px;">All content published on Elocanto, including text, images, logos, design elements, and layout, is either owned by Elocanto or used with proper authorization.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">User Responsibility for Content</h3>
                    <p style="margin-bottom: 20px;">Elocanto is a user-generated content platform. By posting content, you confirm that you own the rights to that content or have proper permission to use it. You also confirm that your content does not infringe on any third-party copyright, trademark, or intellectual property rights.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Reporting Infringement</h3>
                    <p style="margin-bottom: 20px;">If you believe that any content on Elocanto violates your copyright or intellectual property rights, you can report it to us immediately. Please include:
                        <ul style="margin-left: 20px; margin-top: 10px;">
                            <li>Link to the infringing content</li>
                            <li>Description of the copyrighted material</li>
                            <li>Proof of ownership (if available)</li>
                        </ul>
                    </p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Enforcement Actions</h3>
                    <p style="margin-bottom: 20px;">If any content is found to be infringing, we may take immediate action, including removing the content, restricting access, or suspending the user account responsible for repeated violations.</p>

                    <div style="margin-top: 60px; padding: 40px; background: rgba(212, 175, 55, 0.05); border-radius: 20px; border-left: 4px solid var(--accent-gold);">
                        <h4 style="color: var(--white); margin-bottom: 15px;">Contact for Copyright Concerns</h4>
                        <p><strong>Email:</strong> support@elocanto.pk</p>
                        <p><strong>WhatsApp:</strong> +447490809237</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
renderFooter();
?>
