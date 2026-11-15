<?php

namespace App\Http\Controllers;

use App\Models\AbsensiLog;
use App\Models\AdminHistory;
use App\Models\AuditLog;
use App\Models\Content;
use App\Models\Crew;
use App\Models\Episode;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const array DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private const array DAY_COLORS = ['#1abc9c', '#2ecc71', '#3498db', '#9b59b6', '#f1c40f', '#e67e22', '#e74c3c'];

    public function index(Request $request): View
    {
        return match ($request->user()->role) {
            'admin' => $this->admin(),
            'hr' => $this->hr(),
            default => abort(403),
        };
    }

    private function admin(): View
    {
        $crewTotals = Crew::query()->select('role', DB::raw('count(*) as total'))->groupBy('role')->pluck('total', 'role');

        // Riwayat login yang sesinya sudah habis dibuang, seperti di repo lama.
        $expiry = now()->subMinutes((int) config('session.lifetime'));
        AdminHistory::where('last_login', '<', $expiry)->delete();

        return view('admin.index', [
            'navmenu' => 'Dashboard',
            'total_segment' => Content::count(),
            'total_podcast' => Episode::count(),
            'total_news' => News::count(),
            'total_crew' => Crew::count(),
            'crew_chart_data' => [
                'label' => 'Jumlah Crew Per Role',
                'source' => [
                    'labels' => array_keys(Crew::ROLES),
                    'data' => collect(Crew::ROLES)->keys()->map(fn (string $role): int => (int) ($crewTotals[$role] ?? 0))->all(),
                    'backgroundColors' => array_values(Crew::ROLES),
                ],
            ],
            'podcast_chart_data' => [
                'label' => 'Jumlah Podcast',
                'source' => ['labels' => self::DAYS, 'data' => Episode::countsThisWeek(), 'backgroundColors' => self::DAY_COLORS],
            ],
            'news_chart_data' => [
                'label' => 'Jumlah News',
                'source' => ['labels' => self::DAYS, 'data' => News::countsThisWeek()],
            ],
            'admin_history' => AdminHistory::latest('updated_at')->limit(5)->get()->map(fn (AdminHistory $history): array => [
                'ip_address' => $history->ip_address,
                'device' => $history->device,
                'last_login' => $history->last_login->diffForHumans(),
            ]),
            'audit_log' => AuditLog::latest()->limit(10)->get()->map(fn (AuditLog $log): array => [
                'ip_address' => $log->ip_address,
                'content' => $log->content,
                'action' => $log->action,
                'created_at' => $log->created_at->diffForHumans(),
            ]),
        ]);
    }

    private function hr(): View
    {
        $month = fn ($query) => $query->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year);

        return view('admin.absensi.index', [
            'navmenu' => 'Dashboard',
            'tanggal' => now()->format('d F Y'),
            'absensi_hari_ini' => AbsensiLog::withCrew()->withSession()
                ->whereDate('absensi_logs.tanggal', today())
                ->whereNotNull('absensi_logs.session_id')
                ->orderByDesc('absensi_logs.jam_masuk')
                ->get(),
            'tamu_hari_ini' => AbsensiLog::withCrew()
                ->whereDate('absensi_logs.tanggal', today())
                ->whereNull('absensi_logs.session_id')
                ->orderByDesc('absensi_logs.jam_masuk')
                ->get(),
            'chartStatus' => AbsensiLog::query()->tap($month)->whereNotNull('session_id')
                ->select('status', DB::raw('count(id) as total'))->groupBy('status')->get(),
            'chartTrend' => AbsensiLog::query()
                ->whereNotNull('session_id')
                ->where('tanggal', '>=', today()->subDays(7)->toDateString())
                ->select('tanggal', DB::raw('count(id) as total'))->groupBy('tanggal')->orderBy('tanggal')->get(),
            'chartDivisi' => AbsensiLog::withCrew()->tap($month)
                ->reorder()->select('crews.role as divisi', DB::raw('count(absensi_logs.id) as total'))->groupBy('crews.role')->get(),
        ]);
    }
}
