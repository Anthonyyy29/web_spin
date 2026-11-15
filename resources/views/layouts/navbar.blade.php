<!-- app/Views/layout/navbar.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#">
            <img src="/assets/img/logo-strfm.jpg" class="rounded-2" alt="logo-strfm" width="50" height="50" /> Spin The Radio FM
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="/home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="/listen">Listen</a></li>
                <li class="nav-item"><a class="nav-link" href="/news">News</a></li>
                <li class="nav-item"><a class="nav-link" href="/aboutus">About Us</a></li>

            </ul>
            <div class="position-relative">
                <form class="d-flex ms-3" action="/listen/search" method="GET">
                    <input class="form-control me-2" id="searchInput" type="search" name="query" placeholder="Search Podcast..." autocomplete="off" />
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </form>
                <div id="suggestionBox" class="list-group position-absolute w-100 shadow d-none"></div>
            </div>
            <a href="/login" class="btn btn-outline-light ms-3">
                <i class="fa-solid fa-user me-1"></i> Login
            </a>
        </div>
    </div>
</nav>
<script>
    const BASE_URL = "/";
</script>