<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMKS HKBP Siantar - Footer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        .footer {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 40px 30px 20px;
            position: relative;
            z-index: 100;
            width: 100%;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-section {
            flex: 1;
            min-width: 250px;
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .footer-logo img {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }
        
        .footer-school-name {
            font-size: 16px;
            font-weight: 700;
        }
        
        .footer-about {
            font-size: 14px;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .footer-section h3 {
            font-size: 18px;
            margin-bottom: 15px;
            position: relative;
            padding-bottom: 10px;
        }
        
        .footer-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background-color: rgba(255, 255, 255, 0.5);
        }
        
        .contact-info {
            list-style: none;
        }
        
        .contact-info li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .contact-info svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        .quick-links {
            list-style: none;
        }
        
        .quick-links li {
            margin-bottom: 12px;
        }
        
        .quick-links a {
            color: white;
            text-decoration: none;
            font-size: 14px;
            opacity: 0.9;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .quick-links a:hover {
            opacity: 1;
            transform: translateX(5px);
        }
        
        .quick-links svg {
            width: 14px;
            height: 14px;
        }
        
        .social-media {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        
        .social-icon {
            width: 36px;
            height: 36px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .social-icon:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }
        
        .social-icon svg {
            width: 18px;
            height: 18px;
        }
        
        .footer-bottom {
            margin-top: 40px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 13px;
            opacity: 0.8;
        }
        
        @media (max-width: 768px) {
            .footer-content {
                flex-direction: column;
                gap: 40px;
            }
            
            .footer-section {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo">
                    <img src="img/logo-smkshkbpsiantar.png" alt="SMKS HKBP Siantar Logo">
                    <div class="footer-school-name">SMKS HKBP SIANTAR</div>
                </div>
                <p class="footer-about">
                    SMKS HKBP Siantar adalah lembaga pendidikan kejuruan yang berdedikasi untuk mengembangkan potensi siswa dan mempersiapkan mereka menjadi tenaga profesional yang handal dan berakhlak mulia.
                </p>
                <div class="social-media">
                    <a href="#" class="social-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                    <a href="#" class="social-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                    </a>
                    <a href="#" class="social-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                
            </div>
            
            <div class="footer-section">
            <h3>Informasi Kontak</h3>
                <ul class="contact-info">
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Jl. Pendidikan No. 123, Siantar, Sumatera Utara</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                        <span>(061) 1234567</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>info@smkshkbpsiantar.sch.id</span>
                    </li>
                    <li>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Senin - Jumat: 07:30 - 15:30<br>Sabtu: 07:30 - 12:00</span>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 SMKS HKBP Siantar. Hak Cipta Dilindungi.</p>
        </div>
    </footer>
</body>
</html>