<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WaxTaan - Messagerie du Sénégal</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/home.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="home-body">
    <!-- Header -->
    <header class="main-header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="logo">
                    <span class="logo-icon">💬</span>
                    <span class="logo-text">WaxTaan</span>
                </div>
                <div class="nav-menu">
                    <a href="connexion/login.php" class="nav-btn login">Se connecter</a>
                    <a href="connexion/register.php" class="nav-btn register">S'inscrire</a>
                </div>
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-container">
            <div class="hero-content">
                <div class="hero-badge">
                    🇸🇳 Made in Sénégal
                </div>
                <h1 class="hero-title">
                    Connectez-vous avec
                    <span class="highlight">vos proches</span>
                </h1>
                <p class="hero-subtitle">
                    WaxTaan est la messagerie moderne conçue pour les Sénégalais. 
                    Simple, rapide et sécurisée.
                </p>
                <div class="hero-actions">
                    <a href="connexion/register.php" class="cta-primary">
                        <span>Commencer maintenant</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                            <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <a href="connexion/login.php" class="cta-secondary">
                        J'ai déjà un compte
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="chat-mockup">
                    <div class="chat-header">
                        <div class="chat-avatar">AM</div>
                        <div class="chat-info">
                            <div class="chat-name">Aminata</div>
                            <div class="chat-status">En ligne</div>
                        </div>
                    </div>
                    <div class="chat-messages">
                        <div class="message received">
                            <div class="message-text">Salut ! Comment tu vas ?</div>
                            <div class="message-time">14:30</div>
                        </div>
                        <div class="message sent">
                            <div class="message-text">Ça va bien alhamdoulillah ! Et toi ?</div>
                            <div class="message-time">14:32</div>
                        </div>
                        <div class="message received">
                            <div class="message-text">Parfait ! On se voit ce soir ? 😊</div>
                            <div class="message-time">14:33</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>Pourquoi choisir WaxTaan ?</h2>
                <p>Une messagerie pensée pour vous</p>
            </div>
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">⚡</div>
                    <h3>Ultra rapide</h3>
                    <p>Messages instantanés même avec une connexion lente</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔒</div>
                    <h3>Sécurisé</h3>
                    <p>Vos conversations restent privées et protégées</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">👥</div>
                    <h3>Groupes</h3>
                    <p>Créez des groupes pour famille, amis et collègues</p>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📱</div>
                    <h3>Simple</h3>
                    <p>Interface intuitive et facile à utiliser</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Prêt à commencer ?</h2>
                <p>Rejoignez des milliers d'utilisateurs qui font confiance à WaxTaan</p>
                <a href="connexion/register.php" class="cta-button">
                    Créer mon compte gratuitement
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <div class="logo">
                        <span class="logo-icon">💬</span>
                        <span class="logo-text">WaxTaan</span>
                    </div>
                    <p>La messagerie moderne du Sénégal</p>
                </div>
                <div class="footer-links">
                    <a href="#">À propos</a>
                    <a href="#">Confidentialité</a>
                    <a href="#">Support</a>
                    <a href="#">Contact</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 WaxTaan. Fait avec Fait avec le groupe 2 de XML❤️ au Sénégal</p>
            </div>
        </div>
    </footer>

    <script src="js/home.js"></script>
</body>
</html>
    