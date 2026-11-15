<x-layouts.dashboard :navmenu="$navmenu">

<!--begin::Container-->
<div class="container-fluid">
  <!--begin::Row-->
  <div class="row">

    <!-- Total Segment -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-danger position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_lomba_poster }}</h3>
          <p>Total Lomba Poster</p>
        </div>
        <i class="bi bi-image-fill small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/lomba/jenis/poster" class="small-box-footer text-white text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>

    <!-- Total Podcast -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-primary position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_lomba_podcast }}</h3>
          <p>Total Lomba Podcast</p>
        </div>
        <i class="bi bi-mic small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/lomba/jenis/podcast" class="small-box-footer text-white text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>

    <!-- Total News -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-success position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_lomba_konten }}</h3>
          <p>Total Lomba Konten</p>
        </div>
        <i class="bi bi-camera-reels-fill small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/lomba/jenis/konten" class="small-box-footer text-white text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>

    <!-- Total Crew -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-warning position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_lomba_presenter }}</h3>
          <p>Total Lomba Presenter</p>
        </div>
        <i class="bi bi-person-video3 small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/lomba/jenis/presenter" class="small-box-footer text-dark text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>
  </div>

  <!-- /.row (main row) -->
</div>

<!--end::Container-->
</x-layouts.dashboard>