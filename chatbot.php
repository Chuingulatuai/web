<?php
include 'connect_db.php';

// Kiểm tra thời gian trò chuyện
if (!isset($_SESSION['chat_start_time'])) {
    $_SESSION['chat_start_time'] = time();
} else {
    // Xóa lịch sử trò chuyện nếu thời gian trò chuyện lớn hơn 10 phút
    if (time() - $_SESSION['chat_start_time'] > 600) {
        unset($_SESSION['chat_history']);
        $_SESSION['chat_start_time'] = time();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = isset($_POST['message']) ? strtolower(trim($_POST['message'])) : '';

    // Khởi tạo lịch sử trò chuyện nếu chưa tồn tại
    if (!isset($_SESSION['chat_history'])) {
        $_SESSION['chat_history'] = [];
    }
    $_SESSION['chat_history'][] = ['user' => $userMessage, 'time' => time()];

    $botResponse = handleUserMessage($userMessage, $con);
    $_SESSION['chat_history'][] = ['bot' => $botResponse, 'time' => time()];

    echo $botResponse;
    $con->close();
    exit();
}

function handleUserMessage($message, $con)
{
    if (
        strpos($message, 'kiểm tra đơn hàng') !== false ||
        strpos($message, 'trạng thái đơn hàng') !== false ||
        strpos($message, 'đơn hàng của tôi') !== false ||
        strpos($message, 'theo dõi đơn hàng') !== false
    ) {
        return checkOrder($message, $con);
    } elseif (
        strpos($message, 'sản phẩm rẻ nhất') !== false ||
        strpos($message, 'xe rẻ nhất') !== false ||
        strpos($message, 'mẫu xe giá thấp nhất') !== false ||
        strpos($message, 'giá xe thấp nhất') !== false
    ) {
        return getCheapestProduct($con);
    } elseif (
        strpos($message, 'sản phẩm mắc nhất') !== false ||
        strpos($message, 'xe mắc nhất') !== false ||
        strpos($message, 'mẫu xe giá cao nhất') !== false ||
        strpos($message, 'giá xe cao nhất') !== false
    ) {
        return getMostExpensiveProduct($con);
    } elseif (
        strpos($message, 'phương thức thanh toán') !== false ||
        strpos($message, 'cách thanh toán') !== false ||
        strpos($message, 'thanh toán như thế nào') !== false ||
        strpos($message, 'hình thức thanh toán') !== false
    ) {
        return getPaymentMethods();
    } elseif (
        strpos($message, 'thông tin sản phẩm') !== false ||
        strpos($message, 'chi tiết sản phẩm') !== false ||
        strpos($message, 'thông tin xe') !== false ||
        strpos($message, 'chi tiết xe') !== false ||
        strpos($message, 'các thông số kỹ thuật') !== false
    ) {
        return getProductInfo($message, $con);
    } else {
        return "Xin lỗi, tôi không hiểu câu hỏi của bạn. <br>Bạn có thể hỏi về: <br>
        - Kiểm tra đơn hàng bằng cách hỏi 'kiểm tra đơn hàng [ID]'. <br>
        - Sản phẩm rẻ nhất<br>
        - Sản phẩm mắc nhất<br>
        - Phương thức thanh toán<br>
        - Thông tin sản phẩm bằng cách hỏi 'thông tin sản phẩm [tên sản phẩm]'.<br>";
    }
}

function checkOrder($message, $con)
{
    preg_match('/kiểm tra đơn hàng\s*(\d+)/', $message, $matches);
    $orderId = isset($matches[1]) ? trim($matches[1]) : '';

    if (!empty($orderId)) {
        $query = "SELECT id, name, email, phone, address, content, created_time, last_updated, status, payment_method 
                  FROM orders 
                  WHERE id = ?";

        $stmt = $con->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return formatOrderSummary($result);
        } else {
            return "Xin lỗi, không tìm thấy đơn hàng nào với ID '" . htmlspecialchars($orderId) . "'.";
        }
        $stmt->close();
    } else {
        return "Xin vui lòng cung cấp ID đơn hàng bạn muốn kiểm tra.";
    }
}

function formatOrderSummary($result)
{
    $order = $result->fetch_assoc();
    $statusText = getOrderStatusText($order['status']);

    return "Thông tin đơn hàng của bạn:<br>
            ID: " . htmlspecialchars($order['id']) . "<br>
            Tên: " . htmlspecialchars($order['name']) . "<br>
            Email: " . htmlspecialchars($order['email']) . "<br>
            Điện thoại: " . htmlspecialchars($order['phone']) . "<br>
            Địa chỉ: " . htmlspecialchars($order['address']) . "<br>
            Nội dung: " . htmlspecialchars($order['content']) . "<br>
            Thời gian tạo: " . date('d/m/Y H:i:s', $order['created_time']) . "<br>
            Cập nhật lần cuối: " . date('d/m/Y H:i:s', $order['last_updated']) . "<br>
            Tình trạng: " . $statusText . "<br>
            Phương thức thanh toán: " . htmlspecialchars($order['payment_method']) . "<br><br>";
}

function getOrderStatusText($status)
{
    switch ($status) {
        case 0:
            return '<span style="color: orange;">Đang xử lý</span>';
        case 1:
            return '<span style="color: blue;">Đang giao hàng</span>';
        case 2:
            return '<span style="color: green;">Thành công</span>';
        case 3:
            return '<span style="color: red;">Bị hủy</span>';
        default:
            return '<span style="color: gray;">Không xác định</span>';
    }
}

function getCheapestProduct($con)
{
    $query = "SELECT name, price_new FROM product ORDER BY price_new ASC LIMIT 1";
    $result = $con->query($query);

    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        return "Sản phẩm rẻ nhất là: " . htmlspecialchars($product['name']) . " với giá " . number_format($product['price_new'], 0, ',', '.') . " VNĐ.";
    } else {
        return "Xin lỗi, không tìm thấy sản phẩm nào.";
    }
}

function getMostExpensiveProduct($con)
{
    $query = "SELECT name, price_new FROM product ORDER BY price_new DESC LIMIT 1";
    $result = $con->query($query);

    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        return "Sản phẩm mắc nhất là: " . htmlspecialchars($product['name']) . " với giá " . number_format($product['price_new'], 0, ',', '.') . " VNĐ.";
    } else {
        return "Xin lỗi, không tìm thấy sản phẩm nào.";
    }
}

function getPaymentMethods()
{
    return "Hiện tại chúng tôi chấp nhận các phương thức thanh toán sau: <br>
            - Thanh toán khi nhận hàng (COD)<br>
            - Ví điện tử Momo<br>
            Nếu bạn cần thêm thông tin chi tiết, hãy cho tôi biết!";
}

function getProductInfo($message, $con)
{
    preg_match('/thông tin sản phẩm\s*(.*)/', $message, $matches);
    $productName = isset($matches[1]) ? trim($matches[1]) : '';

    if (!empty($productName)) {
        $query = "SELECT name, price_new, content FROM product WHERE name LIKE ?";
        $stmt = $con->prepare($query);
        $likeProductName = "%" . $productName . "%";
        $stmt->bind_param("s", $likeProductName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return formatProductInfo($result);
        } else {
            return "Xin lỗi, không tìm thấy sản phẩm nào với tên '" . htmlspecialchars($productName) . "'.";
        }
        $stmt->close();
    } else {
        return "Xin vui lòng cung cấp tên sản phẩm bạn muốn biết thông tin.";
    }
}

function formatProductInfo($result)
{
    $product = $result->fetch_assoc();
    return "Thông tin sản phẩm:<br>
            Tên: " . htmlspecialchars($product['name']) . "<br>
            Giá: " . number_format($product['price_new'], 0, ',', '.') . " VNĐ<br>
            Mô tả: " . htmlspecialchars(strip_tags($product['content'])) . "<br>";
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }

        .chat-container {
            max-width: 400px;
            height: 500px;
            position: fixed;
            bottom: 120px;
            right: 20px;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            border-radius: 15px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            display: none;
            flex-direction: column;
            justify-content: space-between;
            z-index: 1000;
            border: 2px solid gray;
            padding: 0;
        }

        .chat-box {
            padding: 15px;
            height: 80%;
            overflow-y: auto;
            background-color: #fff;
            border-radius: 15px 15px 0 0;
            box-shadow: inset 0px 4px 12px rgba(0, 0, 0, 0.05);
        }

        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
            background-color: #fff;
            border-radius: 0 0 15px 15px;
            box-shadow: inset 0px -4px 12px rgba(0, 0, 0, 0.05);
        }

        .chat-input input {
            width: 100%;
            padding: 10px;
            border-radius: 20px;
            border: 1px solid #ccc;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transition: border 0.3s ease;
        }

        .chat-input button {
            padding: 10px;
            margin-left: 5px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 50%;
            transition: background-color 0.3s ease;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .chat-input button:hover {
            background-color: #0056b3;
        }

        .user-message,
        .bot-message {
            margin: 5px 0;
            padding: 10px;
            border-radius: 10px;
            max-width: 80%;
        }

        .user-message {
            background-color: #d1e7dd;
            align-self: flex-end;
        }

        .bot-message {
            background-color: #f8d7da;
            align-self: flex-start;
        }

        #toggleChat {
            position: fixed;
            bottom: 25px;
            right: 25px;
            width: 60px; /* Increased size */
            height: 60px; /* Increased size */
            background: linear-gradient(45deg, #007bff, #0056b3); /* Gradient color */
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: flex; /* To center the icon */
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease; /* Smooth transition */
            animation: pulse-animation 2.5s infinite cubic-bezier(0.66, 0, 0, 1);
        }

        #toggleChat:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        #toggleChat.open {
            background: linear-gradient(45deg, #6c757d, #343a40);
            animation: none; /* Stop pulsing when open */
            transform: scale(1);
        }

        #toggleChat i {
            font-size: 28px; /* Larger icon */
            transition: transform 0.3s ease-in-out;
        }

        @keyframes pulse-animation {
            0% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 6px 16px rgba(0, 123, 255, 0.4);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            }
        }

        .clear-button {
            background-color: #dc3545;
            color: white;
            padding: 5px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            margin-left: 5px;
        }

        .clear-button:hover {
            background-color: #c82333;
        }
    </style>
</head>

<body>
    <button id="toggleChat"><i class="fas fa-comments"></i></button>
    <div class="chat-container" id="chatContainer">
        <div class="chat-box" id="chatBox"></div>
        <div class="chat-input">
            <input type="text" id="userInput" placeholder="Nhập câu hỏi của bạn..." onkeypress="checkEnter(event)">
            <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            <button class="clear-button" onclick="clearChat()"><i class="fas fa-trash"></i></button>
        </div>
    </div>

    <script>
        document.getElementById('toggleChat').onclick = function() {
            const chatContainer = document.getElementById('chatContainer');
            const toggleButton = this; // The button itself
            const icon = toggleButton.querySelector('i');

            const isOpening = chatContainer.style.display === 'none' || chatContainer.style.display === '';

            if (isOpening) {
                chatContainer.style.display = 'flex';
                toggleButton.classList.add('open');
                icon.classList.remove('fa-comments');
                icon.classList.add('fa-xmark'); // Use fa-xmark for FA6
                initChatbot();
            } else {
                chatContainer.style.display = 'none';
                toggleButton.classList.remove('open');
                icon.classList.remove('fa-xmark');
                icon.classList.add('fa-comments'); // Change back to chat icon
            }
        }

        function checkEnter(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        async function sendMessage() {
            const userInput = document.getElementById('userInput');
            const userMessage = userInput.value.trim();
            if (userMessage === '') return;

            const chatBox = document.getElementById('chatBox');
            chatBox.innerHTML += `<div class="user-message">${userMessage}</div>`;
            userInput.value = '';

            // Lưu lịch sử vào local storage
            saveChatHistory({
                user: userMessage
            });

            try {
                const response = await fetch('chatbot.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'message=' + encodeURIComponent(userMessage)
                });
                const data = await response.text();
                chatBox.innerHTML += `<div class="bot-message">${data}</div>`;
                chatBox.scrollTop = chatBox.scrollHeight;

                // Lưu phản hồi của bot vào local storage
                saveChatHistory({
                    bot: data
                });
            } catch (error) {
                console.error('Có lỗi xảy ra:', error);
                chatBox.innerHTML += `<div class="bot-message">Có lỗi trong quá trình xử lý yêu cầu.</div>`;
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        function initChatbot() {
            const chatBox = document.getElementById('chatBox');
            const welcomeMessage = "Xin chào! Bạn có thể hỏi về: <br>" +
                "- Kiểm tra đơn hàng<br>" +
                "- Sản phẩm rẻ nhất<br>" +
                "- Sản phẩm mắc nhất<br>" +
                "- Phương thức thanh toán<br>" +
                "- Thông tin sản phẩm<br>";
            chatBox.innerHTML = `<div class="bot-message">${welcomeMessage}</div>`;
            chatBox.scrollTop = chatBox.scrollHeight; // Cuộn xuống cuối cùng

            // Tải lịch sử từ local storage
            loadChatHistory();
        }

        function saveChatHistory(message) {
            let history = JSON.parse(localStorage.getItem('chatHistory')) || [];
            history.push({
                ...message,
                time: new Date().toISOString()
            });
            localStorage.setItem('chatHistory', JSON.stringify(history));
        }

        function loadChatHistory() {
            const chatBox = document.getElementById('chatBox');
            const history = JSON.parse(localStorage.getItem('chatHistory')) || [];
            history.forEach(entry => {
                if (entry.user) {
                    chatBox.innerHTML += `<div class="user-message">${entry.user}</div>`;
                }
                if (entry.bot) {
                    chatBox.innerHTML += `<div class="bot-message">${entry.bot}</div>`;
                }
            });
            chatBox.scrollTop = chatBox.scrollHeight; // Cuộn xuống cuối cùng
        }

        function clearChat() {
            localStorage.removeItem('chatHistory');
            document.getElementById('chatBox').innerHTML = '';
            initChatbot(); // Khởi tạo lại chatbot
        }
    </script>
</body>

</html>