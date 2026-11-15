<x-layouts.dashboard :navmenu="$navmenu">

<!--begin::Container-->
<div class="container-fluid">
  <!--begin::Row-->
  <div class="row">

    <!-- Total Segment -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-danger position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_segment }}</h3>
          <p>Total Segment</p>
        </div>
        <i class="bi bi-play-btn small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/podcast" class="small-box-footer text-white text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>

    <!-- Total Podcast -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-primary position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_podcast }}</h3>
          <p>Total Podcast</p>
        </div>
        <i class="bi bi-mic small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/podcast" class="small-box-footer text-white text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>

    <!-- Total News -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-success position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_news }}</h3>
          <p>Total News</p>
        </div>
        <i class="bi bi-newspaper small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/news" class="small-box-footer text-white text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>

    <!-- Total Crew -->
    <div class="col-6 col-md-6 col-xl-3 mb-4">
      <div class="small-box text-bg-warning position-relative p-3 rounded">
        <div class="inner">
          <h3>{{ $total_crew }}</h3>
          <p>Total Crew</p>
        </div>
        <i class="bi bi-people small-box-icon fs-1 position-absolute bottom-0 m-3 "></i>
        <a href="/dashboard/crew" class="small-box-footer text-dark text-decoration-none d-block mt-2">
          More info <i class="bi bi-arrow-right-circle"></i>
        </a>
      </div>
    </div>
  </div>
  <!--end::Row-->
  <div class="row">

    <div class="col-12 col-lg-6 mb-4">
      <div class="card" style="position: relative; left: 0px; top: 0px;">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Chart Crew
          </h3>
          <div class="card-tools">

          </div>
        </div><!-- /.card-header -->
        <div class="card-body">
          <canvas id="crewChart" width="400" height="400"></canvas>
        </div><!-- /.card-body -->
      </div>
    </div>
    <div class="col-12 col-lg-6 mb-4">
      <div class="card" style="position: relative; left: 0px; top: 0px;">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Weekly Podcast
          </h3>
          <div class="card-tools">

          </div>
        </div><!-- /.card-header -->
        <div class="card-body">
          <canvas id="podcastChart" width="400" height="400"></canvas>
        </div><!-- /.card-body -->
      </div>
    </div>
    <div class="col-12 mb-4">
      <div class="card" style="position: relative; left: 0px; top: 0px;">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Weekly News
          </h3>
          <div class="card-tools">

          </div>
        </div><!-- /.card-header -->
        <div class="card-body">
          <canvas id="newsChart" width="400" height="400"></canvas>
        </div><!-- /.card-body -->
      </div>
    </div>
    <div class="col-12 col-lg-6 mb-4">
      <div class="card h-100" style="position: relative; left: 0px; top: 0px;">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Logs
          </h3>
          <div class="card-tools">

          </div>
        </div><!-- /.card-header -->
        <div class="card-body">
          <div class="table-responsive" style="height: 400px; overflow-y: auto;">
            <table class="table table-hover table-responsive">
              <thead>
                <tr>
                  <th scope="col">No</th>
                  <th scope="col">IP Address</th>
                  <th scope="col">Content</th>
                  <th scope="col">Action</th>
                  <th scope="col">Last Action</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($audit_log as $index => $log)
                  <tr>
                    <th scope="row">{{ $index + 1 }}</th>
                    <td>{{ $log['ip_address'] }}</td>
                    <td>{{ $log['content'] }}</td>
                    <td>{{ $log['action'] }}</td>
                    <td>{{ $log['created_at'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div><!-- /.card-body -->
      </div>
    </div>
    <div class="col-12 col-lg-6 mb-4">
      <div class="card h-100" style="position: relative; left: 0px; top: 0px;">
        <div class="card-header">
          <h3 class="card-title">
            <i class="fas fa-chart-pie mr-1"></i>
            Admin Login
          </h3>
          <div class="card-tools">

          </div>
        </div><!-- /.card-header -->
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th scope="col">No</th>
                  <th scope="col">IP Address</th>
                  <th scope="col">Last Login</th>
                  <th scope="col">Device</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($admin_history as $index => $history)
                  <tr>
                    <th scope="row">{{ $index + 1 }}</th>
                    <td>{{ $history['ip_address'] }}</td>
                    <td>{{ $history['last_login'] }}</td>
                    <td>{{ $history['device'] }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div><!-- /.card-body -->
      </div>
    </div>
  </div>

  <!-- /.row (main row) -->
</div>
@include('charts.line', ['source' => $news_chart_data, 'id' => 'newsChart'])
@include('charts.bar', ['source' => $podcast_chart_data, 'id' => 'podcastChart'])
@include('charts.donut', ['source' => $crew_chart_data, 'id' => 'crewChart'])

<!--end::Container-->
</x-layouts.dashboard>