    <?php // src/login.php
    session_start();
    require 'db.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        // ค้นหาผู้ใช้จากฐานข้อมูล
        $query = "SELECT username, user_role FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_role'] = $user['user_role']; // เก็บ role ของผู้ใช้

            if ($user['user_role'] === 'admin') {
                header("Location: admin_dashboard.php"); // ถ้าเป็น admin ให้ไปหลังบ้าน
            } else {
                header("Location: index.php"); // ถ้าเป็น user ทั่วไปให้ไปหน้าหลัก
            }

            exit;
        } else {
            $error = "อีเมลหรือรหัสผ่านไม่ถูกต้อง";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login Page</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" />
    </head>
    <body class="bg-gray-100">
        <div class="selection:bg-black selection:text-white">
            <div class="min-h-screen bg-gray-100 flex justify-center items-center">
                <div class="p-8 flex-1">
                    <div class="w-80 bg-white rounded-3xl mx-auto overflow-hidden shadow-xl">
                        <div class="relative h-48 bg-black rounded-bl-4xl">
                            <svg class="absolute -bottom-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                            <path fill="#ffffff" fill-opacity="1" d="M0,64L48,80C96,96,192,128,288,128C384,128,480,96,576,85.3C672,75,768,85,864,122.7C960,160,1056,224,1152,245.3C1248,267,1344,245,1392,234.7L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                            </svg>
                        </div>
                        <div class="px-10 pt-4 pb-8 bg-white rounded-tr-4xl border-none">
                            <h1 class="text-3xl block text-center font-semibold"><i class="fa-solid fa-user"></i> Login</h1>
                            <form action="login_db.php" method="post">
                                <div class="relative mt-8">
                                    <input id="username" name="username" type="text" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Username" />
                                    <label for="username" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Username</label>
                                </div>
                                <div class="relative mt-8">
                                    <input id="password" name="password" type="password" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Password" />
                                    <label for="password" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Password</label>
                                </div>
                                <?php if (isset($_SESSION['errors'])) : ?>
                                    <div class="mt-3 bg-red-100 border-l-4 border-red-500 text-red-700 p-3">
                                        <strong>Error:</strong>
                                        <ul class="list-disc pl-5">
                                            <?php 
                                                foreach ($_SESSION['errors'] as $error) {
                                                    echo "<li>$error</li>";
                                                }
                                                unset($_SESSION['errors']);
                                            ?>
                                        </ul>
                                    </div>
                                <?php endif ?>
                                <div class="mt-8">
                                    <button type="submit" id="loginBtn" name="login_user" class="border-2 border-black bg-black text-white py-1 w-full rounded-md hover:bg-transparent hover:text-black font-semibold">Login</button>
                                </div>
                                <div class="mt-3">
                                    <p class="text-center">Not yet a member? <a href="register.php" class=" text-blue-500 font-semibold">Sign Up</a></p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
                <div class="bg-white p-6 rounded-lg shadow-lg w-80 text-center">
                    <h2 class="text-xl font-semibold mb-2">ยืนยันตัวตน</h2>
                    <p class="text-gray-600">กรุณาใส่ PIN 6 หลัก</p>
                    <input type="password" id="pin-input" name="pin" maxlength="6" class="w-full px-3 py-2 border rounded-lg mt-3 text-center" placeholder="กรอก PIN">
                    <p id="pin-error" class="text-red-500 text-sm hidden mt-2"></p>
                                                
                    <div class="flex mt-4 space-x-2">
                        <button onclick="submitPin()" class="flex-1 bg-black border-2 border-black text-white py-2 rounded-lg hover:bg-transparent hover:text-black font-semibold">ยืนยัน</button>
                        <button onclick="closeModal()" class="flex-1 bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition">ยกเลิก</button>
                    </div>
                </div>
            </div> 

            <script>
                function submitPin() {
                    let pin = document.getElementById('pin-input').value;
                    let pinError = document.getElementById('pin-error');

                    fetch("verify_pin.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: "pin=" + encodeURIComponent(pin)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.href = "index.php"; // ✅ เปลี่ยนหน้าเมื่อยืนยัน PIN สำเร็จ
                        } else {
                            pinError.classList.remove('hidden'); 
                            pinError.textContent = "❌ PIN ไม่ถูกต้อง!";
                        }
                    });
                }

                function closeModal() {
                    document.getElementById('modal').classList.add('hidden');
                }
            </script>
        </div>

        <?php if (isset($_SESSION['show_pin_popup']) && $_SESSION['show_pin_popup'] === true) : ?>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    document.getElementById('modal').classList.remove('hidden');
                });
            </script>
            <?php 
                unset($_SESSION['show_pin_popup']); // เคลียร์ค่าหลังจากแสดง popup
            endif;
        ?>

    </body>
    </html>
