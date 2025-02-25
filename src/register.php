<?php 
    session_start();
    require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="icon" href="./img/icon.png">
</head>
<body>
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
                <h1 class="text-3xl block text-center font-semibold"><i class="fa-regular fa-registered"></i> Register</h1>
                    <form id="register-form" action="register_db.php" method="post" onsubmit="return checkEmail();">
                        <div class="relative mt-8">
                            <input type="text" name="username" id="username" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Username">
                            <label for="username" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Username</label>
                        </div>
                        <div class="relative mt-8">
                            <input type="email" name="email" id="email" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Email">
                            <label for="email" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Email</label>
                        </div>
                        <div class="relative mt-8">
                            <input type="password" name="password_1" id="password_1" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Password">
                            <label for="password_1" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Password</label>
                        </div>
                        <div class="relative mt-8">
                            <input type="password" name="password_2" id="password_2" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Confirm password">
                            <label for="password_2" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Confirm password</label>
                        </div>
                        <?php if (isset($_SESSION['errors'])) : ?>
                                <div class="mt-3 bg-red-100 border-l-4 border-red-500 text-red-700 p-3">
                                    <strong>Error:</strong>
                                    <ul>
                                        <?php 
                                            foreach ($_SESSION['errors'] as $error) {
                                                echo "<li>$error</li>";
                                            }
                                            unset($_SESSION['errors']);
                                        ?>
                                    </ul>
                                </div>
                            <?php endif ?>
                                        
                        <div class="mt-5">
                            <button type="submit" id="reg_user" name="reg_user" class="border-2 border-black bg-black text-white py-1 w-full rounded-md hover:bg-transparent hover:text-black font-semibold">Register</button>
                        </div>
                        <div class="mt-3">
                            <p class="text-center">Already registered <a href="login.php" class="text-blue-500 font-semibold">Sign in</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded-lg shadow-lg w-80 text-center">
            <h2 class="text-xl font-semibold mb-2">ยืนยันอีเมล</h2>
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
        function checkEmail() {
            let email = document.getElementById('email').value;
            if (email === 'nawaphol@gmail.com') {
                document.getElementById('modal').classList.remove('hidden');
                document.getElementById('pin-error').classList.add('hidden'); // ซ่อน error ทุกครั้งที่เปิด modal
                return false; // หยุดการ submit ไว้ก่อน
            }
            return true;
        }

        function submitPin() {
            let pin = document.getElementById('pin-input').value;
            let pinError = document.getElementById('pin-error');
                
            if (pin === "232542") {
                // ✅ สร้าง input ซ่อนเพื่อส่งค่า PIN ไปกับฟอร์ม
                let pinInputHidden = document.createElement("input");
                pinInputHidden.type = "hidden";
                pinInputHidden.name = "pin";
                pinInputHidden.value = pin;
                document.getElementById("register-form").appendChild(pinInputHidden);
            
                document.getElementById('modal').classList.add('hidden');
                document.getElementById('register-form').submit(); // 🔹 ส่งฟอร์ม
            } else {
                pinError.classList.remove('hidden');
                pinError.textContent = "❌ PIN ไม่ถูกต้อง!";
            }
        }



        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
    }
    </script>
</div>

</body>
</html>