<!doctype html>
<html lang="en" data-bs-theme="dark">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blindtest</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="bg-dark">
        <div id="app" class="container">
            <audio id="audioplayer"></audio>
            <div id="chatroom" class="bg-black card mt-5 border-0 shadow-lg rounded-lg overflow-hidden">
                <div class="p-4">

                    <h1 class="float-start">
                        <span class="fa fa-music"></span> <strong>Blind</strong>test
                    </h1>
                    
                    <div class="rounded bg-dark text-light p-3 float-end">
                        <span class="badge text-bg-success">/next [genre|year]</span> pulls a new random track<br />
                        <span class="badge text-bg-primary">/ff</span> fast forwards in the track<br />
                        <span class="badge text-bg-info">/giveup</span> shows track info<br />
                        <span class="badge text-bg-warning">/reset</span> resets the scores<br />
                    </div>

                    <p class="lead float-start pt-3 ps-2">
                        Tune in, guess on
                    </p>

                </div>
                <div class="p-4">

                    <div 
                    id="messages" 
                    class="overflow-y-scroll overflow-x-hidden text-light fs-5">
                    </div>
                    
                    <div class="mt-4">
                        <input 
                        type="text" 
                        id="messageInput" 
                        class="w-100 p-2 form-control text-bg-dark form-control-lg" 
                        placeholder="Type the track title, remixer, artist, a command or just chat" 
                        autofocus>
                    </div>

                </div>
            </div>
        </div>
    </body>
</html>