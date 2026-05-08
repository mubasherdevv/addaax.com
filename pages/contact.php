<?php
require_once '../includes/website_settings.php';
require_once '../auth/db_connect.php';
require_once '../includes/layout_functions.php';

renderHeader('Contact Us | ADDAAX', 'contact');
?>

<style>
    :root {
        --page-accent: #C9A84C;
        --page-bg: #ffffff;
        --text-gold: #C9A84C;
        --banner-bg: #000000;
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
        background: #ffffff;
        border-radius: 30px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }
    
    .contact-info-card h2 {
        color: var(--page-accent);
        margin-bottom: 30px;
        font-size: 2.2rem;
        font-weight: 800;
        font-family: 'Outfit', sans-serif;
    }
    
    .contact-info-card p {
        color: #C9A84C;
        font-size: 1.15rem;
        line-height: 1.8;
        margin-bottom: 30px;
        font-weight: 500;
    }
    
    .contact-method {
        display: flex;
        align-items: center;
        gap: 20px;
        margin-bottom: 25px;
        padding: 25px;
        background: #fdfbf7;
        border-radius: 20px;
        border: 1px solid #f3ebd5;
        transition: all 0.3s ease;
    }

    .contact-method:hover {
        border-color: var(--page-accent);
        background: #fff;
        box-shadow: 0 10px 20px rgba(201, 168, 76, 0.1);
    }
    
    .contact-method i {
        font-size: 24px;
        color: var(--page-accent);
        width: 60px;
        height: 60px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 18px;
        box-shadow: 0 5px 15px rgba(201, 168, 76, 0.1);
    }
    
    .contact-method div h4 {
        margin: 0;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #999;
    }
    
    .contact-method div p {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 700;
        color: #333;
    }

    .contact-form-card {
        padding: 40px;
        background: #ffffff;
        border-radius: 30px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #333;
    }

    .form-group input, .form-group textarea {
        width: 100%;
        padding: 15px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 1rem;
        color: #333;
        transition: all 0.3s ease;
    }

    .form-group input:focus, .form-group textarea:focus {
        border-color: var(--page-accent);
        outline: none;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(201, 168, 76, 0.1);
    }

    .submit-btn {
        background: var(--page-accent);
        color: #fff;
        padding: 18px 30px;
        border: none;
        border-radius: 15px;
        font-size: 1.1rem;
        font-weight: 700;
        cursor: pointer;
        width: 100%;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(201, 168, 76, 0.3);
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
                    <h2>We're Here to Help</h2>
                    <p>At ADDAAX, we value the customers we serve and are willing to assist. If you've got a query or need help, wish to raise an issue or just want to provide your feedback, we are ready to hear from you.</p>
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

                    <p style="margin-top: 30px; font-style: italic; font-size: 0.95rem; color: #999;">We strive to reply to all inquiries within 24 hours. Urgent issues like frauds or security concerns are prioritised.</p>
                </div>

                <div class="contact-form-card">
                    <h3 style="margin-top: 0; margin-bottom: 25px; font-weight: 800; font-size: 1.5rem; color: var(--page-accent);">Send us a Message</h3>
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
