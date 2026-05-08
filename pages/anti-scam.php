<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Anti-Scam Guide | Addaax', 'anti-scam');
?>

<style>
    :root {
        --page-red: #dc2626;
        --page-black: #111827;
    }
    
    .legal-page-wrapper {
        padding-top: 90px;
        background: #ffffff;
        min-height: 100vh;
    }
    
    .legal-banner {
        background: #f8fafc;
        padding: 80px 0;
        text-align: center;
        border-bottom: 1px solid #eee;
    }
    
    .legal-banner h1 {
        color: var(--page-red);
        font-size: clamp(1.8rem, 5vw, 3.2rem);
        font-weight: 900;
        margin-bottom: 20px;
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: -1px;
    }
    
    .legal-banner .breadcrumbs {
        color: var(--page-black) !important;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .legal-banner .breadcrumbs a {
        color: var(--page-black) !important;
        text-decoration: none;
    }
    
    .legal-banner .breadcrumbs span {
        color: var(--page-red);
        opacity: 1;
    }
    
    .legal-content-section {
        padding: 80px 0;
    }
    
    .timeline-wrapper {
        position: relative;
        max-width: 900px;
        margin: 0 auto;
        padding: 40px 0;
    }

    .timeline-wrapper::before {
        content: '';
        position: absolute;
        left: 31px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #f1f5f9;
    }

    .timeline-item {
        position: relative;
        padding-left: 80px;
        margin-bottom: 50px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        width: 24px;
        height: 24px;
        background: white;
        border: 4px solid var(--page-red);
        border-radius: 50%;
        z-index: 2;
    }

    .timeline-content {
        background: #ffffff;
        padding: 30px;
        border-radius: 24px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }

    .timeline-content:hover {
        transform: translateX(10px);
        border-color: #fee2e2;
        box-shadow: 0 15px 40px rgba(220,38,38,0.05);
    }

    .timeline-content h2 {
        color: var(--page-red);
        font-size: 1.5rem;
        margin-bottom: 15px;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
    }
    
    .timeline-content p {
        margin-bottom: 15px;
        font-size: 1.1rem;
        color: #374151;
        line-height: 1.8;
    }

    .safety-alert {
        background: #fef2f2;
        border: 2px dashed #fee2e2;
        padding: 40px;
        border-radius: 24px;
        margin: 60px 0;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
    }

    .safety-alert h3 {
        color: var(--page-red);
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .timeline-wrapper::before { left: 16px; }
        .timeline-item { padding-left: 45px; }
        .timeline-item::before { left: 5px; width: 20px; height: 20px; }
        .timeline-content { padding: 20px; }
    }
</style>

<div class="legal-page-wrapper">
    <section class="legal-banner">
        <div class="container-wide">
            <h1>Anti-Scam & User Safety Guide</h1>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span style="margin: 0 15px;">/</span>
                <span class="current">Anti-Scam Policy</span>
            </div>
        </div>
    </section>

    <section class="legal-content-section">
        <div class="container-wide">
            <div class="timeline-wrapper">
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Our Commitment to Transparency and Trust</h2>
                        <p>We at Addaax believe that trust is at the heart for any marketplace that succeeds. When you use our platform we aren't only scrolling through ads, but meeting genuine people who have real possibilities and genuine intentions. This is why we are determined to address our users in a clear and honest manner.</p>
                        <p>We would like you to be confident when using our service. This is why it's crucial that we make the following statement: Addaax itself does not take part in any type of fraud, scam or other fraudulent activities. We don't create false listings, we don't fool users and we never modify any transactions. We create a platform for users can interact with each other.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Understanding How Our Platform Operates</h2>
                        <p>In order to understand the importance of security you must know how the platform functions. Addaax is a classified ads site. It means that each ad displayed is made by a single user. It doesn't matter if it's a item that is for sale, a job ad, a home listing or service, it's designed and handled by the user who put it up.</p>
                        <p>We are not the owners of the goods being sold. We do not participate directly in the transactions between buyers and sellers. We are not an intermediary in any deals. Consider Addaax as a virtual platform for meeting, much like a market that allows people to meet for a chat, network, or make bargains on their specifications.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Ongoing Efforts to Maintain a Safe Marketplace</h2>
                        <p>While we're non-directly involved with transactions, our concern for your security is not less important for us. We monitor our platform regularly and take steps to prevent the misuse of our platform.</p>
                        <p>When we spot fraudulent listings, suspicious activities or any behavior that is in violation of our rules, we will decide to take action. It could be removing advertisements and accounts that are not being used, as well as blocking those who abuse the platform.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Reporting System and Quick Action Commitment</h2>
                        <p>We've made it possible to use a number of different choices on our website to let users report every issues they could face. If you come across an advertisement that you feel is unsafe, insensitive or doesn't belong to the platform, then you are able to immediately report it.</p>
                        <p>If a complaint has been submitted, our team reviews the matter carefully and then takes the appropriate steps. We usually try to address problems reported within 24 hours.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Final Thoughts and User Assurance</h2>
                        <p>Addaax was designed to serve real-life people who are looking for genuine opportunities. If you're purchasing, selling or providing services, we'd like the experience to go smoothly and safe. We don't support fraud and actively battle to stop them.</p>
                        <p>Consider this platform an opportunity-rich place, but as in real life you should always take action with a sense of awareness. If you don't like something be sure to trust your gut. If you need assistance, we're there to help.</p>
                    </div>
                </div>

            </div>

            <div class="safety-alert">
                <h3><i class="fas fa-shield-alt"></i> Safety First: Your Responsibility</h3>
                <p>When communicating with an online person, it's always a good decision to conduct your business with an open-minded mentality. If you feel something seems too appealing to be real then it probably is.</p>
                <p><strong>Payment Safety:</strong> We highly recommend that you not make advance payment in particular when working with a person who you don't know, or are not able to verify. Making payments prior to receiving an item or service may be dangerous.</p>
            </div>

        </div>
    </section>
</div>

<?php 
renderFooter();
?>
