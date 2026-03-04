<?php
include 'db_connect.php';
include 'navbar.php';

$message_status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_inquiry'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $sql = "INSERT INTO inquiries (name, email, subject, message) VALUES ('$name', '$email', '$subject', '$message')";

    if (mysqli_query($conn, $sql)) {
        $message_status = "success";
    } else {
        $message_status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About & Contact | Melody Masters Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root { 
            --gold: #e2b04a; 
            --gold-dark: #c19235;
            --bg: #0f172a; 
            --card: #1e293b; 
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);
        }

        body { 
            font-family: 'Poppins', sans-serif; 
            background: var(--bg); 
            color: #f8fafc; 
            margin: 0; 
            line-height: 1.6;
        }

        /* --- Original Styles Start --- */
        .alert { padding: 15px; border-radius: 12px; margin-bottom: 25px; text-align: center; font-weight: 500; }
        .alert-success { background: rgba(0, 255, 127, 0.1); color: #00ff7f; border: 1px solid #00ff7f; }
        .alert-error { background: rgba(255, 107, 157, 0.1); color: #ff6b9d; border: 1px solid #ff6b9d; }

        .page-header { 
            background: linear-gradient(rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.85)), url('uploads/about-bg.jpg');
            background-size: cover; background-position: center; background-attachment: fixed;
            text-align: center; padding: 100px 20px; border-bottom: 1px solid rgba(226, 176, 74, 0.2);
        }

        .container { max-width: 1200px; margin: -60px auto 60px; padding: 0 20px; }
        .section-card { background: var(--card); padding: 50px; border-radius: 25px; box-shadow: 0 20px 40px rgba(0,0,0,0.3); margin-bottom: 40px; border: 1px solid rgba(255,255,255,0.03); }
        .about-grid { display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 50px; align-items: center; }
        .about-text h2 { color: var(--gold); font-size: 2.2em; margin-bottom: 20px; }
        .team-grid { display: flex; justify-content: center; gap: 30px; margin-top: 20px; }
        .team-member img { width: 110px; height: 110px; border-radius: 50%; object-fit: cover; border: 2px solid var(--gold); padding: 4px; }
        .contact-grid { display: grid; grid-template-columns: 1fr 1.5fr; gap: 30px; }
        .info-box { background: rgba(30, 41, 59, 0.5); padding: 40px; border-radius: 25px; border-left: 4px solid var(--gold); }
        .info-item { display: flex; align-items: center; gap: 20px; margin-bottom: 25px; }
        .info-icon { width: 45px; height: 45px; background: rgba(226, 176, 74, 0.1); color: var(--gold); display: flex; align-items: center; justify-content: center; border-radius: 12px; }

        input, textarea { width: 100%; padding: 14px; margin-bottom: 18px; background: #0f172a; border: 1px solid #334155; border-radius: 12px; color: white; }
        .btn-submit { background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%); color: #0f172a; font-weight: 600; border: none; padding: 16px; border-radius: 12px; width: 100%; cursor: pointer; text-transform: uppercase; letter-spacing: 1.5px; }
        /* --- Original Styles End --- */

        /* --- CHATBOT STYLES --- */
        .chat-btn {
            position: fixed; bottom: 30px; right: 30px;
            background: var(--gold); color: var(--bg);
            width: 60px; height: 60px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; cursor: pointer; z-index: 1000;
            box-shadow: 0 10px 25px rgba(226, 176, 74, 0.4);
        }
        .chat-window {
            position: fixed; bottom: 100px; right: 30px;
            width: 320px; background: var(--card);
            border: 1px solid var(--border); border-radius: 20px;
            display: none; flex-direction: column;
            overflow: hidden; z-index: 1000;
            box-shadow: 0 15px 50px rgba(0,0,0,0.5);
        }
        .chat-header { background: var(--gold); color: var(--bg); padding: 15px; font-weight: 700; display: flex; justify-content: space-between; }
        .chat-body { height: 300px; padding: 15px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; background: #161e2e; }
        .chat-footer { padding: 10px; display: flex; gap: 5px; background: var(--card); border-top: 1px solid var(--border); }
        .msg { padding: 8px 12px; border-radius: 12px; font-size: 0.85em; line-height: 1.4; }
        .bot-msg { background: #334155; color: white; align-self: flex-start; border-bottom-left-radius: 2px; }
        .user-msg { background: var(--gold); color: var(--bg); align-self: flex-end; border-bottom-right-radius: 2px; }
        .chat-footer input { margin-bottom: 0; padding: 8px; flex: 1; font-size: 0.9em; }
        .chat-footer button { background: var(--gold); border: none; padding: 0 15px; border-radius: 8px; cursor: pointer; color: var(--bg); }

        @media (max-width: 992px) { .about-grid, .contact-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="page-header">
    <h1 style="font-size: 3.5em; letter-spacing: 8px; margin: 0; color: #fff;">MELODY MASTERS</h1>
    <p style="color: var(--gold); font-size: 1.1em; letter-spacing: 3px; margin-top: 10px;">PREMIUM INSTRUMENTS & EXPERTISE</p>
</div>

<div class="container">
    <?php if ($message_status == "success"): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> Inquiry received successfully!</div>
    <?php elseif ($message_status == "error"): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> Something went wrong.</div>
    <?php endif; ?>

    <div class="section-card">
        <div class="about-grid">
            <div class="about-text">
                <h2>Our Musical Story</h2>
                <p>Melody Masters provides a professional platform for musicians to find high-end instruments that resonate with their passion.</p>
                <p>Since 2024, we've been the heartbeat of the local music community.</p>
            </div>
            <div style="text-align: center;">
                <h3 style="color: #fff; margin-bottom: 25px;">The Team</h3>
                <div class="team-grid">
                    <div class="team-member">
                        <img src="uploads/staff1.jpg" alt="CEO">
                        <p style="margin: 5px 0 0; font-weight: 600;">Raween Kanishka</p>
                        <small style="color: var(--gold);">Founder & CEO</small>
                    </div>
                    <div class="team-member">
                        <img src="uploads/staff2.jpg" alt="Director">
                        <p style="margin: 5px 0 0; font-weight: 600;">Lavan Abishek</p>
                        <small style="color: var(--gold);">Technical Director</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="contact-grid">
        <div class="info-box">
            <h2 style="color: var(--gold); margin-top: 0;">Connect With Us</h2>
            <div class="info-item"><div class="info-icon"><i class="fas fa-map-marker-alt"></i></div><div>Colombo, Sri Lanka</div></div>
            <div class="info-item"><div class="info-icon"><i class="fas fa-phone-alt"></i></div><div>+94 112 345 678</div></div>
            <div class="info-item"><div class="info-icon"><i class="fas fa-envelope"></i></div><div>support@melodymasters.com</div></div>
        </div>

        <div class="section-card" id="contact" style="margin-bottom:0;">
            <h3>Send an Inquiry</h3>
            <form action="" method="POST">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <input type="text" name="name" placeholder="Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <input type="text" name="subject" placeholder="Subject">
                <textarea name="message" rows="4" placeholder="Your Message..." required></textarea>
                <button type="submit" name="submit_inquiry" class="btn-submit">Send Message</button>
            </form>
        </div>
    </div>
</div>

<div class="chat-btn" onclick="toggleChat()"><i class="fas fa-comment-dots"></i></div>
<div class="chat-window" id="chatWindow">
    <div class="chat-header">
        <span>Melody Support</span>
        <i class="fas fa-times" onclick="toggleChat()" style="cursor:pointer;"></i>
    </div>
    <div class="chat-body" id="chatBody">
        <div class="msg bot-msg">Hello! I'm Melody AI. How can I help you today?</div>
    </div>
    <div class="chat-footer">
        <input type="text" id="chatInput" placeholder="Ask me something...">
        <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
function toggleChat() {
    const chat = document.getElementById('chatWindow');
    chat.style.display = (chat.style.display === 'flex') ? 'none' : 'flex';
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const body = document.getElementById('chatBody');
    const msg = input.value.trim();

    if (msg !== "") {
        body.innerHTML += `<div class="msg user-msg">${msg}</div>`;
        input.value = "";
        body.scrollTop = body.scrollHeight;

        setTimeout(() => {
            let response = "I'll let our team know! Anything else?";
            let lowerMsg = msg.toLowerCase();

            if (lowerMsg.includes("hello") || lowerMsg.includes("hi")) {
                response = "Hi there! Looking for a premium instrument?";
            } else if (lowerMsg.includes("price") || lowerMsg.includes("how much")) {
                response = "Prices vary by model. Check our Shop page for details!";
            } else if (lowerMsg.includes("location") || lowerMsg.includes("where")) {
                response = "We are based in Colombo, Sri Lanka.";
            } else if (lowerMsg.includes("thank")) {
                response = "You're welcome! Happy playing! 🎸";
            }

            body.innerHTML += `<div class="msg bot-msg">${response}</div>`;
            body.scrollTop = body.scrollHeight;
        }, 800);
    }
}
// Allow 'Enter' key to send message
document.getElementById("chatInput").addEventListener("keypress", function(event) {
    if (event.key === "Enter") {
        sendMessage();
    }
});
</script>

</body>
</html>