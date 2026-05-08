<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Terms & Conditions | Elocanto', 'terms');
?>

<main>
    <!-- Page Hero -->
    <section class="page-banner" style="background: linear-gradient(135deg, #1e293b, #0f172a); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: var(--accent-gold); font-size: 3.5rem; font-weight: 900; margin-bottom: 15px;">Terms & Conditions</h1>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.6); font-size: 1.1rem;">
                <a href="<?php echo BASE_URL; ?>/index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>Terms & Conditions</span>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="section-padding" style="padding: 80px 0; background: var(--bg-dark);">
        <div class="container-wide" style="max-width: 900px; margin: 0 auto;">
            <div class="content-card" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 32px; padding: 60px; backdrop-filter: blur(10px);">
                <div class="legal-content" style="color: var(--text-muted); line-height: 1.8; font-size: 1.05rem;">
                    <h2 style="color: var(--white); font-size: 2rem; margin-bottom: 25px;">Acceptance and Agreement to Terms</h2>
                    <p style="margin-bottom: 20px;">Welcome to Elocanto. By accessing or using our website, you agree to comply with and be bound by these Terms and Conditions. These terms are designed to ensure a safe, fair, and reliable environment for all users of the platform.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Platform Overview and Nature of Service</h3>
                    <p style="margin-bottom: 20px;">Elocanto is an online classified ads platform that enables users to post listings, explore ads, and connect with others for buying, selling, or offering services. We do not own, control, or directly sell any of the products or services listed on the platform. All content is generated and managed by individual users.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">User Obligations and Responsibilities</h3>
                    <p style="margin-bottom: 20px;">By using Elocanto, you agree to act responsibly, honestly, and in accordance with applicable laws. You are solely responsible for the content you post, including text, images, and any other information. All listings must be accurate, genuine, and not misleading.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Listing Standards and Content Compliance</h3>
                    <p style="margin-bottom: 20px;">All ads posted on Elocanto must meet basic quality and legal standards. Users must ensure that their listings are clear, truthful, and relevant. Posting duplicate ads, misleading information, or unauthorized content is not allowed.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Restricted and Prohibited Activities</h3>
                    <p style="margin-bottom: 20px;">Users are strictly prohibited from engaging in activities that compromise the safety or functionality of the platform. This includes fraudulent behavior, scam attempts, spam posting, fake listings, system abuse, or any attempt to manipulate visibility or engagement.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">User-to-User Transactions</h3>
                    <p style="margin-bottom: 20px;">All transactions conducted through Elocanto take place directly between users. We do not act as a party to any agreement, payment, or delivery process. Therefore, we do not guarantee the quality, safety, legality, or authenticity of any listing.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Payment Disclaimer</h3>
                    <p style="margin-bottom: 20px;">Elocanto does not process or facilitate payments between users. All financial interactions are carried out at the user's own discretion and risk. We strongly recommend avoiding advance payments unless you fully trust the other party.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Limitation of Liability</h3>
                    <p style="margin-bottom: 20px;">Elocanto is provided on an "as-is" basis without any guarantees or warranties. We are not responsible for any direct or indirect loss, damage, or disputes that may occur between users.</p>

                    <div style="margin-top: 50px; padding: 30px; background: rgba(255,255,255,0.03); border-radius: 15px;">
                        <p style="font-size: 0.9rem; margin: 0;">Elocanto reserves the right to update or modify these Terms and Conditions at any time without prior notice. Continued use of the platform after changes means you accept the updated terms.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
renderFooter();
?>
