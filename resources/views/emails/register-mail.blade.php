<!DOCTYPE html>
<html>
    <head>
        @vite('resources/css/app.css')
    </head>
    <body>
            <div class="p-2 flex flex-col justify-center gap-3 w-[50%]">
                <h1 class="text-xl sm:text-2xl lg:text-3xl text-sky-900 font-semibold">Welcome {{ $user['fullname'] ?? "Guest"}} to Educonnect</h1>
                <p>Welcome to <span class="text-sky-900 font-bold">Educonnect</span>, your gateway to a seamless and engaging educational 
                    experience! We’re excited to have you on board as part of our growing learning community.
                </p>
                <!-- <img class="w-[60%]" src="{{ asset('svgs/Welcome-bro.svg') }}" alt="welcome image"> -->
                <a href="http://localhost:5173/about" target="_blank" class="py-2 px-5 bg-sky-900 text-white font-medium rounded-sm w-max hover:bg-sky-700">About Us</a>
            </div>
    </body>
</html>