<?php 

  session_start();
  include('db.php') 
  
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" /></head>
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
                <input id="username" name="username" type="text" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="john@doe.com" />
                <label for="username" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Username</label>
          </div>
          <div class="relative mt-8">
                <input id="password" name="password" type="password" class="peer h-10 w-full border-b-2 border-gray-300 text-gray-900 placeholder-transparent focus:outline-none focus:border-black" placeholder="Password" />
                <label for="Password" class="absolute left-0 -top-3.5 text-gray-600 text-sm transition-all peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-2 peer-focus:-top-3.5 peer-focus:text-gray-600 peer-focus:text-sm">Password</label>
          </div>
                <?php if (isset($_SESSION['error'])) : ?>
                  <div class="mt-3 bg-red-100 border-l-4 border-red-500 text-red-700 p-3">
                    <strong>Error: </strong>
                    <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                    ?>
                  </div>
                <?php endif ?>
          <div class="mt-8">
                <button type="submit" id="loginBtn" name="login_user" class="border-2 border-black bg-black text-white py-1 w-full rounded-md hover:bg-transparent hover:text-black font-semibold">Login</button>
          </div>
          <div class="mt-3">
                <p class="text-center">Not yet a member? <a href="register.php" class=" text-blue-500 font-semibold">Sign Up</a></p>
          </div>
          </div>
          </div>
        </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>