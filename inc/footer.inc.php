<?php
// Footer unifié - Composant réutilisable
?>

<footer class="footer">
    <div class="footer-container">
        <!-- Section Logo & Mission -->
        <div class="footer-brand-section">
            <div class="footer-brand">
                <a href="index.php" class="footer-logo">
                    <img src="<?php echo ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/assets/logo.png'; ?>" alt="logo" style="width: 35px; height: 35px; object-fit: contain;">
                    <img src="<?php echo ($_SERVER['REQUEST_SCHEME'] ?? 'http') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/assets/claque.png'; ?>" alt="claque" style="width: 35px; height: 35px; object-fit: contain; margin-left: 5px;">
                </a>
            </div>
            <p class="footer-mission">
                Bridging generations through the art of cooking.
            </p>
        </div>

        <!-- Main Footer Grid -->
        <div class="footer-grid">
            <!-- About Us -->
            <div class="footer-section">
                <h4><?= isset($tr['about_us']) ? $tr['about_us'] : 'About Us' ?></h4>
                <p><?= isset($tr['about_desc']) ? $tr['about_desc'] : 'Discover our international mission to connect generations through culinary heritage.' ?></p>
            </div>

            <!-- Questions -->
            <div class="footer-section">
                <h4><?= isset($tr['questions']) ? $tr['questions'] : 'Questions' ?></h4>
                <p><?= isset($tr['questions_desc']) ? $tr['questions_desc'] : 'Need help? Find answers on how to use the platform and join meetings.' ?></p>
            </div>

            <!-- Legal Information -->
            <div class="footer-section">
                <h4><?= isset($tr['legal']) ? $tr['legal'] : 'Legal information' ?></h4>
                <ul>
                    <li><a href="pages/terms.php"><?= isset($tr['terms']) ? $tr['terms'] : 'User agreement and terms of service' ?></a></li>
                    <li><a href="pages/privacy.php"><?= isset($tr['privacy']) ? $tr['privacy'] : 'Privacy policy' ?></a></li>
                </ul>
            </div>

            <!-- Social & Trending -->
            <div class="footer-section">
                <h4><?= isset($tr['follow_us']) ? $tr['follow_us'] : 'Follow our journey' ?></h4>
                <div class="footer-social">
                    <a href="#" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link" title="TikTok"><i class="fab fa-tiktok"></i></a>
                    <a href="#" class="social-link" title="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <!-- Trending Groups -->
        <div class="footer-trending">
            <h4><?= isset($tr['trending_groups']) ? $tr['trending_groups'] : 'Trending groups' ?></h4>
            <div class="trending-list">
                <a href="index.php?communautes" class="trending-item">
                    <span class="trending-name">AlbanianKitchen</span>
                    <span class="trending-members">57K Members</span>
                </a>
                <a href="index.php?communautes" class="trending-item">
                    <span class="trending-name">StreetFood</span>
                    <span class="trending-members">268K Members</span>
                </a>
                <a href="index.php?communautes" class="trending-item">
                    <span class="trending-name">VeganRecipes</span>
                    <span class="trending-members">130K Members</span>
                </a>
                <a href="index.php?communautes" class="trending-item">
                    <span class="trending-name">GourmetCooking</span>
                    <span class="trending-members">85K Members</span>
                </a>
                <a href="index.php?communautes" class="trending-item see-more">
                    <?= isset($tr['see_more']) ? $tr['see_more'] : 'See More' ?>
                </a>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> claque - <?= isset($tr['all_rights']) ? $tr['all_rights'] : 'All rights reserved' ?></p>
        </div>
    </div>

    <style>
        .footer {
            background: #FEF5F1;
            border-top: 1px solid #E5D7CC;
            padding: 40px 24px 20px;
            margin-top: 60px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-brand-section {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 40px;
            padding-bottom: 30px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .footer-logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .footer-mission {
            font-size: 16px;
            font-weight: 600;
            color: #F86015;
            margin: 0;
            line-height: 1.4;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #E5D7CC;
        }

        .footer-section h4 {
            font-size: 14px;
            font-weight: 700;
            color: #634444;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 12px 0;
            font-family: 'Baloo', cursive;
        }

        .footer-section p {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
            margin: 0;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section li {
            margin-bottom: 8px;
        }

        .footer-section a {
            font-size: 13px;
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-section a:hover {
            color: #F86015;
        }

        .footer-social {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .social-link {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #FFE8CC;
            border-radius: 50%;
            color: #F86015;
            font-size: 16px;
            transition: all 0.2s;
            text-decoration: none;
        }

        .social-link:hover {
            background: #F86015;
            color: white;
            transform: scale(1.1);
        }

        .footer-trending {
            margin-bottom: 30px;
        }

        .footer-trending h4 {
            font-size: 14px;
            font-weight: 700;
            color: #634444;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 16px 0;
            font-family: 'Baloo', cursive;
        }

        .trending-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px;
        }

        .trending-item {
            background: white;
            padding: 12px 14px;
            border-radius: 12px;
            text-decoration: none;
            border: 1px solid #E5D7CC;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .trending-item:hover {
            border-color: #F86015;
            box-shadow: 0 2px 8px rgba(248, 96, 21, 0.1);
        }

        .trending-name {
            font-weight: 600;
            color: #F86015;
            font-size: 13px;
        }

        .trending-members {
            font-size: 12px;
            color: #999;
        }

        .trending-item.see-more {
            background: #FFE8CC;
            border: none;
            align-items: center;
            justify-content: center;
            color: #F86015;
            font-weight: 600;
            padding: 16px;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #E5D7CC;
        }

        .footer-bottom p {
            font-size: 12px;
            color: #999;
            margin: 0;
        }

        @media (max-width: 768px) {
            .footer {
                padding: 30px 16px 16px;
            }

            .footer-brand-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 30px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .trending-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</footer>
