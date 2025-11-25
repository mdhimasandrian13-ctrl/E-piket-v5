<?php
/**
 * ============================================
 * E-PIKET SMEKDA - Landing Page
 * ============================================
 * File: index.php
 * Deskripsi: Halaman utama landing page sistem
 * ============================================
 */

session_start();

// Jika sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    $role = $_SESSION['role'];
    
    if ($role == 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($role == 'guru') {
        header("Location: guru/dashboard.php");
    } elseif ($role == 'siswa') {
        header("Location: siswa/dashboard.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Piket SMEKDA - Sistem Monitoring Piket Digital</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }
        
        /* Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .navbar-custom.scrolled {
            padding: 10px 0;
            box-shadow: 0 2px 30px rgba(0, 0, 0, 0.15);
        }
        
        .navbar-brand {
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .navbar-brand img{
            height: 40px;
            width: auto;
            object-fit: contain;
        }

        .nav-link-custom {
            color: #333;
            font-weight: 500;
            margin: 0 15px;
            transition: all 0.3s;
            position: relative;
        }
        
        .nav-link-custom:hover {
            color: #667eea;
        }
        
        .nav-link-custom::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
        }
        
        .nav-link-custom:hover::after {
            width: 100%;
        }
        
        .btn-login {
            padding: 10px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
            padding-top: 80px;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            top: -250px;
            right: -250px;
            animation: float 6s ease-in-out infinite;
        }
        
        .hero-section::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            bottom: -150px;
            left: -150px;
            animation: float 8s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            color: white;
        }
        
        .hero-title {
            font-size: 56px;
            font-weight: 800;
            margin-bottom: 20px;
            animation: fadeInUp 0.8s ease;
        }
        
        .hero-subtitle {
            font-size: 20px;
            margin-bottom: 30px;
            opacity: 0.95;
            animation: fadeInUp 1s ease;
        }
        
        .hero-buttons {
            animation: fadeInUp 1.2s ease;
        }
        
        .btn-hero {
            padding: 15px 40px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 30px;
            margin: 10px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-hero-primary {
            background: white;
            color: #667eea;
            border: 2px solid white;
        }
        
        .btn-hero-primary:hover {
            background: transparent;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .btn-hero-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }
        
        .btn-hero-outline:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }
        
        .hero-image {
            position: relative;
            z-index: 2;
            animation: fadeInRight 1s ease;
        }
        
        .hero-image img {
            width: 100%;
            max-width: 500px;
            filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        /* Features Section */
        .features-section {
            padding: 100px 0;
            background: #f8f9fa;
        }
        
        .section-title {
            text-align: center;
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 60px;
            color: #333;
        }
        
        .feature-card {
            background: white;
            padding: 40px 30px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.2);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 36px;
            color: white;
        }
        
        .feature-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #333;
        }
        
        .feature-desc {
            color: #666;
            line-height: 1.8;
        }
        
        /* Stats Section */
        .stats-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 18px;
            opacity: 0.9;
        }
        
        /* About Section */
        .about-section {
            padding: 100px 0;
            background: white;
        }
        
        .about-image {
            position: relative;
        }
        
        .about-image img {
            width: 100%;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        }
        
        .about-content h2 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 25px;
            color: #333;
        }
        
        .about-content p {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 20px;
        }
        
        .check-list {
            list-style: none;
            padding: 0;
        }
        
        .check-list li {
            padding: 10px 0;
            font-size: 16px;
            color: #333;
        }
        
        .check-list li i {
            color: #28a745;
            margin-right: 10px;
        }
        
        /* CTA Section */
        .cta-section {
            padding: 80px 0;
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 42px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .cta-section p {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        /* Footer */
        .footer {
            background: #1a1a2e;
            color: white;
            padding: 60px 0 30px;
        }
        
        .footer h5 {
            font-weight: 600;
            margin-bottom: 20px;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            display: block;
            padding: 5px 0;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            color: white;
            padding-left: 5px;
        }
        
        .social-links a {
            display: inline-block;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .social-links a:hover {
            background: #667eea;
            transform: translateY(-3px);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 40px;
            padding-top: 20px;
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 36px;
            }
            
            .hero-subtitle {
                font-size: 16px;
            }
            
            .section-title {
                font-size: 32px;
            }
            
            .hero-image {
                margin-top: 40px;
            }
            
            .btn-hero {
                padding: 12px 30px;
                font-size: 14px;
            }
        }
        
        /* Scroll Animation */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
            <img src="./asset/img/logo_E_Piket_7.png" alt="Logo E-Piket SMEKDA"
            style="height: 40px; width: auto ;margin-right: 0;">
            <i>E-PIKET SMEKDA</i>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link-custom" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-custom" href="#contact">Kontak</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a href="auth/login.php" class="btn-login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Hero Section -->
    <section class="hero-section" id="home">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Sistem Piket Digital SMKN 2 Surabaya</h1>
                        <p class="hero-subtitle">Monitoring piket siswa secara real-time, efisien, dan terintegrasi. Tingkatkan kedisiplinan dengan teknologi modern.</p>
                        <div class="hero-buttons">
                            <a href="auth/login.php" class="btn-hero btn-hero-primary">
                                <i class="fas fa-rocket"></i> Mulai Sekarang
                            </a>
                            <a href="#features" class="btn-hero btn-hero-outline">
                                <i class="fas fa-info-circle"></i> Pelajari Lebih Lanjut
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image text-center">
                    <img src="./asset/img/logo_E_Piket_8.png"
                        alt="Logo E-Piket SMEKDA"
                        class="img-fluid"
                        style=" height: 500px; width: auto;">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <div class="stat-number"><i class="fas fa-users"></i> 500+</div>
                        <div class="stat-label">Siswa Terdaftar</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <div class="stat-number"><i class="fas fa-chalkboard-teacher"></i> 50+</div>
                        <div class="stat-label">Guru Aktif</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <div class="stat-number"><i class="fas fa-check-circle"></i> 98%</div>
                        <div class="stat-label">Tingkat Kehadiran</div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-item fade-in">
                        <div class="stat-number"><i class="fas fa-calendar-check"></i> 24/7</div>
                        <div class="stat-label">Monitoring Real-time</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features-section" id="features">
        <div class="container">
            <h2 class="section-title fade-in">Fitur Unggulan</h2>
            <div class="row justify-content-center">
                <div class="col-md-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="feature-title">Monitoring Real-time</h3>
                        <p class="feature-desc">Pantau kehadiran piket siswa secara langsung dengan dashboard yang informatif dan mudah dipahami.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3 class="feature-title">Laporan Otomatis</h3>
                        <p class="feature-desc">Generate laporan kehadiran piket otomatis dengan berbagai format dan periode yang dapat disesuaikan.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-mobile-alt"></i>
                        </div>
                        <h3 class="feature-title">Mobile Friendly</h3>
                        <p class="feature-desc">Akses sistem dari mana saja menggunakan smartphone, tablet, atau komputer dengan tampilan responsif.</p>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-4">
                <div class="col-md-4">
                    <div class="feature-card fade-in">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h3 class="feature-title">Keamanan Terjamin</h3>
                        <p class="feature-desc">Data terlindungi dengan enkripsi dan sistem keamanan berlapis untuk menjaga privasi pengguna.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- About Section -->
    <section class="about-section" id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                <img src="./asset/img/logo_E_Piket_5.png"
                     alt="Tentang E-Piket SMEKDA"
                     class="img-fluid"
                             
                </div>
                </div>
                <div class="col-lg-6">
                    <div class="about-content fade-in">
                        <h2>Tentang E-Piket SMEKDA</h2>
                        <p>E-Piket SMEKDA adalah sistem monitoring piket digital yang dirancang khusus untuk SMKN 2 Surabaya. Sistem ini membantu sekolah dalam mengelola dan memonitor kehadiran piket siswa secara efisien dan terorganisir.</p>
                        <p>Dengan menggunakan teknologi terkini, E-Piket SMEKDA memberikan solusi modern untuk meningkatkan kedisiplinan dan tanggung jawab siswa dalam menjalankan tugas piket.</p>
                        <ul class="check-list">
                            <li><i class="fas fa-check-circle"></i> Sistem terintegrasi dan user-friendly</li>
                            <li><i class="fas fa-check-circle"></i> Monitoring real-time 24/7</li>
                            <li><i class="fas fa-check-circle"></i> Laporan lengkap dan terperinci</li>
                            <li><i class="fas fa-check-circle"></i> Multi-level akses (Admin, Guru, Siswa)</li>
                            <li><i class="fas fa-check-circle"></i> Dashboard interaktif dan informatif</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <h2 class="fade-in">Siap Menggunakan E-Piket SMEKDA?</h2>
            <p class="fade-in">Bergabunglah dengan ratusan pengguna yang telah merasakan kemudahan monitoring piket digital</p>
            <a href="auth/login.php" class="btn-hero btn-hero-primary fade-in">
                <i class="fas fa-sign-in-alt"></i> Login Sekarang
            </a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="footer" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5><img src="./asset/img/logo_E_Piket_9.png" alt="Logo E-PIKET SMEKDA"
                    style="width: 90px; height: autox;"> <i>E-PIKET SMEKDA</i></h5>
                    <p style="color: rgba(255,255,255,0.7); line-height: 1.8;">
                        Sistem monitoring piket digital untuk SMKN 2 Surabaya. Meningkatkan kedisiplinan dengan teknologi.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Menu</h5>
                    <a href="#home">Beranda</a>
                    <a href="#features">Fitur</a>
                    <a href="#about">Tentang</a>
                    <a href="auth/login.php">Login</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Informasi</h5>
                    <a href="#">Panduan Pengguna</a>
                    <a href="#">FAQ</a>
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat & Ketentuan</a>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Kontak</h5>
                    <p style="color: rgba(255,255,255,0.7);">
                        <i class="fas fa-map-marker-alt"></i> JL. Tentara Genie Pelajar No.26 Petemon, Kec Sawahan, Surabaya<br>
                        <i class="fas fa-phone"></i> (031)5343708<br>
                        <i class="fas fa-envelope"></i> smekda.surabaya@gmail.com
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 E-Piket SMEKDA. All Rights Reserved. Developed By Marcelino <i class="fas fa-heart" style="color: #e74c3c;"></i></p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar-custom');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
        
        // Smooth scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Scroll animation
        function checkScroll() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                if (elementTop < windowHeight - 100) {
                    element.classList.add('visible');
                }
            });
        }
        
        window.addEventListener('scroll', checkScroll);
        window.addEventListener('load', checkScroll);
        
        // Counter animation
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target;
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 20);
        }
        
        // Trigger counter on scroll
        let countersTriggered = false;
        window.addEventListener('scroll', function() {
            if (!countersTriggered) {
                const statsSection = document.querySelector('.stats-section');
                const rect = statsSection.getBoundingClientRect();
                if (rect.top < window.innerHeight) {
                    countersTriggered = true;
                    // Add counter animation if needed
                }
            }
        });
        
        console.log('🚀 E-Piket SMEKDA Landing Page loaded successfully!');
    </script>
</body>
</html>