<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('About Us | ADDAAX', 'about');
?>

<style>
    :root {
        --page-accent: #C9A84C; /* Royal Gold */
        --page-bg: #ffffff; /* WHITE BACKGROUND */
        --text-gold: #C9A84C; /* Gold for headings */
        --banner-bg: #000000; /* BLACK BANNER */
    }
    
    .legal-page-wrapper {
        background: var(--page-bg);
        min-height: 100vh;
    }
    
    .legal-banner {
        background: var(--banner-bg);
        padding: 120px 0 80px;
        text-align: center;
        border-bottom: 2px solid var(--page-accent);
        position: relative;
    }
    
    .legal-banner h1 {
        background: linear-gradient(to right, #F5E9C8, var(--page-accent));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-size: clamp(2.5rem, 8vw, 4.5rem);
        font-weight: 900;
        margin-bottom: 20px;
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: -1px;
    }
    
    .legal-banner .breadcrumbs {
        color: rgba(255,255,255,0.6) !important;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: 2px;
    }
    
    .legal-banner .breadcrumbs a {
        color: white !important;
        text-decoration: none;
    }
    
    .legal-banner .breadcrumbs span.current {
        color: var(--page-accent);
    }
    
    .legal-content-section {
        background: #ffffff;
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
        width: 3px;
        background: #000000;
        opacity: 0.15;
    }

    .timeline-item {
        position: relative;
        padding-left: 80px;
        margin-bottom: 60px;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        width: 24px;
        height: 24px;
        background: #ffffff;
        border: 4px solid var(--page-accent);
        border-radius: 50%;
        z-index: 2;
        box-shadow: 0 0 10px rgba(201, 168, 76, 0.2);
    }

    .timeline-content {
        background: #ffffff;
        padding: 40px;
        border-radius: 30px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        transition: all 0.4s ease;
    }

    .timeline-content:hover {
        transform: translateY(-5px);
        border-color: var(--page-accent);
        box-shadow: 0 20px 40px rgba(201, 168, 76, 0.1);
    }

    .timeline-content h2 {
        color: var(--page-accent); /* GOLD COLOR FOR HEADINGS */
        font-size: 1.8rem;
        margin-bottom: 20px;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
    }
    
    .timeline-content p {
        margin-bottom: 15px;
        font-size: 1.15rem;
        color: #C9A84C; /* LIGHT GOLD FONT COLOR AS REQUESTED */
        line-height: 1.8;
        font-weight: 500;
    }

    .contact-info-box {
        background: #fdfbf7;
        border: 1px solid #f3ebd5;
        padding: 50px;
        border-radius: 40px;
        margin-top: 80px;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
        text-align: center;
    }

    @media (max-width: 768px) {
        .timeline-wrapper::before { left: 16px; }
        .timeline-item { padding-left: 45px; }
        .timeline-item::before { left: 5px; width: 20px; height: 20px; }
        .timeline-content { padding: 25px; }
    }
</style>

<div class="legal-page-wrapper">
    <section class="legal-banner">
        <div class="container-wide">
            <h1>About ADDAAX</h1>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span style="margin: 0 15px;">/</span>
                <span class="current">About Us</span>
            </div>
        </div>
    </section>

    <section class="legal-content-section">
        <div class="container-wide">
            <div class="timeline-wrapper">
                
                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Our Vision: Connecting People Through Digital Classifieds</h2>
                        <p>ADDAAX is a modern online classified ads platform designed to connect people across Pakistan through simple, fast, and reliable listings. Our goal is to make buying, selling, and offering services easier for everyone by providing a digital space where real people can interact directly.</p>
                        <p>We believe that opportunities should be accessible to everyone, whether you are selling a product, searching for a job, renting a property, or offering a service. ADDAAX is built to bring these opportunities together in one place.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Platform Overview: What ADDAAX Offers</h2>
                        <p>ADDAAX allows users to post and explore a wide range of classified ads across multiple categories. From jobs and real estate to vehicles, services, and personal items, our platform is designed to support everyday needs and business opportunities.</p>
                        <p>We provide a simple and user-friendly system where users can create listings, reach potential buyers, and communicate directly without unnecessary complexity.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>How ADDAAX Works: A User-Driven Marketplace</h2>
                        <p>ADDAAX is a user-generated platform, which means all ads are created and managed by individual users. We do not sell products or services directly. Instead, we provide the platform where users can connect, communicate, and complete transactions independently.</p>
                        <p>This model gives users full control over their listings while allowing flexibility and freedom in how they interact with others.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Our Commitment to Platform Quality and Safety</h2>
                        <p>Our priority is to create a safe, easy, and reliable experience for all users. We continuously work to improve platform performance, reduce fake listings, and maintain a clean and trustworthy environment. We also provide reporting tools so users can report suspicious ads or activity.</p>
                        <p>User feedback is important to us, and we aim to respond to concerns as quickly as possible to maintain a positive user experience.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Building Trust and User Safety Awareness</h2>
                        <p>We understand that trust is essential in any online marketplace. While we provide tools and systems to help maintain safety, we also encourage users to stay alert and make informed decisions when interacting with others.</p>
                        <p>ADDAAX is built for real users and real opportunities, but users are always advised to follow safe practices when dealing online.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Our Mission and Core Objectives</h2>
                        <p>Our mission is to make online classifieds simple, accessible, and reliable for everyone in Pakistan. We aim to empower individuals and businesses by giving them a platform where they can connect without barriers and grow their opportunities in a digital environment.</p>
                    </div>
                </div>

            </div>

            <div class="contact-info-box">
                <h3 style="margin-top: 0; color: var(--page-accent); font-size: 2rem; font-weight: 800; font-family: 'Outfit', sans-serif; margin-bottom: 20px;">User Support</h3>
                <p style="color: #666; line-height: 1.8; font-size: 1.2rem; margin-bottom: 30px;">We are always open to feedback, suggestions, and support requests. If you have any questions or need assistance, you can reach us through our support channels:</p>
                <div style="display: flex; justify-content: center; gap: 60px; flex-wrap: wrap;">
                    <div>
                        <h4 style="color: #999; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;">Email Address</h4>
                        <p style="color: var(--page-accent); font-weight: 700; font-size: 1.3rem;">contactadmin@addaax.com</p>
                    </div>
                    <div>
                        <h4 style="color: #999; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; font-size: 0.9rem;">WhatsApp</h4>
                        <p style="color: var(--page-accent); font-weight: 700; font-size: 1.3rem;">+447490809237</p>
                    </div>
                </div>
            </div>
            
            <p style="margin-top: 80px; font-style: italic; text-align: center; color: #bbb; font-size: 0.95rem;">We are committed to helping our users and continuously improving the platform experience.</p>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
