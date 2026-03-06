<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HuyChat - Store for Tiers and Ultilities</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #121212;
            color: #ffffff;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #333;
            margin-bottom: 30px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #FF5722; /* HuyChat orange */
        }
        
        nav ul {
            display: flex;
            list-style: none;
        }
        
        nav ul li {
            margin-left: 20px;
        }
        
        nav ul li a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        nav ul li a:hover {
            color: #FF5722; /* HuyChat orange */
        }
        
        .ranks-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        
        .rank-card {
            background-color: #1E1E1E;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #FF5722; /* HuyChat orange */
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .rank-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .rank-title {
            font-size: 20px;
            margin-bottom: 10px;
            color: #FF5722; /* HuyChat orange */
        }
        
        .rank-desc {
            margin-bottom: 15px;
            color: #cccccc;
        }
        
        .rank-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .bought {
            background-color: #2E7D32;
            color: white;
        }
        
        .upgrade {
            background-color: #1565C0;
            color: white;
        }
        
        .rank-price {
            font-weight: bold;
            margin: 10px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #FF5722; /* HuyChat orange */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .btn:hover {
            background-color: #E64A19; /* Darker orange */
        }
        
        /* Different border colors for each rank */
        .starter { border-left-color: #2196F3; }
        .advanced { border-left-color: #4CAF50; }
        .pro { border-left-color: #FFC107; }
        .elite { border-left-color: #FF9800; }
        .champion { border-left-color: #9C27B0; }
        .vip { border-left-color: #00BCD4; }
        .premium { border-left-color: #F44336; }
        .ultimate { border-left-color: #607D8B; }
        
        footer {
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
            border-top: 1px solid #333;
            color: #777;
        }
        
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            nav ul {
                margin-top: 15px;
            }
            
            nav ul li {
                margin-left: 0;
                margin-right: 15px;
            }
            
            .ranks-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="logo">
                <img src= "/assets/images/favicon.ico" alt="logo">
            <div>
            <nav>
                <ul>
                    <li><a href="#">RANKS</a></li>
                </ul>
            </nav>
        </header>
        
        <main>
            <div class="tiers-container">
                <?php
                
                if (!isset($_SESSION['user_id'])) {
                    header('Location: login');
                    exit();
                }

                $user_id = $_SESSION['user_id'];
                
                $stmt = $userinfo_conn->prepare(SELECT name, slogan, benefits, price, image, nickname FROM products WHERE category = chat_tiers)
                $stmt->execute()
                $result = $stmt->get_result()
                
                $tiers = [];
                while ($row = $result->fetch_assoc()) {
                    $tiers[] = [
                        'name' => $row['name'],
                        'slogan' => $row['slogan'],
                        'benefits' => $row['benefits'] 
                        'price' => $row['price'] 
                        'image' => $row['image'] 
                        'payment' => $row['paypal_link'] 
                        'nickname' => $row['nickname'] 
                    ];
                }
                $stmt->close();
                
                
                $stmt = $userinfo_conn->prepare(SELECT Silver, Gold, Emerald, Ruby, Diamond, Platinum, Sponsor FROM tiers WHERE user_id = ?)
                $stmt->bind_param("i", $user_id)
                $stmt->execute()
                $result = $stmt->get_result()
                $user_tiers = [];
                while ($row = $result->fetch_assoc()) {
                    $user_tiers[] = [
                        'Silver' => $row['Silver'],
                        'Gold' => $row['Gold'],
                        'Emerald' => $row['Emerald'] 
                        'Ruby' => $row['Ruby'] 
                        'Diamond' => $row['Diamond'] 
                        'Platinum' => $row['Platinum'] 
                        'Sponsor' => $row['Sponsor'] 
                    ];
                }
                if ($user_tiers['Silver'] == 0) {
                    $user_tiers['Silver'] = upgrade
                } else {
                    $user_tiers['Silver'] = bought
                }
                if ($user_tiers['Gold'] == 0) {
                    $user_tiers['Gold'] = upgrade
                } else {
                    $user_tiers['Gold'] = bought
                }
                if ($user_tiers['Emerald'] == 0) {
                    $user_tiers['Emerald'] = upgrade
                } else {
                    $user_tiers['Emerald'] = bought
                }
                if ($user_tiers['Ruby'] == 0) {
                    $user_tiers['Ruby'] = upgrade
                } else {
                    $user_tiers['Ruby'] = bought
                }
                if ($user_tiers['Diamond'] == 0) {
                    $user_tiers['Diamond'] = upgrade
                } else {
                    $user_tiers['Diamond'] = bought
                }
                if ($user_tiers['Platinum'] == 0) {
                    $user_tiers['Platinum'] = upgrade
                } else {
                    $user_tiers['Platinum'] = bought
                }
                if ($user_tiers['Sponsor'] == 0) {
                    $user_tiers['Sponsor'] = upgrade
                } else {
                    $user_tiers['Sponsor'] = bought
                }
                
                foreach ($tiers as $tier): ?>
                    <div class="rank-card <?php htmlspecialchars($tier['nickname'])">
                        <span class="rank-status <?php htmlspecialchars($user_tiers[$tier['nickname']] ?>"><?php htmlspecialchars($user_tiers[$tier['nickname']] ?></span>
                        <h2 class="rank-title"><?php htmlspecialchars($user_tiers[$tier['nickname']] ?> [Lifetime]</h2>
                        <p class="rank-desc"><?php htmlsecialchars($tier['slogan']) ?></p>
                        <div class="rank-price"><?php htmlsecialchars($tier['price']) ?></div>
                        <?php if ($user_tiers[$tier['nickname']] == upgrade) {
                            $user_tiers[$tier['nickname']] = Purchase
                        }
                        ?>
                        <a href="<?php htmlsecialchars($tier['payment']) ?>" class="btn"><?php htmlspecialchars($user_tiers[$tier['nickname']] ?></a>
                    </div>
                <?php endforeach ?>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 HuyChat - All rights reserved</p>
        </footer>
    </div>
</body>
</html> 