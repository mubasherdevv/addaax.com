<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Contact Us | Addaax', 'contact');
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
        font-size: clamp(2rem, 5vw, 3.5rem);
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
    
    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 60px;
        max-width: 1100px;
        margin: 0 auto;
        padding: 0 24px;
    }
    
    .contact-info-card {
        padding: 40px;
        background: #fef2f2;
        border-radius: 30px;
        border: 1px solid #fee2e2;
    }
    
    .contact-info-card h2 {
        color: var(--page-red);
        margin-bottom: 30px;
        font-size: 2.2rem;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
    }
    
    .contact-info-card p {
        color: #4b5563;
        font-size: 1.1rem;
        line-height: 1.8;
        margin-bottom: 30px;
    }
    
    .contact-method {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 25px;
        padding: 20px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    
    .contact-method i {
        font-size: 24px;
        color: var(--page-red);
        width: 50px;
        height: 50px;
        background: #fff1f2;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
    }
    
    .contact-method div h4 {
        margin: 0;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #9ca3af;
    }
    
    .contact-method div p {
        margin: 0;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--page-black);
    }

    .contact-form-card {
        padding: 40px;
        background: white;
        border-radius: 30px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        border: 1px solid #f3f4f6;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--page-black);
    }

    .form-group input, .form-group textarea {
        width: 100%;
        padding: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-group input:focus, .form-group textarea:focus {
        border-color: var(--page-red);
        outline: none;
        box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
    }

    .submit-btn {
        background: var(--page-red);
        color: white;
        padding: 15px 30px;
        border: none;
        border-radius: 12px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s ease;
    }

    .submit-btn:hover {
        background: #b91c1c;
        transform: translateY(-2px);
    }

    @media (max-width: 992px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="legal-page-wrapper">
    <section class="legal-banner">
        <div class="container-wide">
            <h1>Contact Us</h1>
            <div class="breadcrumbs">
                <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
                <span style="margin: 0 15px;">/</span>
                <span class="current">Contact Us</span>
            </div>
        </div>
    </section>

    <section class="legal-content-section">
        <div class="container-wide">
            <div class="contact-grid">
                <div class="contact-info-card">
                    <h2>We're Here to Help You</h2>
                    <p>At Addaax, we value the customers we serve and are willing to assist. If you've got a query or need help, wish to raise an issue or just want to provide your feedback, we are ready to hear from you.</p>
                    <p>We are committed to clear communications as well as quick assistance to ensure your experience with us is smooth and secure.</p>
                    
                    <div class="contact-method">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h4>Email Support</h4>
                            <p>contactadmin@addaax.com</p>
                        </div>
                    </div>

                    <div class="contact-method">
                        <i class="fab fa-whatsapp"></i>
                        <div>
                            <h4>WhatsApp Support</h4>
                            <p>+447490809237</p>
                        </div>
                    </div>

                    <p style="margin-top: 30px; font-style: italic; font-size: 0.95rem;">We strive to reply to all inquiries within 24 hours based on the type of issue. Urgent issues like frauds, scams, or security concerns are prioritised.</p>
                </div>

                <div class="contact-form-card">
                    <h3 style="margin-top: 0; margin-bottom: 25px; font-weight: 800; font-size: 1.5rem;">Send us a Message</h3>
                    <form action="#" method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" placeholder="John Doe" required>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" placeholder="john@example.com" required>
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" placeholder="How can we help?" required>
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea rows="5" placeholder="Tell us more about your inquiry..." required></textarea>
                        </div>
                        <button type="submit" class="submit-btn">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<?php 
renderFooter();
?>
