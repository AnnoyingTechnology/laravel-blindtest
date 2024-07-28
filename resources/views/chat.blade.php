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
            <audio id="audioplayer" src="{{ App\Models\Track::getCurrent()->getUrl(); }}" autoplay></audio>
            <div id="chatroom" class="bg-black card mt-5 border-0 shadow-lg rounded-lg overflow-hidden">
                <div class="p-4">

                    <h1 class="float-start">
                        <span class="fa fa-music"></span> <strong>Blind</strong>test
                    </h1>

                    <p class="lead float-start pt-3 ps-2">
                        Tune in, guess on
                    </p>

                </div>
                <div class="p-4">

                    <div 
                    id="messages" 
                    class="overflow-y-scroll overflow-x-hidden text-light fs-5">
                    </div>
                    
                    <div class="input-group input-group-lg mt-4">
                       
                        <input 
                        type="text" 
                        id="messageInput" 
                        class="form-control" 
                        placeholder="Type the track title, remix, artist, a command or just chat" 
                        autofocus>


                            <button 
                            class="btn btn-outline-light command" 
                            type="button" 
                            data-command="/next" 
                            data-toggle="tooltip" 
                            title="pulls a new random track">
                                /next
                            </button>
                            
                            <button 
                            class="btn btn-outline-light command" 
                            data-command="/ff" 
                            title="fast forwards in the track by 30sec">
                                /ff
                            </button>

                            <button 
                            class="btn btn-outline-light command" 
                            data-command="/clue" 
                            title="Discloses random letters but lowers scorable points">
                                /clue
                            </button>
                            
                            <button 
                            class="btn btn-outline-info command" 
                            data-command="/giveup" 
                            title="shows track info and rejects new answers">
                                /giveup
                            </button>
                            
                            <button 
                            class="btn btn-outline-warning command" 
                            data-command="/reset" 
                            title="Resets the scores of all users">
                                /reset
                            </button>
                
                    </div>

                </div>
            </div>
        </div>
    </body>
</html>