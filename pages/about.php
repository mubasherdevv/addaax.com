<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('About Us | Addaax', 'about');
?>

<style>
    :root {
        --page-red: #dc2626;
        --page-black: #111827;
    }
    
    .legal-page-wrapper {
        padding-top: 90px; /* Offset for fixed header */
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
        font-size: clamp(2.2rem, 6vw, 3.8rem);
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
        font-size: 1.6rem;
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

    .contact-info-box {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        padding: 30px;
        border-radius: 20px;
        margin-top: 60px;
        max-width: 900px;
        margin-left: auto;
        margin-right: auto;
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
            <h1>About Addaax</h1>
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
                        <p>Addaax is a modern online classified ads platform designed to connect people across Pakistan through simple, fast, and reliable listings. Our goal is to make buying, selling, and offering services easier for everyone by providing a digital space where real people can interact directly.</p>
                        <p>We believe that opportunities should be accessible to everyone, whether you are selling a product, searching for a job, renting a property, or offering a service. Addaax is built to bring these opportunities together in one place.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>Platform Overview: What Addaax Offers</h2>
                        <p>Addaax allows users to post and explore a wide range of classified ads across multiple categories. From jobs and real estate to vehicles, services, and personal items, our platform is designed to support everyday needs and business opportunities.</p>
                        <p>We provide a simple and user-friendly system where users can create listings, reach potential buyers, and communicate directly without unnecessary complexity.</p>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-content">
                        <h2>How Addaax Works: A User-Driven Marketplace</h2>
                        <p>Addaax is a user-generated platform, which means all ads are created and managed by individual users. We do not sell products or services directly. Instead, we provide the platform where users can connect, communicate, and complete transactions independently.</p>
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
                        <p>Addaax is built for real users and real opportunities, but users are always advised to follow safe practices when dealing online.</p>
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
                <h3 style="margin-top: 0; color: var(--page-red); font-size: 1.5rem; font-weight: 800; font-family: 'Outfit', sans-serif; margin-bottom: 15px;">Contact Us and User Support</h3>
                <p style="color: #374151; line-height: 1.8;">We are always open to feedback, suggestions, and support requests. If you have any questions or need assistance, you can reach us through our support channels:</p>
                <div style="margin-top: 20px;">
                    <p style="margin-bottom: 10px; font-size: 1.1rem;"><strong>Email:</strong> contactadmin@addaax.com</p>
                    <p style="margin-bottom: 0; font-size: 1.1rem;"><strong>WhatsApp:</strong> +447490809237</p>
                </div>
            </div>
            
            <p style="margin-top: 40px; font-style: italic; text-align: center; color: #6b7280;">We are committed to helping our users and continuously improving the platform experience.</p>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
