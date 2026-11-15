<!-- Header Start -->
<div class="header">
        <div class="container">
            <nav class="navbar navbar-expand-md bg-dark navbar-dark">
                <a href="#" class="navbar-brand">MENU</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>

                <div class="collapse navbar-collapse justify-content-between" id="navbarCollapse">
                    <div class="navbar-nav m-auto">
                        @foreach ($categoryList as $category)
                        <a href="/news/category/{{ $category }}" class="nav-item nav-link">{{ $category }}</a>
                        @endforeach
                    </div>
                </div>
            </nav>
        </div>
    </div>
    <!-- Header End -->