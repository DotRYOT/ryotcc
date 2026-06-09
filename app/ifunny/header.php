<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>iFunny Media Viewer</title>
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="logo.svg">
  <!-- Bootstrap CSS for layout and carousel -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      background-color: #121212;
      color: #ffffff;
    }

    .navbar {
      background-color: #1f1f1f;
    }

    .carousel-item img,
    .carousel-item video {
      max-height: 82vh;
      object-fit: contain;
      margin: auto;
    }

    .carousel-caption {
      background: rgba(0, 0, 0, 0.6);
      border-radius: 8px;
      padding: 10px;
    }

    .card-body {
      display: -webkit-flex;
      display: flex;
      -webkit-flex-direction: column-reverse;
      flex-direction: column-reverse;
      -webkit-flex-wrap: nowrap;
      flex-wrap: nowrap;
      -webkit-justify-content: flex-end;
      justify-content: flex-end;
      -webkit-align-content: stretch;
      align-content: stretch;
      -webkit-align-items: flex-start;
      align-items: flex-start;
    }
  </style>
</head>

<body>
  <nav class="navbar navbar-expand-lg navbar-dark mb-4 shadow-sm">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="logo.svg" alt="Logo" width="36" height="36" class="me-2 rounded-1">
        <span class="fw-bold" style="color: #FFCC00;">iFunny</span>&nbsp;Media
      </a>
      <div class="navbar-nav ms-auto">
        <a class="nav-link" href="index.php">Upload Links</a>
        <a class="nav-link" href="view.php">View Media</a>
      </div>
    </div>
  </nav>
  <div class="container">