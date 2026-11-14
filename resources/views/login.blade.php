<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo-strfm.jpg') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">

    <style>
        .login-image {
            background: url('{{ asset('assets/img/logo-strfm.jpg') }}') no-repeat center center;
            background-size: cover;
            min-height: 300px;
        }
    </style>
</head>

<body>

    <div class="container d-flex justify-content-center align-items-center">
        <div class="row login-container">
            <div class="col-md-6 login-image"></div>
            <div class="col-md-6 login-form">
                <h3 class="mb-4 text-center">LOGIN</h3>
                <form action="{{ url('/login/checklogin') }}" method="post">
                    @csrf
                    <input type="hidden" name="id" value="1">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" id="username" placeholder="Your Username">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="password" placeholder="Your Password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    <p class="mt-3 text-center">Return Home <a href="{{ url('/') }}">Here</a></p>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: @json(session('error')),
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif
</body>

</html>
