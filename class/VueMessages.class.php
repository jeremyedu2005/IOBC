<?php

class VueMessages
{
    public function __toString()
    {
        return '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Messages</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>

:root {
    --bg: #FEF5F1;
    --orange: #F86015;
    --green: #03A272;
    --pink: #D81B60;
    --blue: #078CDF;
    --brown: #634444;
    --text: #7c6f6a;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: var(--bg);
}

/* TOPBAR */
.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 25px;
    background: #FEF5F1;
}

.logo {
    font-weight: bold;
    font-size: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.logo i {
    color: var(--orange);
}

.top-right {
    display: flex;
    align-items: center;
    gap: 15px;
}

.btn {
    background: var(--orange);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
}

.search {
    display: flex;
    align-items: center;
    background: white;
    padding: 6px 10px;
    border-radius: 20px;
}

.search input {
    border: none;
    background: transparent;
    outline: none;
    margin-left: 5px;
}

/* LAYOUT */
.container {
    display: flex;
    height: calc(100vh - 70px);
    border-radius: 10px;
    overflow: hidden;
}

/* SIDEBAR */
.sidebar {
    width: 70px;
    background: #FEF5F1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 20px;
    gap: 25px;

}

.sidebar i {
    font-size: 18px;
    color: black;
    cursor: pointer;
}

/* CHAT LIST */
.chat-list {
    width: 320px;
    background: white;
    border-right: 1px solid #ddd;
    overflow-y: auto;
    border-top-left-radius: 10px;
    border-bottom-left-radius: 10px;

}

.chat-item {
    display: flex;
    gap: 10px;
    padding: 12px;
    border-bottom: 1px solid #eee;
    align-items: center;
    justify-content: space-between;
}

.chat-left {
    display: flex;
    gap: 10px;
    align-items: center;
}

.avatar {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
}

.chat-text {
    display: flex;
    flex-direction: column;
}

.name {
    font-weight: bold;
    color: var(--brown);
    font-size: 14px;
}

.preview {
    font-size: 13px;
    color: var(--text);
}

.tag {
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    color: white;
    margin-left: 5px;
}

.genz { background: #078CDF; }
.genx { background: #D81B60; }
.geny { background: #03A272; }
.boomer { background: #d322f7; }

.meta {
    text-align: right;
    font-size: 11px;
    color: var(--text);
}

.notif {
    background: var(--orange);
    color: white;
    font-size: 11px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-top: 5px;
}

/* CHAT WINDOW */
.chat-window {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f7f7f7;
}

.chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: white;
    border-bottom: 1px solid #ddd;
    font-weight: bold;
}

.chat-actions i {
    margin-left: 15px;
    cursor: pointer;
}

/* MESSAGES */
.messages {
    flex: 1;
    padding: 20px;
}

.bubble {
    max-width: 300px;
    padding: 10px 14px;
    border-radius: 15px;
    margin-bottom: 10px;
    font-size: 14px;
}

.received {
    background: #eaeaea;
}

.sent {
    background: var(--orange);
    color: white;
    margin-left: auto;
}

.message-img {
    width: 220px;
    border-radius: 15px;
    margin-bottom: 10px;
}

/* INPUT */
.input-bar {
    display: flex;
    padding: 10px;
    background: white;
    align-items: center;
}

.input-bar input {
    flex: 1;
    padding: 10px;
    border-radius: 20px;
    border: 1px solid #ccc;
    outline: none;
}

.input-icons {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-left: 10px;
}

.send-btn {
    background: var(--orange);
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
/* FOOTER */
.footer {
    background: white;
    margin-top: 15px;
    border-top: 1px solid #eee;
    padding: 40px 60px 20px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    box-shadow: 0 -2px 8px rgba(0,0,0,0.05);
}

.footer-section {
    max-width: 300px;
    margin-bottom: 20px;
}

.footer-title {
    font-weight: bold;
    color: var(--brown);
    margin-bottom: 12px;
    font-size: 15px;
}

.footer-text {
    font-size: 13px;
    color: var(--text);
    line-height: 1.7;
    text-align: justify;
}

.footer-icons {
    display: flex;
    gap: 15px;
    font-size: 20px;
    color: black;
    margin-top: 12px;
}

.footer-icons i {
    cursor: pointer;
    transition: 0.2s;
}

.footer-icons i:hover {
    opacity: 0.7;
}

/* Bottom line */
.footer-bottom {
    width: 100%;
    text-align: center;
    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid #eee;
    font-size: 12px;
    color: var(--text);
}
</style>
</head>

<body>

<div class="topbar">
    <div class="logo">
        LOGO <i class="fa-solid fa-globe"></i>
    </div>

    <div class="top-right">
        <div class="search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input placeholder="Search">
        </div>
        <div class="btn">Connexion</div>
    </div>
</div>

<div class="container">

    <div class="sidebar">
        <i class="fa-solid fa-house"></i>
        <i class="fa-solid fa-magnifying-glass"></i>
        <i class="fa-solid fa-users"></i>
        <i class="fa-solid fa-comment"></i>
        <i class="fa-regular fa-bookmark"></i>
        <i class="fa-regular fa-calendar"></i>
        <i class="fa-regular fa-user"></i>
    </div>

    <div class="chat-list">

        <div class="chat-item">
            <div class="chat-left">
                <img class="avatar" src="assets/img/stella.avif">
                <div class="chat-text">
                    <div class="name">Stella <span class="tag genz">GEN Z</span></div>
                    <div class="preview">My sweet Renée, your cookies are a hit!</div>
                </div>
            </div>
            <div class="meta">
                15:46
                <div class="notif">3</div>
            </div>
        </div>

        <div class="chat-item">
            <div class="chat-left">
                <img class="avatar" src="assets/img/theo.jpg">
                <div class="chat-text">
                    <div class="name">Theo <span class="tag genz">GEN Z</span></div>
                    <div class="preview">See you on Saturday?</div>
                </div>
            </div>
            <div class="meta">
                15:20
                <div class="notif">1</div>
            </div>
        </div>

        <div class="chat-item">
            <div class="chat-left">
                <img class="avatar" src="assets/img/maria.jpg">
                <div class="chat-text">
                    <div class="name">Maria <span class="tag boomer">BOOMER</span></div>
                    <div class="preview">Yes, absolutely!</div>
                </div>
            </div>
            <div class="meta">
                12:29
                <div class="notif">3</div>
            </div>
        </div>


        <div class="chat-item">
            <div class="chat-left">
                <img class="avatar" src="assets/img/janelle.avif">
                <div class="chat-text">
                    <div class="name">Janelle <span class="tag genx">GEN X</span></div>
                    <div class="preview">Do you have any recipe...</div>
                </div>
            </div>
            <div class="meta">
                11:20
                <div class="notif">1</div>
            </div>
        </div>

    </div>

    <div class="chat-window">

        <div class="chat-header">
            Stella
            <div class="chat-actions">
                <i class="fa-solid fa-video"></i>
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>

        <div class="messages">

            <div class="bubble received">Hi! I’m gonna do your recipe.</div>
            <div class="bubble received">I hope they turn out delicious</div>

            <img class="message-img" src="assets/img/cookies.jpeg">

            <div class="bubble received">My sweet Renée, your cookies are a hit!</div>

            <div class="bubble sent">Thank you so much! I’m so glad that you made the recipe.</div>

        </div>

        <div class="input-bar">
            <input placeholder="Write a message...">
            <div class="input-icons">
                <i class="fa-regular fa-face-smile"></i>
                <i class="fa-solid fa-paperclip"></i>
                <div class="send-btn">
                    <i class="fa-solid fa-microphone"></i>
                </div>
            </div>
        </div>

    </div>

</div>

<div class="footer">

    <div class="footer-section">
        <div class="footer-title">User Agreement</div>
        <div class="footer-text">
            By using this platform, you agree to comply with our terms and conditions. 
            We are committed to protecting your data and ensuring a safe and respectful 
            environment for all users.
        </div>
    </div>

    <div class="footer-section">
        <div class="footer-title">About Us</div>
        <div class="footer-text">
            Kami is a multicultural platform that connects people through food, 
            communication, and shared experiences. Our goal is to make global 
            interaction simple, inclusive, and meaningful.
        </div>
    </div>

    <div class="footer-section">
        <div class="footer-title">Questions</div>
        <div class="footer-text">
            Need help or have any questions? Feel free to contact our support team. 
            We are here to assist you anytime and ensure the best experience possible.
        </div>

        <div class="footer-icons">
            <i class="fa-brands fa-facebook"></i>
            <i class="fa-brands fa-instagram"></i>
        </div>
    </div>

    <div class="footer-bottom">
    © 2026 Kami Platform — All rights reserved.
    </div>

</div>


</body>
</html>
';
    }
}
?>
