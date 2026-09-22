<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Content\PortfolioRepository;
use Illuminate\Contracts\View\View;

class PortfolioController extends Controller
{
    public function __construct(private readonly PortfolioRepository $portfolio) {}

    public function index(): View
    {
        return view('pages.portfolio.index', [
            'projects' => $this->portfolio->all(),
        ]);
    }

    public function show(string $slug): View
    {
        $project = $this->portfolio->find($slug);

        abort_if($project === null, 404);

        return view('pages.portfolio.show', [
            'project' => $project,
            'others' => $this->portfolio->others($slug),
        ]);
    }
}
