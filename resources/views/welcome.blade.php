<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>PoliSlot</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" href="{{ asset('assets/img/Polibatam.png') }}" type="image/x-icon" />
  <meta name="description" content="">
  <meta name="keywords" content="">

  <style>
    .card-img-top {
      aspect-ratio: 3 / 4;
      object-fit: cover;
    }

    /* Hide login/register links on desktop, show dropdown trigger */
    .navmenu .masuk-desktop {
      display: block;
    }
    .navmenu .masuk-mobile {
      display: none;
    }

    /* On mobile view, hide the dropdown trigger and show the direct links */
    .mobile-nav-active .navmenu .masuk-desktop {
      display: none;
    }
    .mobile-nav-active .navmenu .masuk-mobile {
      display: block;
    }
  </style>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/aos/aos.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">

  <!-- =======================================================
  * Template Name: Bootslander
  * Template URL: https://bootstrapmade.com/bootslander-free-bootstrap-landing-page-template/
  * Updated: Aug 07 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->
</head>

<body class="index-page">

    <header id="header" class="header d-flex align-items-center fixed-top">
        <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

            <a href="#" class="logo d-flex align-items-center">
                <img src="assets/img/Polibatam.png" alt="">
            </a>

            <nav id="navmenu" class="navmenu">
                <ul>
                  <li><a href="#beranda">Beranda</a></li>
                  <li><a href="#tentang">Tentang</a></li>
                  <li><a href="{{ route('login.form') }}">Masuk</a></li>
                  <li class="masuk-mobile"><a href="{{ route('login.form') }}">Masuk</a></li>
                </ul>
                <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
            </nav>

        </div>
    </header>

    <main class="main">

    <section id="beranda" class="hero section dark-background">
        <div class="container">
            <div class="row gy-4 justify-content-between">
                <div class="col-lg-4 order-lg-last hero-img" data-aos="zoom-out" data-aos-delay="100">
                    <img src="assets/img/Polibatam.png" class="img-fluid animated" alt="">
                </div>
                <div class="col-lg-6  d-flex flex-column justify-content-center" data-aos="fade-in">
                    <h1>Permudah pencarian parkir kendaraan Anda dengan <span>PoliSlot</span></h1>
                    <p>Platform validasi data parkir berbasis crowdsourcing untuk sistem pemantauan parkir otomatis</p>

                </div>
            </div>
        </div>

        <svg class="hero-waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28 " preserveAspectRatio="none">

            <defs>
            <path id="wave-path" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z"></path>
            </defs>

            <g class="wave1">
            <use xlink:href="#wave-path" x="50" y="3"></use>
            </g>

            <g class="wave2">
            <use xlink:href="#wave-path" x="50" y="0"></use>
            </g>

            <g class="wave3">
            <use xlink:href="#wave-path" x="50" y="9"></use>
            </g>
        </svg>
    </section>

    <style>
        .partner-logo {
          max-height: 130px;
          object-fit: contain;
          padding: 10px;
        }
    </style>

    <section id="tentang" class="tentang py-5 bg-light">
        <div class="container">
          <!-- Judul -->
          <div class="text-center mb-5">
            <h1 class="fw-bold">Tentang</h1>
            <hr class="w-25 mx-auto border-3">
          </div>

          <!-- Paragraf Tentang -->
          <div class="row g-4 mb-5">
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="150">
              <p class="text-justify">
                <strong>MyInternship</strong> adalah aplikasi manajer magang yang mengelola semua tahapan magang, mulai dari proses pendaftaran, pelaksanaan, hingga penilaian. Per 5 Oktober 2022, MyInternship telah digunakan oleh lebih dari 6000 mahasiswa yang melibatkan 300 dosen pembimbing industri dan 200 dosen pembimbing magang di Politeknik. MyInternship juga merupakan platform komunikasi antara mahasiswa, dosen dan dosen pembimbing.
              </p>
            </div>
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
              <p class="text-justify">
                Perguruan tinggi dapat menugaskan pengawas magang, serta industri juga dapat menugaskan dosen pembimbing untuk setiap mahasiswa di tempat magang mereka. Melalui MyInternship, akan lebih mudah bagi politeknik dan industri untuk memantau dan mengevaluasi magang mahasiswa. Kinerja siswa dapat dilaporkan secara berkala melalui laporan kinerja. Melalui laporan kinerja ini, dosen pembimbing magang dapat memantau kemajuan belajar mahasiswa selama magang.
              </p>
            </div>
          </div>

          <!-- Statistik dan Gambar -->
          <div class="row align-items-center">
            <div class="col-xl-5 mb-4 mb-xl-0 text-center" data-aos="fade-right" data-aos-delay="150">
              <img src="assets/img/goals.png" alt="Goals" class="img-fluid rounded shadow-sm">
            </div>
            <div class="col-xl-7" data-aos="fade-left" data-aos-delay="300">
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="count-box p-4 bg-white rounded shadow-sm text-center">
                    <i class="bi bi-mortarboard display-5 text-primary mb-2"></i>
                    <h2 class="purecounter" data-purecounter-start="0" data-purecounter-end="5074" data-purecounter-duration="1">5074</h2>
                    <p class="mb-0"><strong>Mahasiswa</strong> magang terdaftar</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="count-box p-4 bg-white rounded shadow-sm text-center">
                    <i class="bi bi-building display-5 text-success mb-2"></i>
                    <h2 class="purecounter" data-purecounter-start="0" data-purecounter-end="1301" data-purecounter-duration="1">1301</h2>
                    <p class="mb-0"><strong>Industri</strong> Terlibat</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="count-box p-4 bg-white rounded shadow-sm text-center">
                    <i class="bi bi-people display-5 text-warning mb-2"></i>
                    <h2 class="purecounter" data-purecounter-start="0" data-purecounter-end="298" data-purecounter-duration="1">298</h2>
                    <p class="mb-0"><strong>Dosen</strong> Pembimbing</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="count-box p-4 bg-white rounded shadow-sm text-center">
                    <i class="bi bi-hospital display-5 text-danger mb-2"></i>
                    <h2 class="purecounter" data-purecounter-start="0" data-purecounter-end="7" data-purecounter-duration="1">7</h2>
                    <p class="mb-0"><strong>Universitas</strong> Bekerja Sama</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
    </section>


    <style>
        .section-title img {
          max-width: 100%;
          height: auto;
          margin-bottom: 1rem;
        }

        .judul-fitur img {
          max-width: 100%;
          border-radius: 10px;
          margin: 20px 0;
          box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .fitur-card {
          background: #fff;
          border: none;
          border-radius: 20px;
          padding: 30px;
          transition: all 0.3s ease-in-out;
          box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
          text-align: center;
        }

        .fitur-card:hover {
          transform: translateY(-8px);
          box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .fitur-icon {
          font-size: 40px;
          margin-bottom: 20px;
          color: #0d6efd;
        }

        .fitur-card h3 {
          font-size: 1.2rem;
          font-weight: 600;
          color: #0d6efd;
          margin-bottom: 10px;
        }

        .fitur-card p {
          font-size: 0.95rem;
          color: #555;
        }
    </style>

    <style>
        .card-team {
          border: none;
          border-radius: 20px;
          overflow: hidden;
          transition: all 0.4s ease;
          box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
          background: rgba(255, 255, 255, 0.9);
          backdrop-filter: blur(6px);
        }

        .card-team:hover {
          transform: translateY(-10px);
          box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .card-team img {
          height: 280px;
          object-fit: cover;
          border-bottom: 4px solid #0d6efd;
          transition: transform 0.3s ease;
        }

        .card-team:hover img {
          transform: scale(1.05);
        }

        .card-title {
          font-weight: 700;
          color: #0d6efd;
        }

        .card-text {
          font-size: 0.95rem;
          color: #555;
        }

        .contribution-icon {
          margin-right: 6px;
          color: #0d6efd;
        }

        .section-title-line {
          width: 50px;
          height: 4px;
          background-color: #0d6efd;
          border-radius: 10px;
        }
    </style>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <!-- Preloader -->
  <div id="preloader"></div>
  <!-- Vendor JS Files -->
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <!-- Main JS File -->
  <script src="assets/js/main.js"></script>
</body>
</html>