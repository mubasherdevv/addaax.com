<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Anti-Scam Guide | Elocanto', 'anti-scam');
?>

<main>
    <!-- Page Hero -->
    <section class="page-banner" style="background: linear-gradient(135deg, #b91c1c, #7f1d1d); padding: 80px 0; text-align: center;">
        <div class="container-wide">
            <h1 style="color: white; font-size: 3.5rem; font-weight: 900; margin-bottom: 15px;">Anti-Scam & Safety Guide</h1>
            <div class="breadcrumbs" style="color: rgba(255,255,255,0.7); font-size: 1.1rem;">
                <a href="<?php echo BASE_URL; ?>/index.php" style="color: white; text-decoration: none;">Home</a>
                <span style="margin: 0 10px;">/</span>
                <span>Anti-Scam</span>
            </div>
        </div>
    </section>

    <!-- Content Section -->
    <section class="section-padding" style="padding: 80px 0; background: var(--bg-dark);">
        <div class="container-wide" style="max-width: 900px; margin: 0 auto;">
            <div class="content-card" style="background: var(--glass-bg); border: 1px solid var(--glass-border); border-radius: 32px; padding: 60px; backdrop-filter: blur(10px);">
                <div class="legal-content" style="color: var(--text-muted); line-height: 1.8; font-size: 1.05rem;">
                    <h2 style="color: var(--white); font-size: 2rem; margin-bottom: 25px;">Our Commitment to Your Safety</h2>
                    <p style="margin-bottom: 20px;">Elocanto itself does not take part in any type of fraud, scam or other fraudulent activities. We don't create false listings, we don't fool users and we never modify any transactions. We create a platform where users can interact with each other.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Understanding How Our Platform Operates</h3>
                    <p style="margin-bottom: 20px;">Elocanto is a classified ads site. It means that each ad displayed is made by a single user. We are not the owners of the goods being sold. We do not participate directly in the transactions between buyers and sellers. We are not an intermediary in any deals.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Ongoing Efforts to Maintain a Safe Marketplace</h3>
                    <p style="margin-bottom: 20px;">We monitor our platform regularly and take steps to prevent misuse. When we spot fraudulent listings or suspicious activities, we take action, which could include removing advertisements or blocking accounts.</p>

                    <div style="background: rgba(255,0,0,0.05); border-left: 4px solid #ef4444; padding: 30px; margin: 40px 0; border-radius: 12px;">
                        <h4 style="color: #ef4444; margin-bottom: 15px; font-weight: 700;">Critical Safety Tips</h4>
                        <ul style="list-style: none; padding: 0;">
                            <li style="margin-bottom: 10px;"><i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i> <strong>Never make advance payments</strong> to someone you don't know.</li>
                            <li style="margin-bottom: 10px;"><i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i> If a deal seems too good to be true, it probably is.</li>
                            <li style="margin-bottom: 10px;"><i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i> Always meet in a safe, public place for in-person transactions.</li>
                            <li style="margin-bottom: 10px;"><i class="fas fa-exclamation-triangle" style="margin-right: 10px;"></i> Trust your intuition. If something feels wrong, step back.</li>
                        </ul>
                    </div>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Identifying Suspicious Activity</h3>
                    <p style="margin-bottom: 20px;">Authentic users typically speak in a simple and professional manner. Be wary of pressure to move fast, requests for personal/financial data, or confusing communication.</p>

                    <h3 style="color: var(--accent-gold); font-size: 1.5rem; margin-top: 40px; margin-bottom: 15px;">Reporting Issues</h3>
                    <p style="margin-bottom: 20px;">If you encounter a suspicious ad or user, please report it immediately. We review all complaints carefully and aim to take action within 24 hours.</p>

                    <div style="margin-top: 50px; text-align: center; padding: 40px; border-top: 1px solid var(--glass-border);">
                        <p style="font-weight: 700; color: var(--white);">Together, we can make Elocanto a safer community for everyone.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php 
renderFooter();
?>
