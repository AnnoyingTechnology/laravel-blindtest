<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Blindtest</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/css/app.scss', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
		<div class="container mt-5 vh-100">
			<div class="row h-100 justify-content-center align-items-center">
				<div class="col-md-4">
					<form action="{{ route('chat.login') }}" method="POST">
						@csrf
						<div class="input-group rounded shadow-lg">
							<input type="text" class="form-control form-control-lg" name="username" placeholder="Enter your firstname" autofocus required>
							<button type="submit" class="btn btn-primary btn-lg">
								<span class="fa fa-arrow-right"></span>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>
    </body>
</html>
