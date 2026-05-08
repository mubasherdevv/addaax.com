<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('About Us | Elocanto', 'about');
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
        background: #f8fafc; /* Lightest gray/blue to look clean */
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
    
    .legal-card {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 24px;
        color: var(--page-black);
        line-height: 1.8;
    }
    
    .legal-card h2 {
        color: var(--page-red);
        font-size: 2rem;
        margin-top: 50px;
        margin-bottom: 25px;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
        border-left: 5px solid var(--page-red);
        padding-left: 20px;
    }
    
    .legal-card h3 {
        color: var(--page-black);
        font-size: 1.4rem;
        margin-top: 40px;
        margin-bottom: 15px;
        font-weight: 700;
    }
    
    .legal-card p {
        margin-bottom: 25px;
        font-size: 1.15rem;
        color: #374151;
    }

    .contact-info-box {
        background: #fef2f2;
        border: 1px solid #fee2e2;
        padding: 30px;
        border-radius: 20px;
        margin-top: 60px;
    }

    @media (max-width: 768px) {
        .legal-banner {
            padding: 60px 0;
        }
        .legal-content-section {
            padding: 40px 0;
        }
    }
</style>

<div class="legal-page-wrapper">
    <section class="legal-banner">
        <div class="container-wide">
            <h1>About Elocanto</h1>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span style="margin: 0 15px;">/</span>
                <span class="current">About Us</span>
            </div>
        </div>
    </section>

    <section class="legal-content-section">
        <div class="container-wide">
            <div class="legal-card">
                <h2>Our Vision: Connecting People Through Digital Classifieds</h2>
                <p>Elocanto is a modern online classified ads platform designed to connect people across Pakistan through simple, fast, and reliable listings. Our goal is to make buying, selling, and offering services easier for everyone by providing a digital space where real people can interact directly.</p>
                <p>We believe that opportunities should be accessible to everyone, whether you are selling a product, searching for a job, renting a property, or offering a service. Elocanto is built to bring these opportunities together in one place.</p>

                <h2>Platform Overview: What Elocanto Offers</h2>
                <p>Elocanto allows users to post and explore a wide range of classified ads across multiple categories. From jobs and real estate to vehicles, services, and personal items, our platform is designed to support everyday needs and business opportunities.</p>
                <p>We provide a simple and user-friendly system where users can create listings, reach potential buyers, and communicate directly without unnecessary complexity.</p>

                <h2>How Elocanto Works: A User-Driven Marketplace</h2>
                <p>Elocanto is a user-generated platform, which means all ads are created and managed by individual users. We do not sell products or services directly. Instead, we provide the platform where users can connect, communicate, and complete transactions independently.</p>
                <p>This model gives users full control over their listings while allowing flexibility and freedom in how they interact with others.</p>

                <h2>Our Commitment to Platform Quality and Safety</h2>
                <p>Our priority is to create a safe, easy, and reliable experience for all users. We continuously work to improve platform performance, reduce fake listings, and maintain a clean and trustworthy environment. We also provide reporting tools so users can report suspicious ads or activity.</p>
                <p>User feedback is important to us, and we aim to respond to concerns as quickly as possible to maintain a positive user experience.</p>

                <h2>Building Trust and User Safety Awareness</h2>
                <p>We understand that trust is essential in any online marketplace. While we provide tools and systems to help maintain safety, we also encourage users to stay alert and make informed decisions when interacting with others.</p>
                <p>Elocanto is built for real users and real opportunities, but users are always advised to follow safe practices when dealing online.</p>

                <h2>Our Mission and Core Objectives</h2>
                <p>Our mission is to make online classifieds simple, accessible, and reliable for everyone in Pakistan. We aim to empower individuals and businesses by giving them a platform where they can connect without barriers and grow their opportunities in a digital environment.</p>

                <div class="contact-info-box">
                    <h3 style="margin-top: 0; color: var(--page-red);">Contact Us and User Support</h3>
                    <p>We are always open to feedback, suggestions, and support requests. If you have any questions or need assistance, you can reach us through our support channels:</p>
                    <p style="margin-bottom: 10px;"><strong>Email:</strong> support@elocanto.pk</p>
                    <p style="margin-bottom: 0;"><strong>WhatsApp:</strong> +447490809237</p>
                </div>
                
                <p style="margin-top: 40px; font-style: italic; text-align: center; color: #6b7280;">We are committed to helping our users and continuously improving the platform experience.</p>
            </div>
        </div>
    </section>

    <section class="timeline-section">
        <div class="container-wide">
            <div style="text-align: center; margin-bottom: 60px;">
                <h2 style="color: var(--page-red); font-size: 2.5rem; font-weight: 900; margin-bottom: 15px;">Our Journey</h2>
                <p style="color: #6b7280; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Building a trusted marketplace takes time and commitment. Here is how we've grown.</p>
            </div>

            <div class="timeline-container">
                <div class="timeline-line"></div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Foundation</span>
                            <h3>The Vision Begins</h3>
                            <p>Elocanto was founded with a simple goal: to make classified ads simple, accessible, and reliable for everyone across Pakistan.</p>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Growth</span>
                            <h3>Expanding Community</h3>
                            <p>We successfully connected thousands of buyers and sellers, creating a vibrant digital ecosystem for trade and services.</p>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Safety</span>
                            <h3>Trust & Security</h3>
                            <p>Implemented advanced anti-scam policies and reporting tools to ensure a safe environment for all our users.</p>
                        </div>
                    </div>
                </div>

                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div class="timeline-card">
                            <span class="timeline-year">Future</span>
                            <h3>Constant Innovation</h3>
                            <p>We continue to evolve, adding new features and improving performance to remain Pakistan's favorite classifieds platform.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
