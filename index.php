<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CaliTex Delivery</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Bienvenido a Calitex Dlivery</h1>
        <div class="row justify-content-center">
            <div class="col-md-4">
                <form method="post" action="login.php">
                    <div class="mb-3">
                        <label for="email">Correo electronico</label>
                        <input type="email" name="email" class="form-conmtrol" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraaeña</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">ingresar</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>